<?php

namespace App\Jobs;

use App\Models\GoogleSheetsIntegration;
use App\Models\GoogleSheetsSyncLog;
use App\Models\Contact;
use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleSheetsSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 1200; // 20 minutes

    /**
     * The Google Sheets integration instance.
     */
    protected GoogleSheetsIntegration $integration;

    /**
     * The sync direction (crm_to_sheets, sheets_to_crm, or bidirectional).
     */
    protected string $direction;

    /**
     * Create a new job instance.
     */
    public function __construct(GoogleSheetsIntegration $integration, string $direction = 'bidirectional')
    {
        $this->integration = $integration;
        $this->direction = $direction;
        $this->onQueue('google-sheets');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if integration is still active
            if (!$this->integration->is_active) {
                Log::info("Google Sheets integration {$this->integration->id} is not active, skipping sync");
                return;
            }

            // Create sync log entry
            $syncLog = GoogleSheetsSyncLog::create([
                'integration_id' => $this->integration->id,
                'sync_direction' => $this->direction,
                'status' => 'running',
                'started_at' => now(),
            ]);

            Log::info("Starting Google Sheets sync for integration {$this->integration->id} in direction: {$this->direction}");

            // Get GoogleSheetsService instance
            $googleSheetsService = app(GoogleSheetsService::class);

            // Set the integration for the service
            $googleSheetsService->setIntegration($this->integration);

            $crmRecordsProcessed = 0;
            $sheetsRecordsProcessed = 0;
            $errors = [];

            // Perform sync based on direction
            switch ($this->direction) {
                case 'crm_to_sheets':
                    $result = $this->syncCrmToSheets($googleSheetsService);
                    $crmRecordsProcessed = $result['processed'];
                    $errors = $result['errors'];
                    break;

                case 'sheets_to_crm':
                    $result = $this->syncSheetsToCrm($googleSheetsService);
                    $sheetsRecordsProcessed = $result['processed'];
                    $errors = $result['errors'];
                    break;

                case 'bidirectional':
                    // First sync CRM to Sheets
                    $crmResult = $this->syncCrmToSheets($googleSheetsService);
                    $crmRecordsProcessed = $crmResult['processed'];
                    $errors = array_merge($errors, $crmResult['errors']);

                    // Then sync Sheets to CRM
                    $sheetsResult = $this->syncSheetsToCrm($googleSheetsService);
                    $sheetsRecordsProcessed = $sheetsResult['processed'];
                    $errors = array_merge($errors, $sheetsResult['errors']);
                    break;

                default:
                    throw new Exception("Invalid sync direction: {$this->direction}");
            }

            // Update sync log with results
            $syncLog->update([
                'status' => empty($errors) ? 'completed' : 'completed_with_errors',
                'crm_records_processed' => $crmRecordsProcessed,
                'sheets_records_processed' => $sheetsRecordsProcessed,
                'error_count' => count($errors),
                'errors' => $errors,
                'completed_at' => now(),
            ]);

            // Update integration last sync time
            $this->integration->update([
                'last_sync_at' => now(),
                'last_sync_status' => empty($errors) ? 'success' : 'success_with_errors',
                'last_error' => empty($errors) ? null : 'Completed with ' . count($errors) . ' errors'
            ]);

            Log::info("Google Sheets sync completed for integration {$this->integration->id}. " . 
                      "CRM records: {$crmRecordsProcessed}, Sheets records: {$sheetsRecordsProcessed}, Errors: " . count($errors));

        } catch (Exception $e) {
            Log::error("GoogleSheetsSyncJob failed for integration {$this->integration->id}: " . $e->getMessage());

            // Update sync log with error
            if (isset($syncLog)) {
                $syncLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            // Update integration error status
            $this->integration->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'error',
                'last_error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Sync CRM data to Google Sheets.
     */
    protected function syncCrmToSheets(GoogleSheetsService $googleSheetsService): array
    {
        $processed = 0;
        $errors = [];

        try {
            // Get contacts that need to be synced
            $query = Contact::where('is_active', true);

            // Apply segment filter if specified
            if ($this->integration->segment_id) {
                $query->whereHas('segments', function ($q) {
                    $q->where('contact_segments.id', $this->integration->segment_id);
                });
            }

            // Get contacts updated since last sync (if this isn't the first sync)
            if ($this->integration->last_sync_at) {
                $query->where('updated_at', '>', $this->integration->last_sync_at);
            }

            $contacts = $query->get();

            Log::info("Found {$contacts->count()} contacts to sync to Google Sheets");

            // Get field mapping
            $fieldMapping = $this->integration->field_mapping;

            // Prepare data for Google Sheets
            $sheetsData = [];
            $headers = array_values($fieldMapping); // Google Sheets column names

            foreach ($contacts as $contact) {
                $row = [];
                foreach ($fieldMapping as $crmField => $sheetsColumn) {
                    $value = $this->getContactFieldValue($contact, $crmField);
                    $row[] = $value;
                }
                $sheetsData[] = $row;
                $processed++;
            }

            // If we have data to sync, update the Google Sheet
            if (!empty($sheetsData)) {
                // Add headers if this is the first sync or sheet is empty
                $sheetData = $googleSheetsService->getSheetData();
                if (empty($sheetData)) {
                    array_unshift($sheetsData, $headers);
                }

                // Update the Google Sheet
                $result = $googleSheetsService->updateSheetData($sheetsData);
                
                if (!$result['success']) {
                    $errors[] = "Failed to update Google Sheets: " . $result['error'];
                }
            }

        } catch (Exception $e) {
            $errors[] = "CRM to Sheets sync error: " . $e->getMessage();
            Log::error("Error syncing CRM to Sheets: " . $e->getMessage());
        }

        return [
            'processed' => $processed,
            'errors' => $errors
        ];
    }

    /**
     * Sync Google Sheets data to CRM.
     */
    protected function syncSheetsToCrm(GoogleSheetsService $googleSheetsService): array
    {
        $processed = 0;
        $errors = [];

        try {
            // Get data from Google Sheets
            $result = $googleSheetsService->getSheetData();
            
            if (!$result['success']) {
                throw new Exception("Failed to get Google Sheets data: " . $result['error']);
            }

            $sheetsData = $result['data'];
            
            if (empty($sheetsData)) {
                Log::info("No data found in Google Sheets");
                return ['processed' => 0, 'errors' => []];
            }

            // First row should be headers
            $headers = array_shift($sheetsData);
            
            // Reverse field mapping (Sheets column -> CRM field)
            $reverseFieldMapping = array_flip($this->integration->field_mapping);

            Log::info("Found " . count($sheetsData) . " rows to sync from Google Sheets to CRM");

            foreach ($sheetsData as $rowIndex => $row) {
                try {
                    // Create associative array from row data
                    $rowData = array_combine($headers, $row);
                    
                    // Map Google Sheets data to CRM fields
                    $contactData = [];
                    foreach ($reverseFieldMapping as $sheetsColumn => $crmField) {
                        if (isset($rowData[$sheetsColumn])) {
                            $contactData[$crmField] = $this->convertSheetValue($rowData[$sheetsColumn], $crmField);
                        }
                    }

                    // Skip if no email (required field)
                    if (empty($contactData['email'])) {
                        $errors[] = "Row " . ($rowIndex + 2) . ": Email is required but missing";
                        continue;
                    }

                    // Check if contact exists
                    $existingContact = Contact::where('email', $contactData['email'])->first();

                    if ($existingContact) {
                        // Update existing contact (only if Google Sheets data is newer)
                        $sheetsUpdatedAt = $this->extractUpdatedAt($rowData);
                        if (!$sheetsUpdatedAt || $sheetsUpdatedAt > $existingContact->updated_at) {
                            $existingContact->update($contactData);
                            $processed++;
                        }
                    } else {
                        // Create new contact
                        Contact::create(array_merge($contactData, [
                            'contact_source' => 'google_sheets',
                            'is_active' => true,
                        ]));
                        $processed++;
                    }

                } catch (Exception $e) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                }
            }

        } catch (Exception $e) {
            $errors[] = "Sheets to CRM sync error: " . $e->getMessage();
            Log::error("Error syncing Sheets to CRM: " . $e->getMessage());
        }

        return [
            'processed' => $processed,
            'errors' => $errors
        ];
    }

    /**
     * Get contact field value with proper formatting.
     */
    protected function getContactFieldValue(Contact $contact, string $field): string
    {
        switch ($field) {
            case 'tags':
                return is_array($contact->tags) ? implode(', ', $contact->tags) : '';
            case 'custom_fields':
                return is_array($contact->custom_fields) ? json_encode($contact->custom_fields) : '';
            case 'is_active':
            case 'is_unsubscribed':
                return $contact->{$field} ? 'Yes' : 'No';
            case 'created_at':
            case 'updated_at':
            case 'last_contacted_at':
                return $contact->{$field} ? $contact->{$field}->format('Y-m-d H:i:s') : '';
            default:
                return (string) ($contact->{$field} ?? '');
        }
    }

    /**
     * Convert Google Sheets value to appropriate CRM format.
     */
    protected function convertSheetValue($value, string $field)
    {
        if (empty($value)) {
            return null;
        }

        switch ($field) {
            case 'is_active':
            case 'is_unsubscribed':
                return in_array(strtolower($value), ['yes', '1', 'true', 'active']);
            case 'tags':
                return array_map('trim', explode(',', $value));
            case 'custom_fields':
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            case 'created_at':
            case 'updated_at':
            case 'last_contacted_at':
                try {
                    return \Carbon\Carbon::parse($value);
                } catch (Exception $e) {
                    return null;
                }
            default:
                return $value;
        }
    }

    /**
     * Extract updated_at timestamp from row data.
     */
    protected function extractUpdatedAt(array $rowData)
    {
        $fieldMapping = $this->integration->field_mapping;
        $updatedAtColumn = $fieldMapping['updated_at'] ?? null;
        
        if (!$updatedAtColumn || !isset($rowData[$updatedAtColumn])) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($rowData[$updatedAtColumn]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("GoogleSheetsSyncJob permanently failed for integration {$this->integration->id}: " . $exception->getMessage());

        // Update integration error status
        $this->integration->update([
            'last_sync_at' => now(),
            'last_sync_status' => 'error',
            'last_error' => $exception->getMessage()
        ]);

        // Create failed sync log entry
        GoogleSheetsSyncLog::create([
            'integration_id' => $this->integration->id,
            'sync_direction' => $this->direction,
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'started_at' => now(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [120, 300, 600]; // 2 minutes, 5 minutes, 10 minutes
    }
}
