<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\GoogleSheetsIntegration;
use App\Models\GoogleSheetsSyncLog;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected $client;

    protected $service;

    public function __construct()
    {
        $this->client = new Google_Client;
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate($authCode)
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

            if (isset($accessToken['error'])) {
                return [
                    'success' => false,
                    'message' => 'Authentication failed: '.$accessToken['error_description'],
                ];
            }

            return [
                'success' => true,
                'access_token' => $accessToken['access_token'],
                'refresh_token' => $accessToken['refresh_token'] ?? null,
                'expires_at' => Carbon::now()->addSeconds($accessToken['expires_in']),
            ];

        } catch (\Exception $e) {
            Log::error('Google Sheets authentication error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Authentication error: '.$e->getMessage(),
            ];
        }
    }

    public function createIntegration(array $data)
    {
        return GoogleSheetsIntegration::create([
            'name' => $data['name'],
            'spreadsheet_id' => $data['spreadsheet_id'],
            'sheet_name' => $data['sheet_name'] ?? 'Sheet1',
            'range' => $data['range'] ?? 'A:Z',
            'access_token' => encrypt($data['access_token']),
            'refresh_token' => encrypt($data['refresh_token'] ?? ''),
            'sync_direction' => $data['sync_direction'] ?? 'import',
            'auto_sync' => $data['auto_sync'] ?? false,
            'sync_frequency' => $data['sync_frequency'] ?? 'daily',
            'field_mapping' => $data['field_mapping'] ?? [],
            'settings' => $data['settings'] ?? [],
            'created_by' => auth()->id(),
        ]);
    }

    public function testConnection(GoogleSheetsIntegration $integration)
    {
        try {
            $this->setClientCredentials($integration);
            $this->service = new Google_Service_Sheets($this->client);

            // Try to get spreadsheet metadata
            $spreadsheet = $this->service->spreadsheets->get($integration->spreadsheet_id);

            return [
                'success' => true,
                'message' => 'Connection successful',
                'spreadsheet_title' => $spreadsheet->getProperties()->getTitle(),
                'sheet_count' => count($spreadsheet->getSheets()),
            ];

        } catch (\Exception $e) {
            Log::error('Google Sheets test connection error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ];
        }
    }

    public function sync(GoogleSheetsIntegration $integration)
    {
        $syncLog = GoogleSheetsSyncLog::create([
            'integration_id' => $integration->id,
            'action' => 'sync',
            'status' => 'success',
            'started_at' => now(),
        ]);

        try {
            $this->setClientCredentials($integration);
            $this->service = new Google_Service_Sheets($this->client);

            $result = match ($integration->sync_direction) {
                'import' => $this->importFromSheet($integration),
                'export' => $this->exportToSheet($integration),
                'bidirectional' => $this->bidirectionalSync($integration),
                default => ['success' => false, 'message' => 'Invalid sync direction']
            };

            $syncLog->update([
                'status' => $result['success'] ? 'success' : 'failed',
                'records_processed' => $result['processed'] ?? 0,
                'records_success' => $result['success_count'] ?? 0,
                'records_failed' => $result['failed_count'] ?? 0,
                'message' => $result['message'] ?? null,
                'error_log' => $result['errors'] ?? null,
                'completed_at' => now(),
            ]);

            $integration->update(['last_sync_at' => now()]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Google Sheets sync error: '.$e->getMessage());

            $syncLog->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Sync failed: '.$e->getMessage(),
            ];
        }
    }

    protected function importFromSheet(GoogleSheetsIntegration $integration)
    {
        try {
            $range = $integration->sheet_name.'!'.$integration->range;
            $response = $this->service->spreadsheets_values->get($integration->spreadsheet_id, $range);
            $values = $response->getValues();

            if (empty($values)) {
                return [
                    'success' => true,
                    'message' => 'No data found in sheet',
                    'processed' => 0,
                    'success_count' => 0,
                    'failed_count' => 0,
                ];
            }

            $headers = array_shift($values); // First row as headers
            $fieldMapping = $integration->field_mapping;

            $processed = 0;
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($values as $rowIndex => $row) {
                $processed++;

                try {
                    $contactData = $this->mapRowToContact($row, $headers, $fieldMapping);

                    if (empty($contactData['first_name']) && empty($contactData['email'])) {
                        $failedCount++;
                        $errors[] = "Row {$rowIndex}: Missing required fields (first_name or email)";

                        continue;
                    }

                    // Check if contact exists
                    $existingContact = Contact::where('email', $contactData['email'])
                        ->orWhere(function ($q) use ($contactData) {
                            if (! empty($contactData['phone'])) {
                                $q->where('phone', $contactData['phone']);
                            }
                        })
                        ->first();

                    if ($existingContact) {
                        // Update existing contact
                        $existingContact->update($contactData);
                    } else {
                        // Create new contact
                        $contactData['source'] = 'google_sheets';
                        $contactData['created_by'] = $integration->created_by;
                        Contact::create($contactData);
                    }

                    $successCount++;

                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Row {$rowIndex}: ".$e->getMessage();
                }
            }

            return [
                'success' => true,
                'message' => "Import completed. Processed: {$processed}, Success: {$successCount}, Failed: {$failedCount}",
                'processed' => $processed,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
            ];
        }
    }

    protected function exportToSheet(GoogleSheetsIntegration $integration)
    {
        try {
            $fieldMapping = $integration->field_mapping;
            $contacts = Contact::where('created_by', $integration->created_by)
                ->orWhere('assigned_to', $integration->created_by)
                ->get();

            if ($contacts->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No contacts found to export',
                    'processed' => 0,
                    'success_count' => 0,
                    'failed_count' => 0,
                ];
            }

            // Prepare data for export
            $headers = array_keys($fieldMapping);
            $values = [$headers]; // Start with headers

            foreach ($contacts as $contact) {
                $row = [];
                foreach ($fieldMapping as $sheetColumn => $contactField) {
                    $row[] = $this->getContactFieldValue($contact, $contactField);
                }
                $values[] = $row;
            }

            // Clear existing data and write new data
            $range = $integration->sheet_name.'!'.$integration->range;

            // Clear the range first
            $clear = new \Google_Service_Sheets_ClearValuesRequest;
            $this->service->spreadsheets_values->clear($integration->spreadsheet_id, $range, $clear);

            // Write new data
            $body = new Google_Service_Sheets_ValueRange([
                'values' => $values,
            ]);

            $params = [
                'valueInputOption' => 'RAW',
            ];

            $this->service->spreadsheets_values->update(
                $integration->spreadsheet_id,
                $range,
                $body,
                $params
            );

            return [
                'success' => true,
                'message' => 'Export completed successfully',
                'processed' => count($contacts),
                'success_count' => count($contacts),
                'failed_count' => 0,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Export failed: '.$e->getMessage(),
            ];
        }
    }

    protected function bidirectionalSync(GoogleSheetsIntegration $integration)
    {
        // First import from sheet
        $importResult = $this->importFromSheet($integration);

        if (! $importResult['success']) {
            return $importResult;
        }

        // Then export to sheet
        $exportResult = $this->exportToSheet($integration);

        return [
            'success' => $importResult['success'] && $exportResult['success'],
            'message' => 'Bidirectional sync completed. '.$importResult['message'].' '.$exportResult['message'],
            'processed' => $importResult['processed'] + $exportResult['processed'],
            'success_count' => $importResult['success_count'] + $exportResult['success_count'],
            'failed_count' => $importResult['failed_count'] + $exportResult['failed_count'],
            'errors' => array_merge($importResult['errors'] ?? [], $exportResult['errors'] ?? []),
        ];
    }

    protected function mapRowToContact(array $row, array $headers, array $fieldMapping)
    {
        $contactData = [];

        foreach ($fieldMapping as $sheetColumn => $contactField) {
            $columnIndex = array_search($sheetColumn, $headers);

            if ($columnIndex !== false && isset($row[$columnIndex])) {
                $value = trim($row[$columnIndex]);

                if (! empty($value)) {
                    if ($contactField === 'tags' || $contactField === 'custom_fields') {
                        $contactData[$contactField] = is_string($value) ? json_decode($value, true) : $value;
                    } else {
                        $contactData[$contactField] = $value;
                    }
                }
            }
        }

        return $contactData;
    }

    protected function getContactFieldValue(Contact $contact, string $field)
    {
        switch ($field) {
            case 'full_name':
                return $contact->full_name;
            case 'tags':
                return json_encode($contact->tags ?? []);
            case 'custom_fields':
                return json_encode($contact->custom_fields ?? []);
            default:
                return $contact->getAttribute($field) ?? '';
        }
    }

    protected function setClientCredentials(GoogleSheetsIntegration $integration)
    {
        $accessToken = [
            'access_token' => decrypt($integration->access_token),
            'refresh_token' => decrypt($integration->refresh_token),
            'expires_in' => 3600,
            'created' => time(),
        ];

        $this->client->setAccessToken($accessToken);

        // Refresh token if needed
        if ($this->client->isAccessTokenExpired()) {
            $newAccessToken = $this->client->fetchAccessTokenWithRefreshToken(decrypt($integration->refresh_token));

            if (isset($newAccessToken['access_token'])) {
                $integration->update([
                    'access_token' => encrypt($newAccessToken['access_token']),
                ]);
            }
        }
    }

    public function getSpreadsheetInfo($spreadsheetId, GoogleSheetsIntegration $integration)
    {
        try {
            $this->setClientCredentials($integration);
            $this->service = new Google_Service_Sheets($this->client);

            $spreadsheet = $this->service->spreadsheets->get($spreadsheetId);
            $sheets = [];

            foreach ($spreadsheet->getSheets() as $sheet) {
                $sheets[] = [
                    'title' => $sheet->getProperties()->getTitle(),
                    'sheet_id' => $sheet->getProperties()->getSheetId(),
                    'grid_properties' => [
                        'rows' => $sheet->getProperties()->getGridProperties()->getRowCount(),
                        'columns' => $sheet->getProperties()->getGridProperties()->getColumnCount(),
                    ],
                ];
            }

            return [
                'success' => true,
                'title' => $spreadsheet->getProperties()->getTitle(),
                'spreadsheet_id' => $spreadsheet->getSpreadsheetId(),
                'sheets' => $sheets,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get spreadsheet info: '.$e->getMessage(),
            ];
        }
    }

    public function getSheetHeaders($spreadsheetId, $sheetName, GoogleSheetsIntegration $integration)
    {
        try {
            $this->setClientCredentials($integration);
            $this->service = new Google_Service_Sheets($this->client);

            $range = $sheetName.'!1:1'; // First row only
            $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            if (empty($values)) {
                return [
                    'success' => false,
                    'message' => 'No headers found in the sheet',
                ];
            }

            return [
                'success' => true,
                'headers' => $values[0],
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get sheet headers: '.$e->getMessage(),
            ];
        }
    }

    public function previewData($spreadsheetId, $sheetName, $range, GoogleSheetsIntegration $integration)
    {
        try {
            $this->setClientCredentials($integration);
            $this->service = new Google_Service_Sheets($this->client);

            $fullRange = $sheetName.'!'.$range;
            $response = $this->service->spreadsheets_values->get($spreadsheetId, $fullRange);
            $values = $response->getValues();

            if (empty($values)) {
                return [
                    'success' => true,
                    'message' => 'No data found in the specified range',
                    'headers' => [],
                    'data' => [],
                ];
            }

            $headers = array_shift($values); // First row as headers
            $previewData = array_slice($values, 0, 5); // First 5 rows for preview

            return [
                'success' => true,
                'headers' => $headers,
                'data' => $previewData,
                'total_rows' => count($values) + 1, // +1 for headers
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to preview data: '.$e->getMessage(),
            ];
        }
    }

    public function scheduleAutoSync()
    {
        $integrations = GoogleSheetsIntegration::active()->autoSync()->get();

        foreach ($integrations as $integration) {
            if ($integration->shouldSync()) {
                // Queue the sync job
                \App\Jobs\GoogleSheetsSyncJob::dispatch($integration);
            }
        }

        return [
            'success' => true,
            'message' => 'Auto sync scheduled for '.count($integrations).' integrations',
        ];
    }
}
