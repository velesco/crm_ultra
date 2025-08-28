<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $this->createPermissions();

        // Create roles and assign permissions
        $this->createRoles();

        $this->command->info('Roles and permissions seeded successfully!');
    }

    /**
     * Create all permissions.
     */
    protected function createPermissions(): void
    {
        $permissions = [
            // Contact Management
            'view contacts',
            'create contacts',
            'edit contacts',
            'delete contacts',
            'restore contacts',
            'force-delete contacts',
            'import contacts',
            'export contacts',
            'assign contacts',
            'bulk-update contacts',
            'bulk-delete contacts',
            'manage segments',

            // Email Campaigns
            'view email-campaigns',
            'create email-campaigns',
            'edit email-campaigns',
            'delete email-campaigns',
            'restore email-campaigns',
            'force-delete email-campaigns',
            'send email-campaigns',
            'send test emails',
            'manage smtp configs',

            // SMS Management
            'view sms',
            'send sms',
            'manage sms-providers',
            'view sms reports',

            // WhatsApp Management
            'view whatsapp-sessions',
            'create whatsapp-sessions',
            'manage whatsapp-sessions',
            'send whatsapp',
            'view whatsapp reports',

            // Data Import/Export
            'view data-imports',
            'create data-imports',
            'delete data-imports',
            'restore data-imports',
            'force-delete data-imports',
            'import data',
            'export data',

            // Google Sheets Integration
            'manage google-sheets',
            'sync google-sheets',

            // Reports & Analytics
            'view reports',
            'export reports',
            'view advanced reports',

            // Communications
            'view communications',
            'send communications',
            'manage communications',

            // Settings & Configuration
            'view settings',
            'manage settings',
            'manage integrations',
            'manage team',
            'manage permissions',

            // API & Webhooks
            'api access',
            'manage webhooks',
            'view logs',

            // System Administration
            'system admin',
            'manage users',
            'manage roles',
            'bulk operations',
            'advanced features',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('Created '.count($permissions).' permissions.');
    }

    /**
     * Create roles and assign permissions.
     */
    protected function createRoles(): void
    {
        // Super Admin Role - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin Role - Most permissions except super admin specific
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $adminPermissions = [
            // Contacts
            'view contacts', 'create contacts', 'edit contacts', 'delete contacts',
            'restore contacts', 'import contacts', 'export contacts', 'assign contacts',
            'bulk-update contacts', 'bulk-delete contacts', 'manage segments',

            // Email Campaigns
            'view email-campaigns', 'create email-campaigns', 'edit email-campaigns',
            'delete email-campaigns', 'restore email-campaigns', 'send email-campaigns',
            'send test emails', 'manage smtp configs',

            // SMS
            'view sms', 'send sms', 'manage sms-providers', 'view sms reports',

            // WhatsApp
            'view whatsapp-sessions', 'create whatsapp-sessions', 'manage whatsapp-sessions',
            'send whatsapp', 'view whatsapp reports',

            // Data Management
            'view data-imports', 'create data-imports', 'delete data-imports',
            'restore data-imports', 'import data', 'export data',

            // Integrations
            'manage google-sheets', 'sync google-sheets',

            // Reports
            'view reports', 'export reports', 'view advanced reports',

            // Communications
            'view communications', 'send communications', 'manage communications',

            // Settings
            'view settings', 'manage settings', 'manage integrations', 'manage team',

            // API
            'api access', 'manage webhooks', 'view logs',

            // Advanced
            'manage users', 'bulk operations', 'advanced features',
        ];
        $admin->givePermissionTo($adminPermissions);

        // Manager Role - Team and campaign management
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $managerPermissions = [
            // Contacts
            'view contacts', 'create contacts', 'edit contacts', 'delete contacts',
            'import contacts', 'export contacts', 'assign contacts', 'manage segments',

            // Email Campaigns
            'view email-campaigns', 'create email-campaigns', 'edit email-campaigns',
            'send email-campaigns', 'send test emails', 'manage smtp configs',

            // SMS
            'view sms', 'send sms', 'manage sms-providers', 'view sms reports',

            // WhatsApp
            'view whatsapp-sessions', 'create whatsapp-sessions', 'manage whatsapp-sessions',
            'send whatsapp', 'view whatsapp reports',

            // Data Management
            'view data-imports', 'create data-imports', 'import data', 'export data',

            // Integrations
            'manage google-sheets', 'sync google-sheets',

            // Reports
            'view reports', 'export reports',

            // Communications
            'view communications', 'send communications', 'manage communications',

            // Settings
            'view settings', 'manage team',

            // API
            'api access', 'view logs',

            // Advanced
            'bulk operations',
        ];
        $manager->givePermissionTo($managerPermissions);

        // Agent Role - Basic operations
        $agent = Role::firstOrCreate(['name' => 'agent']);
        $agentPermissions = [
            // Contacts
            'view contacts', 'create contacts', 'edit contacts', 'import contacts',

            // Email Campaigns
            'view email-campaigns', 'create email-campaigns', 'edit email-campaigns',
            'send test emails',

            // SMS
            'view sms',

            // Data Management
            'view data-imports', 'create data-imports', 'import data',

            // Communications
            'view communications', 'send communications',

            // Basic settings
            'view settings',
        ];
        $agent->givePermissionTo($agentPermissions);

        // Viewer Role - Read-only access
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewerPermissions = [
            'view contacts',
            'view email-campaigns',
            'view sms',
            'view whatsapp-sessions',
            'view data-imports',
            'view reports',
            'view communications',
            'view settings',
        ];
        $viewer->givePermissionTo($viewerPermissions);

        $this->command->info('Created roles: super_admin, admin, manager, agent, viewer');
    }
}
