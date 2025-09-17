<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\GoogleSheetsIntegration;
use App\Models\GoogleSheetsSyncLog;
use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SheetsImportContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private GoogleSheetsIntegration $integration;
    private array $options;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(GoogleSheetsIntegration $integration, array $options = [])
    {
        $this->integration = $integration;
        $this->options = array_merge([
            'max_rows' => 1000,
            'skip_duplicates' => true,
            'update_existing' => false,
            'validate_emails' => true,
            'auto_tag' => true,
        ], $options);
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleSheetsService $googleSheetsService): void
    {
        $syncLog = GoogleSheetsSyncLog::create([
            'integration_id' => $this->integration->id,
            'action' => 'import_contacts',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            Log::info('Starting Google Sheets contact import', [
                'integration_id' => $this->integration->id,
                'spreadsheet_id' => $this->integration->spreadsheet_id,
                'sheet_name' => $this->integration->sheet_name
            ]);

            // Initialize Google Sheets service
            if (!$this->initializeGoogleSheets($googleSheetsService)) {
                throw new Exception('Failed to initialize Google Sheets service');
            }

            // Get sheet data
            $sheetData = $this->getSheetData($googleSheetsService);
            
            if (empty($sheetData['values'])) {
                $this->completeSync($syncLog, 'success', 'No data found in sheet', 0, 0, 0);
                return;
            }

            // Process the import
            $result = $this->processImport($sheetData, $syncLog);

            // Complete the sync
            $this->completeSync(
                $syncLog, 
                'success', 
                $result['message'], 
                $result['processed'], 
                $result['created'] + $result['updated'], 
                $result['failed']
            );

            Log::info('Google Sheets contact import completed successfully', [
                'integration_id' => $this->integration->id,
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Google Sheets contact import failed', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->completeSync($syncLog, 'failed', 'Import failed: ' . $e->getMessage(), 0, 0, 0);

            // Re-throw to trigger job failure handling
            throw $e;
        }
    }

    /**
     * Initialize Google Sheets service with integration credentials.
     */
    private function initializeGoogleSheets(GoogleSheetsService $googleSheetsService): bool
    {
        try {
            $testResult = $googleSheetsService->testConnection($this->integration);
            return $testResult['success'];
        } catch (Exception $e) {
            Log::error('Failed to initialize Google Sheets', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get data from Google Sheet.
     */
    private function getSheetData(GoogleSheetsService $googleSheetsService): array
    {
        $range = $this->integration->sheet_name . '!' . $this->integration->range;
        
        // For large sheets, limit the range
        if ($this->options['max_rows'] > 0) {
            $maxRow = $this->options['max_rows'] + 1; // +1 for header
            $range = $this->integration->sheet_name . '!A1:Z' . $maxRow;
        }

        $result = $googleSheetsService->previewData(
            $this->integration->spreadsheet_id,
            $this->integration->sheet_name,
            'A1:Z' . ($this->options['max_rows'] + 1),
            $this->integration
        );

        if (!$result['success']) {
            throw new Exception($result['message']);
        }

        return [
            'headers' => $result['headers'] ?? [],
            'values' => $result['data'] ?? [],
        ];
    }

    /**
     * Process the import of contact data.
     */
    private function processImport(array $sheetData, GoogleSheetsSyncLog $syncLog): array
    {
        $headers = $sheetData['headers'];
        $rows = $sheetData['values'];
        $fieldMapping = $this->integration->field_mapping;

        $processed = 0;
        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            $processed++;

            try {
                // Update sync log periodically
                if ($processed % 50 === 0) {
                    $syncLog->update([
                        'records_processed' => $processed,
                        'records_success' => $created + $updated,
                        'records_failed' => $failed,
                    ]);
                }

                $contactData = $this->mapRowToContact($row, $headers, $fieldMapping, $rowIndex);

                if (!$this->validateContactData($contactData)) {
                    $failed++;
                    $errors[] = "Row " . ($rowIndex + 2) . ": Missing required fields (first_name or email)";
                    continue;
                }

                $result = $this->createOrUpdateContact($contactData);
                
                if ($result['action'] === 'created') {
                    $created++;
                } elseif ($result['action'] === 'updated') {
                    $updated++;
                }

            } catch (Exception $e) {
                $failed++;
                $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                
                Log::warning('Failed to import contact from row', [
                    'integration_id' => $this->integration->id,
                    'row_index' => $rowIndex,
                    'error' => $e->getMessage(),
                    'row_data' => $row
                ]);
            }
        }

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
            'message' => "Import completed. Processed: {$processed}, Created: {$created}, Updated: {$updated}, Failed: {$failed}",
        ];
    }

    /**
     * Map spreadsheet row to contact data.
     */
    private function mapRowToContact(array $row, array $headers, array $fieldMapping, int $rowIndex): array
    {
        $contactData = [
            'user_id' => $this->integration->created_by,
            'source' => 'google_sheets',
            'source_metadata' => [
                'integration_id' => $this->integration->id,
                'spreadsheet_id' => $this->integration->spreadsheet_id,
                'sheet_name' => $this->integration->sheet_name,
                'row_number' => $rowIndex + 2, // +2 because of 0-index and header row
                'imported_at' => now()->toISOString(),
            ],
            'created_by' => $this->integration->created_by,
        ];

        // Map fields according to field mapping
        foreach ($fieldMapping as $sheetColumn => $contactField) {
            $columnIndex = array_search($sheetColumn, $headers);

            if ($columnIndex !== false && isset($row[$columnIndex])) {
                $value = trim($row[$columnIndex]);

                if (!empty($value)) {
                    $contactData[$contactField] = $this->formatFieldValue($contactField, $value);
                }
            }
        }

        // Auto-tag if enabled
        if ($this->options['auto_tag']) {
            $tags = $contactData['tags'] ?? [];
            if (is_string($tags)) {
                $tags = json_decode($tags, true) ?: [];
            }
            
            $tags[] = 'google-sheets-import';
            $tags[] = 'sheet-' . str_slug($this->integration->name);
            
            $contactData['tags'] = array_unique($tags);
        }

        // Add custom fields with import metadata
        $customFields = $contactData['custom_fields'] ?? [];
        if (is_string($customFields)) {
            $customFields = json_decode($customFields, true) ?: [];
        }

        $customFields['google_sheets_import'] = [
            'integration_name' => $this->integration->name,
            'imported_at' => now()->toISOString(),
            'source_row' => $rowIndex + 2,
            'import_job_id' => $this->job->getJobId(),
        ];

        $contactData['custom_fields'] = $customFields;

        return $contactData;
    }

    /**
     * Format field value according to field type.
     */
    private function formatFieldValue(string $field, string $value): mixed
    {
        switch ($field) {
            case 'tags':
            case 'custom_fields':
            case 'social_profiles':
                // Try to parse as JSON, otherwise create array
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [$value];

            case 'phone':
                // Clean phone number
                return preg_replace('/[^\d+]/', '', $value);

            case 'email':
                // Validate and clean email
                $email = strtolower(trim($value));
                return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : $value;

            case 'website':
                // Add protocol if missing
                if (!empty($value) && !preg_match('/^https?:\/\//', $value)) {
                    return 'https://' . $value;
                }
                return $value;

            case 'first_name':
            case 'last_name':
            case 'company':
                // Capitalize names properly
                return ucwords(strtolower($value));

            default:
                return $value;
        }
    }

    /**
     * Validate contact data.
     */
    private function validateContactData(array $contactData): bool
    {
        // Must have either first_name or email
        if (empty($contactData['first_name']) && empty($contactData['email'])) {
            return false;
        }

        // Validate email format if provided
        if (!empty($contactData['email']) && !filter_var($contactData['email'], FILTER_VALIDATE_EMAIL)) {
            if ($this->options['validate_emails']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create or update contact based on options.
     */
    private function createOrUpdateContact(array $contactData): array
    {
        $existingContact = null;

        // Try to find existing contact
        if (!empty($contactData['email'])) {
            $existingContact = Contact::where('email', $contactData['email'])
                ->where('user_id', $this->integration->created_by)
                ->first();
        }

        // If no email match, try phone
        if (!$existingContact && !empty($contactData['phone'])) {
            $existingContact = Contact::where('phone', $contactData['phone'])
                ->where('user_id', $this->integration->created_by)
                ->first();
        }

        if ($existingContact) {
            if ($this->options['skip_duplicates'] && !$this->options['update_existing']) {
                return ['action' => 'skipped', 'contact_id' => $existingContact->id];
            }

            if ($this->options['update_existing']) {
                // Update existing contact
                $updateData = array_filter($contactData, function ($value, $key) use ($existingContact) {
                    // Only update fields that are empty or explicitly allowed to be overwritten
                    return !empty($value) && (empty($existingContact->{$key}) || $this->shouldOverwriteField($key));
                }, ARRAY_FILTER_USE_BOTH);

                // Merge tags and custom_fields instead of overwriting
                if (isset($updateData['tags'])) {
                    $existingTags = $existingContact->tags ?? [];
                    $updateData['tags'] = array_unique(array_merge($existingTags, $updateData['tags']));
                }

                if (isset($updateData['custom_fields'])) {
                    $existingCustomFields = $existingContact->custom_fields ?? [];
                    $updateData['custom_fields'] = array_merge($existingCustomFields, $updateData['custom_fields']);
                }

                $existingContact->update($updateData);
                return ['action' => 'updated', 'contact_id' => $existingContact->id];
            }

            return ['action' => 'skipped', 'contact_id' => $existingContact->id];
        }

        // Create new contact
        $contact = Contact::create($contactData);
        return ['action' => 'created', 'contact_id' => $contact->id];
    }

    /**
     * Check if field should be overwritten during update.
     */
    private function shouldOverwriteField(string $field): bool
    {
        $overwriteFields = [
            'tags',
            'custom_fields',
            'source_metadata',
            'notes',
        ];

        return in_array($field, $overwriteFields);
    }

    /**
     * Complete the sync log.
     */
    private function completeSync(
        GoogleSheetsSyncLog $syncLog,
        string $status,
        string $message,
        int $processed,
        int $success,
        int $failed
    ): void {
        $syncLog->update([
            'status' => $status,
            'message' => $message,
            'records_processed' => $processed,
            'records_success' => $success,
            'records_failed' => $failed,
            'completed_at' => now(),
        ]);

        // Update integration's last sync time
        $this->integration->update([
            'last_sync_at' => now(),
            'sync_status' => $status === 'success' ? 'active' : 'error',
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 180, 300]; // Retry after 1min, 3min, 5min
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('SheetsImportContactsJob failed permanently', [
            'integration_id' => $this->integration->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Update integration status
        $this->integration->update([
            'sync_status' => 'error',
            'last_sync_at' => now(),
        ]);

        // Create a failed sync log if one doesn't exist
        GoogleSheetsSyncLog::updateOrCreate(
            [
                'integration_id' => $this->integration->id,
                'action' => 'import_contacts',
                'status' => 'running',
            ],
            [
                'status' => 'failed',
                'message' => 'Job failed permanently: ' . $exception->getMessage(),
                'completed_at' => now(),
            ]
        );
    }
}
