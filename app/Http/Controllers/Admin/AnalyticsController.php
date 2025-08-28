<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use App\Models\ContactSegment;
use App\Models\EmailLog;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Analytics Dashboard
     */
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $period = $request->get('period', '30days');
        
        $cacheKey = "analytics_dashboard_{$period}_{$startDate}_{$endDate}";
        
        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            return [
                'overview' => $this->getOverviewStats($startDate, $endDate),
                'growth' => $this->getGrowthMetrics($startDate, $endDate),
                'engagement' => $this->getEngagementMetrics($startDate, $endDate),
                'channels' => $this->getChannelPerformance($startDate, $endDate),
                'segments' => $this->getSegmentAnalytics($startDate, $endDate),
                'users' => $this->getUserActivityStats($startDate, $endDate),
            ];
        });

        return view('admin.analytics.index', compact('data', 'startDate', 'endDate', 'period'));
    }

    /**
     * Revenue Analytics
     */
    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(90)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        
        $cacheKey = "revenue_analytics_{$startDate}_{$endDate}";
        
        $data = Cache::remember($cacheKey, 600, function () use ($startDate, $endDate) {
            return [
                'overview' => $this->getRevenueOverview($startDate, $endDate),
                'trends' => $this->getRevenueTrends($startDate, $endDate),
                'channels' => $this->getRevenueByChannel($startDate, $endDate),
                'segments' => $this->getRevenueBySegment($startDate, $endDate),
                'forecasting' => $this->getRevenueForecast($startDate, $endDate),
            ];
        });

        return view('admin.analytics.revenue', compact('data', 'startDate', 'endDate'));
    }

    /**
     * Campaign Analytics
     */
    public function campaigns(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $channel = $request->get('channel', 'all');
        
        $cacheKey = "campaign_analytics_{$channel}_{$startDate}_{$endDate}";
        
        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $channel) {
            return [
                'performance' => $this->getCampaignPerformance($startDate, $endDate, $channel),
                'engagement' => $this->getCampaignEngagement($startDate, $endDate, $channel),
                'conversion' => $this->getCampaignConversion($startDate, $endDate, $channel),
                'cost_analysis' => $this->getCampaignCostAnalysis($startDate, $endDate, $channel),
                'top_campaigns' => $this->getTopCampaigns($startDate, $endDate, $channel),
            ];
        });

        return view('admin.analytics.campaigns', compact('data', 'startDate', 'endDate', 'channel'));
    }

    /**
     * Contact Analytics
     */
    public function contacts(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        
        $cacheKey = "contact_analytics_{$startDate}_{$endDate}";
        
        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            return [
                'acquisition' => $this->getContactAcquisition($startDate, $endDate),
                'lifecycle' => $this->getContactLifecycle($startDate, $endDate),
                'engagement' => $this->getContactEngagement($startDate, $endDate),
                'segmentation' => $this->getContactSegmentation($startDate, $endDate),
                'quality' => $this->getContactQuality($startDate, $endDate),
            ];
        });

        return view('admin.analytics.contacts', compact('data', 'startDate', 'endDate'));
    }

    /**
     * Performance Analytics API
     */
    public function performance(Request $request)
    {
        $metric = $request->get('metric', 'overall');
        $period = $request->get('period', '7days');
        
        $data = match ($metric) {
            'email' => $this->getEmailPerformanceData($period),
            'sms' => $this->getSmsPerformanceData($period),
            'whatsapp' => $this->getWhatsAppPerformanceData($period),
            'conversion' => $this->getConversionData($period),
            default => $this->getOverallPerformanceData($period),
        };

        return response()->json($data);
    }

    /**
     * Real-time Analytics API
     */
    public function realtime(Request $request)
    {
        $data = [
            'active_campaigns' => $this->getActiveCampaignsCount(),
            'online_users' => $this->getOnlineUsersCount(),
            'recent_activities' => $this->getRecentActivities(10),
            'live_metrics' => $this->getLiveMetrics(),
            'system_status' => $this->getSystemStatus(),
        ];

        return response()->json($data);
    }

    /**
     * Export Analytics Data
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'overview');
        $format = $request->get('format', 'csv');
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        
        $data = match ($type) {
            'revenue' => $this->exportRevenueData($startDate, $endDate),
            'campaigns' => $this->exportCampaignData($startDate, $endDate),
            'contacts' => $this->exportContactData($startDate, $endDate),
            default => $this->exportOverviewData($startDate, $endDate),
        };

        $filename = "analytics_{$type}_{$startDate}_to_{$endDate}.{$format}";
        
        if ($format === 'json') {
            return response()->json($data)
                ->header('Content-Disposition', "attachment; filename={$filename}");
        }
        
        // CSV Export
        $csv = $this->arrayToCsv($data);
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    // Private helper methods for analytics calculations

    private function getOverviewStats($startDate, $endDate)
    {
        return [
            'total_contacts' => Contact::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_campaigns' => EmailCampaign::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_messages' => $this->getTotalMessagesCount($startDate, $endDate),
            'engagement_rate' => $this->calculateOverallEngagementRate($startDate, $endDate),
            'revenue' => $this->calculateTotalRevenue($startDate, $endDate),
            'conversion_rate' => $this->calculateOverallConversionRate($startDate, $endDate),
            'active_users' => User::whereHas('systemLogs', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })->count(),
            'growth_rate' => $this->calculateGrowthRate($startDate, $endDate),
        ];
    }

    private function getGrowthMetrics($startDate, $endDate)
    {
        $previousPeriod = Carbon::parse($startDate)->subDays(
            Carbon::parse($endDate)->diffInDays(Carbon::parse($startDate))
        );

        $current = Contact::whereBetween('created_at', [$startDate, $endDate])->count();
        $previous = Contact::whereBetween('created_at', [$previousPeriod, $startDate])->count();

        return [
            'contact_growth' => $this->calculatePercentageChange($current, $previous),
            'daily_growth' => $this->getDailyGrowthTrend($startDate, $endDate),
            'weekly_growth' => $this->getWeeklyGrowthTrend($startDate, $endDate),
            'monthly_growth' => $this->getMonthlyGrowthTrend($startDate, $endDate),
        ];
    }

    private function getEngagementMetrics($startDate, $endDate)
    {
        $totalEmails = EmailLog::whereBetween('sent_at', [$startDate, $endDate])->count();
        $openedEmails = EmailLog::whereBetween('sent_at', [$startDate, $endDate])
            ->whereNotNull('opened_at')->count();
        $clickedEmails = EmailLog::whereBetween('sent_at', [$startDate, $endDate])
            ->whereNotNull('clicked_at')->count();

        return [
            'email_open_rate' => $totalEmails > 0 ? round(($openedEmails / $totalEmails) * 100, 2) : 0,
            'email_click_rate' => $totalEmails > 0 ? round(($clickedEmails / $totalEmails) * 100, 2) : 0,
            'sms_delivery_rate' => $this->calculateSmsDeliveryRate($startDate, $endDate),
            'whatsapp_response_rate' => $this->calculateWhatsAppResponseRate($startDate, $endDate),
            'overall_engagement' => $this->calculateOverallEngagementRate($startDate, $endDate),
        ];
    }

    private function getChannelPerformance($startDate, $endDate)
    {
        return [
            'email' => [
                'sent' => EmailLog::whereBetween('sent_at', [$startDate, $endDate])->count(),
                'delivered' => EmailLog::whereBetween('sent_at', [$startDate, $endDate])
                    ->where('status', 'delivered')->count(),
                'opened' => EmailLog::whereBetween('sent_at', [$startDate, $endDate])
                    ->whereNotNull('opened_at')->count(),
                'clicked' => EmailLog::whereBetween('sent_at', [$startDate, $endDate])
                    ->whereNotNull('clicked_at')->count(),
            ],
            'sms' => [
                'sent' => SmsMessage::whereBetween('created_at', [$startDate, $endDate])->count(),
                'delivered' => SmsMessage::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'delivered')->count(),
            ],
            'whatsapp' => [
                'sent' => WhatsAppMessage::whereBetween('created_at', [$startDate, $endDate])
                    ->where('direction', 'outbound')->count(),
                'delivered' => WhatsAppMessage::whereBetween('created_at', [$startDate, $endDate])
                    ->where('direction', 'outbound')
                    ->where('status', 'delivered')->count(),
                'received' => WhatsAppMessage::whereBetween('created_at', [$startDate, $endDate])
                    ->where('direction', 'inbound')->count(),
            ],
        ];
    }

    private function getSegmentAnalytics($startDate, $endDate)
    {
        return ContactSegment::with('contacts')
            ->get()
            ->map(function ($segment) use ($startDate, $endDate) {
                $contacts = $segment->contacts()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                return [
                    'name' => $segment->name,
                    'contacts' => $contacts,
                    'engagement' => $this->getSegmentEngagement($segment->id, $startDate, $endDate),
                    'conversion' => $this->getSegmentConversion($segment->id, $startDate, $endDate),
                ];
            });
    }

    private function getUserActivityStats($startDate, $endDate)
    {
        return User::with(['systemLogs' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'activities' => $user->systemLogs->count(),
                    'last_active' => $user->systemLogs->max('created_at'),
                    'campaigns_created' => $user->emailCampaigns()->count(),
                    'contacts_created' => $user->contacts()->count(),
                ];
            });
    }

    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getDailyGrowthTrend($startDate, $endDate)
    {
        return Contact::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    private function getWeeklyGrowthTrend($startDate, $endDate)
    {
        return Contact::selectRaw('YEARWEEK(created_at) as week, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->pluck('count', 'week');
    }

    private function getMonthlyGrowthTrend($startDate, $endDate)
    {
        return Contact::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');
    }

    private function getTotalMessagesCount($startDate, $endDate)
    {
        $emails = EmailLog::whereBetween('sent_at', [$startDate, $endDate])->count();
        $sms = SmsMessage::whereBetween('created_at', [$startDate, $endDate])->count();
        $whatsapp = WhatsAppMessage::whereBetween('created_at', [$startDate, $endDate])->count();
        
        return $emails + $sms + $whatsapp;
    }

    private function calculateOverallEngagementRate($startDate, $endDate)
    {
        // Simplified engagement calculation - can be enhanced based on business logic
        $totalMessages = $this->getTotalMessagesCount($startDate, $endDate);
        $engaged = EmailLog::whereBetween('sent_at', [$startDate, $endDate])
            ->whereNotNull('opened_at')->count();
        
        return $totalMessages > 0 ? round(($engaged / $totalMessages) * 100, 2) : 0;
    }

    private function calculateTotalRevenue($startDate, $endDate)
    {
        // Placeholder - implement based on your revenue tracking
        return 0.00;
    }

    private function calculateOverallConversionRate($startDate, $endDate)
    {
        // Placeholder - implement based on your conversion tracking
        return 0.00;
    }

    private function calculateGrowthRate($startDate, $endDate)
    {
        $current = Contact::whereBetween('created_at', [$startDate, $endDate])->count();
        $days = Carbon::parse($endDate)->diffInDays(Carbon::parse($startDate));
        
        return $days > 0 ? round($current / $days, 2) : 0;
    }

    private function calculateSmsDeliveryRate($startDate, $endDate)
    {
        $total = SmsMessage::whereBetween('created_at', [$startDate, $endDate])->count();
        $delivered = SmsMessage::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'delivered')->count();
        
        return $total > 0 ? round(($delivered / $total) * 100, 2) : 0;
    }

    private function calculateWhatsAppResponseRate($startDate, $endDate)
    {
        $sent = WhatsAppMessage::whereBetween('created_at', [$startDate, $endDate])
            ->where('direction', 'outbound')->count();
        $received = WhatsAppMessage::whereBetween('created_at', [$startDate, $endDate])
            ->where('direction', 'inbound')->count();
        
        return $sent > 0 ? round(($received / $sent) * 100, 2) : 0;
    }

    private function getSegmentEngagement($segmentId, $startDate, $endDate)
    {
        // Simplified calculation - enhance based on business logic
        return rand(15, 85); // Placeholder
    }

    private function getSegmentConversion($segmentId, $startDate, $endDate)
    {
        // Simplified calculation - enhance based on business logic
        return rand(1, 25); // Placeholder
    }

    private function arrayToCsv($data)
    {
        if (empty($data)) return '';
        
        $output = fopen('php://temp', 'w');
        
        // Write headers
        fputcsv($output, array_keys((array) $data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, (array) $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    // Placeholder methods for additional functionality
    private function getRevenueOverview($startDate, $endDate) { return []; }
    private function getRevenueTrends($startDate, $endDate) { return []; }
    private function getRevenueByChannel($startDate, $endDate) { return []; }
    private function getRevenueBySegment($startDate, $endDate) { return []; }
    private function getRevenueForecast($startDate, $endDate) { return []; }
    private function getCampaignPerformance($startDate, $endDate, $channel) { return []; }
    private function getCampaignEngagement($startDate, $endDate, $channel) { return []; }
    private function getCampaignConversion($startDate, $endDate, $channel) { return []; }
    private function getCampaignCostAnalysis($startDate, $endDate, $channel) { return []; }
    private function getTopCampaigns($startDate, $endDate, $channel) { return []; }
    private function getContactAcquisition($startDate, $endDate) { return []; }
    private function getContactLifecycle($startDate, $endDate) { return []; }
    private function getContactEngagement($startDate, $endDate) { return []; }
    private function getContactSegmentation($startDate, $endDate) { return []; }
    private function getContactQuality($startDate, $endDate) { return []; }
    private function getEmailPerformanceData($period) { return []; }
    private function getSmsPerformanceData($period) { return []; }
    private function getWhatsAppPerformanceData($period) { return []; }
    private function getConversionData($period) { return []; }
    private function getOverallPerformanceData($period) { return []; }
    private function getActiveCampaignsCount() { return rand(5, 15); }
    private function getOnlineUsersCount() { return rand(2, 10); }
    private function getRecentActivities($limit) { return []; }
    private function getLiveMetrics() { return []; }
    private function getSystemStatus() { return ['status' => 'healthy']; }
    private function exportRevenueData($startDate, $endDate) { return []; }
    private function exportCampaignData($startDate, $endDate) { return []; }
    private function exportContactData($startDate, $endDate) { return []; }
    private function exportOverviewData($startDate, $endDate) { return []; }
}
