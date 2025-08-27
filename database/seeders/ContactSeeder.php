<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\User;

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
                    'interest_level' => 'high'
                ]
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
                    'interest_level' => 'medium'
                ]
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
                    'interest_level' => 'high'
                ]
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
                    'interest_level' => 'medium'
                ]
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
                    'interest_level' => 'low'
                ]
            ]
        ];

        foreach ($contacts as $contactData) {
            $contact = Contact::create([
                ...$contactData,
                'created_by' => $adminUser->id,
                'assigned_to' => fake()->randomElement([$adminUser->id, $managerUser->id, null]),
                'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
            ]);
        }

        // Create additional random contacts
        for ($i = 0; $i < 45; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $company = fake()->company();
            $domain = strtolower(str_replace([' ', '\'', '.'], ['', '', ''], $company)) . '.com';
            
            Contact::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '@' . $domain),
                'phone' => '+1-555-' . fake()->numberBetween(1000, 9999),
                'whatsapp' => fake()->boolean(60) ? ('+1-555-' . fake()->numberBetween(1000, 9999)) : null,
                'company' => $company,
                'position' => fake()->jobTitle(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'country' => fake()->randomElement(['USA', 'Canada', 'UK', 'Germany', 'France', 'Australia']),
                'tags' => fake()->randomElements([
                    'lead', 'prospect', 'customer', 'vip', 'newsletter-subscriber',
                    'webinar-attendee', 'trial-user', 'enterprise', 'small-business',
                    'startup', 'agency', 'consultant', 'referral-source'
                ], fake()->numberBetween(1, 4)),
                'status' => fake()->randomElement(['active', 'inactive', 'prospect']),
                'source' => fake()->randomElement([
                    'website', 'referral', 'google-ads', 'facebook-ads', 'linkedin',
                    'conference', 'webinar', 'cold-outreach', 'partner', 'organic'
                ]),
                'notes' => fake()->boolean(70) ? fake()->sentence(10) : null,
                'custom_fields' => [
                    'company_size' => fake()->randomElement(['1-50', '50-200', '200-500', '500-1000', '1000+']),
                    'industry' => fake()->randomElement([
                        'Technology', 'Marketing', 'E-commerce', 'Healthcare', 'Finance',
                        'Education', 'Manufacturing', 'Consulting', 'Real Estate', 'Other'
                    ]),
                    'budget' => fake()->randomElement([
                        '$1,000-5,000', '$5,000-25,000', '$25,000-50,000', '$50,000+', 'Not specified'
                    ]),
                    'interest_level' => fake()->randomElement(['low', 'medium', 'high'])
                ],
                'created_by' => fake()->randomElement($users)->id,
                'assigned_to' => fake()->randomElement([
                    null, $adminUser->id, $managerUser->id,
                    User::where('email', 'john.smith@demo.com')->first()?->id,
                    User::where('email', 'sarah.johnson@demo.com')->first()?->id
                ]),
                'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
                'updated_at' => fake()->dateTimeBetween('-2 months', 'now'),
            ]);
        }

        $this->command->info('Created 50 contacts successfully!');
    }
}
