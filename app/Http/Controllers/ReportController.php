<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactSegment;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display main reports dashboard
     */
    public function index()
    {
        $overviewStats = [
            'contacts' => [
                'total' => Contact::count(),
                'active' => Contact::where('status', 'active')->count(),
                'new_this_month' => Contact::whereMonth('created_at', now()->month)->count(),
                'growth_rate' => $this->calculateContactGrowthRate(),
            ],
            'communications' => [
                'emails_sent' => EmailLog::where('status', 'sent')->count(),
                'sms_sent' => SmsMessage::where('status', 'delivered')->count(),
                'whatsapp_sent' => WhatsAppMessage::where('direction', 'outbound')->count(),
                'total_communications' => $this->getTotalCommunications(),
            ],
            'campaigns' => [
                'total' => EmailCampaign::count(),
                'active' => EmailCampaign::where('status', 'active')->count(),
                'completed' => EmailCampaign::where('status', 'completed')->count(),
                'average_open_rate' => $this->getAverageOpenRate(),
            ],
            'segments' => [
                'total' => ContactSegment::count(),
                'dynamic' => ContactSegment::where('is_dynamic', true)->count(),
                'static' => ContactSegment::where('is_dynamic', false)->count(),
                'largest_segment_size' => $this->getLargestSegmentSize(),
            ],
        ];

        // Recent activity trends
        $trends = [
            'contact_registrations' => $this->getContactRegistrationTrend(),
            'email_performance' => $this->getEmailPerformanceTrend(),
            'sms_delivery' => $this->getSmsDeliveryTrend(),
            'whatsapp_activity' => $this->getWhatsAppActivityTrend(),
        ];

        // Top performing campaigns
        $topCampaigns = $this->getTopPerformingCampaigns();

        // Channel comparison
        $channelComparison = $this->getChannelComparison();

        return view('reports.index', compact(
            'overviewStats',
            'trends',
            'topCampaigns',
            'channelComparison'
        ));
    }

    /**
     * Contact reports and analytics
     */
    public function contacts(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $contactStats = [
            'total_contacts' => Contact::count(),
            'new_contacts' => Contact::whereBetween('created_at', $dateRange)->count(),
            'active_contacts' => Contact::where('status', 'active')->count(),
            'inactive_contacts' => Contact::where('status', 'inactive')->count(),
            'bounced_contacts' => Contact::where('status', 'bounced')->count(),
            'unsubscribed_contacts' => Contact::where('status', 'unsubscribed')->count(),
        ];

        // Contact source breakdown
        $contactSources = Contact::select('source', DB::raw('count(*) as count'))
            ->whereNotNull('source')
            ->groupBy('source')
            ->get();

        // Geographic distribution
        $geographicDistribution = Contact::select('country', DB::raw('count(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();

        // Contact growth over time
        $contactGrowth = $this->getContactGrowthData($dateRange);

        // Most engaged contacts
        $engagedContacts = $this->getMostEngagedContacts();

        // Segment performance
        $segmentPerformance = $this->getSegmentPerformance();

        return view('reports.contacts', compact(
            'contactStats',
            'contactSources',
            'geographicDistribution',
            'contactGrowth',
            'engagedContacts',
            'segmentPerformance',
            'dateRange'
        ));
    }

    /**
     * Email campaign reports
     */
    public function emailCampaigns(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $emailStats = [
            'total_campaigns' => EmailCampaign::count(),
            'active_campaigns' => EmailCampaign::where('status', 'active')->count(),
            'completed_campaigns' => EmailCampaign::where('status', 'completed')->count(),
            'total_emails_sent' => EmailLog::where('status', 'sent')->count(),
            'total_opens' => EmailLog::whereNotNull('opened_at')->count(),
            'total_clicks' => EmailLog::whereNotNull('clicked_at')->count(),
            'total_bounces' => EmailLog::whereNotNull('bounced_at')->count(),
            'total_unsubscribes' => EmailLog::where('status', 'unsubscribed')->count(),
        ];

        // Calculate rates
        $emailStats['open_rate'] = $emailStats['total_emails_sent'] > 0
            ? round(($emailStats['total_opens'] / $emailStats['total_emails_sent']) * 100, 2)
            : 0;
        $emailStats['click_rate'] = $emailStats['total_emails_sent'] > 0
            ? round(($emailStats['total_clicks'] / $emailStats['total_emails_sent']) * 100, 2)
            : 0;
        $emailStats['bounce_rate'] = $emailStats['total_emails_sent'] > 0
            ? round(($emailStats['total_bounces'] / $emailStats['total_emails_sent']) * 100, 2)
            : 0;
        $emailStats['unsubscribe_rate'] = $emailStats['total_emails_sent'] > 0
            ? round(($emailStats['total_unsubscribes'] / $emailStats['total_emails_sent']) * 100, 2)
            : 0;

        // Campaign performance over time
        $campaignPerformance = $this->getCampaignPerformanceData($dateRange);

        // Top performing campaigns
        $topCampaigns = EmailCampaign::with(['emailLogs'])
            ->get()
            ->map(function ($campaign) {
                $logs = $campaign->emailLogs;
                $sent = $logs->count();
                $opens = $logs->whereNotNull('opened_at')->count();
                $clicks = $logs->whereNotNull('clicked_at')->count();

                return [
                    'campaign' => $campaign,
                    'sent' => $sent,
                    'opens' => $opens,
                    'clicks' => $clicks,
                    'open_rate' => $sent > 0 ? round(($opens / $sent) * 100, 2) : 0,
                    'click_rate' => $sent > 0 ? round(($clicks / $sent) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('open_rate')
            ->take(10);

        // Email client analysis
        $emailClients = $this->getEmailClientStats();

        // Send time analysis
        $bestSendTimes = $this->getBestSendTimes();

        return view('reports.email-campaigns', compact(
            'emailStats',
            'campaignPerformance',
            'topCampaigns',
            'emailClients',
            'bestSendTimes',
            'dateRange'
        ));
    }

    /**
     * SMS reports and analytics
     */
    public function sms(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $smsStats = [
            'total_messages' => SmsMessage::count(),
            'delivered_messages' => SmsMessage::where('status', 'delivered')->count(),
            'failed_messages' => SmsMessage::where('status', 'failed')->count(),
            'pending_messages' => SmsMessage::where('status', 'pending')->count(),
            'total_cost' => SmsMessage::sum('cost'),
        ];

        // Calculate delivery rate
        $smsStats['delivery_rate'] = $smsStats['total_messages'] > 0
            ? round(($smsStats['delivered_messages'] / $smsStats['total_messages']) * 100, 2)
            : 0;

        // SMS volume over time
        $smsVolume = $this->getSmsVolumeData($dateRange);

        // Provider performance
        $providerPerformance = SmsMessage::select('provider',
            DB::raw('count(*) as total'),
            DB::raw('sum(case when status = "delivered" then 1 else 0 end) as delivered'),
            DB::raw('sum(cost) as total_cost')
        )
            ->groupBy('provider')
            ->get()
            ->map(function ($item) {
                $item->delivery_rate = $item->total > 0 ? round(($item->delivered / $item->total) * 100, 2) : 0;
                $item->avg_cost = $item->total > 0 ? round($item->total_cost / $item->total, 4) : 0;

                return $item;
            });

        // Country analysis
        $countryStats = $this->getSmsCountryStats();

        // Message length analysis
        $messageLengthStats = $this->getSmsLengthStats();

        return view('reports.sms', compact(
            'smsStats',
            'smsVolume',
            'providerPerformance',
            'countryStats',
            'messageLengthStats',
            'dateRange'
        ));
    }

    /**
     * WhatsApp reports and analytics
     */
    public function whatsapp(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $whatsappStats = [
            'total_messages' => WhatsAppMessage::count(),
            'outbound_messages' => WhatsAppMessage::where('direction', 'outbound')->count(),
            'inbound_messages' => WhatsAppMessage::where('direction', 'inbound')->count(),
            'delivered_messages' => WhatsAppMessage::where('status', 'delivered')->count(),
            'read_messages' => WhatsAppMessage::where('status', 'read')->count(),
            'failed_messages' => WhatsAppMessage::where('status', 'failed')->count(),
        ];

        // Calculate engagement rate
        $whatsappStats['delivery_rate'] = $whatsappStats['outbound_messages'] > 0
            ? round(($whatsappStats['delivered_messages'] / $whatsappStats['outbound_messages']) * 100, 2)
            : 0;
        $whatsappStats['read_rate'] = $whatsappStats['delivered_messages'] > 0
            ? round(($whatsappStats['read_messages'] / $whatsappStats['delivered_messages']) * 100, 2)
            : 0;

        // Message volume over time
        $messageVolume = $this->getWhatsAppVolumeData($dateRange);

        // Session performance
        $sessionPerformance = $this->getWhatsAppSessionStats();

        // Message type analysis
        $messageTypes = WhatsAppMessage::select('message_type', DB::raw('count(*) as count'))
            ->whereNotNull('message_type')
            ->groupBy('message_type')
            ->get();

        // Response time analysis
        $responseTimeStats = $this->getWhatsAppResponseTimes();

        // Active conversations
        $activeConversations = $this->getActiveWhatsAppConversations();

        return view('reports.whatsapp', compact(
            'whatsappStats',
            'messageVolume',
            'sessionPerformance',
            'messageTypes',
            'responseTimeStats',
            'activeConversations',
            'dateRange'
        ));
    }

    /**
     * Communications overview report
     */
    public function communications(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        // Overall communication stats
        $commStats = [
            'email' => [
                'sent' => EmailLog::where('status', 'sent')->count(),
                'opened' => EmailLog::whereNotNull('opened_at')->count(),
                'clicked' => EmailLog::whereNotNull('clicked_at')->count(),
            ],
            'sms' => [
                'sent' => SmsMessage::count(),
                'delivered' => SmsMessage::where('status', 'delivered')->count(),
            ],
            'whatsapp' => [
                'sent' => WhatsAppMessage::where('direction', 'outbound')->count(),
                'delivered' => WhatsAppMessage::where('status', 'delivered')->count(),
                'read' => WhatsAppMessage::where('status', 'read')->count(),
            ],
        ];

        // Channel comparison
        $channelComparison = $this->getDetailedChannelComparison($dateRange);

        // Communication timeline
        $communicationTimeline = $this->getCommunicationTimeline($dateRange);

        // Contact engagement scoring
        $engagementScores = $this->getContactEngagementScores();

        // ROI analysis
        $roiAnalysis = $this->getCommunicationROI();

        return view('reports.communications', compact(
            'commStats',
            'channelComparison',
            'communicationTimeline',
            'engagementScores',
            'roiAnalysis',
            'dateRange'
        ));
    }

    /**
     * Export reports to various formats
     */
    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:contacts,email_campaigns,sms,whatsapp,communications',
            'format' => 'required|in:csv,excel,pdf',
            'date_range' => 'required|in:7,30,90,365,all',
        ]);

        $dateRange = $this->getDateRange($request);
        $data = [];

        switch ($request->report_type) {
            case 'contacts':
                $data = $this->getContactsExportData($dateRange);
                break;
            case 'email_campaigns':
                $data = $this->getEmailCampaignsExportData($dateRange);
                break;
            case 'sms':
                $data = $this->getSmsExportData($dateRange);
                break;
            case 'whatsapp':
                $data = $this->getWhatsAppExportData($dateRange);
                break;
            case 'communications':
                $data = $this->getCommunicationsExportData($dateRange);
                break;
        }

        return $this->generateExport($data, $request->format, $request->report_type);
    }

    // Private helper methods

    private function getDateRange(Request $request)
    {
        $days = $request->get('date_range', 30);

        if ($days == 'all') {
            return [Carbon::create(2020, 1, 1), now()];
        }

        return [now()->subDays($days), now()];
    }

    private function calculateContactGrowthRate()
    {
        $thisMonth = Contact::whereMonth('created_at', now()->month)->count();
        $lastMonth = Contact::whereMonth('created_at', now()->subMonth()->month)->count();

        if ($lastMonth == 0) {
            return 100;
        }

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function getTotalCommunications()
    {
        return EmailLog::count() + SmsMessage::count() + WhatsAppMessage::count();
    }

    private function getAverageOpenRate()
    {
        $campaigns = EmailCampaign::with('emailLogs')->get();
        if ($campaigns->isEmpty()) {
            return 0;
        }

        $totalOpenRate = 0;
        $campaignCount = 0;

        foreach ($campaigns as $campaign) {
            $sent = $campaign->emailLogs->count();
            if ($sent > 0) {
                $opens = $campaign->emailLogs->whereNotNull('opened_at')->count();
                $totalOpenRate += ($opens / $sent) * 100;
                $campaignCount++;
            }
        }

        return $campaignCount > 0 ? round($totalOpenRate / $campaignCount, 2) : 0;
    }

    private function getLargestSegmentSize()
    {
        return ContactSegment::withCount('contacts')->orderBy('contacts_count', 'desc')->first()->contacts_count ?? 0;
    }

    private function getContactRegistrationTrend()
    {
        return Contact::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getEmailPerformanceTrend()
    {
        return EmailLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as sent'),
            DB::raw('sum(case when opened_at is not null then 1 else 0 end) as opens'),
            DB::raw('sum(case when clicked_at is not null then 1 else 0 end) as clicks')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getSmsDeliveryTrend()
    {
        return SmsMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as total'),
            DB::raw('sum(case when status = "delivered" then 1 else 0 end) as delivered')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getWhatsAppActivityTrend()
    {
        return WhatsAppMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as total'),
            DB::raw('sum(case when direction = "outbound" then 1 else 0 end) as outbound'),
            DB::raw('sum(case when direction = "inbound" then 1 else 0 end) as inbound')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getTopPerformingCampaigns()
    {
        return EmailCampaign::with(['emailLogs'])
            ->get()
            ->map(function ($campaign) {
                $logs = $campaign->emailLogs;
                $sent = $logs->count();
                $opens = $logs->where('opened', true)->count();
                $clicks = $logs->where('clicked', true)->count();

                return [
                    'name' => $campaign->name,
                    'sent' => $sent,
                    'open_rate' => $sent > 0 ? round(($opens / $sent) * 100, 2) : 0,
                    'click_rate' => $sent > 0 ? round(($clicks / $sent) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('open_rate')
            ->take(5)
            ->values();
    }

    private function getChannelComparison()
    {
        $emailSent = EmailLog::where('status', 'sent')->count();
        $smsSent = SmsMessage::count();
        $whatsappSent = WhatsAppMessage::where('direction', 'outbound')->count();

        $total = $emailSent + $smsSent + $whatsappSent;

        return [
            'email' => [
                'count' => $emailSent,
                'percentage' => $total > 0 ? round(($emailSent / $total) * 100, 1) : 0,
            ],
            'sms' => [
                'count' => $smsSent,
                'percentage' => $total > 0 ? round(($smsSent / $total) * 100, 1) : 0,
            ],
            'whatsapp' => [
                'count' => $whatsappSent,
                'percentage' => $total > 0 ? round(($whatsappSent / $total) * 100, 1) : 0,
            ],
        ];
    }

    private function getContactGrowthData($dateRange)
    {
        return Contact::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getMostEngagedContacts()
    {
        return Contact::with(['emailLogs', 'smsMessages', 'whatsappMessages'])
            ->get()
            ->map(function ($contact) {
                $emailEngagement = $contact->emailLogs->whereNotNull('opened_at')->count() +
                                 ($contact->emailLogs->whereNotNull('clicked_at')->count() * 2);
                $smsEngagement = $contact->smsMessages->where('status', 'delivered')->count();
                $whatsappEngagement = $contact->whatsappMessages->count();

                return [
                    'contact' => $contact,
                    'engagement_score' => $emailEngagement + $smsEngagement + $whatsappEngagement,
                ];
            })
            ->sortByDesc('engagement_score')
            ->take(10)
            ->values();
    }

    private function getSegmentPerformance()
    {
        return ContactSegment::with('contacts')
            ->get()
            ->map(function ($segment) {
                $contactIds = $segment->contacts->pluck('id');
                $emailEngagement = EmailLog::whereIn('contact_id', $contactIds)->whereNotNull('opened_at')->count();
                $totalEmails = EmailLog::whereIn('contact_id', $contactIds)->count();

                return [
                    'segment' => $segment,
                    'contact_count' => $segment->contacts->count(),
                    'engagement_rate' => $totalEmails > 0 ? round(($emailEngagement / $totalEmails) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('engagement_rate');
    }

    private function getCampaignPerformanceData($dateRange)
    {
        return EmailCampaign::with(['emailLogs'])
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->groupBy(function ($campaign) {
                return $campaign->created_at->format('Y-m-d');
            })
            ->map(function ($campaigns, $date) {
                $totalSent = 0;
                $totalOpens = 0;
                $totalClicks = 0;

                foreach ($campaigns as $campaign) {
                    $sent = $campaign->emailLogs->count();
                    $totalSent += $sent;
                    $totalOpens += $campaign->emailLogs->whereNotNull('opened_at')->count();
                    $totalClicks += $campaign->emailLogs->whereNotNull('clicked_at')->count();
                }

                return [
                    'date' => $date,
                    'sent' => $totalSent,
                    'open_rate' => $totalSent > 0 ? round(($totalOpens / $totalSent) * 100, 2) : 0,
                    'click_rate' => $totalSent > 0 ? round(($totalClicks / $totalSent) * 100, 2) : 0,
                ];
            });
    }

    private function getEmailClientStats()
    {
        return EmailLog::select('user_agent', DB::raw('count(*) as count'))
            ->whereNotNull('user_agent')
            ->whereNotNull('opened_at')
            ->groupBy('user_agent')
            ->orderBy('count', 'desc')
            ->take(10)
            ->get();
    }

    private function getBestSendTimes()
    {
        return EmailLog::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('count(*) as sent'),
            DB::raw('sum(case when opened_at is not null then 1 else 0 end) as opens')
        )
            ->where('status', 'sent')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                $item->open_rate = $item->sent > 0 ? round(($item->opens / $item->sent) * 100, 2) : 0;

                return $item;
            });
    }

    private function getSmsVolumeData($dateRange)
    {
        return SmsMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getSmsCountryStats()
    {
        return Contact::select('country', DB::raw('count(sms_messages.id) as message_count'))
            ->leftJoin('sms_messages', 'contacts.id', '=', 'sms_messages.contact_id')
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderBy('message_count', 'desc')
            ->take(10)
            ->get();
    }

    private function getSmsLengthStats()
    {
        return SmsMessage::select(
            DB::raw('
                    CASE 
                        WHEN LENGTH(message) <= 160 THEN "Short (<=160)"
                        WHEN LENGTH(message) <= 320 THEN "Medium (161-320)"
                        ELSE "Long (>320)"
                    END as length_category'
            ),
            DB::raw('count(*) as count'),
            DB::raw('avg(cost) as avg_cost')
        )
            ->groupBy('length_category')
            ->get();
    }

    private function getWhatsAppVolumeData($dateRange)
    {
        return WhatsAppMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count'),
            DB::raw('sum(case when direction = "outbound" then 1 else 0 end) as outbound'),
            DB::raw('sum(case when direction = "inbound" then 1 else 0 end) as inbound')
        )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getWhatsAppSessionStats()
    {
        return DB::table('whatsapp_sessions')
            ->select(
                'session_name',
                DB::raw('count(whatsapp_messages.id) as message_count'),
                DB::raw('count(distinct whatsapp_messages.contact_id) as unique_contacts')
            )
            ->leftJoin('whatsapp_messages', 'whatsapp_sessions.id', '=', 'whatsapp_messages.session_id')
            ->groupBy('whatsapp_sessions.id', 'session_name')
            ->orderBy('message_count', 'desc')
            ->get();
    }

    private function getWhatsAppResponseTimes()
    {
        // Calculate average response times for customer service
        return DB::select("
            SELECT 
                AVG(TIMESTAMPDIFF(MINUTE, inbound.created_at, outbound.created_at)) as avg_response_minutes
            FROM whatsapp_messages inbound
            JOIN whatsapp_messages outbound ON outbound.contact_id = inbound.contact_id
            WHERE inbound.direction = 'inbound' 
            AND outbound.direction = 'outbound'
            AND outbound.created_at > inbound.created_at
            AND outbound.created_at <= DATE_ADD(inbound.created_at, INTERVAL 24 HOUR)
        ");
    }

    private function getActiveWhatsAppConversations()
    {
        return Contact::select('contacts.*')
            ->join('whatsapp_messages', 'contacts.id', '=', 'whatsapp_messages.contact_id')
            ->where('whatsapp_messages.created_at', '>=', now()->subDays(7))
            ->groupBy('contacts.id')
            ->orderBy(DB::raw('MAX(whatsapp_messages.created_at)'), 'desc')
            ->take(20)
            ->get();
    }

    private function getDetailedChannelComparison($dateRange)
    {
        $email = [
            'sent' => EmailLog::whereBetween('created_at', $dateRange)->count(),
            'delivered' => EmailLog::whereBetween('created_at', $dateRange)->where('status', 'sent')->count(),
            'opened' => EmailLog::whereBetween('created_at', $dateRange)->whereNotNull('opened_at')->count(),
            'clicked' => EmailLog::whereBetween('created_at', $dateRange)->whereNotNull('clicked_at')->count(),
        ];

        $sms = [
            'sent' => SmsMessage::whereBetween('created_at', $dateRange)->count(),
            'delivered' => SmsMessage::whereBetween('created_at', $dateRange)->where('status', 'delivered')->count(),
        ];

        $whatsapp = [
            'sent' => WhatsAppMessage::whereBetween('created_at', $dateRange)->where('direction', 'outbound')->count(),
            'delivered' => WhatsAppMessage::whereBetween('created_at', $dateRange)->where('status', 'delivered')->count(),
            'read' => WhatsAppMessage::whereBetween('created_at', $dateRange)->where('status', 'read')->count(),
        ];

        return compact('email', 'sms', 'whatsapp');
    }

    private function getCommunicationTimeline($dateRange)
    {
        $timeline = [];

        // Get email data
        $emailData = EmailLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count'),
            DB::raw('"email" as channel')
        )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->get();

        // Get SMS data
        $smsData = SmsMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count'),
            DB::raw('"sms" as channel')
        )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->get();

        // Get WhatsApp data
        $whatsappData = WhatsAppMessage::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count'),
            DB::raw('"whatsapp" as channel')
        )
            ->where('direction', 'outbound')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->get();

        return $emailData->concat($smsData)->concat($whatsappData)->groupBy('date');
    }

    private function getContactEngagementScores()
    {
        return Contact::with(['emailLogs', 'smsMessages', 'whatsappMessages'])
            ->get()
            ->map(function ($contact) {
                $emailScore = $contact->emailLogs->whereNotNull('opened_at')->count() * 2 +
                             $contact->emailLogs->whereNotNull('clicked_at')->count() * 5;
                $smsScore = $contact->smsMessages->where('status', 'delivered')->count() * 3;
                $whatsappScore = $contact->whatsappMessages->where('direction', 'inbound')->count() * 4;

                return [
                    'contact' => $contact,
                    'engagement_score' => $emailScore + $smsScore + $whatsappScore,
                    'email_score' => $emailScore,
                    'sms_score' => $smsScore,
                    'whatsapp_score' => $whatsappScore,
                ];
            })
            ->sortByDesc('engagement_score')
            ->take(50)
            ->values();
    }

    private function getCommunicationROI()
    {
        $emailCost = 0.01; // Estimated cost per email
        $smsCost = SmsMessage::avg('cost') ?? 0.05; // Average SMS cost
        $whatsappCost = 0.02; // Estimated WhatsApp cost

        return [
            'email' => [
                'total_sent' => EmailLog::count(),
                'total_cost' => EmailLog::count() * $emailCost,
                'opens' => EmailLog::whereNotNull('opened_at')->count(),
                'clicks' => EmailLog::whereNotNull('clicked_at')->count(),
            ],
            'sms' => [
                'total_sent' => SmsMessage::count(),
                'total_cost' => SmsMessage::sum('cost'),
                'delivered' => SmsMessage::where('status', 'delivered')->count(),
            ],
            'whatsapp' => [
                'total_sent' => WhatsAppMessage::where('direction', 'outbound')->count(),
                'total_cost' => WhatsAppMessage::where('direction', 'outbound')->count() * $whatsappCost,
                'delivered' => WhatsAppMessage::where('status', 'delivered')->count(),
                'read' => WhatsAppMessage::where('status', 'read')->count(),
            ],
        ];
    }

    // Export helper methods
    private function getContactsExportData($dateRange)
    {
        return Contact::select([
            'id', 'first_name', 'last_name', 'email', 'phone', 'company',
            'status', 'source', 'country', 'created_at',
        ])
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->toArray();
    }

    private function getEmailCampaignsExportData($dateRange)
    {
        return EmailCampaign::with('emailLogs')
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->map(function ($campaign) {
                $logs = $campaign->emailLogs;
                $sent = $logs->count();
                $opens = $logs->whereNotNull('opened_at')->count();
                $clicks = $logs->whereNotNull('clicked_at')->count();

                return [
                    'campaign_name' => $campaign->name,
                    'subject' => $campaign->subject,
                    'status' => $campaign->status,
                    'sent_count' => $sent,
                    'open_count' => $opens,
                    'click_count' => $clicks,
                    'open_rate' => $sent > 0 ? round(($opens / $sent) * 100, 2) : 0,
                    'click_rate' => $sent > 0 ? round(($clicks / $sent) * 100, 2) : 0,
                    'created_at' => $campaign->created_at,
                ];
            })
            ->toArray();
    }

    private function getSmsExportData($dateRange)
    {
        return SmsMessage::with('contact')
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->map(function ($sms) {
                return [
                    'contact_name' => $sms->contact->first_name.' '.$sms->contact->last_name,
                    'phone' => $sms->to_number,
                    'message' => $sms->message,
                    'status' => $sms->status,
                    'provider' => $sms->provider,
                    'cost' => $sms->cost,
                    'sent_at' => $sms->created_at,
                ];
            })
            ->toArray();
    }

    private function getWhatsAppExportData($dateRange)
    {
        return WhatsAppMessage::with('contact')
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->map(function ($message) {
                return [
                    'contact_name' => $message->contact->first_name.' '.$message->contact->last_name,
                    'phone' => $message->to_number,
                    'message' => $message->message,
                    'direction' => $message->direction,
                    'status' => $message->status,
                    'message_type' => $message->message_type,
                    'timestamp' => $message->created_at,
                ];
            })
            ->toArray();
    }

    private function getCommunicationsExportData($dateRange)
    {
        $data = [];

        // Email data
        $emails = EmailLog::with('contact')
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->map(function ($email) {
                return [
                    'channel' => 'email',
                    'contact_name' => $email->contact->first_name.' '.$email->contact->last_name,
                    'contact_email' => $email->contact->email,
                    'subject' => $email->campaign->subject ?? 'N/A',
                    'status' => $email->status,
                    'opened' => $email->opened_at ? 'Yes' : 'No',
                    'clicked' => $email->clicked_at ? 'Yes' : 'No',
                    'timestamp' => $email->created_at,
                ];
            });

        $data = array_merge($data, $emails->toArray());

        // SMS data
        $sms = SmsMessage::with('contact')
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->map(function ($sms) {
                return [
                    'channel' => 'sms',
                    'contact_name' => $sms->contact->first_name.' '.$sms->contact->last_name,
                    'contact_phone' => $sms->to_number,
                    'message' => $sms->message,
                    'status' => $sms->status,
                    'cost' => $sms->cost,
                    'timestamp' => $sms->created_at,
                ];
            });

        $data = array_merge($data, $sms->toArray());

        // WhatsApp data
        $whatsapp = WhatsAppMessage::with('contact')
            ->whereBetween('created_at', $dateRange)
            ->get()
            ->map(function ($message) {
                return [
                    'channel' => 'whatsapp',
                    'contact_name' => $message->contact->first_name.' '.$message->contact->last_name,
                    'contact_phone' => $message->to_number,
                    'message' => $message->message,
                    'direction' => $message->direction,
                    'status' => $message->status,
                    'timestamp' => $message->created_at,
                ];
            });

        return array_merge($data, $whatsapp->toArray());
    }

    private function generateExport($data, $format, $reportType)
    {
        $fileName = $reportType.'_report_'.now()->format('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($data, $fileName);
            case 'excel':
                return $this->exportToExcel($data, $fileName);
            case 'pdf':
                return $this->exportToPdf($data, $fileName, $reportType);
            default:
                return response()->json(['error' => 'Unsupported format'], 400);
        }
    }

    private function exportToCsv($data, $fileName)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Add headers
            if (! empty($data)) {
                fputcsv($file, array_keys($data[0]));
            }

            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($data, $fileName)
    {
        // This would require a package like Laravel Excel
        // For now, return CSV with Excel MIME type
        return $this->exportToCsv($data, $fileName);
    }

    private function exportToPdf($data, $fileName, $reportType)
    {
        // This would require a PDF generation package
        // For now, return a simple HTML response
        $html = view('reports.pdf.'.$reportType, compact('data'))->render();

        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'.pdf"');
    }
}
