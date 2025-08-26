<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSession;
use App\Models\Contact;
use App\Models\ContactSegment;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
        $this->middleware('auth');
    }

    /**
     * Display WhatsApp chat interface
     */
    public function index(Request $request)
    {
        // Get active WhatsApp session
        $session = WhatsAppSession::where('is_active', true)->first();
        
        if (!$session) {
            return view('whatsapp.no-session');
        }

        // Get recent conversations grouped by contact
        $conversations = WhatsAppMessage::with(['contact'])
            ->select('contact_id')
            ->selectRaw('MAX(created_at) as last_message_at')
            ->selectRaw('COUNT(*) as message_count')
            ->selectRaw('SUM(CASE WHEN direction = "inbound" AND read_at IS NULL THEN 1 ELSE 0 END) as unread_count')
            ->where('whats_app_session_id', $session->id)
            ->groupBy('contact_id')
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        // Get selected contact messages if specified
        $selectedContact = null;
        $messages = collect();
        
        if ($request->filled('contact_id')) {
            $selectedContact = Contact::find($request->contact_id);
            if ($selectedContact) {
                $messages = WhatsAppMessage::with(['contact', 'whatsAppSession'])
                    ->where('whats_app_session_id', $session->id)
                    ->where('contact_id', $selectedContact->id)
                    ->orderBy('created_at')
                    ->get();

                // Mark messages as read
                WhatsAppMessage::where('whats_app_session_id', $session->id)
                    ->where('contact_id', $selectedContact->id)
                    ->where('direction', 'inbound')
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        // Dashboard statistics
        $stats = [
            'total_messages' => WhatsAppMessage::where('whats_app_session_id', $session->id)->count(),
            'messages_today' => WhatsAppMessage::where('whats_app_session_id', $session->id)
                ->whereDate('created_at', today())->count(),
            'unread_messages' => WhatsAppMessage::where('whats_app_session_id', $session->id)
                ->where('direction', 'inbound')->whereNull('read_at')->count(),
            'unique_contacts' => WhatsAppMessage::where('whats_app_session_id', $session->id)
                ->distinct('contact_id')->count(),
            'session_status' => $session->status,
        ];

        return view('whatsapp.index', compact('session', 'conversations', 'selectedContact', 'messages', 'stats'));
    }

    /**
     * Send a new WhatsApp message
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|exists:contacts,id',
            'message' => 'required|string|max:4096',
            'message_type' => 'in:text,image,document,audio',
            'media_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $session = WhatsAppSession::where('is_active', true)->first();
            
            if (!$session) {
                return response()->json(['success' => false, 'message' => 'No active WhatsApp session found'], 400);
            }

            $contact = Contact::find($request->contact_id);
            
            if (!$contact->whatsapp_number) {
                return response()->json(['success' => false, 'message' => 'Contact does not have WhatsApp number'], 400);
            }

            // Send message through WhatsApp service
            $result = $this->whatsappService->sendMessage(
                $contact->whatsapp_number,
                $request->message,
                $request->message_type ?? 'text',
                $request->media_url
            );

            if ($result['success']) {
                // Create message record
                $message = WhatsAppMessage::create([
                    'whats_app_session_id' => $session->id,
                    'contact_id' => $contact->id,
                    'user_id' => Auth::id(),
                    'phone_number' => $contact->whatsapp_number,
                    'message' => $request->message,
                    'message_type' => $request->message_type ?? 'text',
                    'media_url' => $request->media_url,
                    'direction' => 'outbound',
                    'status' => 'sent',
                    'whatsapp_message_id' => $result['message_id'] ?? null,
                    'sent_at' => now(),
                ]);

                return response()->json([
                    'success' => true, 
                    'message' => 'Message sent successfully',
                    'data' => $message->load('contact')
                ]);
            } else {
                return response()->json(['success' => false, 'message' => $result['error'] ?? 'Failed to send message'], 400);
            }

        } catch (\Exception $e) {
            \Log::error('WhatsApp send error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while sending message'], 500);
        }
    }

    /**
     * Send bulk WhatsApp messages
     */
    public function sendBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:4096',
            'send_type' => 'required|in:contacts,segment',
            'contact_ids' => 'required_if:send_type,contacts|array|min:1',
            'contact_ids.*' => 'exists:contacts,id',
            'segment_id' => 'required_if:send_type,segment|exists:contact_segments,id',
            'message_type' => 'in:text,image,document,audio',
            'media_url' => 'nullable|url',
            'schedule_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $session = WhatsAppSession::where('is_active', true)->first();
            
            if (!$session) {
                return back()->withErrors(['session' => 'No active WhatsApp session found']);
            }

            // Get contacts based on send type
            $contacts = $this->getContactsForBulkSend($request);
            
            if ($contacts->isEmpty()) {
                return back()->withErrors(['contacts' => 'No valid contacts found with WhatsApp numbers']);
            }

            $sentCount = 0;
            $failedCount = 0;
            $scheduleAt = $request->schedule_at ? Carbon::parse($request->schedule_at) : null;

            foreach ($contacts as $contact) {
                if (!$contact->whatsapp_number) {
                    $failedCount++;
                    continue;
                }

                try {
                    if ($scheduleAt) {
                        // Schedule for later
                        WhatsAppMessage::create([
                            'whats_app_session_id' => $session->id,
                            'contact_id' => $contact->id,
                            'user_id' => Auth::id(),
                            'phone_number' => $contact->whatsapp_number,
                            'message' => $request->message,
                            'message_type' => $request->message_type ?? 'text',
                            'media_url' => $request->media_url,
                            'direction' => 'outbound',
                            'status' => 'scheduled',
                            'scheduled_at' => $scheduleAt,
                        ]);
                        $sentCount++;
                    } else {
                        // Send immediately
                        $result = $this->whatsappService->sendMessage(
                            $contact->whatsapp_number,
                            $request->message,
                            $request->message_type ?? 'text',
                            $request->media_url
                        );

                        if ($result['success']) {
                            WhatsAppMessage::create([
                                'whats_app_session_id' => $session->id,
                                'contact_id' => $contact->id,
                                'user_id' => Auth::id(),
                                'phone_number' => $contact->whatsapp_number,
                                'message' => $request->message,
                                'message_type' => $request->message_type ?? 'text',
                                'media_url' => $request->media_url,
                                'direction' => 'outbound',
                                'status' => 'sent',
                                'whatsapp_message_id' => $result['message_id'] ?? null,
                                'sent_at' => now(),
                            ]);
                            $sentCount++;
                        } else {
                            $failedCount++;
                        }
                    }

                    // Add delay between messages to avoid rate limiting
                    usleep(500000); // 0.5 second delay

                } catch (\Exception $e) {
                    $failedCount++;
                    \Log::error('WhatsApp bulk send error for contact ' . $contact->id . ': ' . $e->getMessage());
                }
            }

            $totalContacts = $contacts->count();
            
            if ($scheduleAt) {
                $message = "WhatsApp messages scheduled for {$totalContacts} contacts on " . $scheduleAt->format('d/m/Y H:i');
            } else {
                $message = "Bulk WhatsApp sending completed. Sent: {$sentCount}, Failed: {$failedCount} out of {$totalContacts} contacts.";
            }

            return redirect()->route('whatsapp.index')->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while sending bulk messages: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show bulk message form
     */
    public function createBulk()
    {
        $contacts = Contact::select('id', 'first_name', 'last_name', 'whatsapp_number')
            ->whereNotNull('whatsapp_number')
            ->orderBy('first_name')
            ->get();

        $segments = ContactSegment::all();

        return view('whatsapp.bulk', compact('contacts', 'segments'));
    }

    /**
     * Display WhatsApp message history
     */
    public function history(Request $request)
    {
        $query = WhatsAppMessage::with(['contact', 'whatsAppSession', 'user'])
            ->latest('created_at');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhereHas('contact', function($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $messages = $query->paginate(20);

        // Statistics
        $stats = [
            'total_messages' => WhatsAppMessage::count(),
            'messages_today' => WhatsAppMessage::whereDate('created_at', today())->count(),
            'inbound_messages' => WhatsAppMessage::where('direction', 'inbound')->count(),
            'outbound_messages' => WhatsAppMessage::where('direction', 'outbound')->count(),
            'unread_messages' => WhatsAppMessage::where('direction', 'inbound')->whereNull('read_at')->count(),
        ];

        $contacts = Contact::select('id', 'first_name', 'last_name')
            ->whereHas('whatsappMessages')
            ->orderBy('first_name')
            ->get();

        return view('whatsapp.history', compact('messages', 'stats', 'contacts'));
    }

    /**
     * Handle incoming WhatsApp webhook
     */
    public function webhook(Request $request)
    {
        try {
            $result = $this->whatsappService->handleWebhook($request->all());
            
            if ($result['success']) {
                return response()->json(['status' => 'success'], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => $result['error']], 400);
            }

        } catch (\Exception $e) {
            \Log::error('WhatsApp webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Get QR code for WhatsApp session
     */
    public function qrCode()
    {
        try {
            $result = $this->whatsappService->getQRCode();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'qr_code' => $result['qr_code'],
                    'session_status' => $result['session_status'] ?? 'disconnected'
                ]);
            } else {
                return response()->json(['success' => false, 'message' => $result['error']], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get QR code'], 500);
        }
    }

    /**
     * Check WhatsApp session status
     */
    public function sessionStatus()
    {
        try {
            $result = $this->whatsappService->getSessionStatus();
            
            return response()->json([
                'success' => true,
                'status' => $result['status'] ?? 'disconnected',
                'info' => $result['info'] ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get session status'], 500);
        }
    }

    /**
     * Disconnect WhatsApp session
     */
    public function disconnect()
    {
        try {
            $result = $this->whatsappService->disconnect();
            
            if ($result['success']) {
                // Update session status in database
                WhatsAppSession::where('is_active', true)->update([
                    'is_active' => false,
                    'status' => 'disconnected',
                    'disconnected_at' => now(),
                ]);

                return redirect()->route('whatsapp.index')
                    ->with('success', 'WhatsApp session disconnected successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to disconnect session: ' . $result['error']]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while disconnecting: ' . $e->getMessage()]);
        }
    }

    /**
     * Show WhatsApp statistics
     */
    public function stats(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Daily statistics
        $dailyStats = WhatsAppMessage::selectRaw('DATE(created_at) as date, 
                                                  COUNT(*) as total,
                                                  SUM(CASE WHEN direction = "inbound" THEN 1 ELSE 0 END) as inbound,
                                                  SUM(CASE WHEN direction = "outbound" THEN 1 ELSE 0 END) as outbound')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Contact engagement statistics
        $contactStats = WhatsAppMessage::with('contact')
            ->selectRaw('contact_id, 
                         COUNT(*) as total_messages,
                         SUM(CASE WHEN direction = "inbound" THEN 1 ELSE 0 END) as received,
                         SUM(CASE WHEN direction = "outbound" THEN 1 ELSE 0 END) as sent,
                         MAX(created_at) as last_message_at')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('contact_id')
            ->orderBy('total_messages', 'desc')
            ->limit(20)
            ->get();

        // Overall statistics
        $overallStats = [
            'total_messages' => WhatsAppMessage::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'inbound_messages' => WhatsAppMessage::where('direction', 'inbound')
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'outbound_messages' => WhatsAppMessage::where('direction', 'outbound')
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'unique_contacts' => WhatsAppMessage::whereBetween('created_at', [$dateFrom, $dateTo])
                ->distinct('contact_id')->count(),
            'unread_messages' => WhatsAppMessage::where('direction', 'inbound')
                ->whereNull('read_at')->count(),
        ];

        return view('whatsapp.stats', compact('dailyStats', 'contactStats', 'overallStats', 'dateFrom', 'dateTo'));
    }

    /**
     * Get contacts for bulk sending based on request type
     */
    private function getContactsForBulkSend(Request $request)
    {
        switch ($request->send_type) {
            case 'contacts':
                return Contact::whereIn('id', $request->contact_ids)
                    ->whereNotNull('whatsapp_number')
                    ->get();

            case 'segment':
                $segment = ContactSegment::with('contacts')->find($request->segment_id);
                return $segment ? $segment->contacts()->whereNotNull('whatsapp_number')->get() : collect();

            default:
                return collect();
        }
    }
}
