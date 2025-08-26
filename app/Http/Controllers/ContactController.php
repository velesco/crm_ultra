<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactSegment;
use App\Models\Communication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $query = Contact::with(['creator', 'assignedUser', 'segments'])
            ->where(function($q) {
                $q->where('created_by', auth()->id())
                  ->orWhere('assigned_to', auth()->id());
            });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('company', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by segment
        if ($request->filled('segment')) {
            $query->whereHas('segments', function($q) use ($request) {
                $q->where('contact_segments.id', $request->get('segment'));
            });
        }

        // Filter by tags
        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->get('tag'));
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->get('source'));
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->get('assigned_to'));
        }

        // Date filters
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->get('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->get('created_to'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        
        if ($sortBy === 'name') {
            $query->orderBy('first_name', $sortDir)->orderBy('last_name', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $contacts = $query->paginate(25)->withQueryString();

        // Get filter options
        $segments = ContactSegment::where('created_by', auth()->id())->get();
        $tags = $this->getAvailableTags();
        $sources = Contact::where('created_by', auth()->id())
            ->orWhere('assigned_to', auth()->id())
            ->distinct()
            ->pluck('source')
            ->filter()
            ->sort()
            ->values();
        
        $assignedUsers = \App\Models\User::whereIn('id', 
            Contact::where('created_by', auth()->id())
                ->orWhere('assigned_to', auth()->id())
                ->distinct()
                ->pluck('assigned_to')
                ->filter()
        )->get();

        return view('contacts.index', compact(
            'contacts',
            'segments',
            'tags',
            'sources',
            'assignedUsers'
        ));
    }

    public function create()
    {
        $segments = ContactSegment::where('created_by', auth()->id())->get();
        $users = \App\Models\User::active()->get();
        
        return view('contacts.create', compact('segments', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
            'status' => 'required|in:active,inactive,blocked',
            'source' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'segments' => 'nullable|array',
            'segments.*' => 'exists:contact_segments,id'
        ]);

        DB::beginTransaction();
        try {
            $contact = Contact::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'company' => $request->company,
                'position' => $request->position,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'tags' => $request->tags,
                'notes' => $request->notes,
                'custom_fields' => $request->custom_fields,
                'status' => $request->status,
                'source' => $request->source ?: 'manual',
                'created_by' => auth()->id(),
                'assigned_to' => $request->assigned_to,
            ]);

            // Attach segments
            if ($request->filled('segments')) {
                $contact->segments()->attach($request->segments);
            }

            DB::commit();

            return redirect()->route('contacts.show', $contact)
                ->with('success', 'Contact created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withInput()
                ->with('error', 'Failed to create contact: ' . $e->getMessage());
        }
    }

    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);
        
        $contact->load(['creator', 'assignedUser', 'segments']);
        
        // Get communications history
        $communications = Communication::where('contact_id', $contact->id)
            ->with('user')
            ->latest()
            ->paginate(20);
        
        // Get activity timeline (communications + contact updates)
        $activities = $this->getContactActivities($contact);
        
        return view('contacts.show', compact('contact', 'communications', 'activities'));
    }

    public function edit(Contact $contact)
    {
        $this->authorize('update', $contact);
        
        $contact->load('segments');
        $segments = ContactSegment::where('created_by', auth()->id())->get();
        $users = \App\Models\User::active()->get();
        
        return view('contacts.edit', compact('contact', 'segments', 'users'));
    }

    public function update(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
            'status' => 'required|in:active,inactive,blocked',
            'source' => 'nullable|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'segments' => 'nullable|array',
            'segments.*' => 'exists:contact_segments,id'
        ]);

        DB::beginTransaction();
        try {
            $contact->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'company' => $request->company,
                'position' => $request->position,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'tags' => $request->tags,
                'notes' => $request->notes,
                'custom_fields' => $request->custom_fields,
                'status' => $request->status,
                'source' => $request->source,
                'assigned_to' => $request->assigned_to,
            ]);

            // Sync segments
            $segments = $request->segments ?? [];
            $contact->segments()->sync($segments);

            // Refresh dynamic segments
            $this->refreshDynamicSegments();

            DB::commit();

            return redirect()->route('contacts.show', $contact)
                ->with('success', 'Contact updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withInput()
                ->with('error', 'Failed to update contact: ' . $e->getMessage());
        }
    }

    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);
        
        try {
            $contact->delete();
            
            return redirect()->route('contacts.index')
                ->with('success', 'Contact deleted successfully.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete contact: ' . $e->getMessage());
        }
    }

    private function getAvailableTags()
    {
        return Contact::where('created_by', auth()->id())
            ->orWhere('assigned_to', auth()->id())
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->values()
            ->sort();
    }

    private function getContactActivities(Contact $contact)
    {
        // Get communications
        $communications = Communication::where('contact_id', $contact->id)
            ->with('user')
            ->get();

        // Get contact history (you might want to implement an audit log)
        // For now, we'll just return communications
        return $communications->map(function($comm) {
            return [
                'id' => $comm->id,
                'type' => 'communication',
                'action' => $comm->type,
                'description' => $this->getCommunicationDescription($comm),
                'user' => $comm->user->name,
                'created_at' => $comm->created_at,
                'metadata' => [
                    'direction' => $comm->direction,
                    'status' => $comm->status,
                    'subject' => $comm->subject
                ]
            ];
        })->sortByDesc('created_at');
    }

    private function getCommunicationDescription(Communication $communication)
    {
        $action = ucfirst($communication->type);
        $direction = $communication->direction === 'outbound' ? 'sent' : 'received';
        $subject = $communication->subject ? ' - ' . Str::limit($communication->subject, 50) : '';
        
        return "{$action} {$direction}{$subject}";
    }

    private function refreshDynamicSegments()
    {
        ContactSegment::where('created_by', auth()->id())
            ->where('is_dynamic', true)
            ->each(function($segment) {
                $segment->refreshDynamicContacts();
            });
    }
}
