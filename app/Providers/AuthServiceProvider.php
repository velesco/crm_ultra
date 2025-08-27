<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Import Models
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\WhatsAppSession;
use App\Models\SmsProvider;
use App\Models\DataImport;

// Import Policies
use App\Policies\ContactPolicy;
use App\Policies\EmailCampaignPolicy;
use App\Policies\WhatsAppSessionPolicy;
use App\Policies\SmsProviderPolicy;
use App\Policies\DataImportPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Contact::class => ContactPolicy::class,
        EmailCampaign::class => EmailCampaignPolicy::class,
        WhatsAppSession::class => WhatsAppSessionPolicy::class,
        SmsProvider::class => SmsProviderPolicy::class,
        DataImport::class => DataImportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register custom gates
        $this->registerCustomGates();
        
        // Register role-based permissions
        $this->registerRoleBasedPermissions();
        
        // Register feature gates
        $this->registerFeatureGates();
    }

    /**
     * Register custom authorization gates.
     */
    protected function registerCustomGates(): void
    {
        // Super Admin gate - bypasses all authorization
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // Bulk operations gate
        Gate::define('bulk-operations', function ($user) {
            return $user->hasRole(['admin', 'manager']) || 
                   $user->hasPermissionTo('bulk operations');
        });

        // Advanced features gate
        Gate::define('advanced-features', function ($user) {
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['pro', 'enterprise']) || 
                   $user->hasRole(['admin']);
        });

        // API access gate
        Gate::define('api-access', function ($user) {
            return $user->hasPermissionTo('api access') || 
                   $user->hasRole(['admin', 'manager']);
        });

        // System administration gate
        Gate::define('system-admin', function ($user) {
            return $user->hasRole(['admin', 'super_admin']);
        });

        // Team management gate
        Gate::define('team-management', function ($user) {
            return $user->hasRole(['admin', 'manager']) || 
                   $user->hasPermissionTo('manage team');
        });

        // Reports access gate
        Gate::define('reports-access', function ($user) {
            return $user->hasPermissionTo('view reports') || 
                   $user->hasRole(['admin', 'manager']);
        });

        // Integration management gate
        Gate::define('integration-management', function ($user) {
            return $user->hasPermissionTo('manage integrations') || 
                   $user->hasRole(['admin', 'manager']);
        });
    }

    /**
     * Register role-based permissions.
     */
    protected function registerRoleBasedPermissions(): void
    {
        // Admin role permissions
        Gate::define('admin-panel-access', function ($user) {
            return $user->hasRole(['admin', 'super_admin']);
        });

        // Manager role permissions
        Gate::define('manager-panel-access', function ($user) {
            return $user->hasRole(['admin', 'manager']);
        });

        // Agent role permissions
        Gate::define('agent-panel-access', function ($user) {
            return $user->hasRole(['admin', 'manager', 'agent']);
        });

        // Multi-channel communication permissions
        Gate::define('send-emails', function ($user) {
            return $user->hasPermissionTo('send emails') || 
                   $user->hasRole(['admin', 'manager', 'agent']);
        });

        Gate::define('send-sms', function ($user) {
            return $user->hasPermissionTo('send sms') || 
                   $user->hasRole(['admin', 'manager']);
        });

        Gate::define('send-whatsapp', function ($user) {
            return $user->hasPermissionTo('send whatsapp') || 
                   $user->hasRole(['admin', 'manager']);
        });

        // Data management permissions
        Gate::define('import-data', function ($user) {
            return $user->hasPermissionTo('import data') || 
                   $user->hasRole(['admin', 'manager', 'agent']);
        });

        Gate::define('export-data', function ($user) {
            return $user->hasPermissionTo('export data') || 
                   $user->hasRole(['admin', 'manager']);
        });

        // Configuration permissions
        Gate::define('manage-smtp-configs', function ($user) {
            return $user->hasPermissionTo('manage smtp configs') || 
                   $user->hasRole(['admin', 'manager']);
        });

        Gate::define('manage-sms-providers', function ($user) {
            return $user->hasPermissionTo('manage sms providers') || 
                   $user->hasRole(['admin', 'manager']);
        });
    }

    /**
     * Register feature-based gates.
     */
    protected function registerFeatureGates(): void
    {
        // Email campaigns feature
        Gate::define('email-campaigns-enabled', function ($user) {
            if (!config('features.email_campaigns.enabled', true)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['free', 'basic', 'pro', 'enterprise']);
        });

        // SMS feature
        Gate::define('sms-enabled', function ($user) {
            if (!config('features.sms.enabled', true)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['basic', 'pro', 'enterprise']);
        });

        // WhatsApp feature
        Gate::define('whatsapp-enabled', function ($user) {
            if (!config('features.whatsapp.enabled', true)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['pro', 'enterprise']);
        });

        // Google Sheets integration
        Gate::define('google-sheets-enabled', function ($user) {
            if (!config('features.google_sheets.enabled', true)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['basic', 'pro', 'enterprise']);
        });

        // Advanced segments
        Gate::define('advanced-segments-enabled', function ($user) {
            if (!config('features.advanced_segments.enabled', true)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['pro', 'enterprise']);
        });

        // API access
        Gate::define('api-enabled', function ($user) {
            if (!config('features.api.enabled', false)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['pro', 'enterprise']);
        });

        // Webhooks
        Gate::define('webhooks-enabled', function ($user) {
            if (!config('features.webhooks.enabled', false)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['pro', 'enterprise']);
        });

        // Custom fields
        Gate::define('custom-fields-enabled', function ($user) {
            if (!config('features.custom_fields.enabled', true)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['basic', 'pro', 'enterprise']);
        });

        // Automation
        Gate::define('automation-enabled', function ($user) {
            if (!config('features.automation.enabled', false)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['pro', 'enterprise']);
        });

        // Advanced reporting
        Gate::define('advanced-reports-enabled', function ($user) {
            if (!config('features.advanced_reports.enabled', true)) {
                return false;
            }
            
            $plan = $user->subscription_plan ?? 'free';
            return in_array($plan, ['pro', 'enterprise']);
        });
    }
}
