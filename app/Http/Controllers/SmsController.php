<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactSegment;
use App\Models\SmsMessage;
use App\Models\SmsProvider;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
        $this->middleware('auth');
    }

    /**
     * Display SMS dashboard with overview
     */
    public function index(Request $request)
    {
        $query = SmsMessage::with(['contact', 'user'])
            ->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('message', 'like', '%' . $request->search . '%')
                  ->orWhereHas('contact', function($contactQuery) use ($request) {
                      $contactQuery->where('name', 'like', '%' . $request->search . '%')
                                 ->orWhere('phone', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $messages = $query->paginate(20);

        // Get statistics
        $stats = $this->getSmsStatistics();

        return view('sms.index', compact('messages', 'stats'));
    }

    /**
     * Show SMS compose form
     */
    public function create()
    {
        $contacts = Contact::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();

        $segments = ContactSegment::with('contacts')
            ->orderBy('name')
            ->get();

        $providers = SmsProvider::where('is_active', true)
            ->orderBy('name')
            ->get();

        $templates = $this->getSmsTemplates();

        return view('sms.create', compact('contacts', 'segments', 'providers', 'templates'));
    }

    /**
     * Send SMS message(s)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1600',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:contacts,id',
            'provider_id' => 'nullable|exists:sms_providers,id',
            'schedule_at' => 'nullable|date|after:now',
            'send_type' => 'required|in:now,scheduled,test'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $recipientContacts = Contact::whereIn('id', $request->recipients)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get();

            if ($recipientContacts->isEmpty()) {
                throw new \Exception('No valid phone numbers found for selected contacts.');
            }

            $results = [
                'success' => 0,
                'failed' => 0,
                'scheduled' => 0,
                'messages' => []
            ];

            foreach ($recipientContacts as $contact) {
                $smsMessage = SmsMessage::create([
                    'contact_id' => $contact->id,
                    'user_id' => Auth::id(),
                    'message' => $this->personalizeMessage($request->message, $contact),
                    'phone_number' => $contact->phone,
                    'provider' => $request->provider_id ? SmsProvider::find($request->provider_id)->name : null,
                    'status' => $request->send_type === 'scheduled' ? 'scheduled' : 'pending',
                    'scheduled_at' => $request->schedule_at,
                    'metadata' => json_encode([
                        'send_type' => $request->send_type,
                        'original_message' => $request->message,
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip()
                    ])
                ]);

                if ($request->send_type === 'now') {
                    try {
                        $response = $this->smsService->sendSms(
                            $contact->phone,
                            $smsMessage->message,
                            $request->provider_id
                        );

                        $smsMessage->update([
                            'status' => $response['success'] ? 'sent' : 'failed',
                            'provider_message_id' => $response['message_id'] ?? null,
                            'sent_at' => $response['success'] ? now() : null,
                            'error_message' => $response['error'] ?? null,
                            'cost' => $response['cost'] ?? 0
                        ]);

                        if ($response['success']) {
                            $results['success']++;
                        } else {
                            $results['failed']++;
                        }

                    } catch (\Exception $e) {
                        $smsMessage->update([
                            'status' => 'failed',
                            'error_message' => $e->getMessage()
                        ]);
                        $results['failed']++;
                        Log::error('SMS sending failed', [
                            'contact_id' => $contact->id,
                            'phone' => $contact->phone,
                            'error' => $e->getMessage()
                        ]);
                    }
                } elseif ($request->send_type === 'scheduled') {
                    $results['scheduled']++;
                } elseif ($request->send_type === 'test') {
                    // For test messages, just mark as sent without actually sending
                    $smsMessage->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'cost' => 0
                    ]);
                    $results['success']++;
                }

                $results['messages'][] = $smsMessage;
            }

            DB::commit();

            // Create success message based on send type
            if ($request->send_type === 'scheduled') {
                $message = "SMS scheduled successfully for {$results['scheduled']} recipient(s) at " . 
                          Carbon::parse($request->schedule_at)->format('M j, Y \a\t g:i A');
            } elseif ($request->send_type === 'test') {
                $message = "Test SMS created for {$results['success']} recipient(s)";
            } else {
                $message = "SMS sent successfully to {$results['success']} recipient(s)";
                if ($results['failed'] > 0) {
                    $message .= " ({$results['failed']} failed)";
                }
            }

            return redirect()->route('sms.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS sending process failed', ['error' => $e->getMessage()]);
            
            return redirect()->back()
                ->with('error', 'Failed to send SMS: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display specific SMS message details
     */
    public function show(SmsMessage $smsMessage)
    {
        $smsMessage->load(['contact', 'user']);
        
        return view('sms.show', compact('smsMessage'));
    }

    /**
     * Show SMS editing form
     */
    public function edit(SmsMessage $smsMessage)
    {
        // Only allow editing of scheduled messages
        if ($smsMessage->status !== 'scheduled') {
            return redirect()->route('sms.show', $smsMessage)
                ->with('error', 'Only scheduled SMS messages can be edited.');
        }

        $contacts = Contact::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();

        $providers = SmsProvider::where('is_active', true)
            ->orderBy('name')
            ->get();

        $templates = $this->getSmsTemplates();

        return view('sms.edit', compact('smsMessage', 'contacts', 'providers', 'templates'));
    }

    /**
     * Update SMS message
     */
    public function update(Request $request, SmsMessage $smsMessage)
    {
        // Only allow updating of scheduled messages
        if ($smsMessage->status !== 'scheduled') {
            return redirect()->route('sms.show', $smsMessage)
                ->with('error', 'Only scheduled SMS messages can be updated.');
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1600',
            'schedule_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $smsMessage->update([
                'message' => $this->personalizeMessage($request->message, $smsMessage->contact),
                'scheduled_at' => $request->schedule_at,
                'metadata' => array_merge(
                    json_decode($smsMessage->metadata, true) ?? [],
                    [
                        'updated_at' => now()->toISOString(),
                        'updated_by' => Auth::id()
                    ]
                )
            ]);

            return redirect()->route('sms.show', $smsMessage)
                ->with('success', 'SMS message updated successfully.');

        } catch (\Exception $e) {
            Log::error('SMS update failed', [
                'sms_id' => $smsMessage->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update SMS: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete SMS message
     */
    public function destroy(SmsMessage $smsMessage)
    {
        try {
            // Only allow deletion of scheduled, failed, or draft messages
            if (!in_array($smsMessage->status, ['scheduled', 'failed', 'draft'])) {
                return redirect()->route('sms.index')
                    ->with('error', 'Cannot delete sent SMS messages.');
            }

            $smsMessage->delete();

            return redirect()->route('sms.index')
                ->with('success', 'SMS message deleted successfully.');

        } catch (\Exception $e) {
            Log::error('SMS deletion failed', [
                'sms_id' => $smsMessage->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('sms.index')
                ->with('error', 'Failed to delete SMS message.');
        }
    }

    /**
     * Bulk send SMS to contact segment
     */
    public function sendToSegment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'segment_id' => 'required|exists:contact_segments,id',
            'message' => 'required|string|max:1600',
            'provider_id' => 'nullable|exists:sms_providers,id',
            'schedule_at' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $segment = ContactSegment::with(['contacts' => function($query) {
                $query->whereNotNull('phone')->where('phone', '!=', '');
            }])->findOrFail($request->segment_id);

            if ($segment->contacts->isEmpty()) {
                return response()->json(['error' => 'No contacts with valid phone numbers found in this segment.'], 400);
            }

            // Create job for bulk SMS sending
            $recipientIds = $segment->contacts->pluck('id')->toArray();
            
            $newRequest = new Request([
                'message' => $request->message,
                'recipients' => $recipientIds,
                'provider_id' => $request->provider_id,
                'schedule_at' => $request->schedule_at,
                'send_type' => $request->schedule_at ? 'scheduled' : 'now'
            ]);

            return $this->store($newRequest);

        } catch (\Exception $e) {
            Log::error('Bulk SMS to segment failed', [
                'segment_id' => $request->segment_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to send SMS to segment: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get SMS statistics
     */
    public function statistics()
    {
        $stats = $this->getSmsStatistics();
        
        return response()->json($stats);
    }

    /**
     * Cancel scheduled SMS
     */
    public function cancel(SmsMessage $smsMessage)
    {
        if ($smsMessage->status !== 'scheduled') {
            return redirect()->back()
                ->with('error', 'Only scheduled SMS messages can be cancelled.');
        }

        try {
            $smsMessage->update([
                'status' => 'cancelled',
                'metadata' => array_merge(
                    json_decode($smsMessage->metadata, true) ?? [],
                    [
                        'cancelled_at' => now()->toISOString(),
                        'cancelled_by' => Auth::id()
                    ]
                )
            ]);

            return redirect()->back()
                ->with('success', 'SMS message cancelled successfully.');

        } catch (\Exception $e) {
            Log::error('SMS cancellation failed', [
                'sms_id' => $smsMessage->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to cancel SMS message.');
        }
    }

    /**
     * Resend failed SMS
     */
    public function resend(SmsMessage $smsMessage)
    {
        if ($smsMessage->status !== 'failed') {
            return redirect()->back()
                ->with('error', 'Only failed SMS messages can be resent.');
        }

        try {
            $response = $this->smsService->sendSms(
                $smsMessage->phone_number,
                $smsMessage->message
            );

            $smsMessage->update([
                'status' => $response['success'] ? 'sent' : 'failed',
                'provider_message_id' => $response['message_id'] ?? null,
                'sent_at' => $response['success'] ? now() : $smsMessage->sent_at,
                'error_message' => $response['error'] ?? null,
                'cost' => ($smsMessage->cost ?? 0) + ($response['cost'] ?? 0),
                'metadata' => array_merge(
                    json_decode($smsMessage->metadata, true) ?? [],
                    [
                        'resent_at' => now()->toISOString(),
                        'resent_by' => Auth::id()
                    ]
                )
            ]);

            $message = $response['success'] ? 'SMS resent successfully.' : 'Failed to resend SMS: ' . ($response['error'] ?? 'Unknown error');
            $type = $response['success'] ? 'success' : 'error';

            return redirect()->back()->with($type, $message);

        } catch (\Exception $e) {
            Log::error('SMS resend failed', [
                'sms_id' => $smsMessage->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to resend SMS: ' . $e->getMessage());
        }
    }

    /**
     * Get SMS statistics for dashboard
     */
    private function getSmsStatistics()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        return [
            'total_sent' => SmsMessage::where('status', 'sent')->count(),
            'total_failed' => SmsMessage::where('status', 'failed')->count(),
            'total_scheduled' => SmsMessage::where('status', 'scheduled')->count(),
            'today_sent' => SmsMessage::where('status', 'sent')
                ->whereDate('sent_at', $today)->count(),
            'this_month_sent' => SmsMessage::where('status', 'sent')
                ->where('sent_at', '>=', $thisMonth)->count(),
            'total_cost' => SmsMessage::sum('cost'),
            'this_month_cost' => SmsMessage::where('sent_at', '>=', $thisMonth)
                ->sum('cost'),
            'delivery_rate' => $this->calculateDeliveryRate(),
            'providers_stats' => $this->getProviderStats(),
            'recent_activity' => $this->getRecentActivity()
        ];
    }

    /**
     * Calculate SMS delivery rate
     */
    private function calculateDeliveryRate()
    {
        $total = SmsMessage::whereIn('status', ['sent', 'failed', 'delivered'])->count();
        $delivered = SmsMessage::whereIn('status', ['sent', 'delivered'])->count();
        
        return $total > 0 ? round(($delivered / $total) * 100, 2) : 0;
    }

    /**
     * Get provider statistics
     */
    private function getProviderStats()
    {
        return SmsMessage::select('provider', DB::raw('count(*) as count'), DB::raw('sum(cost) as total_cost'))
            ->whereNotNull('provider')
            ->groupBy('provider')
            ->orderByDesc('count')
            ->get();
    }

    /**
     * Get recent SMS activity
     */
    private function getRecentActivity()
    {
        return SmsMessage::with(['contact', 'user'])
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Personalize SMS message with contact data
     */
    private function personalizeMessage($message, $contact)
    {
        $replacements = [
            '{name}' => $contact->name,
            '{first_name}' => $contact->first_name ?? $contact->name,
            '{last_name}' => $contact->last_name ?? '',
            '{email}' => $contact->email,
            '{phone}' => $contact->phone,
            '{company}' => $contact->company ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Get SMS templates
     */
    private function getSmsTemplates()
    {
        return [
            [
                'name' => 'Welcome Message',
                'content' => 'Welcome {name}! Thanks for joining us. We\'re excited to have you on board!'
            ],
            [
                'name' => 'Appointment Reminder',
                'content' => 'Hi {name}, this is a reminder about your appointment scheduled for today. See you soon!'
            ],
            [
                'name' => 'Thank You',
                'content' => 'Thank you {name} for your business. We appreciate your trust in our services.'
            ],
            [
                'name' => 'Follow Up',
                'content' => 'Hi {name}, we wanted to follow up with you. Please let us know if you need any assistance.'
            ],
            [
                'name' => 'Promotional',
                'content' => 'Hi {name}! Don\'t miss our special offer. Limited time only. Contact us for details!'
            ]
        ];
    }
}
