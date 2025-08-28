<?php

namespace App\Http\Controllers;

use App\Events\DashboardStatsUpdated;
use App\Models\Communication;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get date ranges
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Contacts statistics
        $totalContacts = Contact::where('created_by', $user->id)
            ->orWhere('assigned_to', $user->id)
            ->count();

        $newContactsToday = Contact::where('created_by', $user->id)
            ->whereDate('created_at', $today)
            ->count();

        $activeContacts = Contact::where('created_by', $user->id)
            ->orWhere('assigned_to', $user->id)
            ->where('status', 'active')
            ->count();

        // Email statistics
        $totalEmailsSent = EmailCampaign::where('created_by', $user->id)
            ->sum('sent_count');

        $emailsSentThisMonth = EmailCampaign::where('created_by', $user->id)
            ->where('created_at', '>=', $thisMonth)
            ->sum('sent_count');

        $emailOpenRate = $this->calculateEmailOpenRate($user->id);

        // SMS statistics
        $totalSmsSent = SmsMessage::whereHas('contact', function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        })->count();

        $smsSentThisMonth = SmsMessage::whereHas('contact', function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        })->where('created_at', '>=', $thisMonth)->count();

        // WhatsApp statistics
        $totalWhatsAppMessages = WhatsAppMessage::whereHas('contact', function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        })->count();

        $whatsAppMessagesToday = WhatsAppMessage::whereHas('contact', function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        })->whereDate('created_at', $today)->count();

        // Recent activities
        $recentCommunications = Communication::whereHas('contact', function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        })
            ->with(['contact', 'user'])
            ->latest()
            ->take(10)
            ->get();

        // Recent contacts
        $recentContacts = Contact::where('created_by', $user->id)
            ->orWhere('assigned_to', $user->id)
            ->with('creator')
            ->latest()
            ->take(5)
            ->get();

        // Active campaigns
        $activeCampaigns = EmailCampaign::where('created_by', $user->id)
            ->whereIn('status', ['sending', 'scheduled'])
            ->with('smtpConfig')
            ->latest()
            ->take(3)
            ->get();

        // Chart data - Communications by day (last 30 days)
        $communicationsChart = $this->getCommunicationsChartData($user->id);

        // Chart data - Contacts growth (last 30 days)
        $contactsChart = $this->getContactsChartData($user->id);

        // Quick stats for comparison
        $stats = [
            'contacts' => [
                'total' => $totalContacts,
                'new_today' => $newContactsToday,
                'active' => $activeContacts,
                'inactive' => $totalContacts - $activeContacts,
            ],
            'email' => [
                'total_sent' => $totalEmailsSent,
                'sent_this_month' => $emailsSentThisMonth,
                'open_rate' => $emailOpenRate,
                'active_campaigns' => $activeCampaigns->count(),
            ],
            'sms' => [
                'total_sent' => $totalSmsSent,
                'sent_this_month' => $smsSentThisMonth,
                'delivery_rate' => $this->calculateSmsDeliveryRate($user->id),
            ],
            'whatsapp' => [
                'total_messages' => $totalWhatsAppMessages,
                'messages_today' => $whatsAppMessagesToday,
                'active_sessions' => $user->whatsappSessions()->active()->connected()->count(),
            ],
        ];

        return view('dashboard.index', compact(
            'stats',
            'recentCommunications',
            'recentContacts',
            'activeCampaigns',
            'communicationsChart',
            'contactsChart'
        ));
    }

    /**
     * Calculate comprehensive dashboard statistics
     */
    private function calculateStats($user)
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Contacts statistics with comparisons
        $contactsThisMonth = Contact::where('created_by', $user->id)
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $contactsLastMonth = Contact::where('created_by', $user->id)
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->count();

        $contactsGrowth = $contactsLastMonth > 0 ?
            round((($contactsThisMonth - $contactsLastMonth) / $contactsLastMonth) * 100, 1) : 0;

        // Total contacts
        $totalContacts = Contact::where('created_by', $user->id)
            ->orWhere('assigned_to', $user->id)
            ->count();

        $activeContacts = Contact::where('created_by', $user->id)
            ->orWhere('assigned_to', $user->id)
            ->where('status', 'active')
            ->count();

        // Email statistics with performance metrics
        $emailStats = EmailLog::join('contacts', 'email_logs.contact_id', '=', 'contacts.id')
            ->where('contacts.created_by', $user->id)
            ->selectRaw('
                COUNT(*) as total_sent,
                COUNT(CASE WHEN email_logs.status = "delivered" THEN 1 END) as delivered,
                COUNT(CASE WHEN email_logs.opened_at IS NOT NULL THEN 1 END) as opened,
                COUNT(CASE WHEN email_logs.clicked_at IS NOT NULL THEN 1 END) as clicked,
                COUNT(CASE WHEN email_logs.status = "failed" OR email_logs.status = "bounced" THEN 1 END) as failed
            ')
            ->first();

        $emailOpenRate = $emailStats->total_sent > 0 ?
            round(($emailStats->opened / $emailStats->total_sent) * 100, 1) : 0;

        $emailClickRate = $emailStats->total_sent > 0 ?
            round(($emailStats->clicked / $emailStats->total_sent) * 100, 1) : 0;

        // SMS statistics
        $smsStats = SmsMessage::whereHas('contact', function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        })
            ->selectRaw('
                COUNT(*) as total_sent,
                COUNT(CASE WHEN status = "delivered" THEN 1 END) as delivered,
                COUNT(CASE WHEN status = "failed" THEN 1 END) as failed,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as sent_today
            ', [$today])
            ->first();

        $smsDeliveryRate = $smsStats->total_sent > 0 ?
            round(($smsStats->delivered / $smsStats->total_sent) * 100, 1) : 0;

        // WhatsApp statistics
        $whatsAppStats = WhatsAppMessage::whereHas('contact', function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        })
            ->selectRaw('
                COUNT(*) as total_messages,
                COUNT(CASE WHEN direction = "outbound" THEN 1 END) as sent,
                COUNT(CASE WHEN direction = "inbound" THEN 1 END) as received,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as today
            ', [$today])
            ->first();

        // Campaign statistics
        $campaignStats = EmailCampaign::where('created_by', $user->id)
            ->selectRaw('
                COUNT(*) as total_campaigns,
                COUNT(CASE WHEN status = "sending" OR status = "scheduled" THEN 1 END) as active,
                COUNT(CASE WHEN status = "completed" THEN 1 END) as completed,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as this_month
            ', [$thisMonth])
            ->first();

        return [
            'contacts' => [
                'total' => $totalContacts,
                'active' => $activeContacts,
                'inactive' => $totalContacts - $activeContacts,
                'this_month' => $contactsThisMonth,
                'last_month' => $contactsLastMonth,
                'growth_rate' => $contactsGrowth,
                'new_today' => Contact::where('created_by', $user->id)
                    ->whereDate('created_at', $today)->count(),
            ],
            'email' => [
                'total_sent' => $emailStats->total_sent,
                'delivered' => $emailStats->delivered,
                'opened' => $emailStats->opened,
                'clicked' => $emailStats->clicked,
                'failed' => $emailStats->failed,
                'open_rate' => $emailOpenRate,
                'click_rate' => $emailClickRate,
                'delivery_rate' => $emailStats->total_sent > 0 ?
                    round(($emailStats->delivered / $emailStats->total_sent) * 100, 1) : 0,
            ],
            'sms' => [
                'total_sent' => $smsStats->total_sent,
                'delivered' => $smsStats->delivered,
                'failed' => $smsStats->failed,
                'sent_today' => $smsStats->sent_today,
                'delivery_rate' => $smsDeliveryRate,
            ],
            'whatsapp' => [
                'total_messages' => $whatsAppStats->total_messages,
                'sent' => $whatsAppStats->sent,
                'received' => $whatsAppStats->received,
                'today' => $whatsAppStats->today,
                'active_sessions' => $user->whatsappSessions()->where('status', 'connected')->count(),
            ],
            'campaigns' => [
                'total' => $campaignStats->total_campaigns,
                'active' => $campaignStats->active,
                'completed' => $campaignStats->completed,
                'this_month' => $campaignStats->this_month,
            ],
        ];
    }

    /**
     * Get recent activities for real-time updates
     */
    private function getRecentActivities($user, $limit = 10)
    {
        return Communication::whereHas('contact', function ($query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);
        })
            ->with(['contact', 'user'])
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($communication) {
                return [
                    'id' => $communication->id,
                    'type' => $communication->type,
                    'direction' => $communication->direction,
                    'contact_name' => $communication->contact->full_name,
                    'contact_avatar' => $communication->contact->avatar_url,
                    'subject' => $communication->subject,
                    'content' => \Str::limit($communication->content, 100),
                    'status' => $communication->status,
                    'status_color' => $communication->status_color,
                    'created_at' => $communication->created_at,
                    'created_at_human' => $communication->created_at->diffForHumans(),
                    'user_name' => $communication->user->name ?? 'System',
                ];
            });
    }

    /**
     * Get active campaigns
     */
    private function getActiveCampaigns($user)
    {
        return EmailCampaign::where('created_by', $user->id)
            ->whereIn('status', ['sending', 'scheduled', 'paused'])
            ->with(['smtpConfig', 'segments'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'status_color' => $campaign->status_color,
                    'scheduled_at' => $campaign->scheduled_at,
                    'scheduled_at_human' => $campaign->scheduled_at ? $campaign->scheduled_at->diffForHumans() : null,
                    'sent_count' => $campaign->sent_count,
                    'total_recipients' => $campaign->total_recipients,
                    'progress' => $campaign->total_recipients > 0 ?
                        round(($campaign->sent_count / $campaign->total_recipients) * 100, 1) : 0,
                ];
            });
    }

    /**
     * Get pending notifications
     */
    private function getPendingNotifications($user)
    {
        // This would integrate with your notification system
        $notifications = [];

        // Check for failed campaigns
        $failedCampaigns = EmailCampaign::where('created_by', $user->id)
            ->where('status', 'failed')
            ->where('updated_at', '>=', Carbon::now()->subHours(24))
            ->count();

        if ($failedCampaigns > 0) {
            $notifications[] = [
                'type' => 'warning',
                'icon' => 'fas fa-exclamation-triangle',
                'message' => "{$failedCampaigns} campaign(s) failed in the last 24 hours",
                'action_url' => route('email.campaigns.index', ['status' => 'failed']),
                'action_text' => 'View Campaigns',
            ];
        }

        // Check for low SMS credits (if applicable)
        // Check for inactive WhatsApp sessions
        $inactiveSessions = $user->whatsappSessions()
            ->where('status', 'disconnected')
            ->where('updated_at', '>=', Carbon::now()->subHours(6))
            ->count();

        if ($inactiveSessions > 0) {
            $notifications[] = [
                'type' => 'info',
                'icon' => 'fab fa-whatsapp',
                'message' => "{$inactiveSessions} WhatsApp session(s) disconnected",
                'action_url' => route('whatsapp.sessions.index'),
                'action_text' => 'Reconnect',
            ];
        }

        return $notifications;
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts($user)
    {
        $alerts = [];

        // Check SMTP configurations
        $inactiveSmtpConfigs = $user->smtpConfigs()
            ->where('is_active', false)
            ->count();

        if ($inactiveSmtpConfigs > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$inactiveSmtpConfigs} SMTP configuration(s) are inactive",
                'action_url' => route('smtp-configs.index'),
            ];
        }

        return $alerts;
    }

    /**
     * Get enhanced chart data for communications
     */
    private function getCommunicationsChartData($userId)
    {
        $days = collect();
        $now = Carbon::now();

        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);

            $stats = Communication::whereHas('contact', function ($query) use ($userId) {
                $query->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId);
            })
                ->whereDate('created_at', $date)
                ->selectRaw('
                COUNT(CASE WHEN type = "email" THEN 1 END) as email,
                COUNT(CASE WHEN type = "sms" THEN 1 END) as sms,
                COUNT(CASE WHEN type = "whatsapp" THEN 1 END) as whatsapp,
                COUNT(CASE WHEN direction = "inbound" THEN 1 END) as inbound,
                COUNT(CASE WHEN direction = "outbound" THEN 1 END) as outbound
            ')
                ->first();

            $days->push([
                'date' => $date->format('M j'),
                'fullDate' => $date->format('Y-m-d'),
                'email' => $stats->email ?? 0,
                'sms' => $stats->sms ?? 0,
                'whatsapp' => $stats->whatsapp ?? 0,
                'inbound' => $stats->inbound ?? 0,
                'outbound' => $stats->outbound ?? 0,
                'total' => ($stats->email ?? 0) + ($stats->sms ?? 0) + ($stats->whatsapp ?? 0),
            ]);
        }

        return $days;
    }

    /**
     * Get email performance data
     */
    private function getEmailPerformanceData($userId)
    {
        return EmailLog::join('contacts', 'email_logs.contact_id', '=', 'contacts.id')
            ->where('contacts.created_by', $userId)
            ->where('email_logs.created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('
                DATE(email_logs.created_at) as date,
                COUNT(*) as sent,
                COUNT(CASE WHEN email_logs.status = "delivered" THEN 1 END) as delivered,
                COUNT(CASE WHEN email_logs.opened_at IS NOT NULL THEN 1 END) as opened,
                COUNT(CASE WHEN email_logs.clicked_at IS NOT NULL THEN 1 END) as clicked,
                COUNT(CASE WHEN email_logs.status = "bounced" THEN 1 END) as bounced,
                COUNT(CASE WHEN email_logs.status = "failed" THEN 1 END) as failed
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($stat) {
                return [
                    'date' => Carbon::parse($stat->date)->format('M j'),
                    'sent' => $stat->sent,
                    'delivered' => $stat->delivered,
                    'opened' => $stat->opened,
                    'clicked' => $stat->clicked,
                    'bounced' => $stat->bounced,
                    'failed' => $stat->failed,
                    'open_rate' => $stat->sent > 0 ? round(($stat->opened / $stat->sent) * 100, 1) : 0,
                    'click_rate' => $stat->sent > 0 ? round(($stat->clicked / $stat->sent) * 100, 1) : 0,
                ];
            });
    }

    /**
     * Get channel comparison data
     */
    private function getChannelComparisonData($userId)
    {
        $emailCount = EmailLog::join('contacts', 'email_logs.contact_id', '=', 'contacts.id')
            ->where('contacts.created_by', $userId)
            ->where('email_logs.created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $smsCount = SmsMessage::whereHas('contact', function ($query) use ($userId) {
            $query->where('created_by', $userId);
        })
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $whatsappCount = WhatsAppMessage::whereHas('contact', function ($query) use ($userId) {
            $query->where('created_by', $userId);
        })
            ->where('direction', 'outbound')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        $total = $emailCount + $smsCount + $whatsappCount;

        return [
            'email' => [
                'count' => $emailCount,
                'percentage' => $total > 0 ? round(($emailCount / $total) * 100, 1) : 0,
            ],
            'sms' => [
                'count' => $smsCount,
                'percentage' => $total > 0 ? round(($smsCount / $total) * 100, 1) : 0,
            ],
            'whatsapp' => [
                'count' => $whatsappCount,
                'percentage' => $total > 0 ? round(($whatsappCount / $total) * 100, 1) : 0,
            ],
        ];
    }

    private function getContactsChartData($userId)
    {
        $days = collect();
        $now = Carbon::now();

        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);

            $newContacts = Contact::where('created_by', $userId)
                ->whereDate('created_at', $date)
                ->count();

            $days->push([
                'date' => $date->format('M j'),
                'contacts' => $newContacts,
            ]);
        }

        return $days;
    }

    /**
     * Get real-time dashboard stats (API endpoint)
     */
    public function getStats(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'month');

        // Clear cache if requested
        if ($request->boolean('refresh')) {
            Cache::forget("dashboard_stats_{$user->id}");
        }

        $stats = $this->calculateStats($user);

        // Broadcast updated stats via WebSocket
        broadcast(new DashboardStatsUpdated($user->id, $stats));

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get real-time activities (API endpoint)
     */
    public function getRecentActivity()
    {
        $user = auth()->user();
        $activities = $this->getRecentActivities($user, 20);

        return response()->json([
            'success' => true,
            'activities' => $activities,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get live system status
     */
    public function getSystemStatus()
    {
        $user = auth()->user();

        $status = [
            'database' => 'connected',
            'redis' => 'connected',
            'queue' => 'running',
            'websocket' => 'connected',
            'smtp_configs' => $user->smtpConfigs()->where('is_active', true)->count(),
            'sms_providers' => $user->smsProviders()->where('is_active', true)->count(),
            'whatsapp_sessions' => $user->whatsappSessions()->where('status', 'connected')->count(),
            'active_campaigns' => $user->emailCampaigns()->whereIn('status', ['sending', 'scheduled'])->count(),
            'pending_jobs' => \Queue::size() ?? 0,
        ];

        return response()->json([
            'success' => true,
            'status' => $status,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get chart data with caching
     */
    public function getChartData(Request $request)
    {
        $user = auth()->user();
        $type = $request->get('type', 'communications');
        $period = $request->get('period', 30); // days

        $cacheKey = "dashboard_chart_{$user->id}_{$type}_{$period}";

        $data = Cache::remember($cacheKey, 900, function () use ($user, $type) { // Cache for 15 minutes
            return match ($type) {
                'communications' => $this->getCommunicationsChartData($user->id),
                'contacts' => $this->getContactsChartData($user->id),
                'email_performance' => $this->getEmailPerformanceData($user->id),
                'channel_comparison' => $this->getChannelComparisonData($user->id),
                default => []
            };
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'type' => $type,
            'period' => $period,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Stream live dashboard updates
     */
    public function streamUpdates(Request $request)
    {
        $user = auth()->user();

        return response()->stream(function () use ($user) {
            while (true) {
                // Get fresh stats
                $stats = $this->calculateStats($user);
                $activities = $this->getRecentActivities($user, 5);
                $notifications = $this->getPendingNotifications($user);

                $data = [
                    'type' => 'dashboard_update',
                    'data' => [
                        'stats' => $stats,
                        'activities' => $activities,
                        'notifications' => $notifications,
                        'timestamp' => now()->toISOString(),
                    ],
                ];

                echo 'data: '.json_encode($data)."\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

                sleep(5); // Update every 5 seconds
            }
        }, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function quickActions()
    {
        $user = auth()->user();

        $actions = [
            'can_send_email' => $user->canSendEmails() && $user->hasActiveSmtpConfig(),
            'can_send_sms' => $user->canSendSms() && $user->hasActiveSmsProvider(),
            'can_use_whatsapp' => $user->canUseWhatsApp() && $user->hasActiveWhatsAppSession(),
            'can_import_data' => $user->canImportData(),
            'can_manage_settings' => $user->canManageSettings(),
        ];

        return response()->json($actions);
    }

    /**
     * Calculate email open rate for a specific user
     */
    private function calculateEmailOpenRate($userId)
    {
        $emailStats = EmailLog::join('contacts', 'email_logs.contact_id', '=', 'contacts.id')
            ->where('contacts.created_by', $userId)
            ->selectRaw('
                COUNT(*) as total_sent,
                COUNT(CASE WHEN email_logs.opened_at IS NOT NULL THEN 1 END) as opened
            ')
            ->first();

        return $emailStats && $emailStats->total_sent > 0 ?
            round(($emailStats->opened / $emailStats->total_sent) * 100, 1) : 0;
    }

    /**
     * Calculate SMS delivery rate for a specific user
     */
    private function calculateSmsDeliveryRate($userId)
    {
        $smsStats = SmsMessage::whereHas('contact', function ($query) use ($userId) {
            $query->where('created_by', $userId)
                ->orWhere('assigned_to', $userId);
        })
            ->selectRaw('
                COUNT(*) as total_sent,
                COUNT(CASE WHEN status = "delivered" THEN 1 END) as delivered
            ')
            ->first();

        return $smsStats && $smsStats->total_sent > 0 ?
            round(($smsStats->delivered / $smsStats->total_sent) * 100, 1) : 0;
    }

    /**
     * Get dashboard statistics (cached version)
     */
    public function getDashboardStats(Request $request)
    {
        $user = auth()->user();
        $cacheKey = "dashboard_stats_{$user->id}";

        // Clear cache if requested
        if ($request->boolean('refresh')) {
            Cache::forget($cacheKey);
        }

        $stats = Cache::remember($cacheKey, 300, function () use ($user) { // Cache for 5 minutes
            return $this->calculateStats($user);
        });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'cached' => ! $request->boolean('refresh'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
