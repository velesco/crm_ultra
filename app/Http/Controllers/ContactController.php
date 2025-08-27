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
        
        // Get recent activity (last 10 activities)
        $recentActivity = $this->getContactActivities($contact)->take(10);
        
        // Get contact statistics
        $stats = $this->getContactStats($contact);
        
        return view('contacts.show', compact('contact', 'communications', 'recentActivity', 'stats'));
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
            ->latest()
            ->get();

        // Transform communications into activity format
        return $communications->map(function($comm) {
            return (object)[
                'id' => $comm->id,
                'type' => $comm->type, // email, sms, whatsapp
                'description' => $this->getCommunicationDescription($comm),
                'user' => $comm->user ? $comm->user->name : 'System',
                'created_at' => $comm->created_at,
                'metadata' => [
                    'direction' => $comm->direction,
                    'status' => $comm->status,
                    'subject' => $comm->subject
                ]
            ];
        });
    }

    private function getCommunicationDescription(Communication $communication)
    {
        $action = ucfirst($communication->type);
        $direction = $communication->direction === 'outbound' ? 'sent' : 'received';
        $subject = $communication->subject ? ' - ' . Str::limit($communication->subject, 50) : '';
        
        return "{$action} {$direction}{$subject}";
    }

    /**
     * Show import form
     */
    public function import()
    {
        $segments = ContactSegment::where('created_by', auth()->id())->get();
        
        return view('contacts.import', compact('segments'));
    }

    /**
     * Process contact import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240', // Max 10MB
            'segment_id' => 'nullable|exists:contact_segments,id',
            'mapping' => 'required|array',
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        try {
            // Create import job
            $import = \App\Models\DataImport::create([
                'type' => 'contacts',
                'status' => 'processing',
                'file_path' => $request->file('file')->store('imports'),
                'mapping' => $request->mapping,
                'options' => [
                    'segment_id' => $request->segment_id,
                    'skip_duplicates' => $request->boolean('skip_duplicates'),
                    'update_existing' => $request->boolean('update_existing'),
                ],
                'created_by' => auth()->id()
            ]);

            // Dispatch import job
            \App\Jobs\ImportContactsJob::dispatch($import);

            return redirect()->route('contacts.import.status', $import)
                ->with('success', 'Import started successfully! You will be notified when it completes.');
                
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start import: ' . $e->getMessage());
        }
    }

    /**
     * Show import status
     */
    public function importStatus($importId)
    {
        $import = \App\Models\DataImport::findOrFail($importId);
        
        // Check if user owns this import
        if ($import->created_by !== auth()->id()) {
            abort(403);
        }

        return view('contacts.import-status', compact('import'));
    }

    /**
     * Export contacts
     */
    public function export(Request $request)
    {
        $query = Contact::with(['creator', 'assignedUser', 'segments'])
            ->where(function($q) {
                $q->where('created_by', auth()->id())
                  ->orWhere('assigned_to', auth()->id());
            });

        // Apply same filters as index
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

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('segment')) {
            $query->whereHas('segments', function($q) use ($request) {
                $q->where('contact_segments.id', $request->get('segment'));
            });
        }

        $contacts = $query->get();

        $filename = 'contacts_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($contacts) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'First Name', 'Last Name', 'Email', 'Phone', 'Company', 
                'Position', 'Status', 'Source', 'Tags', 'Address', 
                'City', 'Country', 'Notes', 'Created At'
            ]);

            // CSV data
            foreach ($contacts as $contact) {
                fputcsv($file, [
                    $contact->first_name,
                    $contact->last_name,
                    $contact->email,
                    $contact->phone,
                    $contact->company,
                    $contact->position,
                    $contact->status,
                    $contact->source,
                    is_array($contact->tags) ? implode(', ', $contact->tags) : $contact->tags,
                    $contact->address,
                    $contact->city,
                    $contact->country,
                    $contact->notes,
                    $contact->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function refreshDynamicSegments()
    {
        ContactSegment::where('created_by', auth()->id())
            ->where('is_dynamic', true)
            ->each(function($segment) {
                $segment->refreshDynamicContacts();
            });
    }

    /**
     * Search contacts for API/AJAX requests
     */
    public function searchContacts(Request $request)
    {
        $query = Contact::where(function($q) {
            $q->where('created_by', auth()->id())
              ->orWhere('assigned_to', auth()->id());
        });

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('company', 'LIKE', "%{$search}%");
            });
        }

        $contacts = $query->select('id', 'first_name', 'last_name', 'email', 'phone', 'whatsapp')
            ->orderBy('first_name')
            ->limit(50)
            ->get();

        return response()->json($contacts);
    }

    /**
     * Get contact statistics
     */
    private function getContactStats(Contact $contact)
    {
        $stats = [
            'emails_sent' => 0,
            'emails_opened' => 0,
            'sms_sent' => 0,
            'whatsapp_messages' => 0
        ];

        // Get email statistics
        if (class_exists('\App\Models\EmailLog')) {
            $emailStats = \App\Models\EmailLog::where('contact_id', $contact->id)
                ->selectRaw('COUNT(*) as sent, SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened')
                ->first();
            
            if ($emailStats) {
                $stats['emails_sent'] = $emailStats->sent;
                $stats['emails_opened'] = $emailStats->opened;
            }
        }

        // Get SMS statistics
        if (class_exists('\App\Models\SmsMessage')) {
            $stats['sms_sent'] = \App\Models\SmsMessage::where('contact_id', $contact->id)
                ->where('status', 'sent')
                ->count();
        }

        // Get WhatsApp statistics
        $stats['whatsapp_messages'] = Communication::where('contact_id', $contact->id)
            ->where('type', 'whatsapp')
            ->count();

        return $stats;
    }
}
