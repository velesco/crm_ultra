<?php

namespace App\Http\Controllers;

use App\Models\SmsMessage;
use App\Models\SmsProvider;
use App\Models\Contact;
use App\Models\ContactSegment;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
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
     * Display a listing of SMS messages
     */
    public function index(Request $request)
    {
        $query = SmsMessage::with(['contact', 'smsProvider', 'user'])
            ->latest('created_at');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider_id')) {
            $query->where('sms_provider_id', $request->provider_id);
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

        // Statistics for dashboard
        $stats = [
            'total_sent' => SmsMessage::count(),
            'sent_today' => SmsMessage::whereDate('created_at', today())->count(),
            'delivered' => SmsMessage::where('status', 'delivered')->count(),
            'failed' => SmsMessage::where('status', 'failed')->count(),
            'pending' => SmsMessage::where('status', 'pending')->count(),
            'delivery_rate' => SmsMessage::count() > 0 ? 
                round((SmsMessage::where('status', 'delivered')->count() / SmsMessage::count()) * 100, 2) : 0,
        ];

        $providers = SmsProvider::where('is_active', true)->get();

        return view('sms.index', compact('messages', 'stats', 'providers'));
    }

    /**
     * Show the form for creating a new SMS message
     */
    public function create()
    {
        $contacts = Contact::select('id', 'first_name', 'last_name', 'phone')
            ->where('phone', '!=', null)
            ->orderBy('first_name')
            ->get();

        $segments = ContactSegment::all();
        $providers = SmsProvider::where('is_active', true)->get();

        return view('sms.create', compact('contacts', 'segments', 'providers'));
    }

    /**
     * Store a newly created SMS message
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1600',
            'send_type' => 'required|in:individual,bulk,segment',
            'contact_id' => 'required_if:send_type,individual|exists:contacts,id',
            'contact_ids' => 'required_if:send_type,bulk|array|min:1',
            'contact_ids.*' => 'exists:contacts,id',
            'segment_id' => 'required_if:send_type,segment|exists:contact_segments,id',
            'provider_id' => 'nullable|exists:sms_providers,id',
            'schedule_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $message = $request->message;
            $scheduleAt = $request->schedule_at ? Carbon::parse($request->schedule_at) : null;
            $providerId = $request->provider_id;

            $contacts = $this->getContactsBasedOnSendType($request);

            if ($contacts->isEmpty()) {
                return back()->withErrors(['contacts' => 'No valid contacts found for SMS sending.'])->withInput();
            }

            $sentCount = 0;
            $failedCount = 0;

            foreach ($contacts as $contact) {
                if (!$contact->phone) {
                    $failedCount++;
                    continue;
                }

                try {
                    // Send SMS immediately or schedule
                    if ($scheduleAt) {
                        $this->scheduleMessage($contact, $message, $scheduleAt, $providerId);
                    } else {
                        $result = $this->smsService->sendSms(
                            $contact->phone,
                            $message,
                            $contact->id,
                            $providerId
                        );
                        
                        if ($result['success']) {
                            $sentCount++;
                        } else {
                            $failedCount++;
                        }
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    \Log::error('SMS send error: ' . $e->getMessage());
                }
            }

            $totalContacts = $contacts->count();
            
            if ($scheduleAt) {
                return redirect()->route('sms.index')
                    ->with('success', "SMS scheduled successfully for {$totalContacts} contacts on " . $scheduleAt->format('d/m/Y H:i'));
            } else {
                $message = "SMS sending completed. Sent: {$sentCount}, Failed: {$failedCount} out of {$totalContacts} contacts.";
                return redirect()->route('sms.index')->with('success', $message);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while processing SMS: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified SMS message
     */
    public function show(SmsMessage $sms)
    {
        $sms->load(['contact', 'smsProvider', 'user']);
        
        return view('sms.show', compact('sms'));
    }

    /**
     * Show the form for editing the specified SMS message
     */
    public function edit(SmsMessage $sms)
    {
        // Only allow editing of failed or scheduled messages
        if (!in_array($sms->status, ['failed', 'scheduled'])) {
            return redirect()->route('sms.show', $sms)
                ->withErrors(['error' => 'Only failed or scheduled SMS messages can be edited.']);
        }

        $contacts = Contact::select('id', 'first_name', 'last_name', 'phone')
            ->where('phone', '!=', null)
            ->orderBy('first_name')
            ->get();

        $providers = SmsProvider::where('is_active', true)->get();

        return view('sms.edit', compact('sms', 'contacts', 'providers'));
    }

    /**
     * Update the specified SMS message
     */
    public function update(Request $request, SmsMessage $sms)
    {
        // Only allow updating of failed or scheduled messages
        if (!in_array($sms->status, ['failed', 'scheduled'])) {
            return redirect()->route('sms.show', $sms)
                ->withErrors(['error' => 'Only failed or scheduled SMS messages can be updated.']);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1600',
            'phone_number' => 'required|string',
            'provider_id' => 'nullable|exists:sms_providers,id',
            'schedule_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $sms->update([
                'message' => $request->message,
                'phone_number' => $request->phone_number,
                'sms_provider_id' => $request->provider_id,
                'scheduled_at' => $request->schedule_at ? Carbon::parse($request->schedule_at) : null,
                'status' => $request->schedule_at ? 'scheduled' : 'pending',
            ]);

            return redirect()->route('sms.show', $sms)
                ->with('success', 'SMS message updated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while updating SMS: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified SMS message from storage
     */
    public function destroy(SmsMessage $sms)
    {
        try {
            $sms->delete();
            
            return redirect()->route('sms.index')
                ->with('success', 'SMS message deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while deleting SMS: ' . $e->getMessage()]);
        }
    }

    /**
     * Resend a failed SMS message
     */
    public function resend(SmsMessage $sms)
    {
        if ($sms->status !== 'failed') {
            return back()->withErrors(['error' => 'Only failed SMS messages can be resent.']);
        }

        try {
            $result = $this->smsService->sendSms(
                $sms->phone_number,
                $sms->message,
                $sms->contact_id,
                $sms->sms_provider_id
            );

            if ($result['success']) {
                return back()->with('success', 'SMS message resent successfully.');
            } else {
                return back()->withErrors(['error' => 'Failed to resend SMS: ' . ($result['error'] ?? 'Unknown error')]);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while resending SMS: ' . $e->getMessage()]);
        }
    }

    /**
     * Show SMS statistics and reports
     */
    public function stats(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Daily SMS statistics
        $dailyStats = SmsMessage::selectRaw('DATE(created_at) as date, 
                                             COUNT(*) as total,
                                             SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered,
                                             SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                                             SUM(cost) as total_cost')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Provider statistics
        $providerStats = SmsMessage::with('smsProvider')
            ->selectRaw('sms_provider_id, 
                         COUNT(*) as total,
                         SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered,
                         SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                         SUM(cost) as total_cost')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('sms_provider_id')
            ->get();

        // Overall statistics
        $overallStats = [
            'total_messages' => SmsMessage::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'delivered_messages' => SmsMessage::where('status', 'delivered')
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'failed_messages' => SmsMessage::where('status', 'failed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_cost' => SmsMessage::whereBetween('created_at', [$dateFrom, $dateTo])->sum('cost'),
            'average_cost' => SmsMessage::whereBetween('created_at', [$dateFrom, $dateTo])->avg('cost'),
        ];

        $overallStats['delivery_rate'] = $overallStats['total_messages'] > 0 ? 
            round(($overallStats['delivered_messages'] / $overallStats['total_messages']) * 100, 2) : 0;

        return view('sms.stats', compact('dailyStats', 'providerStats', 'overallStats', 'dateFrom', 'dateTo'));
    }

    /**
     * Handle webhook for SMS delivery status updates
     */
    public function webhook(Request $request, $provider = null)
    {
        try {
            $result = $this->smsService->handleWebhook($request->all(), $provider);
            
            if ($result['success']) {
                return response()->json(['status' => 'success'], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => $result['error']], 400);
            }

        } catch (\Exception $e) {
            \Log::error('SMS webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    /**
     * Get contacts based on send type
     */
    private function getContactsBasedOnSendType(Request $request)
    {
        switch ($request->send_type) {
            case 'individual':
                return Contact::where('id', $request->contact_id)
                    ->where('phone', '!=', null)
                    ->get();

            case 'bulk':
                return Contact::whereIn('id', $request->contact_ids)
                    ->where('phone', '!=', null)
                    ->get();

            case 'segment':
                $segment = ContactSegment::with('contacts')->find($request->segment_id);
                return $segment ? $segment->contacts()->where('phone', '!=', null)->get() : collect();

            default:
                return collect();
        }
    }

    /**
     * Schedule SMS message for later sending
     */
    private function scheduleMessage($contact, $message, $scheduleAt, $providerId = null)
    {
        SmsMessage::create([
            'contact_id' => $contact->id,
            'user_id' => Auth::id(),
            'sms_provider_id' => $providerId,
            'phone_number' => $contact->phone,
            'message' => $message,
            'status' => 'scheduled',
            'scheduled_at' => $scheduleAt,
            'created_at' => now(),
        ]);
    }
}
