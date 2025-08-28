<?php

namespace Database\Seeders;

use App\Models\CustomReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users for report creation
        $superAdmin = User::where('email', 'superadmin@crmultra.com')->first();
        $admin = User::where('email', 'admin@crmultra.com')->first();
        $manager = User::where('email', 'manager@crmultra.com')->first();

        // Ensure we have the users
        if (! $superAdmin || ! $admin || ! $manager) {
            $this->command->warn('Required users not found. Please run UserSeeder first.');

            return;
        }

        // Contact Reports
        CustomReport::create([
            'name' => 'VIP Contacts Report',
            'description' => 'List of all VIP contacts with their engagement metrics',
            'category' => 'contacts',
            'data_source' => 'contacts',
            'columns' => ['first_name', 'last_name', 'email', 'phone', 'company', 'industry', 'status', 'created_at'],
            'filters' => [
                [
                    'column' => 'status',
                    'operator' => 'equals',
                    'value' => 'active',
                ],
            ],
            'sorting' => [
                ['column' => 'created_at', 'direction' => 'desc'],
            ],
            'visibility' => 'shared',
            'export_format' => 'both',
            'chart_config' => [
                'type' => 'bar',
                'x_axis' => 'industry',
                'y_axis' => 'id',
                'title' => 'Contacts by Industry',
            ],
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        CustomReport::create([
            'name' => 'New Contacts This Month',
            'description' => 'Report showing all contacts created in the current month',
            'category' => 'contacts',
            'data_source' => 'contacts',
            'columns' => ['first_name', 'last_name', 'email', 'company', 'created_at'],
            'filters' => [
                [
                    'column' => 'created_at',
                    'operator' => 'date_range',
                    'value' => [
                        'start' => now()->startOfMonth()->format('Y-m-d'),
                        'end' => now()->endOfMonth()->format('Y-m-d'),
                    ],
                ],
            ],
            'sorting' => [
                ['column' => 'created_at', 'direction' => 'desc'],
            ],
            'visibility' => 'public',
            'export_format' => 'table',
            'is_active' => true,
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        // Campaign Reports
        CustomReport::create([
            'name' => 'Email Campaign Performance',
            'description' => 'Comprehensive analysis of email campaign performance metrics',
            'category' => 'campaigns',
            'data_source' => 'email_campaigns',
            'columns' => ['name', 'subject', 'status', 'sent_count', 'opened_count', 'clicked_count', 'created_at'],
            'filters' => [
                [
                    'column' => 'status',
                    'operator' => 'in',
                    'value' => ['sent', 'completed'],
                ],
            ],
            'sorting' => [
                ['column' => 'sent_count', 'direction' => 'desc'],
            ],
            'visibility' => 'shared',
            'export_format' => 'both',
            'chart_config' => [
                'type' => 'line',
                'x_axis' => 'created_at',
                'y_axis' => 'opened_count',
                'title' => 'Email Opens Over Time',
            ],
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        CustomReport::create([
            'name' => 'Low Performing Campaigns',
            'description' => 'Identify campaigns with low open rates for optimization',
            'category' => 'campaigns',
            'data_source' => 'email_campaigns',
            'columns' => ['name', 'subject', 'sent_count', 'opened_count', 'clicked_count'],
            'filters' => [
                [
                    'column' => 'sent_count',
                    'operator' => 'greater_than',
                    'value' => 10,
                ],
            ],
            'sorting' => [
                ['column' => 'opened_count', 'direction' => 'asc'],
            ],
            'visibility' => 'private',
            'export_format' => 'table',
            'is_active' => true,
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        // Revenue Reports
        CustomReport::create([
            'name' => 'Monthly Revenue Analysis',
            'description' => 'Detailed breakdown of revenue by month and source',
            'category' => 'revenue',
            'data_source' => 'revenues',
            'columns' => ['amount', 'currency', 'type', 'source', 'customer_name', 'created_at'],
            'filters' => [
                [
                    'column' => 'status',
                    'operator' => 'equals',
                    'value' => 'confirmed',
                ],
            ],
            'sorting' => [
                ['column' => 'amount', 'direction' => 'desc'],
            ],
            'grouping' => ['source'],
            'visibility' => 'private',
            'export_format' => 'both',
            'chart_config' => [
                'type' => 'pie',
                'label_column' => 'source',
                'value_column' => 'amount',
                'title' => 'Revenue by Source',
            ],
            'is_active' => true,
            'created_by' => $superAdmin->id,
            'updated_by' => $superAdmin->id,
        ]);

        // SMS Reports
        CustomReport::create([
            'name' => 'SMS Delivery Report',
            'description' => 'SMS delivery status and cost analysis',
            'category' => 'system',
            'data_source' => 'sms_messages',
            'columns' => ['to_number', 'content', 'status', 'sent_at', 'delivered_at', 'cost', 'provider'],
            'filters' => [
                [
                    'column' => 'sent_at',
                    'operator' => 'date_range',
                    'value' => [
                        'start' => now()->subDays(30)->format('Y-m-d'),
                        'end' => now()->format('Y-m-d'),
                    ],
                ],
            ],
            'sorting' => [
                ['column' => 'sent_at', 'direction' => 'desc'],
            ],
            'visibility' => 'shared',
            'export_format' => 'table',
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // WhatsApp Reports
        CustomReport::create([
            'name' => 'WhatsApp Communication Log',
            'description' => 'Complete log of WhatsApp messages and status',
            'category' => 'system',
            'data_source' => 'whatsapp_messages',
            'columns' => ['session_id', 'phone_number', 'message_type', 'status', 'sent_at'],
            'filters' => [
                [
                    'column' => 'status',
                    'operator' => 'in',
                    'value' => ['sent', 'delivered', 'read'],
                ],
            ],
            'sorting' => [
                ['column' => 'sent_at', 'direction' => 'desc'],
            ],
            'visibility' => 'shared',
            'export_format' => 'table',
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Segment Analysis Report
        CustomReport::create([
            'name' => 'Contact Segment Analysis',
            'description' => 'Analysis of contact segments and their growth',
            'category' => 'general',
            'data_source' => 'contact_segments',
            'columns' => ['name', 'is_dynamic', 'contact_count', 'created_at'],
            'filters' => [
                [
                    'column' => 'is_dynamic',
                    'operator' => 'equals',
                    'value' => true,
                ],
            ],
            'sorting' => [
                ['column' => 'contact_count', 'direction' => 'desc'],
            ],
            'visibility' => 'public',
            'export_format' => 'both',
            'chart_config' => [
                'type' => 'doughnut',
                'label_column' => 'name',
                'value_column' => 'contact_count',
                'title' => 'Contacts by Segment',
            ],
            'is_active' => true,
            'created_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        // Communication Overview Report
        CustomReport::create([
            'name' => 'Multi-Channel Communication Overview',
            'description' => 'Overview of all communications across email, SMS, and WhatsApp',
            'category' => 'general',
            'data_source' => 'communications',
            'columns' => ['contact_id', 'type', 'status', 'subject', 'sent_at', 'opened_at'],
            'filters' => [
                [
                    'column' => 'sent_at',
                    'operator' => 'date_range',
                    'value' => [
                        'start' => now()->subDays(7)->format('Y-m-d'),
                        'end' => now()->format('Y-m-d'),
                    ],
                ],
            ],
            'sorting' => [
                ['column' => 'sent_at', 'direction' => 'desc'],
            ],
            'visibility' => 'shared',
            'export_format' => 'table',
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Executive Summary Report
        CustomReport::create([
            'name' => 'Executive Dashboard Summary',
            'description' => 'High-level metrics for executive reporting',
            'category' => 'general',
            'data_source' => 'contacts',
            'columns' => ['id', 'status', 'industry', 'created_at'],
            'filters' => [],
            'sorting' => [],
            'grouping' => ['status', 'industry'],
            'visibility' => 'private',
            'export_format' => 'both',
            'chart_config' => [
                'type' => 'bar',
                'x_axis' => 'industry',
                'y_axis' => 'id',
                'title' => 'Contact Distribution',
            ],
            'is_active' => true,
            'created_by' => $superAdmin->id,
            'updated_by' => $superAdmin->id,
        ]);

        $this->command->info('Custom Reports seeded successfully!');
        $this->command->info('Created 10 sample reports across all categories.');
        $this->command->info('Reports include: contacts, campaigns, revenue, SMS, WhatsApp, segments, and executive summaries.');
    }
}
