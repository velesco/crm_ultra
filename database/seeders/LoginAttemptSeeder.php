<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoginAttempt;
use App\Models\User;
use Carbon\Carbon;

class LoginAttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $currentTime = now();
        
        // Sample IP addresses and user agents
        $ipAddresses = [
            '192.168.1.100',
            '10.0.0.25',
            '203.0.113.45',
            '198.51.100.67',
            '172.16.0.89',
            '127.0.0.1',
            '85.122.45.67',
            '45.123.67.89',
            '123.45.67.89',
            '87.65.43.21'
        ];
        
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 11; Mobile; rv:89.0) Gecko/89.0 Firefox/89.0',
        ];
        
        $locations = [
            ['country' => 'Romania', 'city' => 'Bucharest'],
            ['country' => 'Romania', 'city' => 'Cluj-Napoca'],
            ['country' => 'United States', 'city' => 'New York'],
            ['country' => 'United Kingdom', 'city' => 'London'],
            ['country' => 'Germany', 'city' => 'Berlin'],
            ['country' => 'France', 'city' => 'Paris'],
            ['country' => 'Italy', 'city' => 'Rome'],
            ['country' => 'Spain', 'city' => 'Madrid'],
        ];
        
        $devices = [
            'Desktop - Windows',
            'Desktop - macOS',
            'Desktop - Linux',
            'Mobile - iPhone',
            'Mobile - Android',
            'Tablet - iPad',
            'Tablet - Android',
        ];
        
        $browsers = [
            'Chrome 91',
            'Firefox 89',
            'Safari 14',
            'Edge 91',
            'Opera 77',
        ];

        // Create successful login attempts
        foreach ($users as $user) {
            $successCount = rand(10, 50);
            for ($i = 0; $i < $successCount; $i++) {
                $timestamp = $currentTime->copy()->subHours(rand(1, 720)); // Last 30 days
                
                LoginAttempt::create([
                    'email' => $user->email,
                    'ip_address' => $ipAddresses[array_rand($ipAddresses)],
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'type' => 'success',
                    'metadata' => [
                        'country' => $locations[array_rand($locations)]['country'],
                        'city' => $locations[array_rand($locations)]['city'],
                        'device' => $devices[array_rand($devices)],
                        'browser' => $browsers[array_rand($browsers)],
                        'login_duration' => rand(300, 7200), // 5 minutes to 2 hours
                    ],
                    'user_id' => $user->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }

        // Create failed login attempts
        $failedEmails = [
            'hacker@example.com',
            'test@test.com',
            'admin@admin.com',
            'user@user.com',
            'root@localhost',
            'administrator@domain.com',
            'guest@guest.com',
            'demo@demo.com',
        ];
        
        // Add some failed attempts for existing users too
        foreach ($users as $user) {
            if (rand(1, 3) === 1) { // 33% chance
                $failedEmails[] = $user->email;
            }
        }

        foreach ($failedEmails as $email) {
            $failedCount = rand(3, 25);
            for ($i = 0; $i < $failedCount; $i++) {
                $timestamp = $currentTime->copy()->subHours(rand(1, 168)); // Last 7 days
                $ip = $ipAddresses[array_rand($ipAddresses)];
                
                LoginAttempt::create([
                    'email' => $email,
                    'ip_address' => $ip,
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'type' => 'failed',
                    'metadata' => [
                        'country' => $locations[array_rand($locations)]['country'],
                        'city' => $locations[array_rand($locations)]['city'],
                        'device' => $devices[array_rand($devices)],
                        'browser' => $browsers[array_rand($browsers)],
                        'failed_reason' => collect(['Invalid password', 'User not found', 'Account locked', 'Invalid email format'])->random(),
                    ],
                    'user_id' => User::where('email', $email)->first()?->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }

        // Create some blocked attempts
        $suspiciousIPs = ['85.122.45.67', '45.123.67.89', '123.45.67.89'];
        
        foreach ($suspiciousIPs as $ip) {
            $blockedCount = rand(5, 15);
            for ($i = 0; $i < $blockedCount; $i++) {
                $timestamp = $currentTime->copy()->subHours(rand(1, 48)); // Last 2 days
                $blockedUntil = $timestamp->copy()->addHours(rand(1, 24));
                
                LoginAttempt::create([
                    'email' => $failedEmails[array_rand($failedEmails)],
                    'ip_address' => $ip,
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'type' => 'blocked',
                    'metadata' => [
                        'country' => collect(['Russia', 'China', 'Unknown'])->random(),
                        'city' => collect(['Moscow', 'Beijing', 'Unknown'])->random(),
                        'device' => 'Bot',
                        'browser' => 'Automated',
                        'blocked_reason' => collect(['Too many failed attempts', 'Suspicious activity', 'Brute force detected'])->random(),
                        'blocked_by' => 'system',
                    ],
                    'blocked_until' => $blockedUntil,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }

        // Create some currently blocked IPs
        $currentlyBlockedIPs = ['87.65.43.21'];
        foreach ($currentlyBlockedIPs as $ip) {
            LoginAttempt::create([
                'email' => 'system_block',
                'ip_address' => $ip,
                'user_agent' => 'Automated Bot',
                'type' => 'blocked',
                'metadata' => [
                    'country' => 'Unknown',
                    'city' => 'Unknown',
                    'device' => 'Bot',
                    'browser' => 'Automated',
                    'blocked_reason' => 'Manual block - Suspicious activity',
                    'blocked_by' => 1, // Admin user ID
                    'manual_block' => true,
                ],
                'blocked_until' => $currentTime->copy()->addHours(24),
                'created_at' => $currentTime->copy()->subHour(),
                'updated_at' => $currentTime->copy()->subHour(),
            ]);
        }

        $this->command->info('Created login attempt test data:');
        $this->command->info('- Success attempts: ' . LoginAttempt::success()->count());
        $this->command->info('- Failed attempts: ' . LoginAttempt::failed()->count());
        $this->command->info('- Blocked attempts: ' . LoginAttempt::blocked()->count());
        $this->command->info('- Currently blocked: ' . LoginAttempt::currentlyBlocked()->count());
    }
}
