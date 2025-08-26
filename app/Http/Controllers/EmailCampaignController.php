<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\SmtpConfig;
use App\Models\Contact;
use App\Models\ContactSegment;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class EmailCampaignController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of email campaigns
     */
    public function index(Request $request)
    {
        $query = EmailCampaign::with(['creator', 'template', 'smtpConfig'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by name or subject
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('subject', 'like', '%' . $request->search . '%');
            });
        }

        $campaigns = $query->paginate(15);

        $stats = [
            'total' => EmailCampaign::count(),
            'active' => EmailCampaign::where('status', 'active')->count(),
            'sent' => EmailCampaign::where('status', 'sent')->count(),
            'draft' => EmailCampaign::where('status', 'draft')->count(),
            'scheduled' => EmailCampaign::where('status', 'scheduled')->count(),
        ];

        return view('email.campaigns.index', compact('campaigns', 'stats'));
    }

    /**
     * Show the form for creating a new email campaign
     */
    public function create()
    {
        $templates = EmailTemplate::where('is_active', true)->get();
        $smtpConfigs = SmtpConfig::where('is_active', true)->get();
        $segments = ContactSegment::all();

        return view('email.campaigns.create', compact('templates', 'smtpConfigs', 'segments'));
    }

    /**
     * Store a newly created email campaign
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'content' => 'required|string',
            'smtp_config_id' => 'required|exists:smtp_configs,id',
            'template_id' => 'nullable|exists:email_templates,id',
            'scheduled_at' => 'nullable|date|after:now',
            'recipients_type' => 'required|in:all,segments,manual',
            'segment_ids' => 'required_if:recipients_type,segments|array',
            'segment_ids.*' => 'exists:contact_segments,id',
            'contact_ids' => 'required_if:recipients_type,manual|array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $campaign = $this->emailService->createCampaign($request->all());

            // Add recipients based on type
            $contactIds = $this->getRecipientsFromRequest($request);
            if (!empty($contactIds)) {
                $this->emailService->addContactsToCampaign($campaign, $contactIds);
            }

            return redirect()->route('email.campaigns.show', $campaign)
                ->with('success', 'Email campaign created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create campaign: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified email campaign
     */
    public function show(EmailCampaign $emailCampaign)
    {
        $emailCampaign->load(['creator', 'template', 'smtpConfig', 'contacts']);

        $stats = [
            'total_recipients' => $emailCampaign->total_recipients,
            'sent_count' => $emailCampaign->sent_count,
            'delivered_count' => $emailCampaign->delivered_count,
            'opened_count' => $emailCampaign->opened_count,
            'clicked_count' => $emailCampaign->clicked_count,
            'bounced_count' => $emailCampaign->bounced_count,
            'failed_count' => $emailCampaign->failed_count,
            'open_rate' => $emailCampaign->open_rate,
            'click_rate' => $emailCampaign->click_rate,
            'bounce_rate' => $emailCampaign->bounce_rate,
        ];

        return view('email.campaigns.show', compact('emailCampaign', 'stats'));
    }

    /**
     * Show the form for editing the specified email campaign
     */
    public function edit(EmailCampaign $emailCampaign)
    {
        // Only allow editing draft campaigns
        if ($emailCampaign->status !== 'draft') {
            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('error', 'Only draft campaigns can be edited.');
        }

        $templates = EmailTemplate::where('is_active', true)->get();
        $smtpConfigs = SmtpConfig::where('is_active', true)->get();
        $segments = ContactSegment::all();

        return view('email.campaigns.edit', compact('emailCampaign', 'templates', 'smtpConfigs', 'segments'));
    }

    /**
     * Update the specified email campaign
     */
    public function update(Request $request, EmailCampaign $emailCampaign)
    {
        // Only allow updating draft campaigns
        if ($emailCampaign->status !== 'draft') {
            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('error', 'Only draft campaigns can be updated.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'content' => 'required|string',
            'smtp_config_id' => 'required|exists:smtp_configs,id',
            'template_id' => 'nullable|exists:email_templates,id',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $emailCampaign->update($request->only([
                'name', 'subject', 'content', 'smtp_config_id', 
                'template_id', 'scheduled_at'
            ]));

            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('success', 'Email campaign updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update campaign: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified email campaign
     */
    public function destroy(EmailCampaign $emailCampaign)
    {
        // Only allow deletion of draft campaigns
        if ($emailCampaign->status !== 'draft') {
            return redirect()->route('email.campaigns.index')
                ->with('error', 'Only draft campaigns can be deleted.');
        }

        try {
            $emailCampaign->delete();

            return redirect()->route('email.campaigns.index')
                ->with('success', 'Email campaign deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete campaign: ' . $e->getMessage());
        }
    }

    /**
     * Send the email campaign immediately
     */
    public function send(EmailCampaign $emailCampaign)
    {
        if (!in_array($emailCampaign->status, ['draft', 'scheduled'])) {
            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('error', 'Campaign cannot be sent in its current status.');
        }

        if ($emailCampaign->total_recipients == 0) {
            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('error', 'Cannot send campaign with no recipients.');
        }

        try {
            $this->emailService->sendCampaign($emailCampaign);

            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('success', 'Email campaign sent successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send campaign: ' . $e->getMessage());
        }
    }

    /**
     * Pause a running email campaign
     */
    public function pause(EmailCampaign $emailCampaign)
    {
        if ($emailCampaign->status !== 'active') {
            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('error', 'Only active campaigns can be paused.');
        }

        try {
            $emailCampaign->update(['status' => 'paused']);

            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('success', 'Email campaign paused successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to pause campaign: ' . $e->getMessage());
        }
    }

    /**
     * Resume a paused email campaign
     */
    public function resume(EmailCampaign $emailCampaign)
    {
        if ($emailCampaign->status !== 'paused') {
            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('error', 'Only paused campaigns can be resumed.');
        }

        try {
            $emailCampaign->update(['status' => 'active']);

            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('success', 'Email campaign resumed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to resume campaign: ' . $e->getMessage());
        }
    }

    /**
     * Schedule an email campaign
     */
    public function schedule(Request $request, EmailCampaign $emailCampaign)
    {
        if ($emailCampaign->status !== 'draft') {
            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('error', 'Only draft campaigns can be scheduled.');
        }

        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $emailCampaign->update([
                'scheduled_at' => $request->scheduled_at,
                'status' => 'scheduled'
            ]);

            return redirect()->route('email.campaigns.show', $emailCampaign)
                ->with('success', 'Email campaign scheduled successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to schedule campaign: ' . $e->getMessage());
        }
    }

    /**
     * Show campaign statistics
     */
    public function stats(EmailCampaign $emailCampaign)
    {
        $emailCampaign->load(['contacts', 'logs']);

        $hourlyStats = $this->emailService->getHourlyStats($emailCampaign);
        $clickStats = $this->emailService->getClickStats($emailCampaign);
        $deviceStats = $this->emailService->getDeviceStats($emailCampaign);

        return view('email.campaigns.stats', compact('emailCampaign', 'hourlyStats', 'clickStats', 'deviceStats'));
    }

    /**
     * Duplicate an existing campaign
     */
    public function duplicate(EmailCampaign $emailCampaign)
    {
        try {
            $duplicatedCampaign = $this->emailService->duplicateCampaign($emailCampaign);

            return redirect()->route('email.campaigns.edit', $duplicatedCampaign)
                ->with('success', 'Campaign duplicated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to duplicate campaign: ' . $e->getMessage());
        }
    }

    /**
     * Preview campaign content
     */
    public function preview(EmailCampaign $emailCampaign, Request $request)
    {
        $contactId = $request->get('contact_id');
        $contact = $contactId ? Contact::find($contactId) : Contact::first();

        if (!$contact) {
            return response()->json(['error' => 'No contact available for preview'], 404);
        }

        try {
            $previewContent = $this->emailService->generatePreview($emailCampaign, $contact);
            
            return response()->json([
                'subject' => $previewContent['subject'],
                'content' => $previewContent['content'],
                'contact' => $contact->only(['first_name', 'last_name', 'email'])
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate preview: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get recipients from request based on type
     */
    private function getRecipientsFromRequest(Request $request): array
    {
        $contactIds = [];

        switch ($request->recipients_type) {
            case 'all':
                $contactIds = Contact::pluck('id')->toArray();
                break;
                
            case 'segments':
                if ($request->segment_ids) {
                    $contactIds = Contact::whereHas('segments', function($query) use ($request) {
                        $query->whereIn('contact_segments.id', $request->segment_ids);
                    })->pluck('id')->toArray();
                }
                break;
                
            case 'manual':
                $contactIds = $request->contact_ids ?? [];
                break;
        }

        return array_unique($contactIds);
    }
}
