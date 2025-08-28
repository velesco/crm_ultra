<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class WebhookLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display webhook logs with filtering and statistics
     */
    public function index(Request $request)
    {
        $query = WebhookLog::orderBy('webhook_received_at', 'desc');

        // Apply filters
        if ($request->filled('webhook_type')) {
            $query->type($request->webhook_type);
        }

        if ($request->filled('provider')) {
            $query->provider($request->provider);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('event_type')) {
            $query->eventType($request->event_type);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $logs = $query->paginate(50);

        // Get statistics
        $statistics = $this->getStatistics();

        // Get filter options
        $webhookTypes = [
            WebhookLog::TYPE_EMAIL => 'Email',
            WebhookLog::TYPE_SMS => 'SMS',
            WebhookLog::TYPE_WHATSAPP => 'WhatsApp',
            WebhookLog::TYPE_GOOGLE_SHEETS => 'Google Sheets',
            WebhookLog::TYPE_API => 'API',
        ];

        $providers = [
            WebhookLog::PROVIDER_SENDGRID => 'SendGrid',
            WebhookLog::PROVIDER_MAILGUN => 'Mailgun',
            WebhookLog::PROVIDER_SES => 'Amazon SES',
            WebhookLog::PROVIDER_TWILIO => 'Twilio',
            WebhookLog::PROVIDER_NEXMO => 'Nexmo',
            WebhookLog::PROVIDER_WHATSAPP => 'WhatsApp',
            WebhookLog::PROVIDER_GOOGLE => 'Google',
        ];

        $statuses = [
            WebhookLog::STATUS_PENDING => 'Pending',
            WebhookLog::STATUS_PROCESSING => 'Processing',
            WebhookLog::STATUS_COMPLETED => 'Completed',
            WebhookLog::STATUS_FAILED => 'Failed',
            WebhookLog::STATUS_RETRYING => 'Retrying',
        ];

        $eventTypes = [
            WebhookLog::EVENT_DELIVERED => 'Delivered',
            WebhookLog::EVENT_BOUNCED => 'Bounced',
            WebhookLog::EVENT_OPENED => 'Opened',
            WebhookLog::EVENT_CLICKED => 'Clicked',
            WebhookLog::EVENT_FAILED => 'Failed',
            WebhookLog::EVENT_SPAM => 'Spam',
            WebhookLog::EVENT_UNSUBSCRIBE => 'Unsubscribe',
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.webhook-logs.table', compact('logs'))->render(),
                'statistics' => $statistics,
            ]);
        }

        return view('admin.webhook-logs.index', compact(
            'logs',
            'statistics',
            'webhookTypes',
            'providers',
            'statuses',
            'eventTypes'
        ));
    }

    /**
     * Show detailed webhook log view
     */
    public function show(WebhookLog $webhookLog)
    {
        // Get related logs (same reference_id or webhook_id)
        $relatedLogs = collect();

        if ($webhookLog->reference_id && $webhookLog->reference_type) {
            $relatedLogs = WebhookLog::where('reference_id', $webhookLog->reference_id)
                ->where('reference_type', $webhookLog->reference_type)
                ->where('id', '!=', $webhookLog->id)
                ->orderBy('webhook_received_at', 'desc')
                ->limit(10)
                ->get();
        } elseif ($webhookLog->webhook_id) {
            $relatedLogs = WebhookLog::where('webhook_id', $webhookLog->webhook_id)
                ->where('id', '!=', $webhookLog->id)
                ->orderBy('webhook_received_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('admin.webhook-logs.show', compact('webhookLog', 'relatedLogs'));
    }

    /**
     * Retry failed webhook
     */
    public function retry(WebhookLog $webhookLog)
    {
        if (! $webhookLog->canRetry()) {
            return back()->with('error', 'Webhook cannot be retried.');
        }

        try {
            // Mark as pending for reprocessing
            $webhookLog->update([
                'status' => WebhookLog::STATUS_PENDING,
                'error_message' => null,
                'error_context' => null,
                'next_retry_at' => null,
            ]);

            // Here you would dispatch the job to reprocess the webhook
            // ProcessWebhookJob::dispatch($webhookLog);

            return back()->with('success', 'Webhook queued for retry.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry webhook: '.$e->getMessage());
        }
    }

    /**
     * Bulk retry failed webhooks
     */
    public function bulkRetry(Request $request)
    {
        $request->validate([
            'webhook_ids' => 'required|array',
            'webhook_ids.*' => 'exists:webhook_logs,id',
        ]);

        try {
            $webhooks = WebhookLog::whereIn('id', $request->webhook_ids)
                ->where('status', WebhookLog::STATUS_FAILED)
                ->where('attempts', '<', 5)
                ->get();

            $retryCount = 0;
            foreach ($webhooks as $webhook) {
                if ($webhook->canRetry()) {
                    $webhook->update([
                        'status' => WebhookLog::STATUS_PENDING,
                        'error_message' => null,
                        'error_context' => null,
                        'next_retry_at' => null,
                    ]);

                    // ProcessWebhookJob::dispatch($webhook);
                    $retryCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Queued {$retryCount} webhooks for retry.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry webhooks: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear old webhooks
     */
    public function clearOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'status' => 'nullable|in:completed,failed,all',
        ]);

        try {
            $query = WebhookLog::where('created_at', '<', now()->subDays($request->days));

            if ($request->status && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $deletedCount = $query->count();
            $query->delete();

            return response()->json([
                'success' => true,
                'message' => "Deleted {$deletedCount} old webhook logs.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear old webhooks: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export webhooks to CSV
     */
    public function export(Request $request)
    {
        $query = WebhookLog::orderBy('webhook_received_at', 'desc');

        // Apply same filters as index
        if ($request->filled('webhook_type')) {
            $query->type($request->webhook_type);
        }

        if ($request->filled('provider')) {
            $query->provider($request->provider);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('event_type')) {
            $query->eventType($request->event_type);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $webhooks = $query->limit(10000)->get(); // Limit for performance

        $filename = 'webhook_logs_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return Response::stream(function () use ($webhooks) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'ID',
                'Type',
                'Provider',
                'Event Type',
                'Status',
                'Method',
                'URL',
                'Attempts',
                'Response Code',
                'Error Message',
                'Webhook ID',
                'Reference ID',
                'Reference Type',
                'IP Address',
                'Received At',
                'Processed At',
                'Processing Time (ms)',
            ]);

            foreach ($webhooks as $webhook) {
                fputcsv($handle, [
                    $webhook->id,
                    $webhook->webhook_type,
                    $webhook->provider,
                    $webhook->event_type,
                    $webhook->status,
                    $webhook->method,
                    $webhook->url,
                    $webhook->attempts,
                    $webhook->response_code,
                    $webhook->error_message,
                    $webhook->webhook_id,
                    $webhook->reference_id,
                    $webhook->reference_type,
                    $webhook->ip_address,
                    $webhook->webhook_received_at?->format('Y-m-d H:i:s'),
                    $webhook->processed_at?->format('Y-m-d H:i:s'),
                    $webhook->processing_time,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Get chart data for webhook analytics
     */
    public function chartData(Request $request)
    {
        $period = $request->input('period', '7'); // days

        $data = Cache::remember("webhook_chart_data_{$period}", 300, function () use ($period) {
            $startDate = now()->subDays($period);

            // Daily webhook counts
            $dailyCounts = WebhookLog::where('webhook_received_at', '>=', $startDate)
                ->selectRaw('DATE(webhook_received_at) as date')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as completed', [WebhookLog::STATUS_COMPLETED])
                ->selectRaw('COUNT(CASE WHEN status = ? THEN 1 END) as failed', [WebhookLog::STATUS_FAILED])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Provider distribution
            $providerStats = WebhookLog::getProviderStatistics();

            // Type distribution
            $typeStats = WebhookLog::getTypeStatistics();

            return [
                'daily_counts' => $dailyCounts,
                'provider_stats' => $providerStats,
                'type_stats' => $typeStats,
            ];
        });

        return response()->json($data);
    }

    /**
     * Get health metrics
     */
    public function healthMetrics()
    {
        $metrics = Cache::remember('webhook_health_metrics', 300, function () {
            $recentFailed = WebhookLog::failed()
                ->where('webhook_received_at', '>=', now()->subHour())
                ->count();

            $pendingWebhooks = WebhookLog::pending()->count();

            $avgProcessingTime = WebhookLog::whereNotNull('processed_at')
                ->whereNotNull('webhook_received_at')
                ->where('webhook_received_at', '>=', now()->subDay())
                ->get()
                ->avg(function ($log) {
                    return $log->processing_time;
                });

            $readyForRetry = WebhookLog::readyForRetry()->count();

            return [
                'recent_failures' => $recentFailed,
                'pending_webhooks' => $pendingWebhooks,
                'avg_processing_time' => round($avgProcessingTime ?? 0, 2),
                'ready_for_retry' => $readyForRetry,
                'health_status' => $recentFailed > 50 ? 'critical' : ($recentFailed > 10 ? 'warning' : 'good'),
            ];
        });

        return response()->json($metrics);
    }

    /**
     * Get recent activity
     */
    public function recentActivity()
    {
        $recentLogs = WebhookLog::orderBy('webhook_received_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'html' => view('admin.webhook-logs.recent-activity', compact('recentLogs'))->render(),
        ]);
    }

    /**
     * Get webhook statistics
     */
    private function getStatistics()
    {
        return Cache::remember('webhook_log_statistics', 300, function () {
            return WebhookLog::getStatistics();
        });
    }
}
