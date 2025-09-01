<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display the main revenue analytics dashboard
     */
    public function index(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $stats = $this->getRevenueStats($dateRange);
        $trends = $this->getRevenueTrends($dateRange);
        $topCustomers = $this->getTopCustomers($dateRange);
        $channelRevenue = $this->getChannelRevenue($dateRange);

        return view('admin.revenue.index', compact(
            'stats',
            'trends',
            'topCustomers',
            'channelRevenue',
            'dateRange'
        ));
    }

    /**
     * Display monthly revenue analysis
     */
    public function monthly(Request $request)
    {
        $year = $request->get('year', now()->year);
        $monthlyStats = $this->getMonthlyStats($year);
        $monthlyTrends = $this->getMonthlyTrends($year);
        $yearComparison = $this->getYearComparison($year);

        return view('admin.revenue.monthly', compact(
            'monthlyStats',
            'monthlyTrends',
            'yearComparison',
            'year'
        ));
    }

    /**
     * Display customer revenue analysis
     */
    public function customers(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $customerStats = $this->getCustomerStats($dateRange);
        $customerSegments = $this->getCustomerSegments($dateRange);
        $lifetimeValue = $this->getCustomerLifetimeValue($dateRange);

        return view('admin.revenue.customers', compact(
            'customerStats',
            'customerSegments',
            'lifetimeValue',
            'dateRange'
        ));
    }

    /**
     * Display forecasting and predictions
     */
    public function forecast(Request $request)
    {
        $months = $request->get('months', 6);
        $forecastData = $this->generateRevenueForecast($months);
        $trendAnalysis = $this->getTrendAnalysis();
        $seasonalPatterns = $this->getSeasonalPatterns();

        return view('admin.revenue.forecast', compact(
            'forecastData',
            'trendAnalysis',
            'seasonalPatterns',
            'months'
        ));
    }

    /**
     * API endpoint for revenue statistics
     */
    public function getStats(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $cacheKey = 'revenue_stats_'.md5(serialize($dateRange));

        return Cache::remember($cacheKey, 300, function () use ($dateRange) {
            return response()->json([
                'stats' => $this->getRevenueStats($dateRange),
                'trends' => $this->getRevenueTrends($dateRange),
                'channel_breakdown' => $this->getChannelRevenue($dateRange),
                'growth_metrics' => $this->getGrowthMetrics($dateRange),
            ]);
        });
    }

    /**
     * API endpoint for chart data
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'monthly');
        $dateRange = $this->getDateRange($request);

        switch ($type) {
            case 'monthly':
                return response()->json($this->getMonthlyChartData($dateRange));
            case 'weekly':
                return response()->json($this->getWeeklyChartData($dateRange));
            case 'daily':
                return response()->json($this->getDailyChartData($dateRange));
            case 'channel':
                return response()->json($this->getChannelChartData($dateRange));
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    /**
     * Export revenue data to CSV
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'summary');
        $dateRange = $this->getDateRange($request);

        $filename = 'revenue_'.$type.'_'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($type, $dateRange) {
            $file = fopen('php://output', 'w');

            switch ($type) {
                case 'summary':
                    $this->exportSummary($file, $dateRange);
                    break;
                case 'customers':
                    $this->exportCustomers($file, $dateRange);
                    break;
                case 'channels':
                    $this->exportChannels($file, $dateRange);
                    break;
                case 'monthly':
                    $this->exportMonthly($file, $dateRange);
                    break;
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get date range from request
     */
    private function getDateRange(Request $request)
    {
        $period = $request->get('period', '30_days');

        switch ($period) {
            case '7_days':
                return [
                    'start' => now()->subDays(7)->startOfDay(),
                    'end' => now()->endOfDay(),
                ];
            case '30_days':
                return [
                    'start' => now()->subDays(30)->startOfDay(),
                    'end' => now()->endOfDay(),
                ];
            case '90_days':
                return [
                    'start' => now()->subDays(90)->startOfDay(),
                    'end' => now()->endOfDay(),
                ];
            case 'this_year':
                return [
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear(),
                ];
            case 'custom':
                return [
                    'start' => Carbon::parse($request->get('start_date'))->startOfDay(),
                    'end' => Carbon::parse($request->get('end_date'))->endOfDay(),
                ];
            default:
                return [
                    'start' => now()->subDays(30)->startOfDay(),
                    'end' => now()->endOfDay(),
                ];
        }
    }

    /**
     * Get main revenue statistics
     */
    private function getRevenueStats($dateRange)
    {
        // Calculate total revenue from campaigns
        $totalRevenue = $this->calculateTotalRevenue($dateRange);
        $previousRevenue = $this->calculateTotalRevenue([
            'start' => $dateRange['start']->copy()->subDays($dateRange['start']->diffInDays($dateRange['end'])),
            'end' => $dateRange['start']->copy()->subDay(),
        ]);

        $averageOrderValue = $this->calculateAverageOrderValue($dateRange);
        $customerCount = $this->getActiveCustomerCount($dateRange);
        $conversionRate = $this->calculateConversionRate($dateRange);

        return [
            'total_revenue' => $totalRevenue,
            'previous_revenue' => $previousRevenue,
            'revenue_growth' => $previousRevenue > 0 ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0,
            'average_order_value' => $averageOrderValue,
            'customer_count' => $customerCount,
            'conversion_rate' => $conversionRate,
            'revenue_per_customer' => $customerCount > 0 ? $totalRevenue / $customerCount : 0,
        ];
    }

    /**
     * Calculate total revenue from all channels
     */
    private function calculateTotalRevenue($dateRange)
    {
        // This is a simplified calculation - in a real scenario, you'd have order/transaction data
        // For demonstration, we'll calculate based on campaign performance and estimated values

        $emailRevenue = EmailCampaign::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum(DB::raw('COALESCE(opened_count, 0) * 0.1')); // $0.1 per email open

        $smsRevenue = SmsMessage::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'delivered')
            ->count() * 0.05; // $0.05 per SMS

        $whatsappRevenue = WhatsAppMessage::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'delivered')
            ->count() * 0.02; // $0.02 per WhatsApp message

        return $emailRevenue + $smsRevenue + $whatsappRevenue;
    }

    /**
     * Calculate average order value
     */
    private function calculateAverageOrderValue($dateRange)
    {
        // Simplified calculation based on successful campaigns
        $successfulCampaigns = EmailCampaign::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'sent')
            ->count();

        return $successfulCampaigns > 0 ? $this->calculateTotalRevenue($dateRange) / $successfulCampaigns : 0;
    }

    /**
     * Get active customer count
     */
    private function getActiveCustomerCount($dateRange)
    {
        return Contact::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'active')
            ->count();
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate($dateRange)
    {
        $totalCampaigns = EmailCampaign::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        $successfulCampaigns = EmailCampaign::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'sent')
            ->where('opened_count', '>', 0)
            ->count();

        return $totalCampaigns > 0 ? ($successfulCampaigns / $totalCampaigns) * 100 : 0;
    }

    /**
     * Get revenue trends over time
     */
    private function getRevenueTrends($dateRange)
    {
        $days = $dateRange['start']->diffInDays($dateRange['end']);
        $interval = $days > 60 ? 'week' : 'day';

        return $this->getRevenueByInterval($dateRange, $interval);
    }

    /**
     * Get revenue by time interval
     */
    private function getRevenueByInterval($dateRange, $interval = 'day')
    {
        $data = [];
        $current = $dateRange['start']->copy();

        while ($current->lte($dateRange['end'])) {
            $next = $interval === 'week' ? $current->copy()->addWeek() : $current->copy()->addDay();

            $revenue = $this->calculateTotalRevenue([
                'start' => $current,
                'end' => min($next, $dateRange['end']),
            ]);

            $data[] = [
                'date' => $current->format($interval === 'week' ? 'Y-W' : 'Y-m-d'),
                'revenue' => $revenue,
                'formatted_date' => $current->format($interval === 'week' ? 'M d, Y' : 'M d'),
            ];

            $current = $next;
        }

        return $data;
    }

    /**
     * Get top customers by revenue
     */
    private function getTopCustomers($dateRange)
    {
        // This would typically join with orders/transactions table
        // For now, we'll get contacts with most campaign interactions
        return Contact::select('contacts.*')
            ->selectRaw('COUNT(email_logs.id) as email_interactions')
            ->selectRaw('COUNT(sms_messages.id) as sms_interactions')
            ->leftJoin('email_logs', function ($join) use ($dateRange) {
                $join->on('contacts.id', '=', 'email_logs.contact_id')
                    ->whereBetween('email_logs.created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->leftJoin('sms_messages', function ($join) use ($dateRange) {
                $join->on('contacts.phone', '=', 'sms_messages.to_number')
                    ->whereBetween('sms_messages.created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->groupBy('contacts.id')
            ->orderByRaw('(email_interactions + sms_interactions) DESC')
            ->limit(10)
            ->get()
            ->map(function ($contact) {
                $contact->estimated_revenue = ($contact->email_interactions * 0.1) + ($contact->sms_interactions * 0.05);

                return $contact;
            });
    }

    /**
     * Get revenue by channel
     */
    private function getChannelRevenue($dateRange)
    {
        return [
            'email' => [
                'revenue' => EmailCampaign::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->sum(DB::raw('COALESCE(opened_count, 0) * 0.1')),
                'count' => EmailCampaign::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            ],
            'sms' => [
                'revenue' => SmsMessage::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'delivered')->count() * 0.05,
                'count' => SmsMessage::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'delivered')->count(),
            ],
            'whatsapp' => [
                'revenue' => WhatsAppMessage::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'delivered')->count() * 0.02,
                'count' => WhatsAppMessage::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'delivered')->count(),
            ],
        ];
    }

    /**
     * Get monthly statistics for a year
     */
    private function getMonthlyStats($year)
    {
        $stats = [];
        for ($month = 1; $month <= 12; $month++) {
            $dateRange = [
                'start' => Carbon::create($year, $month, 1)->startOfMonth(),
                'end' => Carbon::create($year, $month, 1)->endOfMonth(),
            ];

            $stats[] = [
                'month' => $month,
                'month_name' => Carbon::create($year, $month, 1)->format('F'),
                'revenue' => $this->calculateTotalRevenue($dateRange),
                'customers' => $this->getActiveCustomerCount($dateRange),
                'campaigns' => EmailCampaign::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            ];
        }

        return $stats;
    }

    /**
     * Get monthly trends for comparison
     */
    private function getMonthlyTrends($year)
    {
        $currentYear = $this->getMonthlyStats($year);
        $previousYear = $this->getMonthlyStats($year - 1);

        return [
            'current' => $currentYear,
            'previous' => $previousYear,
        ];
    }

    /**
     * Get year comparison data
     */
    private function getYearComparison($year)
    {
        $currentYearTotal = collect($this->getMonthlyStats($year))->sum('revenue');
        $previousYearTotal = collect($this->getMonthlyStats($year - 1))->sum('revenue');

        return [
            'current_year' => $currentYearTotal,
            'previous_year' => $previousYearTotal,
            'growth_percentage' => $previousYearTotal > 0 ? (($currentYearTotal - $previousYearTotal) / $previousYearTotal) * 100 : 0,
        ];
    }

    /**
     * Get customer statistics
     */
    private function getCustomerStats($dateRange)
    {
        $newCustomers = Contact::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();
        $activeCustomers = Contact::where('status', 'active')->count();
        $totalCustomers = Contact::count();

        return [
            'new_customers' => $newCustomers,
            'active_customers' => $activeCustomers,
            'total_customers' => $totalCustomers,
            'customer_growth' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0,
        ];
    }

    /**
     * Get customer segments
     */
    private function getCustomerSegments($dateRange)
    {
        return [
            'vip' => Contact::where('tags', 'like', '%VIP%')->count(),
            'enterprise' => Contact::where('company_size', 'large')->count(),
            'smb' => Contact::where('company_size', 'small')->count(),
            'individual' => Contact::whereNull('company')->count(),
        ];
    }

    /**
     * Calculate customer lifetime value
     */
    private function getCustomerLifetimeValue($dateRange)
    {
        $totalRevenue = $this->calculateTotalRevenue($dateRange);
        $customerCount = $this->getActiveCustomerCount($dateRange);

        return $customerCount > 0 ? $totalRevenue / $customerCount : 0;
    }

    /**
     * Generate revenue forecast
     */
    private function generateRevenueForecast($months)
    {
        // Get historical data for trend analysis
        $historical = $this->getRevenueByInterval([
            'start' => now()->subMonths(12),
            'end' => now(),
        ], 'month');

        $forecast = [];
        $baseRevenue = collect($historical)->avg('revenue');
        $growth = $this->calculateGrowthRate($historical);

        for ($i = 1; $i <= $months; $i++) {
            $forecastDate = now()->addMonths($i);
            $seasonalMultiplier = $this->getSeasonalMultiplier($forecastDate->month);

            $predictedRevenue = $baseRevenue * (1 + $growth) * $seasonalMultiplier;

            $forecast[] = [
                'date' => $forecastDate->format('Y-m'),
                'predicted_revenue' => round($predictedRevenue, 2),
                'confidence' => max(0.5, 1 - ($i * 0.1)), // Confidence decreases over time
            ];
        }

        return $forecast;
    }

    /**
     * Calculate growth rate from historical data
     */
    private function calculateGrowthRate($historical)
    {
        if (count($historical) < 2) {
            return 0.05;
        } // Default 5% growth

        $firstRevenue = $historical[0]['revenue'];
        $lastRevenue = end($historical)['revenue'];
        $periods = count($historical) - 1;

        return $periods > 0 && $firstRevenue > 0 ?
            pow(($lastRevenue / $firstRevenue), (1 / $periods)) - 1 : 0.05;
    }

    /**
     * Get seasonal multiplier for month
     */
    private function getSeasonalMultiplier($month)
    {
        // Seasonal patterns (can be customized based on business)
        $seasonalFactors = [
            1 => 0.85,  // January - post-holiday dip
            2 => 0.90,  // February
            3 => 1.10,  // March - spring increase
            4 => 1.05,  // April
            5 => 1.15,  // May
            6 => 1.20,  // June
            7 => 1.10,  // July
            8 => 1.05,  // August
            9 => 1.15,  // September - back to business
            10 => 1.25, // October
            11 => 1.35, // November - Black Friday
            12 => 1.40,  // December - holiday season
        ];

        return $seasonalFactors[$month] ?? 1.0;
    }

    /**
     * Get trend analysis
     */
    private function getTrendAnalysis()
    {
        $last6Months = $this->getRevenueByInterval([
            'start' => now()->subMonths(6),
            'end' => now(),
        ], 'month');

        $revenues = collect($last6Months)->pluck('revenue')->toArray();

        return [
            'trend' => $this->calculateTrend($revenues),
            'volatility' => $this->calculateVolatility($revenues),
            'average' => array_sum($revenues) / count($revenues),
        ];
    }

    /**
     * Calculate trend direction
     */
    private function calculateTrend($data)
    {
        if (count($data) < 2) {
            return 'stable';
        }

        $increases = 0;
        $decreases = 0;

        for ($i = 1; $i < count($data); $i++) {
            if ($data[$i] > $data[$i - 1]) {
                $increases++;
            } elseif ($data[$i] < $data[$i - 1]) {
                $decreases++;
            }
        }

        if ($increases > $decreases) {
            return 'increasing';
        }
        if ($decreases > $increases) {
            return 'decreasing';
        }

        return 'stable';
    }

    /**
     * Calculate volatility
     */
    private function calculateVolatility($data)
    {
        if (count($data) < 2) {
            return 0;
        }

        $mean = array_sum($data) / count($data);
        $variance = array_sum(array_map(function ($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $data)) / count($data);

        return sqrt($variance);
    }

    /**
     * Get seasonal patterns
     */
    private function getSeasonalPatterns()
    {
        $patterns = [];
        for ($month = 1; $month <= 12; $month++) {
            $patterns[] = [
                'month' => $month,
                'month_name' => Carbon::create(null, $month, 1)->format('F'),
                'multiplier' => $this->getSeasonalMultiplier($month),
                'description' => $this->getSeasonalDescription($month),
            ];
        }

        return $patterns;
    }

    /**
     * Get seasonal description
     */
    private function getSeasonalDescription($month)
    {
        $descriptions = [
            1 => 'Post-holiday recovery period',
            2 => 'Winter business planning',
            3 => 'Spring growth begins',
            4 => 'Q1 reporting season',
            5 => 'Spring peak activity',
            6 => 'Mid-year strong performance',
            7 => 'Summer steady growth',
            8 => 'Late summer activity',
            9 => 'Back-to-business surge',
            10 => 'Q3 strong performance',
            11 => 'Pre-holiday peak',
            12 => 'Holiday season maximum',
        ];

        return $descriptions[$month] ?? 'Standard business period';
    }

    /**
     * Get growth metrics
     */
    private function getGrowthMetrics($dateRange)
    {
        $currentRevenue = $this->calculateTotalRevenue($dateRange);
        $previousRevenue = $this->calculateTotalRevenue([
            'start' => $dateRange['start']->copy()->subDays($dateRange['start']->diffInDays($dateRange['end'])),
            'end' => $dateRange['start']->copy()->subDay(),
        ]);

        return [
            'revenue_growth' => $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0,
            'customer_growth' => $this->getCustomerGrowthRate($dateRange),
            'campaign_efficiency' => $this->getCampaignEfficiency($dateRange),
        ];
    }

    /**
     * Get customer growth rate
     */
    private function getCustomerGrowthRate($dateRange)
    {
        $currentCustomers = $this->getActiveCustomerCount($dateRange);
        $previousCustomers = Contact::whereBetween('created_at', [
            $dateRange['start']->copy()->subDays($dateRange['start']->diffInDays($dateRange['end'])),
            $dateRange['start']->copy()->subDay(),
        ])->count();

        return $previousCustomers > 0 ? (($currentCustomers - $previousCustomers) / $previousCustomers) * 100 : 0;
    }

    /**
     * Get campaign efficiency
     */
    private function getCampaignEfficiency($dateRange)
    {
        $totalCampaigns = EmailCampaign::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();
        $totalRevenue = $this->calculateTotalRevenue($dateRange);

        return $totalCampaigns > 0 ? $totalRevenue / $totalCampaigns : 0;
    }

    // Chart data methods
    private function getMonthlyChartData($dateRange)
    {
        return $this->getRevenueByInterval($dateRange, 'month');
    }

    private function getWeeklyChartData($dateRange)
    {
        return $this->getRevenueByInterval($dateRange, 'week');
    }

    private function getDailyChartData($dateRange)
    {
        return $this->getRevenueByInterval($dateRange, 'day');
    }

    private function getChannelChartData($dateRange)
    {
        $channelData = $this->getChannelRevenue($dateRange);

        return [
            'labels' => array_keys($channelData),
            'data' => array_map(function ($channel) {
                return $channel['revenue'];
            }, $channelData),
        ];
    }

    // Export methods
    private function exportSummary($file, $dateRange)
    {
        fputcsv($file, ['Metric', 'Value', 'Period']);

        $stats = $this->getRevenueStats($dateRange);
        foreach ($stats as $key => $value) {
            fputcsv($file, [
                ucwords(str_replace('_', ' ', $key)),
                is_numeric($value) ? number_format($value, 2) : $value,
                $dateRange['start']->format('Y-m-d').' to '.$dateRange['end']->format('Y-m-d'),
            ]);
        }
    }

    private function exportCustomers($file, $dateRange)
    {
        fputcsv($file, ['Customer Name', 'Email', 'Company', 'Email Interactions', 'SMS Interactions', 'Estimated Revenue']);

        $customers = $this->getTopCustomers($dateRange);
        foreach ($customers as $customer) {
            fputcsv($file, [
                $customer->first_name.' '.$customer->last_name,
                $customer->email,
                $customer->company ?? 'N/A',
                $customer->email_interactions,
                $customer->sms_interactions,
                number_format($customer->estimated_revenue, 2),
            ]);
        }
    }

    private function exportChannels($file, $dateRange)
    {
        fputcsv($file, ['Channel', 'Revenue', 'Count']);

        $channels = $this->getChannelRevenue($dateRange);
        foreach ($channels as $name => $data) {
            fputcsv($file, [
                ucfirst($name),
                number_format($data['revenue'], 2),
                $data['count'],
            ]);
        }
    }

    private function exportMonthly($file, $dateRange)
    {
        fputcsv($file, ['Month', 'Revenue', 'Customers', 'Campaigns']);

        $year = $dateRange['start']->year;
        $monthlyStats = $this->getMonthlyStats($year);

        foreach ($monthlyStats as $month) {
            fputcsv($file, [
                $month['month_name'],
                number_format($month['revenue'], 2),
                $month['customers'],
                $month['campaigns'],
            ]);
        }
    }
}
