<?php

namespace App\Http\Controllers;

use App\Models\ContactSegment;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContactSegmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of contact segments
     */
    public function index(Request $request)
    {
        $query = ContactSegment::withCount(['contacts']);

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $segments = $query->orderBy('name')->paginate(20);

        // Add calculated fields for each segment
        foreach ($segments as $segment) {
            if ($segment->type === 'dynamic') {
                // Refresh dynamic segment count
                $segment->contacts_count = $this->calculateDynamicSegmentCount($segment);
            }
            
            $segment->engagement_rate = $this->calculateSegmentEngagement($segment);
            $segment->last_used_at = $this->getSegmentLastUsed($segment);
        }

        $segmentTypes = [
            'static' => 'Static',
            'dynamic' => 'Dynamic'
        ];

        return view('segments.index', compact('segments', 'segmentTypes'));
    }

    /**
     * Show the form for creating a new contact segment
     */
    public function create()
    {
        $segmentTypes = [
            'static' => 'Static Segment',
            'dynamic' => 'Dynamic Segment'
        ];

        // Available fields for dynamic segmentation
        $availableFields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
            'position' => 'Position',
            'city' => 'City',
            'country' => 'Country',
            'source' => 'Source',
            'status' => 'Status',
            'tags' => 'Tags',
            'created_at' => 'Date Added',
            'updated_at' => 'Last Updated',
        ];

        $operators = [
            'equals' => 'Equals',
            'not_equals' => 'Not Equals',
            'contains' => 'Contains',
            'not_contains' => 'Does Not Contain',
            'starts_with' => 'Starts With',
            'ends_with' => 'Ends With',
            'is_empty' => 'Is Empty',
            'is_not_empty' => 'Is Not Empty',
            'greater_than' => 'Greater Than',
            'less_than' => 'Less Than',
            'in_last_days' => 'In Last X Days',
            'not_in_last_days' => 'Not In Last X Days',
        ];

        return view('segments.create', compact('segmentTypes', 'availableFields', 'operators'));
    }

    /**
     * Store a newly created contact segment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:contact_segments,name',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:static,dynamic',
            'conditions' => 'required_if:type,dynamic|nullable|array',
            'conditions.*.field' => 'required_if:type,dynamic|string',
            'conditions.*.operator' => 'required_if:type,dynamic|string',
            'conditions.*.value' => 'nullable|string',
            'logic' => 'required_if:type,dynamic|in:and,or',
            'contact_ids' => 'required_if:type,static|array',
            'contact_ids.*' => 'exists:contacts,id',
            'color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $segment = ContactSegment::create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'conditions' => $request->type === 'dynamic' ? $request->conditions : null,
                'logic' => $request->type === 'dynamic' ? $request->logic : 'and',
                'color' => $request->color,
            ]);

            if ($request->type === 'static' && $request->filled('contact_ids')) {
                // Add contacts to static segment
                $segment->contacts()->attach($request->contact_ids);
            }

            DB::commit();

            return redirect()->route('segments.show', $segment)
                ->with('success', 'Contact segment created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create segment: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified contact segment
     */
    public function show(ContactSegment $segment, Request $request)
    {
        // Load contacts based on segment type
        if ($segment->type === 'static') {
            $query = $segment->contacts();
        } else {
            // For dynamic segments, apply conditions
            $query = $this->buildDynamicQuery($segment);
        }

        // Apply additional filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contacts = $query->paginate(20);

        // Segment statistics
        $stats = [
            'total_contacts' => $segment->type === 'static' ? 
                $segment->contacts()->count() : 
                $this->calculateDynamicSegmentCount($segment),
            'email_campaigns_sent' => $this->getEmailCampaignsSent($segment),
            'sms_campaigns_sent' => $this->getSmsCampaignsSent($segment),
            'whatsapp_messages_sent' => $this->getWhatsAppMessagesSent($segment),
            'engagement_rate' => $this->calculateSegmentEngagement($segment),
            'avg_open_rate' => $this->getAverageOpenRate($segment),
            'avg_click_rate' => $this->getAverageClickRate($segment),
        ];

        // Recent activity
        $recentActivity = $this->getSegmentRecentActivity($segment);

        return view('segments.show', compact('segment', 'contacts', 'stats', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified contact segment
     */
    public function edit(ContactSegment $segment)
    {
        $segmentTypes = [
            'static' => 'Static Segment',
            'dynamic' => 'Dynamic Segment'
        ];

        $availableFields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
            'position' => 'Position',
            'city' => 'City',
            'country' => 'Country',
            'source' => 'Source',
            'status' => 'Status',
            'tags' => 'Tags',
            'created_at' => 'Date Added',
            'updated_at' => 'Last Updated',
        ];

        $operators = [
            'equals' => 'Equals',
            'not_equals' => 'Not Equals',
            'contains' => 'Contains',
            'not_contains' => 'Does Not Contain',
            'starts_with' => 'Starts With',
            'ends_with' => 'Ends With',
            'is_empty' => 'Is Empty',
            'is_not_empty' => 'Is Not Empty',
            'greater_than' => 'Greater Than',
            'less_than' => 'Less Than',
            'in_last_days' => 'In Last X Days',
            'not_in_last_days' => 'Not In Last X Days',
        ];

        // For static segments, get current contacts
        $selectedContacts = [];
        if ($segment->type === 'static') {
            $selectedContacts = $segment->contacts()->pluck('contacts.id')->toArray();
        }

        return view('segments.edit', compact('segment', 'segmentTypes', 'availableFields', 'operators', 'selectedContacts'));
    }

    /**
     * Update the specified contact segment
     */
    public function update(Request $request, ContactSegment $segment)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('contact_segments', 'name')->ignore($segment->id)],
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:static,dynamic',
            'conditions' => 'required_if:type,dynamic|nullable|array',
            'conditions.*.field' => 'required_if:type,dynamic|string',
            'conditions.*.operator' => 'required_if:type,dynamic|string',
            'conditions.*.value' => 'nullable|string',
            'logic' => 'required_if:type,dynamic|in:and,or',
            'contact_ids' => 'required_if:type,static|array',
            'contact_ids.*' => 'exists:contacts,id',
            'color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $segment->update([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'conditions' => $request->type === 'dynamic' ? $request->conditions : null,
                'logic' => $request->type === 'dynamic' ? $request->logic : 'and',
                'color' => $request->color,
            ]);

            if ($request->type === 'static') {
                // Update static segment contacts
                $segment->contacts()->sync($request->contact_ids ?? []);
            } else {
                // For dynamic segments, clear any existing static contacts
                $segment->contacts()->detach();
            }

            DB::commit();

            return redirect()->route('segments.show', $segment)
                ->with('success', 'Contact segment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update segment: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified contact segment
     */
    public function destroy(ContactSegment $segment)
    {
        try {
            // Check if segment is being used in campaigns
            $emailCampaigns = $segment->emailCampaigns()->count();
            $smsCampaigns = 0; // TODO: Add when SMS campaigns are implemented
            
            if ($emailCampaigns > 0) {
                return back()->withErrors(['error' => "Cannot delete segment that is used in {$emailCampaigns} email campaign(s)."]);
            }

            $segment->delete();
            
            return redirect()->route('segments.index')
                ->with('success', 'Contact segment deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete segment: ' . $e->getMessage()]);
        }
    }

    /**
     * Refresh dynamic segment (recalculate contacts)
     */
    public function refresh(ContactSegment $segment)
    {
        if ($segment->type !== 'dynamic') {
            return back()->withErrors(['error' => 'Only dynamic segments can be refreshed.']);
        }

        try {
            // Update the segment's updated_at timestamp to trigger recalculation
            $segment->touch();
            
            $contactCount = $this->calculateDynamicSegmentCount($segment);
            
            return back()->with('success', "Dynamic segment refreshed. Found {$contactCount} contacts.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to refresh segment: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate an existing segment
     */
    public function duplicate(ContactSegment $segment)
    {
        try {
            DB::beginTransaction();

            $newSegment = $segment->replicate();
            $newSegment->name = $segment->name . ' (Copy)';
            $newSegment->save();

            // If it's a static segment, copy the contacts
            if ($segment->type === 'static') {
                $contactIds = $segment->contacts()->pluck('contacts.id')->toArray();
                $newSegment->contacts()->attach($contactIds);
            }

            DB::commit();

            return redirect()->route('segments.edit', $newSegment)
                ->with('success', 'Segment duplicated successfully. Please review and save.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to duplicate segment: ' . $e->getMessage()]);
        }
    }

    /**
     * Add contacts to segment
     */
    public function addContacts(Request $request, ContactSegment $segment)
    {
        if ($segment->type !== 'static') {
            return response()->json(['success' => false, 'message' => 'Can only add contacts to static segments'], 400);
        }

        $validator = Validator::make($request->all(), [
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $addedCount = 0;
            foreach ($request->contact_ids as $contactId) {
                if (!$segment->contacts()->where('contact_id', $contactId)->exists()) {
                    $segment->contacts()->attach($contactId);
                    $addedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Added {$addedCount} contacts to segment."
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add contacts'], 500);
        }
    }

    /**
     * Remove contacts from segment
     */
    public function removeContacts(Request $request, ContactSegment $segment)
    {
        if ($segment->type !== 'static') {
            return response()->json(['success' => false, 'message' => 'Can only remove contacts from static segments'], 400);
        }

        $validator = Validator::make($request->all(), [
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $segment->contacts()->detach($request->contact_ids);

            return response()->json([
                'success' => true,
                'message' => 'Contacts removed from segment successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to remove contacts'], 500);
        }
    }

    /**
     * Build query for dynamic segment
     */
    private function buildDynamicQuery(ContactSegment $segment)
    {
        $query = Contact::query();
        
        if (!$segment->conditions || empty($segment->conditions)) {
            return $query->where('id', 0); // No results for empty conditions
        }

        $conditions = $segment->conditions;
        $logic = $segment->logic ?? 'and';

        if ($logic === 'or') {
            $query->where(function($q) use ($conditions) {
                foreach ($conditions as $condition) {
                    $q->orWhere(function($subQ) use ($condition) {
                        $this->applyCondition($subQ, $condition);
                    });
                }
            });
        } else {
            foreach ($conditions as $condition) {
                $query->where(function($q) use ($condition) {
                    $this->applyCondition($q, $condition);
                });
            }
        }

        return $query;
    }

    /**
     * Apply individual condition to query
     */
    private function applyCondition($query, $condition)
    {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = $condition['value'] ?? '';

        switch ($operator) {
            case 'equals':
                $query->where($field, '=', $value);
                break;
            case 'not_equals':
                $query->where($field, '!=', $value);
                break;
            case 'contains':
                $query->where($field, 'like', "%{$value}%");
                break;
            case 'not_contains':
                $query->where($field, 'not like', "%{$value}%");
                break;
            case 'starts_with':
                $query->where($field, 'like', "{$value}%");
                break;
            case 'ends_with':
                $query->where($field, 'like', "%{$value}");
                break;
            case 'is_empty':
                $query->where(function($q) use ($field) {
                    $q->whereNull($field)->orWhere($field, '');
                });
                break;
            case 'is_not_empty':
                $query->whereNotNull($field)->where($field, '!=', '');
                break;
            case 'greater_than':
                $query->where($field, '>', $value);
                break;
            case 'less_than':
                $query->where($field, '<', $value);
                break;
            case 'in_last_days':
                $query->where($field, '>=', now()->subDays(intval($value)));
                break;
            case 'not_in_last_days':
                $query->where($field, '<', now()->subDays(intval($value)));
                break;
        }
    }

    /**
     * Calculate dynamic segment count
     */
    private function calculateDynamicSegmentCount(ContactSegment $segment)
    {
        if ($segment->type !== 'dynamic') {
            return $segment->contacts()->count();
        }

        return $this->buildDynamicQuery($segment)->count();
    }

    /**
     * Calculate segment engagement rate
     */
    private function calculateSegmentEngagement(ContactSegment $segment)
    {
        // TODO: Implement based on email opens, clicks, SMS delivery, etc.
        return 0;
    }

    /**
     * Get when segment was last used
     */
    private function getSegmentLastUsed(ContactSegment $segment)
    {
        // TODO: Check last email campaign, SMS campaign, etc.
        return null;
    }

    /**
     * Get email campaigns sent to segment
     */
    private function getEmailCampaignsSent(ContactSegment $segment)
    {
        return $segment->emailCampaigns()->count();
    }

    /**
     * Get SMS campaigns sent to segment
     */
    private function getSmsCampaignsSent(ContactSegment $segment)
    {
        // TODO: Implement when SMS campaigns are added
        return 0;
    }

    /**
     * Get WhatsApp messages sent to segment
     */
    private function getWhatsAppMessagesSent(ContactSegment $segment)
    {
        // TODO: Implement based on WhatsApp bulk sends
        return 0;
    }

    /**
     * Get average open rate for segment
     */
    private function getAverageOpenRate(ContactSegment $segment)
    {
        // TODO: Calculate from email campaigns sent to this segment
        return 0;
    }

    /**
     * Get average click rate for segment
     */
    private function getAverageClickRate(ContactSegment $segment)
    {
        // TODO: Calculate from email campaigns sent to this segment
        return 0;
    }

    /**
     * Get recent activity for segment
     */
    private function getSegmentRecentActivity(ContactSegment $segment)
    {
        // TODO: Get recent campaigns, messages, etc. sent to this segment
        return collect();
    }
}
