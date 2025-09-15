<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmtpConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SmtpConfig::create([
            'name' => 'Gmail Test Account',
            'provider' => 'gmail',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'test@gmail.com',
            'password' => encrypt('testpassword'),
            'from_email' => 'test@gmail.com',
            'from_name' => 'CRM Ultra',
            'is_active' => true,
            'daily_limit' => 500,
            'hourly_limit' => 50,
            'sent_today' => 0,
            'sent_this_hour' => 0,
        ]);

        \App\Models\SmtpConfig::create([
            'name' => 'Outlook Test Account',
            'provider' => 'outlook',
            'host' => 'smtp-mail.outlook.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'test@outlook.com',
            'password' => encrypt('testpassword'),
            'from_email' => 'test@outlook.com',
            'from_name' => 'CRM Ultra Team',
            'is_active' => true,
            'daily_limit' => 300,
            'hourly_limit' => 30,
            'sent_today' => 0,
            'sent_this_hour' => 0,
        ]);

        \App\Models\SmtpConfig::create([
            'name' => 'SendGrid Production',
            'provider' => 'sendgrid',
            'host' => 'smtp.sendgrid.net',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'apikey',
            'password' => encrypt('your-sendgrid-api-key'),
            'from_email' => 'noreply@yourcompany.com',
            'from_name' => 'Your Company',
            'is_active' => false, // Disabled by default
            'daily_limit' => 40000,
            'hourly_limit' => 4000,
            'sent_today' => 0,
            'sent_this_hour' => 0,
        ]);
    }
}
