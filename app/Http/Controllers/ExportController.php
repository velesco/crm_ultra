<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use App\Models\Revenue;
use App\Models\SystemLog;
use App\Models\ExportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use League\Csv\Writer;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContactsExport;
use App\Exports\CampaignsExport;
use App\Exports\RevenueExport;
use App\Exports\CommunicationsExport;
use App\Exports\CustomDataExport;
use App\Jobs\ProcessExportJob;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin|manager']);
    }

    /**
     * Display export dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $exports = ExportRequest::with(['user', 'createdBy'])
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                return $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('is_public', true);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Export statistics
        $stats = [
            'total_exports' => ExportRequest::count(),
            'pending_exports' => ExportRequest::where('status', 'pending')->count(),
            'completed_exports' => ExportRequest::where('status', 'completed')->count(),
            'failed_exports' => ExportRequest::where('status', 'failed')->count(),
            'scheduled_exports' => ExportRequest::whereNotNull('scheduled_for')
                ->where('scheduled_for', '>', now())
                ->count(),
            'total_file_size' => ExportRequest::where('status', 'completed')
                ->sum('file_size'),
            'avg_processing_time' => ExportRequest::where('status', 'completed')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_time')
                ->first()->avg_time ?? 0,
        ];

        // Recent activity
        $recent_activity = ExportRequest::with(['user', 'createdBy'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Data source counts
        $data_sources = [
            'contacts' => Contact::count(),
            'email_campaigns' => EmailCampaign::count(),
            'sms_messages' => SmsMessage::count(),
            'whatsapp_messages' => WhatsAppMessage::count(),
            'revenue' => Revenue::count(),
            'system_logs' => SystemLog::count(),
        ];

        // Export types distribution
        $export_types = ExportRequest::selectRaw('data_type, COUNT(*) as count')
            ->groupBy('data_type')
            ->pluck('count', 'data_type');

        return view('exports.index', compact(
            'exports', 'stats', 'recent_activity', 'data_sources', 'export_types'
        ));
    }

    /**
     * Show export creation form
     */
    public function create()
    {
        $data_types = [
            'contacts' => 'Contacts',
            'email_campaigns' => 'Email Campaigns',
            'sms_messages' => 'SMS Messages',
            'whatsapp_messages' => 'WhatsApp Messages',
            'revenue' => 'Revenue Data',
            'communications' => 'All Communications',
            'system_logs' => 'System Logs',
            'custom' => 'Custom Query'
        ];

        $format_types = [
            'csv' => 'CSV',
            'xlsx' => 'Excel (XLSX)',
            'json' => 'JSON',
            'pdf' => 'PDF Report'
        ];

        return view('exports.create', compact('data_types', 'format_types'));
    }

    /**
     * Store new export request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'data_type' => 'required|string|in:contacts,email_campaigns,sms_messages,whatsapp_messages,revenue,communications,system_logs,custom',
            'format' => 'required|string|in:csv,xlsx,json,pdf',
            'filters' => 'nullable|json',
            'columns' => 'nullable|array',
            'columns.*' => 'string',
            'custom_query' => 'nullable|string',
            'schedule_type' => 'nullable|string|in:immediate,scheduled,recurring',
            'scheduled_for' => 'nullable|date|after:now',
            'recurring_frequency' => 'nullable|string|in:daily,weekly,monthly',
            'is_public' => 'nullable|boolean',
            'notify_on_completion' => 'nullable|boolean',
            'include_attachments' => 'nullable|boolean'
        ]);

        $export = ExportRequest::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'data_type' => $validated['data_type'],
            'format' => $validated['format'],
            'filters' => $validated['filters'] ? json_decode($validated['filters'], true) : null,
            'columns' => $validated['columns'] ?? null,
            'custom_query' => $validated['custom_query'] ?? null,
            'status' => 'pending',
            'scheduled_for' => $validated['scheduled_for'] ?? null,
            'recurring_frequency' => $validated['recurring_frequency'] ?? null,
            'is_public' => $validated['is_public'] ?? false,
            'notify_on_completion' => $validated['notify_on_completion'] ?? true,
            'include_attachments' => $validated['include_attachments'] ?? false,
            'user_id' => Auth::id(),
            'created_by' => Auth::id(),
        ]);

        // Process immediately or schedule
        if ($validated['schedule_type'] === 'immediate') {
            ProcessExportJob::dispatch($export);
        }

        SystemLog::info('export', 'export_created', "Export request '{$export->name}' created", [
            'export_id' => $export->id,
            'data_type' => $export->data_type,
            'format' => $export->format
        ]);

        return redirect()->route('exports.show', $export)
            ->with('success', 'Export request created successfully!');
    }

    /**
     * Show export details
     */
    public function show(ExportRequest $export)
    {
        $this->authorize('view', $export);
        
        $export->load(['user', 'createdBy']);

        // Related exports
        $related_exports = ExportRequest::where('data_type', $export->data_type)
            ->where('id', '!=', $export->id)
            ->where(function ($query) use ($export) {
                $query->where('user_id', $export->user_id)
                      ->orWhere('is_public', true);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('exports.show', compact('export', 'related_exports'));
    }

    /**
     * Edit export request
     */
    public function edit(ExportRequest $export)
    {
        $this->authorize('update', $export);

        if ($export->status !== 'pending') {
            return back()->with('error', 'Can only edit pending exports.');
        }

        $data_types = [
            'contacts' => 'Contacts',
            'email_campaigns' => 'Email Campaigns',
            'sms_messages' => 'SMS Messages',
            'whatsapp_messages' => 'WhatsApp Messages',
            'revenue' => 'Revenue Data',
            'communications' => 'All Communications',
            'system_logs' => 'System Logs',
            'custom' => 'Custom Query'
        ];

        $format_types = [
            'csv' => 'CSV',
            'xlsx' => 'Excel (XLSX)',
            'json' => 'JSON',
            'pdf' => 'PDF Report'
        ];

        return view('exports.edit', compact('export', 'data_types', 'format_types'));
    }

    /**
     * Update export request
     */
    public function update(Request $request, ExportRequest $export)
    {
        $this->authorize('update', $export);

        if ($export->status !== 'pending') {
            return back()->with('error', 'Can only update pending exports.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'data_type' => 'required|string|in:contacts,email_campaigns,sms_messages,whatsapp_messages,revenue,communications,system_logs,custom',
            'format' => 'required|string|in:csv,xlsx,json,pdf',
            'filters' => 'nullable|json',
            'columns' => 'nullable|array',
            'custom_query' => 'nullable|string',
            'scheduled_for' => 'nullable|date|after:now',
            'recurring_frequency' => 'nullable|string|in:daily,weekly,monthly',
            'is_public' => 'nullable|boolean',
            'notify_on_completion' => 'nullable|boolean',
            'include_attachments' => 'nullable|boolean'
        ]);

        $export->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'data_type' => $validated['data_type'],
            'format' => $validated['format'],
            'filters' => $validated['filters'] ? json_decode($validated['filters'], true) : null,
            'columns' => $validated['columns'] ?? null,
            'custom_query' => $validated['custom_query'] ?? null,
            'scheduled_for' => $validated['scheduled_for'] ?? null,
            'recurring_frequency' => $validated['recurring_frequency'] ?? null,
            'is_public' => $validated['is_public'] ?? false,
            'notify_on_completion' => $validated['notify_on_completion'] ?? true,
            'include_attachments' => $validated['include_attachments'] ?? false,
        ]);

        SystemLog::info('export', 'export_updated', "Export request '{$export->name}' updated", [
            'export_id' => $export->id
        ]);

        return redirect()->route('exports.show', $export)
            ->with('success', 'Export request updated successfully!');
    }

    /**
     * Delete export request
     */
    public function destroy(ExportRequest $export)
    {
        $this->authorize('delete', $export);

        // Delete associated file if exists
        if ($export->file_path && Storage::exists($export->file_path)) {
            Storage::delete($export->file_path);
        }

        $exportName = $export->name;
        $export->delete();

        SystemLog::info('export', 'export_deleted', "Export request '{$exportName}' deleted");

        return redirect()->route('exports.index')
            ->with('success', 'Export request deleted successfully!');
    }

    /**
     * Start export processing
     */
    public function start(ExportRequest $export)
    {
        $this->authorize('update', $export);

        if ($export->status !== 'pending') {
            return back()->with('error', 'Export is not in pending status.');
        }

        ProcessExportJob::dispatch($export);

        return back()->with('success', 'Export processing started!');
    }

    /**
     * Cancel export processing
     */
    public function cancel(ExportRequest $export)
    {
        $this->authorize('update', $export);

        if (!in_array($export->status, ['pending', 'processing'])) {
            return back()->with('error', 'Cannot cancel completed export.');
        }

        $export->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'error_message' => 'Cancelled by user'
        ]);

        SystemLog::info('export', 'export_cancelled', "Export '{$export->name}' cancelled by user");

        return back()->with('success', 'Export cancelled successfully!');
    }

    /**
     * Duplicate export request
     */
    public function duplicate(ExportRequest $export)
    {
        $this->authorize('create', ExportRequest::class);

        $newExport = $export->replicate([
            'file_path', 'file_size', 'status', 'started_at', 
            'completed_at', 'error_message', 'download_count'
        ]);
        
        $newExport->name = $export->name . ' (Copy)';
        $newExport->status = 'pending';
        $newExport->user_id = Auth::id();
        $newExport->created_by = Auth::id();
        $newExport->save();

        return redirect()->route('exports.show', $newExport)
            ->with('success', 'Export duplicated successfully!');
    }

    /**
     * Download export file
     */
    public function download(ExportRequest $export)
    {
        $this->authorize('view', $export);

        if ($export->status !== 'completed' || !$export->file_path) {
            return back()->with('error', 'Export file not available.');
        }

        if (!Storage::exists($export->file_path)) {
            return back()->with('error', 'Export file not found.');
        }

        // Increment download count
        $export->increment('download_count');

        SystemLog::info('export', 'export_downloaded', "Export '{$export->name}' downloaded", [
            'export_id' => $export->id,
            'download_count' => $export->download_count
        ]);

        return Storage::download($export->file_path, $export->getFileName());
    }

    /**
     * Get export progress
     */
    public function progress(ExportRequest $export)
    {
        $this->authorize('view', $export);

        return response()->json([
            'status' => $export->status,
            'progress' => $export->progress ?? 0,
            'message' => $export->status_message,
            'started_at' => $export->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $export->completed_at?->format('Y-m-d H:i:s'),
            'error_message' => $export->error_message,
            'file_size' => $export->file_size,
            'download_url' => $export->status === 'completed' && $export->file_path 
                ? route('exports.download', $export) 
                : null
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulk(Request $request)
    {
        $this->authorize('update', ExportRequest::class);

        $validated = $request->validate([
            'action' => 'required|in:start,cancel,delete',
            'export_ids' => 'required|array',
            'export_ids.*' => 'exists:export_requests,id'
        ]);

        $exports = ExportRequest::whereIn('id', $validated['export_ids'])->get();
        $count = 0;

        foreach ($exports as $export) {
            switch ($validated['action']) {
                case 'start':
                    if ($export->status === 'pending') {
                        ProcessExportJob::dispatch($export);
                        $count++;
                    }
                    break;
                
                case 'cancel':
                    if (in_array($export->status, ['pending', 'processing'])) {
                        $export->update([
                            'status' => 'cancelled',
                            'completed_at' => now(),
                            'error_message' => 'Cancelled by user'
                        ]);
                        $count++;
                    }
                    break;
                
                case 'delete':
                    if ($export->file_path && Storage::exists($export->file_path)) {
                        Storage::delete($export->file_path);
                    }
                    $export->delete();
                    $count++;
                    break;
            }
        }

        $action = ucfirst($validated['action']);
        return response()->json([
            'success' => true,
            'message' => "{$count} exports {$action}d successfully!"
        ]);
    }

    /**
     * Get available columns for data type
     */
    public function columns(Request $request)
    {
        $dataType = $request->input('data_type');
        $columns = [];

        switch ($dataType) {
            case 'contacts':
                $columns = [
                    'id' => 'ID',
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'company' => 'Company',
                    'position' => 'Position',
                    'industry' => 'Industry',
                    'status' => 'Status',
                    'created_at' => 'Created At'
                ];
                break;
            
            case 'email_campaigns':
                $columns = [
                    'id' => 'ID',
                    'name' => 'Campaign Name',
                    'subject' => 'Subject',
                    'status' => 'Status',
                    'sent_count' => 'Sent Count',
                    'open_rate' => 'Open Rate',
                    'click_rate' => 'Click Rate',
                    'created_at' => 'Created At'
                ];
                break;

            case 'revenue':
                $columns = [
                    'id' => 'ID',
                    'contact_id' => 'Contact ID',
                    'amount' => 'Amount',
                    'currency' => 'Currency',
                    'source' => 'Source',
                    'status' => 'Status',
                    'transaction_date' => 'Transaction Date',
                    'created_at' => 'Created At'
                ];
                break;
        }

        return response()->json(['columns' => $columns]);
    }

    /**
     * Get scheduled exports
     */
    public function scheduled()
    {
        $scheduled_exports = ExportRequest::whereNotNull('scheduled_for')
            ->where('scheduled_for', '>', now())
            ->orderBy('scheduled_for')
            ->paginate(15);

        return view('exports.scheduled', compact('scheduled_exports'));
    }

    /**
     * Export statistics API
     */
    public function stats()
    {
        $stats = [
            'total' => ExportRequest::count(),
            'pending' => ExportRequest::where('status', 'pending')->count(),
            'processing' => ExportRequest::where('status', 'processing')->count(),
            'completed' => ExportRequest::where('status', 'completed')->count(),
            'failed' => ExportRequest::where('status', 'failed')->count(),
            'cancelled' => ExportRequest::where('status', 'cancelled')->count(),
        ];

        // Recent activity (last 24 hours)
        $recent = ExportRequest::where('created_at', '>=', now()->subDay())
            ->selectRaw("DATE_FORMAT(created_at, '%H:00') as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');

        // Export types distribution
        $types = ExportRequest::selectRaw('data_type, COUNT(*) as count')
            ->groupBy('data_type')
            ->pluck('count', 'data_type');

        return response()->json([
            'stats' => $stats,
            'hourly_activity' => $recent,
            'export_types' => $types
        ]);
    }
}
