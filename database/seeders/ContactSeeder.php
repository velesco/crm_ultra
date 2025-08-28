<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $adminUser = User::where('email', 'admin@crmultra.com')->first();
        $managerUser = User::where('email', 'manager@crmultra.com')->first();

        // Create sample contacts with realistic data
        $contacts = [
            [
                'first_name' => 'James',
                'last_name' => 'Wilson',
                'email' => 'james.wilson@techcorp.com',
                'phone' => '+1-555-0101',
                'whatsapp' => '+1-555-0101',
                'company' => 'TechCorp Solutions',
                'position' => 'CTO',
                'address' => '123 Tech Street',
                'city' => 'San Francisco',
                'country' => 'USA',
                'tags' => ['vip', 'tech-lead', 'decision-maker'],
                'status' => 'active',
                'source' => 'website',
                'notes' => 'Interested in enterprise solutions. Follow up quarterly.',
                'custom_fields' => [
                    'company_size' => '200-500',
                    'industry' => 'Technology',
                    'budget' => '$50,000+',
                    'interest_level' => 'high',
                ],
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'email' => 'maria.garcia@marketingpro.com',
                'phone' => '+1-555-0102',
                'whatsapp' => '+1-555-0102',
                'company' => 'Marketing Pro Agency',
                'position' => 'Marketing Director',
                'address' => '456 Marketing Ave',
                'city' => 'New York',
                'country' => 'USA',
                'tags' => ['marketing', 'agency', 'newsletter-subscriber'],
                'status' => 'active',
                'source' => 'referral',
                'notes' => 'Runs email campaigns for multiple clients. Great referral source.',
                'custom_fields' => [
                    'company_size' => '50-200',
                    'industry' => 'Marketing',
                    'budget' => '$10,000-50,000',
                    'interest_level' => 'medium',
                ],
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Chen',
                'email' => 'david.chen@ecommerce-store.com',
                'phone' => '+1-555-0103',
                'company' => 'Chen\'s E-commerce Store',
                'position' => 'Owner',
                'address' => '789 Commerce Blvd',
                'city' => 'Los Angeles',
                'country' => 'USA',
                'tags' => ['ecommerce', 'small-business', 'self-service'],
                'status' => 'active',
                'source' => 'google-ads',
                'notes' => 'Small business owner looking for automated email marketing.',
                'custom_fields' => [
                    'company_size' => '1-50',
                    'industry' => 'E-commerce',
                    'budget' => '$1,000-5,000',
                    'interest_level' => 'high',
                ],
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Anderson',
                'email' => 'sophie.anderson@startup-hub.com',
                'phone' => '+1-555-0104',
                'whatsapp' => '+1-555-0104',
                'company' => 'StartupHub Incubator',
                'position' => 'Program Manager',
                'address' => '321 Innovation Drive',
                'city' => 'Austin',
                'country' => 'USA',
                'tags' => ['startup', 'incubator', 'influencer'],
                'status' => 'active',
                'source' => 'conference',
                'notes' => 'Works with multiple startups. Good networking contact.',
                'custom_fields' => [
                    'company_size' => '50-200',
                    'industry' => 'Startup/Incubator',
                    'budget' => '$5,000-25,000',
                    'interest_level' => 'medium',
                ],
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Thompson',
                'email' => 'robert.thompson@consulting-firm.com',
                'phone' => '+1-555-0105',
                'company' => 'Thompson Consulting',
                'position' => 'Senior Consultant',
                'address' => '654 Business Plaza',
                'city' => 'Chicago',
                'country' => 'USA',
                'tags' => ['consultant', 'b2b', 'enterprise'],
                'status' => 'active',
                'source' => 'linkedin',
                'notes' => 'Consultant who could recommend our services to clients.',
                'custom_fields' => [
                    'company_size' => '10-50',
                    'industry' => 'Consulting',
                    'budget' => '$15,000-75,000',
                    'interest_level' => 'low',
                ],
            ],
        ];

        foreach ($contacts as $contactData) {
            $contact = Contact::create([
                ...$contactData,
                'created_by' => $adminUser->id,
                'assigned_to' => $adminUser->id,
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(15),
            ]);
        }

        // Create additional simple contacts without faker
        $additionalContacts = [
            ['first_name' => 'John', 'last_name' => 'Smith', 'email' => 'john.smith@example.com', 'company' => 'Example Corp', 'industry' => 'Technology'],
            ['first_name' => 'Sarah', 'last_name' => 'Johnson', 'email' => 'sarah.johnson@demo.com', 'company' => 'Demo Inc', 'industry' => 'Marketing'],
            ['first_name' => 'Mike', 'last_name' => 'Davis', 'email' => 'mike.davis@test.com', 'company' => 'Test LLC', 'industry' => 'Finance'],
            ['first_name' => 'Emily', 'last_name' => 'Brown', 'email' => 'emily.brown@sample.com', 'company' => 'Sample Ltd', 'industry' => 'Healthcare'],
            ['first_name' => 'Alex', 'last_name' => 'Wilson', 'email' => 'alex.wilson@business.com', 'company' => 'Business Co', 'industry' => 'Education'],
            ['first_name' => 'Lisa', 'last_name' => 'Miller', 'email' => 'lisa.miller@corporate.com', 'company' => 'Corporate Group', 'industry' => 'Manufacturing'],
            ['first_name' => 'Tom', 'last_name' => 'Anderson', 'email' => 'tom.anderson@firm.com', 'company' => 'Anderson Firm', 'industry' => 'Consulting'],
            ['first_name' => 'Kate', 'last_name' => 'Taylor', 'email' => 'kate.taylor@agency.com', 'company' => 'Taylor Agency', 'industry' => 'Marketing'],
            ['first_name' => 'Ryan', 'last_name' => 'White', 'email' => 'ryan.white@solutions.com', 'company' => 'White Solutions', 'industry' => 'Technology'],
            ['first_name' => 'Anna', 'last_name' => 'Martin', 'email' => 'anna.martin@services.com', 'company' => 'Martin Services', 'industry' => 'Real Estate'],
        ];

        $statuses = ['active', 'inactive', 'prospect'];
        $sources = ['website', 'referral', 'google-ads', 'linkedin', 'conference'];
        $companySizes = ['1-50', '50-200', '200-500', '500-1000', '1000+'];
        $budgets = ['$1,000-5,000', '$5,000-25,000', '$25,000-50,000', '$50,000+'];
        $interestLevels = ['low', 'medium', 'high'];
        $tags = [['lead'], ['prospect'], ['customer'], ['newsletter-subscriber'], ['trial-user']];

        foreach ($additionalContacts as $index => $contactData) {
            Contact::create([
                'first_name' => $contactData['first_name'],
                'last_name' => $contactData['last_name'],
                'email' => $contactData['email'],
                'phone' => '+1-555-'.str_pad($index + 200, 4, '0', STR_PAD_LEFT),
                'whatsapp' => ($index % 3 == 0) ? '+1-555-'.str_pad($index + 300, 4, '0', STR_PAD_LEFT) : null,
                'company' => $contactData['company'],
                'position' => 'Manager',
                'address' => ($index + 100).' Business Street',
                'city' => 'New York',
                'country' => 'USA',
                'tags' => $tags[$index % count($tags)],
                'status' => $statuses[$index % count($statuses)],
                'source' => $sources[$index % count($sources)],
                'notes' => 'Sample contact for testing purposes.',
                'custom_fields' => [
                    'company_size' => $companySizes[$index % count($companySizes)],
                    'industry' => $contactData['industry'],
                    'budget' => $budgets[$index % count($budgets)],
                    'interest_level' => $interestLevels[$index % count($interestLevels)],
                ],
                'created_by' => ($users->count() > 0) ? $users->random()->id : $adminUser->id,
                'assigned_to' => ($index % 2 == 0) ? $adminUser->id : null,
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now()->subDays(rand(1, 60)),
            ]);
        }

        $this->command->info('Created 15 contacts successfully!');
    }
}
