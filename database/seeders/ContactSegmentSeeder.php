<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContactSegment;
use App\Models\User;
use App\Models\Contact;

class ContactSegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@crmultra.com')->first();
        $managerUser = User::where('email', 'manager@crmultra.com')->first();

        $segments = [
            [
                'name' => 'VIP Customers',
                'description' => 'High-value customers with premium accounts and significant purchase history.',
                'is_dynamic' => false,
                'conditions' => [
                    'rules' => [
                        ['field' => 'tags', 'operator' => 'contains', 'value' => 'vip'],
                        ['field' => 'status', 'operator' => '=', 'value' => 'active']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#e74c3c'
            ],
            [
                'name' => 'Tech Industry Leads',
                'description' => 'Contacts from technology companies and startups.',
                'is_dynamic' => true,
                'conditions' => [
                    'rules' => [
                        ['field' => 'custom_fields.industry', 'operator' => '=', 'value' => 'Technology'],
                        ['field' => 'status', 'operator' => '=', 'value' => 'active']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#3498db'
            ],
            [
                'name' => 'High-Value Prospects',
                'description' => 'Prospects with high budget potential and decision-making authority.',
                'is_dynamic' => true,
                'conditions' => [
                    'rules' => [
                        ['field' => 'custom_fields.budget', 'operator' => 'in', 'value' => '$25,000-50,000,$50,000+'],
                        ['field' => 'tags', 'operator' => 'contains', 'value' => 'decision-maker']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#9b59b6'
            ],
            [
                'name' => 'Small Business Owners',
                'description' => 'Small business contacts who could benefit from our SMB solutions.',
                'is_dynamic' => true,
                'conditions' => [
                    'rules' => [
                        ['field' => 'custom_fields.company_size', 'operator' => 'in', 'value' => '1-10,11-50'],
                        ['field' => 'tags', 'operator' => 'contains', 'value' => 'small-business']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#f39c12'
            ],
            [
                'name' => 'Enterprise Clients',
                'description' => 'Large enterprise contacts with complex requirements.',
                'is_dynamic' => true,
                'conditions' => [
                    'rules' => [
                        ['field' => 'custom_fields.company_size', 'operator' => 'in', 'value' => '501-1000,1000+'],
                        ['field' => 'tags', 'operator' => 'contains', 'value' => 'enterprise']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#2c3e50'
            ],
            [
                'name' => 'Newsletter Subscribers',
                'description' => 'Contacts who have subscribed to our newsletter.',
                'is_dynamic' => false,
                'conditions' => [
                    'rules' => [
                        ['field' => 'tags', 'operator' => 'contains', 'value' => 'newsletter-subscriber'],
                        ['field' => 'status', 'operator' => '=', 'value' => 'active']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#16a085'
            ],
            [
                'name' => 'High Interest Leads',
                'description' => 'Leads with high interest level and engagement.',
                'is_dynamic' => true,
                'conditions' => [
                    'rules' => [
                        ['field' => 'custom_fields.interest_level', 'operator' => '=', 'value' => 'high'],
                        ['field' => 'status', 'operator' => '=', 'value' => 'active']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#e67e22'
            ],
            [
                'name' => 'Inactive Contacts',
                'description' => 'Contacts who have been inactive and need re-engagement.',
                'is_dynamic' => true,
                'conditions' => [
                    'rules' => [
                        ['field' => 'status', 'operator' => '=', 'value' => 'inactive']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#95a5a6'
            ],
            [
                'name' => 'Referral Sources',
                'description' => 'Contacts who can provide referrals to new prospects.',
                'is_dynamic' => false,
                'conditions' => [
                    'rules' => [
                        ['field' => 'tags', 'operator' => 'contains', 'value' => 'referral-source'],
                        ['field' => 'status', 'operator' => '=', 'value' => 'active']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#8e44ad'
            ],
            [
                'name' => 'Recent Contacts',
                'description' => 'Contacts added in the last 30 days.',
                'is_dynamic' => true,
                'conditions' => [
                    'rules' => [
                        ['field' => 'created_at', 'operator' => '>=', 'value' => '30 days ago'],
                        ['field' => 'status', 'operator' => '=', 'value' => 'active']
                    ],
                    'logic' => 'AND'
                ],
                'color' => '#27ae60'
            ]
        ];

        foreach ($segments as $segmentData) {
            $segment = ContactSegment::create([
                ...$segmentData,
                'created_by' => fake()->randomElement([$adminUser->id, $managerUser->id]),
                'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
                'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
            ]);

            // For static segments, manually assign some contacts
            if (!$segment->is_dynamic) {
                $this->assignContactsToStaticSegment($segment);
            }
        }

        $this->command->info('Created ' . count($segments) . ' contact segments successfully!');
    }

    /**
     * Assign contacts to static segments based on their data.
     */
    protected function assignContactsToStaticSegment(ContactSegment $segment): void
    {
        $contactsToAssign = [];

        switch ($segment->name) {
            case 'VIP Customers':
                $contactsToAssign = Contact::whereJsonContains('tags', 'vip')
                    ->where('status', 'active')
                    ->take(5)
                    ->pluck('id')
                    ->toArray();
                break;

            case 'Newsletter Subscribers':
                $contactsToAssign = Contact::whereJsonContains('tags', 'newsletter-subscriber')
                    ->where('status', 'active')
                    ->take(15)
                    ->pluck('id')
                    ->toArray();
                break;

            case 'Referral Sources':
                // Create some contacts with referral-source tag if they don't exist
                $existingReferrals = Contact::whereJsonContains('tags', 'referral-source')->count();
                if ($existingReferrals < 3) {
                    $contacts = Contact::where('status', 'active')->take(3)->get();
                    foreach ($contacts as $contact) {
                        $tags = $contact->tags ?? [];
                        if (!in_array('referral-source', $tags)) {
                            $tags[] = 'referral-source';
                            $contact->update(['tags' => $tags]);
                        }
                    }
                }

                $contactsToAssign = Contact::whereJsonContains('tags', 'referral-source')
                    ->where('status', 'active')
                    ->pluck('id')
                    ->toArray();
                break;
        }

        if (!empty($contactsToAssign) && $segment->contacts()) {
            $segment->contacts()->sync($contactsToAssign);
        }
    }
}
