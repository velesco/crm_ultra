<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AppSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        
        $query = AppSetting::query()->active()->orderBy('sort_order')->orderBy('label');
        
        if ($category !== 'all') {
            $query->where('category', $category);
        }
        
        $settings = $query->get()->groupBy('category');
        $categories = AppSetting::getCategories();
        
        return view('admin.settings.app-settings.index', compact('settings', 'categories', 'category'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = AppSetting::getCategories()->toArray();
        $types = ['string', 'boolean', 'integer', 'float', 'json', 'encrypted'];
        
        return view('admin.settings.app-settings.create', compact('categories', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:app_settings,key|regex:/^[a-z0-9._-]+$/',
            'label' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'type' => 'required|in:string,boolean,integer,float,json,encrypted',
            'value' => 'nullable',
            'description' => 'nullable|string',
            'is_env_synced' => 'boolean',
            'env_key' => 'nullable|string|regex:/^[A-Z0-9_]+$/',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'sort_order' => 'integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();
            
            // Handle encrypted settings
            if ($data['type'] === 'encrypted') {
                $data['is_encrypted'] = true;
            }
            
            // Generate env_key if needed
            if ($data['is_env_synced'] && !$data['env_key']) {
                $data['env_key'] = strtoupper(str_replace(['.', '-'], '_', $data['key']));
            }
            
            $setting = AppSetting::create($data);
            
            // Sync to .env if needed
            if ($setting->is_env_synced && $setting->env_key) {
                AppSetting::syncToEnv($setting->env_key, $setting->value);
            }

            DB::commit();

            return redirect()->route('admin.app-settings.index', ['category' => $setting->category])
                           ->with('success', 'Setting created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('App Settings Store Error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withInput()->with('error', 'Failed to create setting: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AppSetting $appSetting)
    {
        return view('admin.settings.app-settings.show', compact('appSetting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AppSetting $appSetting)
    {
        $categories = AppSetting::getCategories()->toArray();
        $types = ['string', 'boolean', 'integer', 'float', 'json', 'encrypted'];
        
        return view('admin.settings.app-settings.edit', compact('appSetting', 'categories', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AppSetting $appSetting)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'type' => 'required|in:string,boolean,integer,float,json,encrypted',
            'value' => 'nullable',
            'description' => 'nullable|string',
            'is_env_synced' => 'boolean',
            'env_key' => 'nullable|string|regex:/^[A-Z0-9_]+$/',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'sort_order' => 'integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();
            
            // Handle encrypted settings
            if ($data['type'] === 'encrypted') {
                $data['is_encrypted'] = true;
            }
            
            // Generate env_key if needed
            if ($data['is_env_synced'] && !$data['env_key']) {
                $data['env_key'] = strtoupper(str_replace(['.', '-'], '_', $appSetting->key));
            }

            // Validate value if validation rules exist
            if (!empty($data['validation_rules'])) {
                $validator = Validator::make(['value' => $data['value']], ['value' => $data['validation_rules']]);
                
                if ($validator->fails()) {
                    return back()->withInput()->withErrors($validator);
                }
            }

            $appSetting->update($data);
            
            // Sync to .env if needed
            if ($appSetting->is_env_synced && $appSetting->env_key) {
                AppSetting::syncToEnv($appSetting->env_key, $appSetting->value);
            }

            DB::commit();

            return redirect()->route('admin.app-settings.index', ['category' => $appSetting->category])
                           ->with('success', 'Setting updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('App Settings Update Error', [
                'error' => $e->getMessage(),
                'setting_id' => $appSetting->id,
                'data' => $request->all()
            ]);

            return back()->withInput()->with('error', 'Failed to update setting: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AppSetting $appSetting)
    {
        try {
            // Remove from .env if it was synced
            if ($appSetting->is_env_synced && $appSetting->env_key) {
                $this->removeFromEnv($appSetting->env_key);
            }

            $appSetting->delete();

            return redirect()->route('admin.app-settings.index')
                           ->with('success', 'Setting deleted successfully.');

        } catch (\Exception $e) {
            Log::error('App Settings Delete Error', [
                'error' => $e->getMessage(),
                'setting_id' => $appSetting->id
            ]);

            return back()->with('error', 'Failed to delete setting: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update settings by category
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:50',
            'settings' => 'required|array',
            'settings.*' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $settings = AppSetting::where('category', $request->category)->get()->keyBy('key');
            $updated = 0;
            $errors = [];

            foreach ($request->settings as $key => $value) {
                if (!isset($settings[$key])) {
                    continue;
                }

                $setting = $settings[$key];

                // Validate value if validation rules exist
                if ($setting->validation_rules) {
                    $validator = Validator::make(['value' => $value], ['value' => $setting->validation_rules]);
                    
                    if ($validator->fails()) {
                        $errors[] = "Validation failed for {$setting->label}: " . $validator->errors()->first('value');
                        continue;
                    }
                }

                try {
                    $setting->value = $value;
                    $setting->save();

                    // Sync to .env if needed
                    if ($setting->is_env_synced && $setting->env_key) {
                        AppSetting::syncToEnv($setting->env_key, $setting->value);
                    }

                    $updated++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to update {$setting->label}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Updated {$updated} settings successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->route('admin.app-settings.index', ['category' => $request->category])
                           ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('App Settings Bulk Update Error', [
                'error' => $e->getMessage(),
                'category' => $request->category,
                'data' => $request->settings
            ]);

            return back()->withInput()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults()
    {
        try {
            $defaultSettings = $this->getDefaultSettings();

            foreach ($defaultSettings as $setting) {
                AppSetting::firstOrCreate(
                    ['key' => $setting['key']],
                    $setting
                );
            }

            return redirect()->route('admin.app-settings.index')
                           ->with('success', 'Default settings initialized successfully.');

        } catch (\Exception $e) {
            Log::error('Initialize Default Settings Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to initialize default settings: ' . $e->getMessage());
        }
    }

    /**
     * Remove key from .env file
     */
    private function removeFromEnv($envKey)
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);
        
        // Remove the line with the key
        $envContent = preg_replace("/^{$envKey}=.*\r?\n?/m", '', $envContent);
        
        file_put_contents($envPath, $envContent);
        
        return true;
    }

    /**
     * Get default settings configuration
     */
    private function getDefaultSettings()
    {
        return [
            // Google Settings
            [
                'key' => 'google.client_id',
                'label' => 'Google Client ID',
                'category' => 'google',
                'type' => 'string',
                'description' => 'Google OAuth Client ID for API access',
                'is_env_synced' => true,
                'env_key' => 'GOOGLE_CLIENT_ID',
                'validation_rules' => ['required', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'google.client_secret',
                'label' => 'Google Client Secret',
                'category' => 'google',
                'type' => 'encrypted',
                'description' => 'Google OAuth Client Secret',
                'is_encrypted' => true,
                'is_env_synced' => true,
                'env_key' => 'GOOGLE_CLIENT_SECRET',
                'validation_rules' => ['required', 'string'],
                'sort_order' => 2,
            ],
            [
                'key' => 'google.redirect_uri',
                'label' => 'Google Redirect URI',
                'category' => 'google',
                'type' => 'string',
                'description' => 'Google OAuth Redirect URI',
                'is_env_synced' => true,
                'env_key' => 'GOOGLE_REDIRECT_URI',
                'validation_rules' => ['required', 'url'],
                'sort_order' => 3,
            ],

            // SMS Settings
            [
                'key' => 'sms.twilio.sid',
                'label' => 'Twilio Account SID',
                'category' => 'sms',
                'type' => 'string',
                'description' => 'Twilio Account SID for SMS service',
                'is_env_synced' => true,
                'env_key' => 'TWILIO_ACCOUNT_SID',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 1,
            ],
            [
                'key' => 'sms.twilio.token',
                'label' => 'Twilio Auth Token',
                'category' => 'sms',
                'type' => 'encrypted',
                'description' => 'Twilio Auth Token',
                'is_encrypted' => true,
                'is_env_synced' => true,
                'env_key' => 'TWILIO_AUTH_TOKEN',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 2,
            ],
            [
                'key' => 'sms.twilio.from',
                'label' => 'Twilio From Number',
                'category' => 'sms',
                'type' => 'string',
                'description' => 'Twilio phone number to send SMS from',
                'is_env_synced' => true,
                'env_key' => 'TWILIO_FROM_NUMBER',
                'validation_rules' => ['nullable', 'string'],
                'sort_order' => 3,
            ],

            // WhatsApp Settings
            [
                'key' => 'whatsapp.server_url',
                'label' => 'WhatsApp Server URL',
                'category' => 'whatsapp',
                'type' => 'string',
                'description' => 'WhatsApp Server URL for API communication',
                'is_env_synced' => true,
                'env_key' => 'WHATSAPP_SERVER_URL',
                'validation_rules' => ['nullable', 'url'],
                'sort_order' => 1,
            ],

            // Email Settings
            [
                'key' => 'mail.default_from_name',
                'label' => 'Default From Name',
                'category' => 'email',
                'type' => 'string',
                'description' => 'Default sender name for emails',
                'is_env_synced' => true,
                'env_key' => 'MAIL_FROM_NAME',
                'validation_rules' => ['required', 'string', 'max:255'],
                'sort_order' => 1,
            ],
            [
                'key' => 'mail.default_from_email',
                'label' => 'Default From Email',
                'category' => 'email',
                'type' => 'string',
                'description' => 'Default sender email address',
                'is_env_synced' => true,
                'env_key' => 'MAIL_FROM_ADDRESS',
                'validation_rules' => ['required', 'email'],
                'sort_order' => 2,
            ],

            // General App Settings
            [
                'key' => 'app.name',
                'label' => 'Application Name',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Name of the application',
                'is_env_synced' => true,
                'env_key' => 'APP_NAME',
                'validation_rules' => ['required', 'string', 'max:255'],
                'sort_order' => 1,
            ],
            [
                'key' => 'app.url',
                'label' => 'Application URL',
                'category' => 'general',
                'type' => 'string',
                'description' => 'Base URL of the application',
                'is_env_synced' => true,
                'env_key' => 'APP_URL',
                'validation_rules' => ['required', 'url'],
                'sort_order' => 2,
            ],
        ];
    }
}
