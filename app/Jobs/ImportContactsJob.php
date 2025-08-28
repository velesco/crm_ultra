<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ContactSegment;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImportContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 300;

    /**
     * Array of contact data to import.
     */
    protected array $contactsData;

    /**
     * Import options.
     */
    protected array $options;

    /**
     * User ID who initiated the import.
     */
    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $contactsData, array $options = [], ?int $userId = null)
    {
        $this->contactsData = $contactsData;
        $this->options = $options;
        $this->userId = $userId;
        $this->onQueue('contact-imports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting contact import job with '.count($this->contactsData).' contacts');

            $processedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            $errors = [];

            // Get import options
            $skipDuplicates = $this->options['skip_duplicates'] ?? true;
            $updateExisting = $this->options['update_existing'] ?? false;
            $segmentIds = $this->options['segment_ids'] ?? [];
            $defaultSource = $this->options['source'] ?? 'bulk_import';

            foreach ($this->contactsData as $index => $contactData) {
                try {
                    $result = $this->processContactData($contactData, $skipDuplicates, $updateExisting, $defaultSource);

                    switch ($result['status']) {
                        case 'created':
                        case 'updated':
                            $processedCount++;

                            // Add to segments if specified
                            if (! empty($segmentIds) && $result['contact']) {
                                $this->addContactToSegments($result['contact'], $segmentIds);
                            }
                            break;

                        case 'skipped':
                            $skippedCount++;
                            break;

                        case 'error':
                            $errorCount++;
                            $errors[] = [
                                'row' => $index + 1,
                                'data' => $contactData,
                                'error' => $result['error'],
                            ];
                            break;
                    }

                } catch (Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'row' => $index + 1,
                        'data' => $contactData,
                        'error' => $e->getMessage(),
                    ];

                    Log::error('Error processing contact at row '.($index + 1).': '.$e->getMessage());
                }

                // Stop if too many errors
                if ($errorCount > 100) {
                    Log::error('Too many errors in contact import, stopping at row '.($index + 1));
                    break;
                }
            }

            Log::info("Contact import completed. Processed: {$processedCount}, Skipped: {$skippedCount}, Errors: {$errorCount}");

            // Log final results
            if (! empty($errors)) {
                Log::warning('Contact import completed with errors', [
                    'processed' => $processedCount,
                    'skipped' => $skippedCount,
                    'errors' => $errorCount,
                    'error_details' => array_slice($errors, 0, 10), // Log first 10 errors
                ]);
            }

        } catch (Exception $e) {
            Log::error('ImportContactsJob failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Process individual contact data.
     */
    protected function processContactData(array $contactData, bool $skipDuplicates, bool $updateExisting, string $defaultSource): array
    {
        // Validate required fields
        $validator = Validator::make($contactData, [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'error' => 'Invalid email: '.implode(', ', $validator->errors()->all()),
                'contact' => null,
            ];
        }

        $email = $contactData['email'];

        // Check if contact already exists
        $existingContact = Contact::where('email', $email)->first();

        if ($existingContact) {
            if ($skipDuplicates && ! $updateExisting) {
                return [
                    'status' => 'skipped',
                    'error' => 'Duplicate email skipped',
                    'contact' => $existingContact,
                ];
            }

            if ($updateExisting) {
                // Update existing contact
                $updateData = $this->prepareContactData($contactData, $defaultSource);
                $existingContact->update($updateData);

                Log::debug("Updated existing contact: {$email}");

                return [
                    'status' => 'updated',
                    'error' => null,
                    'contact' => $existingContact->fresh(),
                ];
            }
        }

        // Create new contact
        try {
            $createData = $this->prepareContactData($contactData, $defaultSource);
            $createData['created_by'] = $this->userId;

            $contact = Contact::create($createData);

            Log::debug("Created new contact: {$email}");

            return [
                'status' => 'created',
                'error' => null,
                'contact' => $contact,
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => 'Failed to create contact: '.$e->getMessage(),
                'contact' => null,
            ];
        }
    }

    /**
     * Prepare contact data for database insertion.
     */
    protected function prepareContactData(array $rawData, string $defaultSource): array
    {
        $contactData = [];

        // Map standard fields
        $fieldMapping = [
            'email' => 'email',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'name' => 'name',
            'phone' => 'phone',
            'company' => 'company',
            'job_title' => 'job_title',
            'website' => 'website',
            'address' => 'address',
            'city' => 'city',
            'state' => 'state',
            'country' => 'country',
            'zip_code' => 'zip_code',
            'notes' => 'notes',
            'contact_source' => 'contact_source',
        ];

        foreach ($fieldMapping as $inputField => $dbField) {
            if (isset($rawData[$inputField]) && ! empty(trim($rawData[$inputField]))) {
                $contactData[$dbField] = trim($rawData[$inputField]);
            }
        }

        // Handle boolean fields
        if (isset($rawData['is_active'])) {
            $contactData['is_active'] = $this->parseBoolean($rawData['is_active']);
        } else {
            $contactData['is_active'] = true; // Default to active
        }

        if (isset($rawData['is_unsubscribed'])) {
            $contactData['is_unsubscribed'] = $this->parseBoolean($rawData['is_unsubscribed']);
        } else {
            $contactData['is_unsubscribed'] = false; // Default to subscribed
        }

        // Handle tags
        if (isset($rawData['tags'])) {
            if (is_string($rawData['tags'])) {
                $tags = array_map('trim', explode(',', $rawData['tags']));
                $contactData['tags'] = array_filter($tags); // Remove empty tags
            } elseif (is_array($rawData['tags'])) {
                $contactData['tags'] = $rawData['tags'];
            }
        }

        // Handle custom fields
        $customFields = [];
        foreach ($rawData as $key => $value) {
            if (str_starts_with($key, 'custom_')) {
                $customFieldName = substr($key, 7); // Remove 'custom_' prefix
                $customFields[$customFieldName] = $value;
            }
        }
        if (! empty($customFields)) {
            $contactData['custom_fields'] = $customFields;
        }

        // Set default contact source
        if (empty($contactData['contact_source'])) {
            $contactData['contact_source'] = $defaultSource;
        }

        // Generate full name if not provided
        if (empty($contactData['name'])) {
            $nameParts = array_filter([
                $contactData['first_name'] ?? '',
                $contactData['last_name'] ?? '',
            ]);
            $contactData['name'] = implode(' ', $nameParts);
        }

        // Split name if full name provided but first/last not mapped separately
        if (! empty($contactData['name']) && empty($contactData['first_name']) && empty($contactData['last_name'])) {
            $nameParts = explode(' ', $contactData['name'], 2);
            $contactData['first_name'] = $nameParts[0] ?? '';
            $contactData['last_name'] = $nameParts[1] ?? '';
        }

        return $contactData;
    }

    /**
     * Parse boolean values from various formats.
     */
    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'active', 'on']);
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return false;
    }

    /**
     * Add contact to specified segments.
     */
    protected function addContactToSegments(Contact $contact, array $segmentIds): void
    {
        foreach ($segmentIds as $segmentId) {
            $segment = ContactSegment::find($segmentId);

            if (! $segment) {
                Log::warning("Segment {$segmentId} not found when adding contact {$contact->id}");

                continue;
            }

            // Skip if contact is already in segment
            if ($segment->contacts()->where('contact_id', $contact->id)->exists()) {
                continue;
            }

            // Add contact to segment
            $segment->contacts()->attach($contact->id, [
                'added_at' => now(),
                'added_by' => $this->userId,
            ]);

            Log::debug("Added contact {$contact->id} to segment {$segment->name}");
        }

        // Update segment contact counts
        ContactSegment::whereIn('id', $segmentIds)->each(function ($segment) {
            $segment->update([
                'contact_count' => $segment->contacts()->count(),
            ]);
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('ImportContactsJob permanently failed: '.$exception->getMessage(), [
            'contacts_count' => count($this->contactsData),
            'user_id' => $this->userId,
            'options' => $this->options,
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300]; // 1 minute, 5 minutes
    }
}
