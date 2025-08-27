<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting CRM Ultra Database Seeding...');

        // First, create roles and permissions
        $this->command->info('1️⃣ Creating roles and permissions...');
        $this->call(RolesAndPermissionsSeeder::class);

        // Then create users with roles
        $this->command->info('2️⃣ Creating users...');
        $this->call(UserSeeder::class);

        // Create contacts
        $this->command->info('3️⃣ Creating contacts...');
        $this->call(ContactSeeder::class);

        // Create email templates
        $this->command->info('4️⃣ Creating email templates...');
        $this->call(EmailTemplateSeeder::class);

        // Create contact segments
        $this->command->info('5️⃣ Creating contact segments...');
        $this->call(ContactSegmentSeeder::class);

        // Create additional sample data using factories
        $this->command->info('6️⃣ Creating additional sample data...');
        $this->createAdditionalSampleData();

        $this->command->info('✅ CRM Ultra Database Seeding Completed Successfully!');
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('Super Admin: superadmin@crmultra.com / SuperAdmin123!');
        $this->command->info('Admin: admin@crmultra.com / Admin123!');
        $this->command->info('Manager: manager@crmultra.com / Manager123!');
        $this->command->info('Agent: agent@crmultra.com / Agent123!');
        $this->command->info('Viewer: viewer@crmultra.com / Viewer123!');
        $this->command->info('');
        $this->command->info('📊 Sample Data Created:');
        $this->command->info('• ' . \App\Models\User::count() . ' Users');
        $this->command->info('• ' . \App\Models\Contact::count() . ' Contacts');
        $this->command->info('• ' . \App\Models\EmailTemplate::count() . ' Email Templates');
        $this->command->info('• ' . \App\Models\ContactSegment::count() . ' Contact Segments');
        $this->command->info('• ' . \Spatie\Permission\Models\Role::count() . ' Roles');
        $this->command->info('• ' . \Spatie\Permission\Models\Permission::count() . ' Permissions');
    }

    /**
     * Create additional sample data using factories.
     */
    protected function createAdditionalSampleData(): void
    {
        // Don't create additional data if we already have enough
        if (\App\Models\Contact::count() >= 45) {
            $this->command->info('   ⏭️  Skipping additional contacts (sufficient data exists)');
            return;
        }

        // Create additional contacts using factory
        $this->command->info('   📝 Creating additional contacts with factories...');
        
        // Create VIP contacts
        \App\Models\Contact::factory()->vip()->count(5)->create();
        
        // Create tech industry contacts
        \App\Models\Contact::factory()->tech()->count(8)->create();
        
        // Create small business contacts
        \App\Models\Contact::factory()->smallBusiness()->count(10)->create();
        
        // Create enterprise contacts
        \App\Models\Contact::factory()->enterprise()->count(6)->create();
        
        // Create newsletter subscribers
        \App\Models\Contact::factory()->newsletterSubscriber()->count(15)->create();
        
        // Create some inactive contacts
        \App\Models\Contact::factory()->inactive()->count(5)->create();
        
        // Create contacts from different countries
        \App\Models\Contact::factory()->fromCountry('USA')->count(20)->create();
        \App\Models\Contact::factory()->fromCountry('UK')->count(5)->create();
        \App\Models\Contact::factory()->fromCountry('Canada')->count(3)->create();
        
        // Create recently active contacts
        \App\Models\Contact::factory()->recentlyActive()->count(12)->create();

        $this->command->info('   ✅ Additional sample data created successfully');
    }
}
