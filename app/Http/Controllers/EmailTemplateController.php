<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailTemplate::with('creator')
            ->orderBy('updated_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by creator
        if ($request->filled('creator') && $request->creator !== 'all') {
            $query->where('created_by', $request->creator);
        }

        $templates = $query->paginate(15);

        // Get filter options
        $categories = EmailTemplate::distinct()->pluck('category')->filter()->sort();
        $creators = User::whereHas('emailTemplates')->get(['id', 'name']);

        // Get usage statistics
        $stats = [
            'total' => EmailTemplate::count(),
            'active' => EmailTemplate::where('is_active', true)->count(),
            'inactive' => EmailTemplate::where('is_active', false)->count(),
            'categories' => $categories->count(),
        ];

        return view('email.templates.index', compact('templates', 'categories', 'creators', 'stats'));
    }

    public function create()
    {
        $categories = EmailTemplate::distinct()->pluck('category')->filter()->sort();
        $predefinedCategories = ['Newsletter', 'Promotional', 'Welcome', 'Transactional', 'Follow-up', 'Event'];

        return view('email.templates.create', compact('categories', 'predefinedCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'is_active' => 'boolean',
            'variables' => 'array'
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        // Extract variables from content and subject
        $template = new EmailTemplate($validated);
        $extractedVariables = $template->extractVariables();

        // Merge with custom variables
        if (!empty($validated['variables'])) {
            $extractedVariables = array_unique(array_merge($extractedVariables, $validated['variables']));
        }

        $validated['variables'] = $extractedVariables;

        $template = EmailTemplate::create($validated);

        return redirect()->route('email.templates.show', $template)
            ->with('success', 'Email template created successfully!');
    }

    public function show(EmailTemplate $emailTemplate)
    {
        $emailTemplate->load('creator', 'emailCampaigns');

        // Get usage statistics
        $stats = [
            'campaigns_used' => $emailTemplate->emailCampaigns->count(),
            'last_used' => $emailTemplate->emailCampaigns()->latest('created_at')->first()?->created_at,
            'variables_count' => count($emailTemplate->variables ?? []),
        ];

        // Generate preview with sample data
        $sampleData = $this->getSampleData($emailTemplate->variables ?? []);
        $preview = $emailTemplate->preview($sampleData);

        return view('email.templates.show', compact('emailTemplate', 'stats', 'preview'));
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $categories = EmailTemplate::distinct()->pluck('category')->filter()->sort();
        $predefinedCategories = ['Newsletter', 'Promotional', 'Welcome', 'Transactional', 'Follow-up', 'Event'];

        return view('email.templates.edit', compact('emailTemplate', 'categories', 'predefinedCategories'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
            'is_active' => 'boolean',
            'variables' => 'array'
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Extract variables from content and subject
        $tempTemplate = new EmailTemplate($validated);
        $extractedVariables = $tempTemplate->extractVariables();

        // Merge with custom variables
        if (!empty($validated['variables'])) {
            $extractedVariables = array_unique(array_merge($extractedVariables, $validated['variables']));
        }

        $validated['variables'] = $extractedVariables;

        $emailTemplate->update($validated);

        return redirect()->route('email.templates.show', $emailTemplate)
            ->with('success', 'Email template updated successfully!');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        // Check if template is being used in active campaigns
        $activeCampaigns = $emailTemplate->emailCampaigns()
            ->whereIn('status', ['sending', 'scheduled', 'paused'])
            ->count();

        if ($activeCampaigns > 0) {
            return back()->with('error', "Cannot delete template. It's being used in {$activeCampaigns} active campaign(s).");
        }

        $emailTemplate->delete();

        return redirect()->route('email.templates.index')
            ->with('success', 'Email template deleted successfully!');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $variables = $request->get('variables', []);

        // If no variables provided, use sample data
        if (empty($variables)) {
            $variables = $this->getSampleData($emailTemplate->variables ?? []);
        }

        $preview = $emailTemplate->preview($variables);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'preview' => $preview
            ]);
        }

        return view('email.templates.preview', compact('emailTemplate', 'preview', 'variables'));
    }


    public function searchTemplates(Request $request)
    {
        $query = EmailTemplate::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        $templates = $query->limit(20)->get(['id', 'name', 'subject', 'category']);

        return response()->json([
            'templates' => $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'text' => $template->name,
                    'subject' => $template->subject,
                    'category' => $template->category,
                    'label' => $template->name . ' (' . $template->category . ')'
                ];
            })
        ]);
    }

    /**
     * Import templates from file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->path());
        $templates = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Invalid JSON file.');
        }

        $imported = 0;
        $errors = [];

        foreach ($templates as $index => $templateData) {
            try {
                $templateData['created_by'] = Auth::id();
                $templateData['created_at'] = now();
                $templateData['updated_at'] = now();

                // Check if template name already exists
                $existingCount = EmailTemplate::where('name', $templateData['name'])->count();
                if ($existingCount > 0) {
                    $templateData['name'] = $templateData['name'] . ' (' . ($existingCount + 1) . ')';
                }

                EmailTemplate::create($templateData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Template " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        $message = "Successfully imported {$imported} templates.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Export templates to JSON
     */
    public function export(Request $request)
    {
        $templateIds = $request->get('templates', []);

        if (empty($templateIds)) {
            return back()->with('error', 'Please select templates to export.');
        }

        $templates = EmailTemplate::whereIn('id', $templateIds)
            ->select(['name', 'subject', 'content', 'category', 'variables', 'is_active'])
            ->get();

        $filename = 'email_templates_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($templates)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Get sample data for template variables
     */
    private function getSampleData(array $variables): array
    {
        $sampleData = [];

        foreach ($variables as $variable) {
            switch (strtolower($variable)) {
                case 'name':
                case 'first_name':
                    $sampleData[$variable] = 'John';
                    break;
                case 'last_name':
                    $sampleData[$variable] = 'Doe';
                    break;
                case 'email':
                    $sampleData[$variable] = 'john.doe@example.com';
                    break;
                case 'company':
                case 'company_name':
                    $sampleData[$variable] = 'Acme Corp';
                    break;
                case 'phone':
                    $sampleData[$variable] = '+1-555-0123';
                    break;
                case 'date':
                case 'today':
                    $sampleData[$variable] = now()->format('F j, Y');
                    break;
                case 'time':
                    $sampleData[$variable] = now()->format('g:i A');
                    break;
                case 'url':
                case 'website':
                    $sampleData[$variable] = 'https://example.com';
                    break;
                case 'unsubscribe_link':
                    $sampleData[$variable] = url('/email/unsubscribe/sample-token');
                    break;
                default:
                    $sampleData[$variable] = ucfirst(str_replace('_', ' ', $variable));
            }
        }

        return $sampleData;
    }

    /**
     * Toggle template status
     */
    public function toggleStatus(EmailTemplate $emailTemplate)
    {
        $emailTemplate->update([
            'is_active' => !$emailTemplate->is_active
        ]);

        $status = $emailTemplate->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Template {$status} successfully!",
            'is_active' => $emailTemplate->is_active
        ]);
    }

    /**
     * Duplicate template
     */
    public function duplicate(EmailTemplate $emailTemplate)
    {
        $newTemplate = $emailTemplate->replicate();
        $newTemplate->name = $emailTemplate->name . ' (Copy)';
        $newTemplate->created_by = Auth::id();
        $newTemplate->is_active = false;
        $newTemplate->created_at = now();
        $newTemplate->updated_at = now();
        $newTemplate->save();

        return redirect()->route('email.templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully! You can now edit the copy.');
    }

    /**
     * Get template content for AJAX
     */
    public function getContent(EmailTemplate $emailTemplate)
    {
        return response()->json([
            'success' => true,
            'template' => [
                'id' => $emailTemplate->id,
                'name' => $emailTemplate->name,
                'subject' => $emailTemplate->subject,
                'content' => $emailTemplate->content,
                'variables' => $emailTemplate->variables,
                'category' => $emailTemplate->category,
            ]
        ]);
    }
}
