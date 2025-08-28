<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsentLog;
use App\Models\Contact;
use App\Models\DataRequest;
use App\Models\DataRetentionPolicy;
use App\Notifications\DataExportReady;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ComplianceController extends Controller
{
    /**
     * Display compliance dashboard
     */
    public function index()
    {
        // Compliance overview statistics
        $stats = [
            'data_requests' => [
                'total' => DataRequest::count(),
                'pending' => DataRequest::pending()->count(),
                'processing' => DataRequest::processing()->count(),
                'completed' => DataRequest::completed()->count(),
                'overdue' => DataRequest::overdue()->count(),
            ],
            'consent_logs' => [
                'total' => ConsentLog::count(),
                'given' => ConsentLog::given()->count(),
                'withdrawn' => ConsentLog::withdrawn()->count(),
                'expired' => ConsentLog::expired()->count(),
            ],
            'retention_policies' => [
                'total' => DataRetentionPolicy::count(),
                'active' => DataRetentionPolicy::active()->count(),
                'auto_delete' => DataRetentionPolicy::autoDelete()->count(),
                'overdue_executions' => DataRetentionPolicy::overdueExecution()->count(),
            ],
            'compliance_score' => $this->calculateComplianceScore(),
        ];

        // Recent activity
        $recentRequests = DataRequest::with('contact')
            ->latest()
            ->take(10)
            ->get();

        $recentConsents = ConsentLog::with('contact')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.compliance.index', compact('stats', 'recentRequests', 'recentConsents'));
    }

    /**
     * Display consent logs
     */
    public function consentLogs(Request $request)
    {
        $query = ConsentLog::with(['contact']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contact', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('consent_type')) {
            $query->where('consent_type', $request->consent_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('given_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('given_at', '<=', $request->date_to);
        }

        $consentLogs = $query->latest('given_at')->paginate(20);

        // Statistics for this view
        $stats = [
            'total_consents' => ConsentLog::count(),
            'given_consents' => ConsentLog::given()->count(),
            'withdrawn_consents' => ConsentLog::withdrawn()->count(),
            'expired_consents' => ConsentLog::expired()->count(),
            'email_consents' => ConsentLog::where('consent_type', 'email')->count(),
            'sms_consents' => ConsentLog::where('consent_type', 'sms')->count(),
            'whatsapp_consents' => ConsentLog::where('consent_type', 'whatsapp')->count(),
            'marketing_consents' => ConsentLog::where('purpose', 'marketing')->count(),
        ];

        return view('admin.compliance.consent-logs', compact('consentLogs', 'stats'));
    }

    /**
     * Display data requests
     */
    public function dataRequests(Request $request)
    {
        $query = DataRequest::with(['contact']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contact', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('email', 'like', "%{$search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }

        $dataRequests = $query->latest('requested_at')->paginate(20);

        // Statistics for this view
        $stats = [
            'total_requests' => DataRequest::count(),
            'export_requests' => DataRequest::where('type', 'export')->count(),
            'delete_requests' => DataRequest::where('type', 'delete')->count(),
            'pending_requests' => DataRequest::pending()->count(),
            'processing_requests' => DataRequest::processing()->count(),
            'completed_requests' => DataRequest::completed()->count(),
            'overdue_requests' => DataRequest::overdue()->count(),
            'avg_processing_time' => $this->getAverageProcessingTime(),
        ];

        return view('admin.compliance.data-requests', compact('dataRequests', 'stats'));
    }

    /**
     * Display retention policies
     */
    public function retentionPolicies(Request $request)
    {
        $query = DataRetentionPolicy::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->filled('data_type')) {
            $query->where('data_type', $request->data_type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        $retentionPolicies = $query->latest()->paginate(20);

        // Statistics for this view
        $stats = [
            'total_policies' => DataRetentionPolicy::count(),
            'active_policies' => DataRetentionPolicy::active()->count(),
            'auto_delete_policies' => DataRetentionPolicy::autoDelete()->count(),
            'overdue_executions' => DataRetentionPolicy::overdueExecution()->count(),
            'records_to_delete' => $this->getRecordsToDeleteCount(),
            'last_execution' => DataRetentionPolicy::whereNotNull('last_executed_at')
                ->latest('last_executed_at')
                ->first()?->last_executed_at,
            'next_execution' => DataRetentionPolicy::active()
                ->orderBy('last_executed_at')
                ->first()?->getNextExecutionTime(),
        ];

        return view('admin.compliance.retention-policies', compact('retentionPolicies', 'stats'));
    }

    /**
     * Process data request
     */
    public function processDataRequest(DataRequest $dataRequest)
    {
        try {
            // Update status to processing
            $dataRequest->update([
                'status' => DataRequest::STATUS_PROCESSING,
                'processed_at' => now(),
                'processed_by' => Auth::id(),
            ]);

            if ($dataRequest->type === DataRequest::TYPE_EXPORT) {
                $this->processExportRequest($dataRequest);
            } elseif ($dataRequest->type === DataRequest::TYPE_DELETE) {
                $this->processDeleteRequest($dataRequest);
            }

            return redirect()->back()->with('success', 'Data request processing started successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to process data request', [
                'request_id' => $dataRequest->id,
                'error' => $e->getMessage(),
            ]);

            $dataRequest->update([
                'status' => DataRequest::STATUS_FAILED,
                'notes' => 'Processing failed: '.$e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to process data request: '.$e->getMessage());
        }
    }

    /**
     * Process export request
     */
    private function processExportRequest(DataRequest $dataRequest)
    {
        $contact = $dataRequest->contact;

        if (! $contact && $dataRequest->email) {
            $contact = Contact::where('email', $dataRequest->email)->first();
        }

        if (! $contact) {
            throw new \Exception('Contact not found for export request');
        }

        // Collect all data
        $exportData = $this->collectContactData($contact, $dataRequest);

        // Generate filename
        $filename = 'data-export-'.$contact->id.'-'.now()->format('Y-m-d-H-i-s').'.json';

        // Store the export file
        Storage::disk('local')->put("exports/{$filename}", json_encode($exportData, JSON_PRETTY_PRINT));

        // Update request status
        $dataRequest->update([
            'status' => DataRequest::STATUS_COMPLETED,
            'completed_at' => now(),
            'export_file_path' => "exports/{$filename}",
            'notes' => 'Data export completed successfully',
        ]);

        // Notify user (if contact has user account)
        if ($contact->user) {
            $contact->user->notify(new DataExportReady($dataRequest));
        }

        Log::info('Data export completed', [
            'request_id' => $dataRequest->id,
            'contact_id' => $contact->id,
            'filename' => $filename,
        ]);
    }

    /**
     * Process delete request
     */
    private function processDeleteRequest(DataRequest $dataRequest)
    {
        $contact = $dataRequest->contact;

        if (! $contact && $dataRequest->email) {
            $contact = Contact::where('email', $dataRequest->email)->first();
        }

        if (! $contact) {
            throw new \Exception('Contact not found for delete request');
        }

        DB::beginTransaction();

        try {
            // Delete related data first
            $contact->emailLogs()->delete();
            $contact->smsMessages()->delete();
            $contact->whatsappMessages()->delete();
            $contact->consentLogs()->delete();
            $contact->contactActivities()->delete();

            // Remove from segments
            $contact->segments()->detach();

            // Delete the contact
            $contact->delete();

            // Update request status
            $dataRequest->update([
                'status' => DataRequest::STATUS_COMPLETED,
                'completed_at' => now(),
                'notes' => 'Contact and all related data deleted successfully',
            ]);

            DB::commit();

            Log::info('Data deletion completed', [
                'request_id' => $dataRequest->id,
                'contact_id' => $contact->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Download export file
     */
    public function downloadExport(DataRequest $dataRequest)
    {
        if ($dataRequest->type !== DataRequest::TYPE_EXPORT) {
            abort(404);
        }

        if (! $dataRequest->export_file_path || ! Storage::exists($dataRequest->export_file_path)) {
            abort(404, 'Export file not found');
        }

        return Storage::download($dataRequest->export_file_path, basename($dataRequest->export_file_path));
    }

    /**
     * Execute retention policy
     */
    public function executeRetentionPolicy(DataRetentionPolicy $policy)
    {
        try {
            $deletedCount = $policy->execute();

            return redirect()->back()->with('success', "Retention policy executed successfully. Deleted {$deletedCount} records.");

        } catch (\Exception $e) {
            Log::error('Failed to execute retention policy', [
                'policy_id' => $policy->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to execute retention policy: '.$e->getMessage());
        }
    }

    /**
     * Collect all contact data for export
     */
    private function collectContactData(Contact $contact, DataRequest $dataRequest)
    {
        return [
            'contact_information' => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'company' => $contact->company,
                'position' => $contact->position,
                'industry' => $contact->industry,
                'country' => $contact->country,
                'city' => $contact->city,
                'address' => $contact->address,
                'created_at' => $contact->created_at,
                'updated_at' => $contact->updated_at,
                'tags' => $contact->tags,
                'notes' => $contact->notes,
                'status' => $contact->status,
                'source' => $contact->source,
            ],
            'segments' => $contact->segments()->get()->map(function ($segment) {
                return [
                    'name' => $segment->name,
                    'description' => $segment->description,
                    'joined_at' => $segment->pivot->created_at,
                ];
            }),
            'email_communications' => $contact->emailLogs()->get()->map(function ($log) {
                return [
                    'subject' => $log->subject,
                    'sent_at' => $log->sent_at,
                    'opened_at' => $log->opened_at,
                    'clicked_at' => $log->clicked_at,
                    'status' => $log->status,
                ];
            }),
            'sms_communications' => $contact->smsMessages()->get()->map(function ($sms) {
                return [
                    'content' => $sms->content,
                    'sent_at' => $sms->sent_at,
                    'delivered_at' => $sms->delivered_at,
                    'status' => $sms->status,
                ];
            }),
            'whatsapp_communications' => $contact->whatsappMessages()->get()->map(function ($msg) {
                return [
                    'content' => $msg->content,
                    'sent_at' => $msg->sent_at,
                    'delivered_at' => $msg->delivered_at,
                    'status' => $msg->status,
                ];
            }),
            'consent_history' => $contact->consentLogs()->get()->map(function ($consent) {
                return [
                    'consent_type' => $consent->consent_type,
                    'status' => $consent->status,
                    'given_at' => $consent->given_at,
                    'withdrawn_at' => $consent->withdrawn_at,
                    'legal_basis' => $consent->legal_basis,
                    'purpose' => $consent->purpose,
                ];
            }),
            'export_metadata' => [
                'exported_at' => now(),
                'request_id' => $dataRequest->id,
                'exported_by' => Auth::user()->name ?? 'System',
            ],
        ];
    }

    /**
     * Run compliance audit
     */
    public function audit()
    {
        $audit = [
            'data_requests' => [
                'overdue' => DataRequest::where('requested_at', '<', now()->subDays(30))
                    ->whereIn('status', ['pending', 'verified', 'processing'])
                    ->count(),
                'pending_verification' => DataRequest::pending()->count(),
                'pending_processing' => DataRequest::verified()->count(),
            ],
            'consent_compliance' => [
                'contacts_without_consent' => Contact::whereDoesntHave('consentLogs', function ($q) {
                    $q->where('status', ConsentLog::STATUS_GIVEN);
                })->count(),
                'expired_consents' => ConsentLog::expired()->count(),
                'withdrawn_consents' => ConsentLog::withdrawn()->count(),
            ],
            'retention_compliance' => [
                'policies_not_executed' => DataRetentionPolicy::active()
                    ->autoDelete()
                    ->where(function ($q) {
                        $q->whereNull('last_executed_at')
                            ->orWhere('last_executed_at', '<', now()->subWeek());
                    })
                    ->count(),
                'overdue_deletions' => $this->getOverdueDeletions(),
            ],
        ];

        return response()->json($audit);
    }

    /**
     * Calculate compliance score (0-100)
     */
    private function calculateComplianceScore()
    {
        $score = 100;

        // Deduct points for overdue requests
        $overdueRequests = DataRequest::overdue()->count();
        $score -= min($overdueRequests * 5, 30);

        // Deduct points for contacts without consent
        $contactsWithoutConsent = Contact::whereDoesntHave('consentLogs')->count();
        $totalContacts = Contact::count();
        if ($totalContacts > 0) {
            $score -= min(($contactsWithoutConsent / $totalContacts) * 40, 40);
        }

        // Deduct points for overdue policy executions
        $overduePolicies = DataRetentionPolicy::overdueExecution()->count();
        $score -= min($overduePolicies * 10, 30);

        return max($score, 0);
    }

    /**
     * Get average processing time in hours
     */
    private function getAverageProcessingTime()
    {
        return DataRequest::whereNotNull('completed_at')
            ->whereNotNull('processed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, processed_at, completed_at)) as avg_time')
            ->value('avg_time') ?? 0;
    }

    /**
     * Get overdue deletions count
     */
    private function getOverdueDeletions()
    {
        $count = 0;
        $policies = DataRetentionPolicy::active()->get();

        foreach ($policies as $policy) {
            $count += $policy->getAffectedRecordsCount();
        }

        return $count;
    }

    /**
     * Get records to delete count
     */
    private function getRecordsToDeleteCount()
    {
        $count = 0;
        $policies = DataRetentionPolicy::active()->get();

        foreach ($policies as $policy) {
            $count += $policy->getAffectedRecordsCount();
        }

        return $count;
    }
}
