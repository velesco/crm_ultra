<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ContactSegment;
use App\Models\DataImport;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessDataImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 1800; // 30 minutes for large imports

    /**
     * The data import instance.
     */
    protected DataImport $dataImport;

    /**
     * Create a new job instance.
     */
    public function __construct(DataImport $dataImport)
    {
        $this->dataImport = $dataImport;
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to processing
            $this->dataImport->update([
                'status' => 'processing',
                'started_at' => now(),
                'progress' => 0,
            ]);

            Log::info("Starting data import processing for import ID: {$this->dataImport->id}");

            // Check if file exists
            if (! Storage::disk('local')->exists($this->dataImport->file_path)) {
                throw new Exception("Import file not found: {$this->dataImport->file_path}");
            }

            // Get file path
            $filePath = Storage::disk('local')->path($this->dataImport->file_path);

            // Determine file type and process accordingly
            $fileExtension = pathinfo($this->dataImport->original_filename, PATHINFO_EXTENSION);

            if (in_array(strtolower($fileExtension), ['csv', 'txt'])) {
                $this->processCsvFile($filePath);
            } elseif (in_array(strtolower($fileExtension), ['xlsx', 'xls'])) {
                $this->processExcelFile($filePath);
            } else {
                throw new Exception("Unsupported file format: {$fileExtension}");
            }

            // Complete the import
            $this->dataImport->update([
                'status' => 'completed',
                'completed_at' => now(),
                'progress' => 100,
            ]);

            // Assign contacts to segments if specified
            if ($this->dataImport->auto_assign_segments) {
                $this->assignContactsToSegments();
            }

            // Clean up file after successful processing if configured
            if (config('crm.cleanup_import_files', true)) {
                Storage::disk('local')->delete($this->dataImport->file_path);
            }

            Log::info("Data import {$this->dataImport->id} completed successfully. Processed {$this->dataImport->processed_records} records.");

        } catch (Exception $e) {
            Log::error("ProcessDataImportJob failed for import {$this->dataImport->id}: ".$e->getMessage());

            $this->dataImport->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'failed_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Process CSV file.
     */
    protected function processCsvFile(string $filePath): void
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw new Exception('Cannot open CSV file for reading');
        }

        $headers = [];
        $rowIndex = 0;
        $processedCount = 0;
        $errorCount = 0;
        $errors = [];

        // Get field mapping
        $fieldMapping = $this->dataImport->field_mapping ?? [];

        // Get import settings
        $settings = $this->dataImport->import_settings ?? [];
        $skipDuplicates = $settings['skip_duplicates'] ?? true;
        $updateExisting = $settings['update_existing'] ?? false;

        while (($row = fgetcsv($handle)) !== false) {
            $rowIndex++;

            // First row contains headers
            if ($rowIndex === 1) {
                $headers = $row;

                continue;
            }

            try {
                // Create associative array from row data
                $rowData = array_combine($headers, $row);

                // Map fields according to mapping configuration
                $contactData = $this->mapFieldsToContact($rowData, $fieldMapping);

                // Validate required fields
                if (empty($contactData['email'])) {
                    throw new Exception("Email is required but missing in row {$rowIndex}");
                }

                // Check for duplicates
                $existingContact = Contact::where('email', $contactData['email'])->first();

                if ($existingContact) {
                    if ($skipDuplicates && ! $updateExisting) {
                        Log::debug("Skipping duplicate contact: {$contactData['email']} at row {$rowIndex}");

                        continue;
                    }

                    if ($updateExisting) {
                        $existingContact->update($contactData);
                        $processedCount++;
                        Log::debug("Updated existing contact: {$contactData['email']}");
                    }
                } else {
                    // Create new contact
                    $contact = Contact::create(array_merge($contactData, [
                        'contact_source' => 'import',
                        'import_id' => $this->dataImport->id,
                        'created_by' => $this->dataImport->user_id,
                    ]));

                    $processedCount++;
                    Log::debug("Created new contact: {$contactData['email']}");
                }

            } catch (Exception $e) {
                $errorCount++;
                $errors[] = [
                    'row' => $rowIndex,
                    'error' => $e->getMessage(),
                    'data' => $rowData ?? [],
                ];

                Log::warning("Error processing row {$rowIndex}: ".$e->getMessage());

                // Stop if too many errors
                if ($errorCount > 100) {
                    throw new Exception('Too many errors encountered during import (>100). Stopping processing.');
                }
            }

            // Update progress every 100 records
            if ($processedCount % 100 === 0) {
                $progress = min(90, ($processedCount / $this->dataImport->total_records) * 90);
                $this->dataImport->update([
                    'progress' => $progress,
                    'processed_records' => $processedCount,
                    'error_count' => $errorCount,
                ]);
            }
        }

        fclose($handle);

        // Update final counts
        $this->dataImport->update([
            'processed_records' => $processedCount,
            'error_count' => $errorCount,
            'errors' => $errors,
            'progress' => 95,
        ]);
    }

    /**
     * Process Excel file.
     */
    protected function processExcelFile(string $filePath): void
    {
        // Use Laravel Excel to process Excel files
        $data = Excel::toArray([], $filePath);

        if (empty($data) || empty($data[0])) {
            throw new Exception('Excel file is empty or cannot be read');
        }

        $worksheet = $data[0]; // First worksheet
        $headers = array_shift($worksheet); // First row as headers

        $processedCount = 0;
        $errorCount = 0;
        $errors = [];

        // Get field mapping and settings
        $fieldMapping = $this->dataImport->field_mapping ?? [];
        $settings = $this->dataImport->import_settings ?? [];
        $skipDuplicates = $settings['skip_duplicates'] ?? true;
        $updateExisting = $settings['update_existing'] ?? false;

        foreach ($worksheet as $rowIndex => $row) {
            try {
                // Create associative array from row data
                $rowData = array_combine($headers, $row);

                // Map fields according to mapping configuration
                $contactData = $this->mapFieldsToContact($rowData, $fieldMapping);

                // Validate required fields
                if (empty($contactData['email'])) {
                    throw new Exception('Email is required but missing in row '.($rowIndex + 2));
                }

                // Check for duplicates
                $existingContact = Contact::where('email', $contactData['email'])->first();

                if ($existingContact) {
                    if ($skipDuplicates && ! $updateExisting) {
                        continue;
                    }

                    if ($updateExisting) {
                        $existingContact->update($contactData);
                        $processedCount++;
                    }
                } else {
                    // Create new contact
                    Contact::create(array_merge($contactData, [
                        'contact_source' => 'import',
                        'import_id' => $this->dataImport->id,
                        'created_by' => $this->dataImport->user_id,
                    ]));

                    $processedCount++;
                }

            } catch (Exception $e) {
                $errorCount++;
                $errors[] = [
                    'row' => $rowIndex + 2,
                    'error' => $e->getMessage(),
                    'data' => $rowData ?? [],
                ];

                // Stop if too many errors
                if ($errorCount > 100) {
                    throw new Exception('Too many errors encountered during import (>100). Stopping processing.');
                }
            }

            // Update progress every 50 records
            if ($processedCount % 50 === 0) {
                $progress = min(90, ($processedCount / $this->dataImport->total_records) * 90);
                $this->dataImport->update([
                    'progress' => $progress,
                    'processed_records' => $processedCount,
                    'error_count' => $errorCount,
                ]);
            }
        }

        // Update final counts
        $this->dataImport->update([
            'processed_records' => $processedCount,
            'error_count' => $errorCount,
            'errors' => $errors,
            'progress' => 95,
        ]);
    }

    /**
     * Map CSV/Excel fields to Contact model fields.
     */
    protected function mapFieldsToContact(array $rowData, array $fieldMapping): array
    {
        $contactData = [];

        foreach ($fieldMapping as $csvField => $contactField) {
            if (isset($rowData[$csvField]) && ! empty(trim($rowData[$csvField]))) {
                $value = trim($rowData[$csvField]);

                // Special handling for different field types
                switch ($contactField) {
                    case 'is_active':
                    case 'is_unsubscribed':
                        $contactData[$contactField] = in_array(strtolower($value), ['1', 'true', 'yes', 'active']);
                        break;
                    case 'tags':
                        // Handle comma-separated tags
                        $contactData[$contactField] = array_map('trim', explode(',', $value));
                        break;
                    case 'custom_fields':
                        // Handle JSON custom fields
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            $contactData[$contactField] = is_array($decoded) ? $decoded : [$csvField => $value];
                        } else {
                            $contactData[$contactField] = [$csvField => $value];
                        }
                        break;
                    default:
                        $contactData[$contactField] = $value;
                        break;
                }
            }
        }

        // Ensure we have required fields with defaults
        $contactData['is_active'] = $contactData['is_active'] ?? true;
        $contactData['is_unsubscribed'] = $contactData['is_unsubscribed'] ?? false;

        // Split name if full name provided and first/last not mapped separately
        if (! empty($contactData['name']) && empty($contactData['first_name']) && empty($contactData['last_name'])) {
            $nameParts = explode(' ', $contactData['name'], 2);
            $contactData['first_name'] = $nameParts[0] ?? '';
            $contactData['last_name'] = $nameParts[1] ?? '';
        }

        // Generate full name if not provided
        if (empty($contactData['name']) && (! empty($contactData['first_name']) || ! empty($contactData['last_name']))) {
            $contactData['name'] = trim(($contactData['first_name'] ?? '').' '.($contactData['last_name'] ?? ''));
        }

        return $contactData;
    }

    /**
     * Assign imported contacts to segments.
     */
    protected function assignContactsToSegments(): void
    {
        if (empty($this->dataImport->auto_assign_segments)) {
            return;
        }

        $segmentIds = is_array($this->dataImport->auto_assign_segments)
            ? $this->dataImport->auto_assign_segments
            : [$this->dataImport->auto_assign_segments];

        // Get all contacts from this import
        $contacts = Contact::where('import_id', $this->dataImport->id)->get();

        foreach ($segmentIds as $segmentId) {
            $segment = ContactSegment::find($segmentId);
            if (! $segment) {
                continue;
            }

            foreach ($contacts as $contact) {
                // Add contact to segment if not already a member
                if (! $segment->contacts()->where('contact_id', $contact->id)->exists()) {
                    $segment->contacts()->attach($contact->id, [
                        'added_at' => now(),
                        'added_by' => $this->dataImport->user_id,
                    ]);
                }
            }

            // Update segment contact count
            $segment->update([
                'contact_count' => $segment->contacts()->count(),
            ]);
        }

        Log::info("Assigned {$contacts->count()} imported contacts to ".count($segmentIds).' segments');
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("ProcessDataImportJob permanently failed for import {$this->dataImport->id}: ".$exception->getMessage());

        $this->dataImport->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'failed_at' => now(),
        ]);
    }
}
