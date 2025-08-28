<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SystemSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display a listing of system settings grouped by category
     */
    public function index(Request $request)
    {
        $selectedGroup = $request->get('group', 'general');
        $search = $request->get('search');

        // Get all groups for navigation
        $groups = SystemSetting::select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->mapWithKeys(function ($group) {
                return [$group => ucfirst(str_replace('_', ' ', $group))];
            });

        // Build query
        $query = SystemSetting::with(['createdBy', 'updatedBy'])
            ->byGroup($selectedGroup)
            ->ordered();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $settings = $query->paginate(20)->appends($request->all());

        // Get statistics
        $stats = $this->getSettingsStats();

        return view('admin.settings.index', compact('settings', 'groups', 'selectedGroup', 'search', 'stats'));
    }

    /**
     * Show the form for creating a new setting
     */
    public function create(Request $request)
    {
        $defaultGroup = $request->get('group', 'general');

        $groups = SystemSetting::distinct('group')->pluck('group')
            ->mapWithKeys(function ($group) {
                return [$group => ucfirst(str_replace('_', ' ', $group))];
            })
            ->union([
                'general' => 'General',
                'email' => 'Email',
                'sms' => 'SMS',
                'whatsapp' => 'WhatsApp',
                'api' => 'API',
                'security' => 'Security',
                'integrations' => 'Integrations',
            ]);

        return view('admin.settings.create', compact('groups', 'defaultGroup'));
    }

    /**
     * Store a newly created setting
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255|unique:system_settings,key|regex:/^[a-z0-9._]+$/',
            'label' => 'required|string|max:255',
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json,text,encrypted',
            'group' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'validation_rules' => 'nullable|json',
            'options' => 'nullable|json',
            'is_encrypted' => 'boolean',
            'is_public' => 'boolean',
            'requires_restart' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate the value against custom validation rules if provided
        if ($request->validation_rules) {
            $customRules = json_decode($request->validation_rules, true);
            if ($customRules && is_array($customRules)) {
                $valueValidator = Validator::make(
                    ['value' => $request->value],
                    ['value' => $customRules]
                );

                if ($valueValidator->fails()) {
                    return redirect()->back()
                        ->withErrors($valueValidator)
                        ->withInput();
                }
            }
        }

        $setting = SystemSetting::create([
            'key' => $request->key,
            'label' => $request->label,
            'value' => $request->value,
            'type' => $request->type,
            'group' => $request->group,
            'description' => $request->description,
            'validation_rules' => $request->validation_rules ? json_decode($request->validation_rules, true) : null,
            'options' => $request->options ? json_decode($request->options, true) : null,
            'is_encrypted' => $request->boolean('is_encrypted'),
            'is_public' => $request->boolean('is_public'),
            'requires_restart' => $request->boolean('requires_restart'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        // Clear cache if needed
        if ($request->boolean('requires_restart')) {
            $this->clearSystemCache();
        }

        return redirect()->route('admin.settings.index', ['group' => $setting->group])
            ->with('success', "Setting '{$setting->label}' created successfully.");
    }

    /**
     * Display the specified setting
     */
    public function show(SystemSetting $systemSetting)
    {
        $systemSetting->load(['createdBy', 'updatedBy']);

        // Get related settings in the same group
        $relatedSettings = SystemSetting::byGroup($systemSetting->group)
            ->where('id', '!=', $systemSetting->id)
            ->ordered()
            ->limit(5)
            ->get();

        return view('admin.settings.show', compact('systemSetting', 'relatedSettings'));
    }

    /**
     * Show the form for editing the specified setting
     */
    public function edit(SystemSetting $systemSetting)
    {
        if (! $systemSetting->isEditable()) {
            return redirect()->route('admin.settings.show', $systemSetting)
                ->with('error', 'This setting cannot be edited.');
        }

        $groups = SystemSetting::distinct('group')->pluck('group')
            ->mapWithKeys(function ($group) {
                return [$group => ucfirst(str_replace('_', ' ', $group))];
            })
            ->union([
                'general' => 'General',
                'email' => 'Email',
                'sms' => 'SMS',
                'whatsapp' => 'WhatsApp',
                'api' => 'API',
                'security' => 'Security',
                'integrations' => 'Integrations',
            ]);

        return view('admin.settings.edit', compact('systemSetting', 'groups'));
    }

    /**
     * Update the specified setting
     */
    public function update(Request $request, SystemSetting $systemSetting)
    {
        if (! $systemSetting->isEditable()) {
            return redirect()->route('admin.settings.show', $systemSetting)
                ->with('error', 'This setting cannot be edited.');
        }

        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255|regex:/^[a-z0-9._]+$/|unique:system_settings,key,'.$systemSetting->id,
            'label' => 'required|string|max:255',
            'value' => 'nullable',
            'type' => 'required|in:string,integer,boolean,json,text,encrypted',
            'group' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'validation_rules' => 'nullable|json',
            'options' => 'nullable|json',
            'is_encrypted' => 'boolean',
            'is_public' => 'boolean',
            'requires_restart' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate the value against custom validation rules if provided
        if ($request->validation_rules) {
            $customRules = json_decode($request->validation_rules, true);
            if ($customRules && is_array($customRules)) {
                $valueValidator = Validator::make(
                    ['value' => $request->value],
                    ['value' => $customRules]
                );

                if ($valueValidator->fails()) {
                    return redirect()->back()
                        ->withErrors($valueValidator)
                        ->withInput();
                }
            }
        }

        $systemSetting->update([
            'key' => $request->key,
            'label' => $request->label,
            'value' => $request->value,
            'type' => $request->type,
            'group' => $request->group,
            'description' => $request->description,
            'validation_rules' => $request->validation_rules ? json_decode($request->validation_rules, true) : null,
            'options' => $request->options ? json_decode($request->options, true) : null,
            'is_encrypted' => $request->boolean('is_encrypted'),
            'is_public' => $request->boolean('is_public'),
            'requires_restart' => $request->boolean('requires_restart'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        // Clear cache if needed
        if ($request->boolean('requires_restart') || $systemSetting->wasChanged('requires_restart')) {
            $this->clearSystemCache();
        }

        return redirect()->route('admin.settings.index', ['group' => $systemSetting->group])
            ->with('success', "Setting '{$systemSetting->label}' updated successfully.");
    }

    /**
     * Remove the specified setting
     */
    public function destroy(SystemSetting $systemSetting)
    {
        if (! $systemSetting->isEditable()) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'This setting cannot be deleted.');
        }

        $group = $systemSetting->group;
        $label = $systemSetting->label;

        $systemSetting->delete();

        return redirect()->route('admin.settings.index', ['group' => $group])
            ->with('success', "Setting '{$label}' deleted successfully.");
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,export,toggle_public',
            'settings' => 'required|array',
            'settings.*' => 'exists:system_settings,id',
        ]);

        $settings = SystemSetting::whereIn('id', $request->settings)->get();
        $action = $request->action;

        $count = 0;

        foreach ($settings as $setting) {
            if (! $setting->isEditable() && in_array($action, ['delete'])) {
                continue;
            }

            switch ($action) {
                case 'delete':
                    $setting->delete();
                    $count++;
                    break;

                case 'toggle_public':
                    $setting->update(['is_public' => ! $setting->is_public]);
                    $count++;
                    break;

                case 'export':
                    // Handle in separate method
                    return $this->exportSettings($settings);
            }
        }

        $actionText = match ($action) {
            'delete' => 'deleted',
            'toggle_public' => 'updated',
            default => 'processed'
        };

        return redirect()->back()
            ->with('success', "{$count} settings {$actionText} successfully.");
    }

    /**
     * Export settings as JSON
     */
    public function export(Request $request)
    {
        $group = $request->get('group');
        $search = $request->get('search');

        $query = SystemSetting::query();

        if ($group) {
            $query->byGroup($group);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        $settings = $query->ordered()->get()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->is_encrypted ? '••••••••' : $setting->value,
                'type' => $setting->type,
                'group' => $setting->group,
                'label' => $setting->label,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
                'sort_order' => $setting->sort_order,
            ];
        });

        $filename = 'system-settings-'.($group ?: 'all').'-'.date('Y-m-d-H-i-s').'.json';

        return response()->json($settings)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    /**
     * Clear system cache
     */
    public function clearCache(Request $request)
    {
        $this->clearSystemCache();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'System cache cleared successfully']);
        }

        return redirect()->back()
            ->with('success', 'System cache cleared successfully.');
    }

    /**
     * Get settings statistics
     */
    private function getSettingsStats()
    {
        return [
            'total' => SystemSetting::count(),
            'by_group' => SystemSetting::selectRaw('`group`, COUNT(*) as count')
                ->groupBy('group')
                ->pluck('count', 'group'),
            'public' => SystemSetting::where('is_public', true)->count(),
            'encrypted' => SystemSetting::where('is_encrypted', true)->count(),
            'requires_restart' => SystemSetting::where('requires_restart', true)->count(),
        ];
    }

    /**
     * Clear system cache
     */
    private function clearSystemCache()
    {
        SystemSetting::clearSettingsCache();
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
    }

    /**
     * Export selected settings
     */
    private function exportSettings($settings)
    {
        $data = $settings->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->is_encrypted ? '••••••••' : $setting->value,
                'type' => $setting->type,
                'group' => $setting->group,
                'label' => $setting->label,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
                'sort_order' => $setting->sort_order,
            ];
        });

        $filename = 'selected-settings-'.date('Y-m-d-H-i-s').'.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}
