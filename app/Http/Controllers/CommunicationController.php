<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\Contact;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\SmsMessage;
use App\Models\SmtpConfig;
use App\Models\WhatsAppMessage;
use App\Services\EmailService;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommunicationController extends Controller
{
    protected $emailService;

    protected $smsService;

    protected $whatsappService;

    public function __construct(
        EmailService $emailService,
        SmsService $smsService,
        WhatsAppService $whatsappService
    ) {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
        $this->whatsappService = $whatsappService;
        $this->middleware('auth');
    }

    /**
     * Display unified communications inbox
     */
    public function index(Request $request)
    {
        // Get communications from all channels
        $communications = $this->getUnifiedCommunications($request);

        // Statistics for dashboard
        $stats = [
            'total_communications' => $communications->total(),
            'unread_count' => $this->getUnreadCount(),
            'emails_today' => EmailLog::whereDate('created_at', today())->count(),
            'sms_today' => SmsMessage::whereDate('created_at', today())->count(),
            'whatsapp_today' => WhatsAppMessage::whereDate('created_at', today())->count(),
            'pending_responses' => $this->getPendingResponsesCount(),
        ];

        // Channel breakdown
        $channelStats = [
            'email' => [
                'total' => EmailLog::count(),
                'unread' => EmailLog::where('status', 'delivered')->whereNull('read_at')->count(),
                'today' => EmailLog::whereDate('created_at', today())->count(),
            ],
            'sms' => [
                'total' => SmsMessage::count(),
                'unread' => 0, // SMS messages don't have direction/read_at - they're typically outbound only
                'today' => SmsMessage::whereDate('created_at', today())->count(),
            ],
            'whatsapp' => [
                'total' => WhatsAppMessage::count(),
                'unread' => WhatsAppMessage::where('direction', 'inbound')->whereNull('read_at')->count(),
                'today' => WhatsAppMessage::whereDate('created_at', today())->count(),
            ],
        ];

        // Available filters
        $channels = [
            'all' => 'All Channels',
            'email' => 'Email',
            'sms' => 'SMS',
            'whatsapp' => 'WhatsApp',
        ];

        $statuses = [
            'all' => 'All Statuses',
            'unread' => 'Unread',
            'read' => 'Read',
            'pending' => 'Pending Response',
            'responded' => 'Responded',
        ];

        return view('communications.index', compact(
            'communications',
            'stats',
            'channelStats',
            'channels',
            'statuses'
        ));
    }

    /**
     * Display conversation thread for a specific contact
     */
    public function conversation(Contact $contact)
    {
        // Get all communications for this contact across all channels
        $emails = EmailLog::where('contact_id', $contact->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($email) {
                return [
                    'id' => $email->id,
                    'type' => 'email',
                    'direction' => 'outbound', // Emails are typically outbound
                    'content' => $email->subject,
                    'status' => $email->status,
                    'created_at' => $email->created_at,
                    'read_at' => $email->read_at,
                    'data' => $email,
                ];
            });

        $smsMessages = SmsMessage::where('contact_id', $contact->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($sms) {
                return [
                    'id' => $sms->id,
                    'type' => 'sms',
                    'direction' => 'outbound', // Most SMS are outbound in this system
                    'content' => $sms->message,
                    'status' => $sms->status,
                    'created_at' => $sms->created_at,
                    'read_at' => $sms->delivered_at,
                    'data' => $sms,
                ];
            });

        $whatsappMessages = WhatsAppMessage::where('contact_id', $contact->id)
            ->orderBy('created_at')
            ->get()
            ->map(function ($whatsapp) {
                return [
                    'id' => $whatsapp->id,
                    'type' => 'whatsapp',
                    'direction' => $whatsapp->direction,
                    'content' => $whatsapp->content,
                    'status' => $whatsapp->status,
                    'created_at' => $whatsapp->created_at,
                    'read_at' => $whatsapp->read_at,
                    'data' => $whatsapp,
                ];
            });

        // Merge and sort all communications by date
        $allCommunications = $emails
            ->concat($smsMessages)
            ->concat($whatsappMessages)
            ->sortBy('created_at');

        // Contact communication stats
        $contactStats = [
            'total_emails' => $emails->count(),
            'total_sms' => $smsMessages->count(),
            'total_whatsapp' => $whatsappMessages->count(),
            'first_contact' => $allCommunications->first()?->created_at,
            'last_contact' => $allCommunications->last()?->created_at,
            'unread_messages' => $whatsappMessages->where('direction', 'inbound')->whereNull('read_at')->count(),
        ];

        return view('communications.conversation', compact(
            'contact',
            'allCommunications',
            'contactStats'
        ));
    }

    /**
     * Mark communication as read
     */
    public function markAsRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,sms,whatsapp',
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            switch ($request->type) {
                case 'email':
                    $communication = EmailLog::find($request->id);
                    if ($communication && ! $communication->read_at) {
                        $communication->update(['read_at' => now()]);
                    }
                    break;

                case 'sms':
                    $communication = SmsMessage::find($request->id);
                    // SMS messages don't have read_at column - they're typically outbound
                    // and don't need read tracking like inbound messages
                    break;

                case 'whatsapp':
                    $communication = WhatsAppMessage::find($request->id);
                    if ($communication && ! $communication->read_at && $communication->direction === 'inbound') {
                        $communication->update(['read_at' => now()]);
                    }
                    break;
            }

            return response()->json(['success' => true, 'message' => 'Marked as read']);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as read: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unified communications with pagination and filtering
     */
    private function getUnifiedCommunications(Request $request)
    {
        $communications = collect();

        // Get base queries for each channel
        $emailQuery = EmailLog::with(['contact'])->latest('created_at');
        $smsQuery = SmsMessage::with(['contact'])->latest('created_at');
        $whatsappQuery = WhatsAppMessage::with(['contact'])->latest('created_at');

        // Apply channel filter
        $channel = $request->input('channel', 'all');
        $includeEmail = $channel === 'all' || $channel === 'email';
        $includeSms = $channel === 'all' || $channel === 'sms';
        $includeWhatsapp = $channel === 'all' || $channel === 'whatsapp';

        // Apply status filters
        $status = $request->input('status', 'all');
        if ($status === 'unread') {
            if ($includeEmail) {
                $emailQuery->whereNull('read_at');
            }
            // SMS messages don't support read_at filtering - skip this filter for SMS
            if ($includeWhatsapp) {
                $whatsappQuery->where('direction', 'inbound')->whereNull('read_at');
            }
        } elseif ($status === 'read') {
            if ($includeEmail) {
                $emailQuery->whereNotNull('read_at');
            }
            // SMS messages don't support read_at filtering - skip this filter for SMS
            if ($includeWhatsapp) {
                $whatsappQuery->whereNotNull('read_at');
            }
        }

        // Apply date filters
        if ($request->filled('date_from')) {
            $emailQuery->whereDate('created_at', '>=', $request->date_from);
            $smsQuery->whereDate('created_at', '>=', $request->date_from);
            $whatsappQuery->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $emailQuery->whereDate('created_at', '<=', $request->date_to);
            $smsQuery->whereDate('created_at', '<=', $request->date_to);
            $whatsappQuery->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;

            if ($includeEmail) {
                $emailQuery->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                        ->orWhereHas('contact', function ($cq) use ($search) {
                            $cq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            if ($includeSms) {
                $smsQuery->where(function ($q) use ($search) {
                    $q->where('message', 'like', "%{$search}%")
                        ->orWhere('to_number', 'like', "%{$search}%")
                        ->orWhereHas('contact', function ($cq) use ($search) {
                            $cq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            }

            if ($includeWhatsapp) {
                $whatsappQuery->where(function ($q) use ($search) {
                    $q->where('content', 'like', "%{$search}%")
                        ->orWhere('from_number', 'like', "%{$search}%")
                        ->orWhere('to_number', 'like', "%{$search}%")
                        ->orWhereHas('contact', function ($cq) use ($search) {
                            $cq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        });
                });
            }
        }

        // Get limited results from each channel (for performance)
        $limit = 50;

        if ($includeEmail) {
            $emails = $emailQuery->limit($limit)->get()->map(function ($email) {
                return [
                    'id' => $email->id,
                    'type' => 'email',
                    'contact' => $email->contact,
                    'subject' => $email->subject,
                    'snippet' => substr(strip_tags($email->body ?? ''), 0, 100),
                    'status' => $email->status,
                    'created_at' => $email->created_at,
                    'read_at' => $email->read_at,
                    'data' => $email,
                ];
            });
            $communications = $communications->concat($emails);
        }

        if ($includeSms) {
            $sms = $smsQuery->limit($limit)->get()->map(function ($sms) {
                return [
                    'id' => $sms->id,
                    'type' => 'sms',
                    'contact' => $sms->contact,
                    'subject' => 'SMS Message',
                    'snippet' => substr($sms->message, 0, 100),
                    'status' => $sms->status,
                    'created_at' => $sms->created_at,
                    'read_at' => $sms->delivered_at,
                    'data' => $sms,
                ];
            });
            $communications = $communications->concat($sms);
        }

        if ($includeWhatsapp) {
            $whatsapp = $whatsappQuery->limit($limit)->get()->map(function ($whatsapp) {
                return [
                    'id' => $whatsapp->id,
                    'type' => 'whatsapp',
                    'contact' => $whatsapp->contact,
                    'subject' => 'WhatsApp Message',
                    'snippet' => substr($whatsapp->content, 0, 100),
                    'status' => $whatsapp->status,
                    'direction' => $whatsapp->direction,
                    'created_at' => $whatsapp->created_at,
                    'read_at' => $whatsapp->read_at,
                    'data' => $whatsapp,
                ];
            });
            $communications = $communications->concat($whatsapp);
        }

        // Sort by date and paginate
        $communications = $communications->sortByDesc('created_at');

        // Manual pagination
        $page = $request->input('page', 1);
        $perPage = 20;
        $total = $communications->count();
        $items = $communications->forPage($page, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Get total unread count across all channels
     */
    private function getUnreadCount()
    {
        // Use the communications table which has the direction column
        // and covers all communication types in a unified way
        $unreadCommunications = \App\Models\Communication::where('direction', 'inbound')
            ->whereNull('read_at')
            ->count();

        // Also check email logs directly as they might not be in communications
        $emailUnread = EmailLog::whereNull('read_at')->count();

        // For WhatsApp, check both tables to be comprehensive
        $whatsappUnread = WhatsAppMessage::where('direction', 'inbound')
            ->whereNull('read_at')
            ->count();

        return $unreadCommunications + $emailUnread + $whatsappUnread;
    }

    /**
     * Get count of communications pending response
     */
    private function getPendingResponsesCount()
    {
        // This is a simplified version - in practice, you'd need more complex logic
        // to determine what constitutes a "pending response"
        return WhatsAppMessage::where('direction', 'inbound')
            ->whereNull('read_at')
            ->whereDate('created_at', '>=', now()->subDays(1))
            ->count();
    }

    /**
     * Send a quick message through the unified modal
     */
    public function sendQuick(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channel' => 'required|in:email,sms,whatsapp',
            'contact_id' => 'required|exists:contacts,id',
            'message' => 'required|string|max:10000',
            'subject' => 'nullable|string|max:255',
            'smtp_config_id' => 'required_if:channel,email|nullable|exists:smtp_configs,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the form errors and try again.');
        }

        try {
            $contact = Contact::findOrFail($request->contact_id);
            $channel = $request->channel;
            $message = $request->message;

            $result = null;

            switch ($channel) {
                case 'email':
                    $smtpConfig = SmtpConfig::findOrFail($request->smtp_config_id);
                    $result = $this->emailService->sendQuickEmail(
                        $contact,
                        $request->subject ?? 'Message from CRM',
                        $message,
                        $smtpConfig
                    );
                    $successMessage = 'Email sent successfully!';
                    break;

                case 'sms':
                    $result = $this->smsService->sendQuickSms(
                        $contact,
                        $message
                    );
                    $successMessage = 'SMS sent successfully!';
                    break;

                case 'whatsapp':
                    $result = $this->whatsappService->sendQuickMessage(
                        $contact,
                        $message
                    );
                    $successMessage = 'WhatsApp message sent successfully!';
                    break;

                default:
                    throw new \Exception('Unsupported communication channel.');
            }

            if ($result && isset($result['success']) && $result['success']) {
                return redirect()->back()
                    ->with('success', $successMessage);
            } else {
                $errorMessage = isset($result['message']) ? $result['message'] : 'Failed to send message. Please try again.';
                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->withInput();
            }

        } catch (\Exception $e) {
            \Log::error('Quick Send Error', [
                'channel' => $request->channel,
                'contact_id' => $request->contact_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send message: ' . $e->getMessage())
                ->withInput();
        }
    }
}
