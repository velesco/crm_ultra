<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use App\Models\Communication;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
        $totalSmsSent = SmsMessage::whereHas('contact', function($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
        })->count();
        
        $smsSentThisMonth = SmsMessage::whereHas('contact', function($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
        })->where('created_at', '>=', $thisMonth)->count();

        // WhatsApp statistics
        $totalWhatsAppMessages = WhatsAppMessage::whereHas('contact', function($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
        })->count();
        
        $whatsAppMessagesToday = WhatsAppMessage::whereHas('contact', function($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
        })->whereDate('created_at', $today)->count();

        // Recent activities
        $recentCommunications = Communication::whereHas('contact', function($query) use ($user) {
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
                'inactive' => $totalContacts - $activeContacts
            ],
            'email' => [
                'total_sent' => $totalEmailsSent,
                'sent_this_month' => $emailsSentThisMonth,
                'open_rate' => $emailOpenRate,
                'active_campaigns' => $activeCampaigns->count()
            ],
            'sms' => [
                'total_sent' => $totalSmsSent,
                'sent_this_month' => $smsSentThisMonth,
                'delivery_rate' => $this->calculateSmsDeliveryRate($user->id)
            ],
            'whatsapp' => [
                'total_messages' => $totalWhatsAppMessages,
                'messages_today' => $whatsAppMessagesToday,
                'active_sessions' => $user->whatsappSessions()->active()->connected()->count()
            ]
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

    private function calculateEmailOpenRate($userId)
    {
        $totalSent = EmailCampaign::where('created_by', $userId)->sum('sent_count');
        $totalOpened = EmailCampaign::where('created_by', $userId)->sum('opened_count');
        
        if ($totalSent == 0) {
            return 0;
        }
        
        return round(($totalOpened / $totalSent) * 100, 1);
    }

    private function calculateSmsDeliveryRate($userId)
    {
        $totalSent = SmsMessage::whereHas('contact', function($query) use ($userId) {
            $query->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId);
        })->count();
        
        $totalDelivered = SmsMessage::whereHas('contact', function($query) use ($userId) {
            $query->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId);
        })->where('status', 'delivered')->count();
        
        if ($totalSent == 0) {
            return 0;
        }
        
        return round(($totalDelivered / $totalSent) * 100, 1);
    }

    private function getCommunicationsChartData($userId)
    {
        $days = collect();
        $now = Carbon::now();
        
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            
            $emailCount = Communication::whereHas('contact', function($query) use ($userId) {
                $query->where('created_by', $userId)
                      ->orWhere('assigned_to', $userId);
            })
            ->where('type', 'email')
            ->whereDate('created_at', $date)
            ->count();
            
            $smsCount = Communication::whereHas('contact', function($query) use ($userId) {
                $query->where('created_by', $userId)
                      ->orWhere('assigned_to', $userId);
            })
            ->where('type', 'sms')
            ->whereDate('created_at', $date)
            ->count();
            
            $whatsappCount = Communication::whereHas('contact', function($query) use ($userId) {
                $query->where('created_by', $userId)
                      ->orWhere('assigned_to', $userId);
            })
            ->where('type', 'whatsapp')
            ->whereDate('created_at', $date)
            ->count();
            
            $days->push([
                'date' => $date->format('M j'),
                'email' => $emailCount,
                'sms' => $smsCount,
                'whatsapp' => $whatsappCount
            ]);
        }
        
        return $days;
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
                'contacts' => $newContacts
            ]);
        }
        
        return $days;
    }

    public function getRecentActivity()
    {
        $user = auth()->user();
        
        $activities = Communication::whereHas('contact', function($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
        })
        ->with(['contact', 'user'])
        ->latest()
        ->take(20)
        ->get()
        ->map(function($communication) {
            return [
                'id' => $communication->id,
                'type' => $communication->type,
                'direction' => $communication->direction,
                'contact_name' => $communication->contact->full_name,
                'subject' => $communication->subject,
                'content' => Str::limit($communication->content, 100),
                'status' => $communication->status,
                'created_at' => $communication->created_at->diffForHumans(),
                'status_color' => $communication->status_color
            ];
        });

        return response()->json($activities);
    }

    public function getStats(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'month'); // day, week, month, year
        
        $startDate = match($period) {
            'day' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth()
        };

        $stats = [
            'contacts' => [
                'total' => Contact::where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                'active' => Contact::where('created_by', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->where('status', 'active')
                    ->where('created_at', '>=', $startDate)
                    ->count()
            ],
            'emails' => [
                'sent' => EmailCampaign::where('created_by', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->sum('sent_count'),
                'opened' => EmailCampaign::where('created_by', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->sum('opened_count')
            ],
            'sms' => [
                'sent' => SmsMessage::whereHas('contact', function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhere('assigned_to', $user->id);
                })
                ->where('created_at', '>=', $startDate)
                ->count()
            ],
            'whatsapp' => [
                'sent' => WhatsAppMessage::whereHas('contact', function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhere('assigned_to', $user->id);
                })
                ->where('direction', 'outbound')
                ->where('created_at', '>=', $startDate)
                ->count(),
                'received' => WhatsAppMessage::whereHas('contact', function($query) use ($user) {
                    $query->where('created_by', $user->id)
                          ->orWhere('assigned_to', $user->id);
                })
                ->where('direction', 'inbound')
                ->where('created_at', '>=', $startDate)
                ->count()
            ]
        ];

        return response()->json($stats);
    }

    public function quickActions()
    {
        $user = auth()->user();
        
        $actions = [
            'can_send_email' => $user->canSendEmails() && $user->hasActiveSmtpConfig(),
            'can_send_sms' => $user->canSendSms() && $user->hasActiveSmsProvider(),
            'can_use_whatsapp' => $user->canUseWhatsApp() && $user->hasActiveWhatsAppSession(),
            'can_import_data' => $user->canImportData(),
            'can_manage_settings' => $user->canManageSettings()
        ];

        return response()->json($actions);
    }
}
