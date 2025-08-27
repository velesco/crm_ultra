<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@crmultra.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'timezone' => 'UTC',
                'language' => 'en',
                'subscription_plan' => 'enterprise',
                'settings' => [
                    'theme' => 'light',
                    'notifications' => [
                        'email' => true,
                        'browser' => true,
                        'mobile' => true
                    ],
                    'dashboard' => [
                        'quick_stats' => true,
                        'recent_activity' => true,
                        'charts' => true
                    ]
                ]
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@crmultra.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'timezone' => 'America/New_York',
                'language' => 'en',
                'subscription_plan' => 'enterprise',
                'settings' => [
                    'theme' => 'dark',
                    'notifications' => [
                        'email' => true,
                        'browser' => true,
                        'mobile' => false
                    ],
                    'dashboard' => [
                        'quick_stats' => true,
                        'recent_activity' => true,
                        'charts' => true
                    ]
                ]
            ]
        );
        $admin->assignRole('admin');

        // Create Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@crmultra.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('Manager123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'timezone' => 'Europe/London',
                'language' => 'en',
                'subscription_plan' => 'pro',
                'settings' => [
                    'theme' => 'light',
                    'notifications' => [
                        'email' => true,
                        'browser' => true,
                        'mobile' => true
                    ],
                    'dashboard' => [
                        'quick_stats' => true,
                        'recent_activity' => false,
                        'charts' => true
                    ]
                ]
            ]
        );
        $manager->assignRole('manager');

        // Create Agent
        $agent = User::firstOrCreate(
            ['email' => 'agent@crmultra.com'],
            [
                'name' => 'Agent User',
                'password' => Hash::make('Agent123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'timezone' => 'America/Los_Angeles',
                'language' => 'en',
                'subscription_plan' => 'basic',
                'settings' => [
                    'theme' => 'light',
                    'notifications' => [
                        'email' => false,
                        'browser' => true,
                        'mobile' => false
                    ],
                    'dashboard' => [
                        'quick_stats' => true,
                        'recent_activity' => true,
                        'charts' => false
                    ]
                ]
            ]
        );
        $agent->assignRole('agent');

        // Create Viewer
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@crmultra.com'],
            [
                'name' => 'Viewer User',
                'password' => Hash::make('Viewer123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'timezone' => 'UTC',
                'language' => 'en',
                'subscription_plan' => 'free',
                'settings' => [
                    'theme' => 'light',
                    'notifications' => [
                        'email' => false,
                        'browser' => false,
                        'mobile' => false
                    ],
                    'dashboard' => [
                        'quick_stats' => true,
                        'recent_activity' => false,
                        'charts' => false
                    ]
                ]
            ]
        );
        $viewer->assignRole('viewer');

        // Create Demo Users for testing
        $demoUsers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@demo.com',
                'role' => 'manager',
                'plan' => 'pro',
                'timezone' => 'America/New_York'
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@demo.com',
                'role' => 'agent',
                'plan' => 'basic',
                'timezone' => 'Europe/London'
            ],
            [
                'name' => 'Mike Davis',
                'email' => 'mike.davis@demo.com',
                'role' => 'agent',
                'plan' => 'basic',
                'timezone' => 'Asia/Tokyo'
            ],
            [
                'name' => 'Emily Brown',
                'email' => 'emily.brown@demo.com',
                'role' => 'viewer',
                'plan' => 'free',
                'timezone' => 'Australia/Sydney'
            ]
        ];

        foreach ($demoUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('Demo123!'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'timezone' => $userData['timezone'],
                    'language' => 'en',
                    'subscription_plan' => $userData['plan'],
                    'last_login_at' => now()->subDays(rand(1, 30)),
                    'settings' => [
                        'theme' => 'light',
                        'notifications' => [
                            'email' => true,
                            'browser' => true,
                            'mobile' => false
                        ],
                        'dashboard' => [
                            'quick_stats' => true,
                            'recent_activity' => true,
                            'charts' => true
                        ]
                    ]
                ]
            );
            $user->assignRole($userData['role']);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Default login credentials:');
        $this->command->info('Super Admin: superadmin@crmultra.com / SuperAdmin123!');
        $this->command->info('Admin: admin@crmultra.com / Admin123!');
        $this->command->info('Manager: manager@crmultra.com / Manager123!');
        $this->command->info('Agent: agent@crmultra.com / Agent123!');
        $this->command->info('Viewer: viewer@crmultra.com / Viewer123!');
    }
}
