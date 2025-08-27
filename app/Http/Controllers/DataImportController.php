<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactSegment;
use App\Models\DataImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use League\Csv\Reader;
use League\Csv\Statement;

class DataImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display import history and status
     */
    public function index(Request $request)
    {
        $query = DataImport::with(['user'])
            ->latest('created_at');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $imports = $query->paginate(20);

        // Statistics
        $stats = [
            'total_imports' => DataImport::count(),
            'successful_imports' => DataImport::where('status', 'completed')->count(),
            'failed_imports' => DataImport::where('status', 'failed')->count(),
            'pending_imports' => DataImport::where('status', 'pending')->count(),
            'processing_imports' => DataImport::where('status', 'processing')->count(),
            'total_records_imported' => DataImport::where('status', 'completed')->sum('imported_count'),
            'imports_today' => DataImport::whereDate('created_at', today())->count(),
        ];

        return view('data.imports.index', compact('imports', 'stats'));
    }

    /**
     * Show contact import form
     */
    public function createContacts()
    {
        $segments = ContactSegment::where('type', 'static')->get();

        return view('data.imports.contacts', compact('segments'));
    }

    /**
     * Handle contact file upload and validation
     */
    public function uploadContacts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
            'has_headers' => 'boolean',
            'segment_id' => 'nullable|exists:contact_segments,id',
            'create_segment' => 'nullable|string|max:255',
            'duplicate_handling' => 'required|in:skip,update,duplicate',
            'import_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Store uploaded file
            $file = $request->file('import_file');
            $filename = 'contacts_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('imports', $filename);

            // Analyze file to detect columns and preview data
            $analysis = $this->analyzeContactFile($path, $request->boolean('has_headers', true));

            if (!$analysis['success']) {
                Storage::delete($path);
                return back()->withErrors(['import_file' => $analysis['error']]);
            }

            // Store import record
            $import = DataImport::create([
                'user_id' => Auth::id(),
                'name' => $request->import_name ?: 'Contact Import ' . now()->format('Y-m-d H:i'),
                'type' => 'contacts',
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'status' => 'analyzing',
                'settings' => [
                    'has_headers' => $request->boolean('has_headers', true),
                    'segment_id' => $request->segment_id,
                    'create_segment' => $request->create_segment,
                    'duplicate_handling' => $request->duplicate_handling,
                ],
                'total_rows' => $analysis['total_rows'],
                'preview_data' => $analysis['preview_data'],
                'detected_columns' => $analysis['detected_columns'],
            ]);

            return redirect()->route('data.imports.map', $import)
                ->with('success', 'File uploaded successfully. Please map the columns before importing.');

        } catch (\Exception $e) {
            if (isset($path)) {
                Storage::delete($path);
            }
            return back()->withErrors(['import_file' => 'Failed to process file: ' . $e->getMessage()]);
        }
    }

    /**
     * Show column mapping interface
     */
    public function mapColumns(DataImport $import)
    {
        if ($import->status !== 'analyzing') {
            return redirect()->route('data.imports.show', $import)
                ->withErrors(['error' => 'Import is not in analyzable state.']);
        }

        // Available contact fields for mapping
        $availableFields = [
            '' => '-- Skip this column --',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'whatsapp_number' => 'WhatsApp Number',
            'company' => 'Company',
            'position' => 'Position/Job Title',
            'address' => 'Address',
            'city' => 'City',
            'state' => 'State/Province',
            'country' => 'Country',
            'postal_code' => 'Postal/ZIP Code',
            'website' => 'Website',
            'source' => 'Lead Source',
            'notes' => 'Notes',
            'tags' => 'Tags (comma separated)',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'status' => 'Status',
        ];

        return view('data.imports.map', compact('import', 'availableFields'));
    }

    /**
     * Save column mapping and start import
     */
    public function saveMapping(Request $request, DataImport $import)
    {
        if ($import->status !== 'analyzing') {
            return redirect()->route('data.imports.show', $import)
                ->withErrors(['error' => 'Import is not in analyzable state.']);
        }

        $validator = Validator::make($request->all(), [
            'field_mapping' => 'required|array',
            'field_mapping.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate that at least email or phone is mapped
        $mapping = $request->field_mapping;
        $hasEmail = in_array('email', $mapping);
        $hasPhone = in_array('phone', $mapping);

        if (!$hasEmail && !$hasPhone) {
            return back()->withErrors(['field_mapping' => 'At least Email or Phone field must be mapped.']);
        }

        try {
            // Save mapping and start import
            $import->update([
                'field_mapping' => $mapping,
                'status' => 'pending',
            ]);

            // Dispatch import job (for now, process synchronously)
            $this->processImport($import);

            return redirect()->route('data.imports.show', $import)
                ->with('success', 'Column mapping saved and import started.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to start import: ' . $e->getMessage()]);
        }
    }

    /**
     * Display specific import details
     */
    public function show(DataImport $import)
    {
        $import->load(['user']);

        // Get import statistics
        $stats = [];
        if ($import->status === 'completed') {
            $stats = [
                'imported_contacts' => $import->imported_count ?? 0,
                'skipped_contacts' => $import->skipped_count ?? 0,
                'failed_contacts' => $import->failed_count ?? 0,
                'updated_contacts' => $import->updated_count ?? 0,
                'processing_time' => $import->completed_at ?
                    $import->created_at->diffForHumans($import->completed_at, true) : null,
            ];
        }

        // Get error details if any
        $errors = $import->errors ?? [];

        return view('data.imports.show', compact('import', 'stats', 'errors'));
    }

    /**
     * Download import template
     */
    public function downloadTemplate($type = 'contacts')
    {
        switch ($type) {
            case 'contacts':
                return $this->downloadContactTemplate();
            default:
                return back()->withErrors(['error' => 'Invalid template type']);
        }
    }

    /**
     * Download contact import template
     */
    private function downloadContactTemplate()
    {
        $headers = [
            'first_name',
            'last_name',
            'email',
            'phone',
            'whatsapp_number',
            'company',
            'position',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
            'website',
            'source',
            'notes',
            'tags',
            'date_of_birth',
            'gender',
            'status'
        ];

        $sampleData = [
            [
                'John',
                'Doe',
                'john.doe@example.com',
                '+1234567890',
                '+1234567890',
                'Acme Corp',
                'Manager',
                '123 Main St',
                'New York',
                'NY',
                'USA',
                '10001',
                'https://johndoe.com',
                'Website',
                'Sample contact for import template',
                'vip,customer',
                '1990-01-15',
                'male',
                'active'
            ]
        ];

        $filename = 'contact_import_template_' . date('Y-m-d') . '.csv';

        $callback = function() use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, $headers);

            // Write sample data
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Cancel/delete an import
     */
    public function destroy(DataImport $import)
    {
        try {
            // Can only delete if not processing
            if ($import->status === 'processing') {
                return back()->withErrors(['error' => 'Cannot delete import while it is being processed.']);
            }

            // Delete associated file
            if ($import->file_path) {
                Storage::delete($import->file_path);
            }

            $import->delete();

            return redirect()->route('data.imports.index')
                ->with('success', 'Import deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete import: ' . $e->getMessage()]);
        }
    }

    /**
     * Retry a failed import
     */
    public function retry(DataImport $import)
    {
        if ($import->status !== 'failed') {
            return back()->withErrors(['error' => 'Only failed imports can be retried.']);
        }

        try {
            $import->update([
                'status' => 'pending',
                'imported_count' => 0,
                'failed_count' => 0,
                'skipped_count' => 0,
                'updated_count' => 0,
                'errors' => null,
                'started_at' => null,
                'completed_at' => null,
            ]);

            // Process import
            $this->processImport($import);

            return redirect()->route('data.imports.show', $import)
                ->with('success', 'Import retry started.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to retry import: ' . $e->getMessage()]);
        }
    }

    /**
     * Analyze uploaded file to detect structure and preview data
     */
    private function analyzeContactFile($filePath, $hasHeaders = true)
    {
        try {
            $fullPath = Storage::path($filePath);

            if (!file_exists($fullPath)) {
                return ['success' => false, 'error' => 'File not found'];
            }

            // Handle CSV files
            if (pathinfo($fullPath, PATHINFO_EXTENSION) === 'csv') {
                $csv = Reader::createFromPath($fullPath, 'r');
                $csv->setHeaderOffset($hasHeaders ? 0 : null);

                $records = (new Statement())
                    ->limit(10) // Preview first 10 rows
                    ->process($csv);

                $totalRows = iterator_count($csv);
                $headers = $hasHeaders ? $csv->getHeader() : [];

                $previewData = [];
                foreach ($records as $record) {
                    $previewData[] = array_values($record);
                }

                return [
                    'success' => true,
                    'total_rows' => $totalRows,
                    'detected_columns' => $headers,
                    'preview_data' => $previewData,
                ];
            }

            // Handle Excel files (basic support)
            if (in_array(pathinfo($fullPath, PATHINFO_EXTENSION), ['xlsx', 'xls'])) {
                return [
                    'success' => true,
                    'total_rows' => 0, // Will be calculated during actual import
                    'detected_columns' => [],
                    'preview_data' => [],
                    'message' => 'Excel file detected. Column mapping will be available after processing.'
                ];
            }

            return ['success' => false, 'error' => 'Unsupported file format'];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to analyze file: ' . $e->getMessage()];
        }
    }

    /**
     * Process the import (in production, this would be a queued job)
     */
    private function processImport(DataImport $import)
    {
        try {
            $import->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $filePath = Storage::path($import->file_path);
            $mapping = $import->field_mapping;
            $settings = $import->settings;

            // Process CSV
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset($settings['has_headers'] ? 0 : null);

            $records = (new Statement())->process($csv);

            $importedCount = 0;
            $skippedCount = 0;
            $failedCount = 0;
            $updatedCount = 0;
            $errors = [];

            // Create segment if requested
            $segment = null;
            if (!empty($settings['create_segment'])) {
                $segment = ContactSegment::create([
                    'name' => $settings['create_segment'],
                    'type' => 'static',
                    'description' => 'Auto-created from import: ' . $import->name,
                ]);
            } elseif (!empty($settings['segment_id'])) {
                $segment = ContactSegment::find($settings['segment_id']);
            }

            foreach ($records as $index => $record) {
                try {
                    $contactData = $this->mapRecordToContact($record, $mapping);

                    if (empty($contactData['email']) && empty($contactData['phone'])) {
                        $skippedCount++;
                        $errors[] = "Row " . ($index + 1) . ": No email or phone provided";
                        continue;
                    }

                    // Handle duplicates
                    $existingContact = $this->findExistingContact($contactData);

                    if ($existingContact) {
                        switch ($settings['duplicate_handling']) {
                            case 'skip':
                                $skippedCount++;
                                continue 2;

                            case 'update':
                                $existingContact->update($contactData);
                                $updatedCount++;

                                // Add to segment if specified
                                if ($segment && !$segment->contacts()->where('contact_id', $existingContact->id)->exists()) {
                                    $segment->contacts()->attach($existingContact->id);
                                }
                                continue 2;

                            case 'duplicate':
                                // Continue to create new contact
                                break;
                        }
                    }

                    // Create new contact
                    $contact = Contact::create($contactData);
                    $importedCount++;

                    // Add to segment if specified
                    if ($segment) {
                        $segment->contacts()->attach($contact->id);
                    }

                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            // Update import status
            $import->update([
                'status' => 'completed',
                'imported_count' => $importedCount,
                'skipped_count' => $skippedCount,
                'failed_count' => $failedCount,
                'updated_count' => $updatedCount,
                'errors' => $errors,
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            $import->update([
                'status' => 'failed',
                'errors' => ['General error: ' . $e->getMessage()],
                'completed_at' => now(),
            ]);

            \Log::error('Import failed: ' . $e->getMessage(), [
                'import_id' => $import->id,
                'file_path' => $import->file_path
            ]);
        }
    }

    /**
     * Map CSV record to contact data
     */
    private function mapRecordToContact($record, $mapping)
    {
        $contactData = [];

        foreach ($mapping as $columnIndex => $field) {
            if (empty($field) || !isset($record[$columnIndex])) {
                continue;
            }

            $value = trim($record[$columnIndex]);

            if (empty($value)) {
                continue;
            }

            // Special handling for certain fields
            switch ($field) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        continue 2; // Skip invalid email
                    }
                    $contactData[$field] = strtolower($value);
                    break;

                case 'phone':
                case 'whatsapp_number':
                    // Basic phone number cleaning
                    $contactData[$field] = preg_replace('/[^\d+]/', '', $value);
                    break;

                case 'date_of_birth':
                    try {
                        $contactData[$field] = \Carbon\Carbon::parse($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Skip invalid date
                        continue 2;
                    }
                    break;

                case 'tags':
                    // Convert comma-separated tags to JSON
                    $tags = array_map('trim', explode(',', $value));
                    $contactData[$field] = json_encode(array_filter($tags));
                    break;

                case 'website':
                    // Ensure URL has protocol
                    if (!empty($value) && !str_starts_with($value, 'http')) {
                        $value = 'https://' . $value;
                    }
                    $contactData[$field] = $value;
                    break;

                default:
                    $contactData[$field] = $value;
            }
        }

        return $contactData;
    }

    /**
     * Find existing contact by email or phone
     */
    private function findExistingContact($contactData)
    {
        $query = Contact::query();

        if (!empty($contactData['email'])) {
            $query->orWhere('email', $contactData['email']);
        }

        if (!empty($contactData['phone'])) {
            $query->orWhere('phone', $contactData['phone']);
        }

        return $query->first();
    }

    /**
     * Show export index page
     */
    public function exportIndex()
    {
        $stats = [
            'total_contacts' => Contact::count(),
            'active_contacts' => Contact::where('status', 'active')->count(),
            'total_communications' => \App\Models\Communication::count(),
            'recent_exports' => DataImport::where('type', 'export')->latest()->limit(5)->get()
        ];

        return view('data.exports.index', compact('stats'));
    }

    /**
     * Export contacts to CSV/Excel
     */
    public function exportContacts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_ids' => 'sometimes|array',
            'contact_ids.*' => 'exists:contacts,id',
            'segment_id' => 'sometimes|exists:contact_segments,id',
            'status' => 'sometimes|in:active,inactive,blocked,prospect,customer',
            'format' => 'required|in:csv,xlsx',
            'fields' => 'sometimes|array',
            'include_custom_fields' => 'boolean',
            'include_tags' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $query = Contact::with(['creator', 'assignedUser', 'segments']);

            // Apply filters
            if ($request->filled('contact_ids')) {
                $query->whereIn('id', $request->contact_ids);
            }

            if ($request->filled('segment_id')) {
                $query->whereHas('segments', function($q) use ($request) {
                    $q->where('contact_segments.id', $request->segment_id);
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Add user filtering
            $query->where(function($q) {
                $q->where('created_by', auth()->id())
                  ->orWhere('assigned_to', auth()->id());
            });

            $contacts = $query->get();

            if ($contacts->isEmpty()) {
                return back()->with('warning', 'No contacts found for export.');
            }

            // Generate filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "contacts_export_{$timestamp}.{$request->format}";

            if ($request->format === 'csv') {
                return $this->exportContactsAsCsv($contacts, $filename, $request);
            } else {
                return $this->exportContactsAsExcel($contacts, $filename, $request);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export communications to CSV/Excel
     */
    public function exportCommunications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_id' => 'sometimes|exists:contacts,id',
            'type' => 'sometimes|in:email,sms,whatsapp',
            'status' => 'sometimes|in:sent,delivered,failed,pending',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'format' => 'required|in:csv,xlsx'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $query = \App\Models\Communication::with(['contact', 'user']);

            // Apply filters
            if ($request->filled('contact_id')) {
                $query->where('contact_id', $request->contact_id);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $communications = $query->latest()->get();

            if ($communications->isEmpty()) {
                return back()->with('warning', 'No communications found for export.');
            }

            // Generate filename
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "communications_export_{$timestamp}.{$request->format}";

            if ($request->format === 'csv') {
                return $this->exportCommunicationsAsCsv($communications, $filename);
            } else {
                return $this->exportCommunicationsAsExcel($communications, $filename);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Export contacts as CSV
     */
    private function exportContactsAsCsv($contacts, $filename, $request)
    {
        $callback = function() use ($contacts, $request) {
            $file = fopen('php://output', 'w');

            // CSV headers
            $headers = [
                'First Name', 'Last Name', 'Email', 'Phone', 'WhatsApp', 'Company',
                'Position', 'Address', 'City', 'Country', 'Status', 'Source',
                'Created At', 'Last Updated'
            ];

            if ($request->boolean('include_tags')) {
                $headers[] = 'Tags';
            }

            if ($request->boolean('include_custom_fields')) {
                $headers[] = 'Custom Fields';
            }

            $headers[] = 'Segments';
            $headers[] = 'Created By';
            $headers[] = 'Assigned To';

            fputcsv($file, $headers);

            // CSV data
            foreach ($contacts as $contact) {
                $row = [
                    $contact->first_name,
                    $contact->last_name,
                    $contact->email,
                    $contact->phone,
                    $contact->whatsapp,
                    $contact->company,
                    $contact->position,
                    $contact->address,
                    $contact->city,
                    $contact->country,
                    $contact->status,
                    $contact->source,
                    $contact->created_at->format('Y-m-d H:i:s'),
                    $contact->updated_at->format('Y-m-d H:i:s')
                ];

                if ($request->boolean('include_tags')) {
                    $row[] = is_array($contact->tags) ? implode(', ', $contact->tags) : $contact->tags;
                }

                if ($request->boolean('include_custom_fields')) {
                    $customFields = is_array($contact->custom_fields) ?
                        json_encode($contact->custom_fields) : $contact->custom_fields;
                    $row[] = $customFields;
                }

                $row[] = $contact->segments->pluck('name')->join(', ');
                $row[] = $contact->creator ? $contact->creator->name : '';
                $row[] = $contact->assignedUser ? $contact->assignedUser->name : '';

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export contacts as Excel (placeholder - requires PhpSpreadsheet)
     */
    private function exportContactsAsExcel($contacts, $filename, $request)
    {
        // For now, fallback to CSV
        return $this->exportContactsAsCsv($contacts, str_replace('.xlsx', '.csv', $filename), $request);
    }

    /**
     * Export communications as CSV
     */
    private function exportCommunicationsAsCsv($communications, $filename)
    {
        $callback = function() use ($communications) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Date', 'Type', 'Direction', 'Contact Name', 'Contact Email',
                'Subject', 'Status', 'User', 'Message Preview'
            ]);

            // CSV data
            foreach ($communications as $comm) {
                fputcsv($file, [
                    $comm->created_at->format('Y-m-d H:i:s'),
                    ucfirst($comm->type),
                    ucfirst($comm->direction),
                    $comm->contact ? $comm->contact->full_name : 'Unknown',
                    $comm->contact ? $comm->contact->email : '',
                    $comm->subject,
                    ucfirst($comm->status),
                    $comm->user ? $comm->user->name : 'System',
                    Str::limit(strip_tags($comm->content), 100)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export communications as Excel (placeholder)
     */
    private function exportCommunicationsAsExcel($communications, $filename)
    {
        // For now, fallback to CSV
        return $this->exportCommunicationsAsCsv($communications, str_replace('.xlsx', '.csv', $filename));
    }
}
