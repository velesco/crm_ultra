<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@crmultra.com')->first();

        $templates = [
            [
                'name' => 'Welcome Email',
                'subject' => 'Welcome to {{company_name}}!',
                'content' => $this->getWelcomeTemplate(),
                'category' => 'system',
                'is_active' => true,
                'variables' => ['company_name', 'first_name'],
            ],
            [
                'name' => 'Product Demo Invitation',
                'subject' => 'See {{product_name}} in action - Book your demo',
                'content' => $this->getDemoTemplate(),
                'category' => 'marketing',
                'is_active' => true,
                'variables' => ['product_name', 'first_name'],
            ],
            [
                'name' => 'Follow-up After Demo',
                'subject' => 'Thanks for the demo! Next steps with {{company_name}}',
                'content' => $this->getFollowUpTemplate(),
                'category' => 'sales',
                'is_active' => true,
                'variables' => ['company_name', 'first_name'],
            ],
            [
                'name' => 'Newsletter Template',
                'subject' => '{{company_name}} Monthly Newsletter - {{month}} {{year}}',
                'content' => $this->getNewsletterTemplate(),
                'category' => 'marketing',
                'is_active' => true,
                'variables' => ['company_name', 'month', 'year', 'first_name'],
            ],
            [
                'name' => 'Event Invitation',
                'subject' => 'You\'re invited! {{event_name}} - {{event_date}}',
                'content' => $this->getEventTemplate(),
                'category' => 'marketing',
                'is_active' => true,
                'variables' => ['event_name', 'event_date', 'event_time', 'event_location', 'first_name'],
            ],
            [
                'name' => 'Thank You Email',
                'subject' => 'Thank you, {{first_name}}!',
                'content' => $this->getThankYouTemplate(),
                'category' => 'system',
                'is_active' => true,
                'variables' => ['first_name', 'company_name'],
            ],
            [
                'name' => 'Proposal Follow-up',
                'subject' => 'Following up on our proposal - {{company_name}}',
                'content' => $this->getProposalTemplate(),
                'category' => 'sales',
                'is_active' => true,
                'variables' => ['first_name', 'project_name', 'deliverable_1', 'deliverable_2', 'deliverable_3'],
            ],
            [
                'name' => 'Customer Survey',
                'subject' => 'Help us improve - Quick 2-minute survey',
                'content' => $this->getSurveyTemplate(),
                'category' => 'feedback',
                'is_active' => true,
                'variables' => ['first_name', 'company_name'],
            ],
            [
                'name' => 'Re-engagement Campaign',
                'subject' => 'We miss you, {{first_name}}!',
                'content' => $this->getReEngagementTemplate(),
                'category' => 'marketing',
                'is_active' => true,
                'variables' => ['first_name'],
            ],
            [
                'name' => 'Monthly Report',
                'subject' => 'Your monthly report is ready - {{current_date}}',
                'content' => $this->getMonthlyReportTemplate(),
                'category' => 'system',
                'is_active' => true,
                'variables' => ['first_name', 'current_date', 'total_contacts', 'campaigns_sent'],
            ],
        ];

        foreach ($templates as $templateData) {
            EmailTemplate::create([
                ...$templateData,
                'created_by' => $adminUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Created '.count($templates).' email templates successfully!');
    }

    private function getWelcomeTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; font-size: 28px; margin: 0;">Welcome to {{company_name}}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 24px; margin: 0 0 20px 0;">Hello {{first_name}},</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Thank you for joining {{company_name}}! We're excited to have you as part of our community.
                            </p>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Here are some things you can do to get started:
                            </p>
                            <ul style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0 0 25px 20px;">
                                <li>Complete your profile setup</li>
                                <li>Explore our features and tools</li>
                                <li>Join our community forum</li>
                                <li>Contact us if you need help</li>
                            </ul>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #3498db; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: 500; display: inline-block;">Get Started</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getDemoTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Demo</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background-color: #2c3e50; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; font-size: 26px; margin: 0;">See {{product_name}} in Action!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 22px; margin: 0 0 20px 0;">Hi {{first_name}},</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Ready to see how {{product_name}} can transform your business? Book a personalized demo with our team!
                            </p>
                            <div style="background-color: #ecf0f1; padding: 20px; border-radius: 8px; margin: 25px 0;">
                                <h3 style="color: #2c3e50; font-size: 18px; margin: 0 0 15px 0;">What you'll learn:</h3>
                                <ul style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>Key features and capabilities</li>
                                    <li>How it integrates with your workflow</li>
                                    <li>ROI and business benefits</li>
                                    <li>Implementation timeline</li>
                                </ul>
                            </div>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #e74c3c; color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 5px; font-weight: bold; display: inline-block;">Book Your Demo</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getFollowUpTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Follow-up</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 30px; background-color: #27ae60; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; font-size: 24px; margin: 0; text-align: center;">Thanks for the Demo!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 20px; margin: 0 0 20px 0;">Hi {{first_name}},</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Thank you for taking the time to see {{product_name}} in action today. I hope you found the demonstration valuable!
                            </p>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                As promised, here are the next steps:
                            </p>
                            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 25px 0;">
                                <ol style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>Review the proposal we discussed</li>
                                    <li>Share with your team for feedback</li>
                                    <li>Schedule a follow-up call next week</li>
                                </ol>
                            </div>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #27ae60; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: 500; display: inline-block;">Schedule Follow-up</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getNewsletterTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 30px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; font-size: 28px; margin: 0; text-align: center;">{{company_name}} Newsletter</h1>
                            <p style="color: #ffffff; font-size: 16px; margin: 10px 0 0 0; text-align: center;">{{month}} {{year}} Edition</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 22px; margin: 0 0 20px 0;">Hello {{first_name}},</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Here's what's been happening at {{company_name}} this month!
                            </p>
                            
                            <div style="margin: 30px 0; padding: 20px; border-left: 4px solid #3498db;">
                                <h3 style="color: #2c3e50; font-size: 18px; margin: 0 0 10px 0;">New Features</h3>
                                <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0;">
                                    We've launched some exciting new features based on your feedback!
                                </p>
                            </div>
                            
                            <div style="margin: 30px 0; padding: 20px; border-left: 4px solid #e74c3c;">
                                <h3 style="color: #2c3e50; font-size: 18px; margin: 0 0 10px 0;">Company Updates</h3>
                                <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0;">
                                    Check out our latest milestones and achievements.
                                </p>
                            </div>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #3498db; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: 500; display: inline-block;">Read More</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getEventTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Invitation</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="color: #2c3e50; font-size: 26px; margin: 0;">You're Invited!</h1>
                            <h2 style="color: #2c3e50; font-size: 20px; margin: 10px 0 0 0;">{{event_name}}</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h3 style="color: #2c3e50; font-size: 18px; margin: 0 0 20px 0;">Hi {{first_name}},</h3>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                We're excited to invite you to {{event_name}} on {{event_date}}!
                            </p>
                            
                            <div style="background-color: #f8f9fa; padding: 25px; border-radius: 8px; margin: 25px 0;">
                                <h4 style="color: #2c3e50; font-size: 16px; margin: 0 0 15px 0;">Event Details:</h4>
                                <p style="color: #666666; font-size: 14px; margin: 5px 0;"><strong>Date:</strong> {{event_date}}</p>
                                <p style="color: #666666; font-size: 14px; margin: 5px 0;"><strong>Time:</strong> {{event_time}}</p>
                                <p style="color: #666666; font-size: 14px; margin: 5px 0;"><strong>Location:</strong> {{event_location}}</p>
                            </div>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 30px; font-weight: bold; display: inline-block;">Reserve Your Spot</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getThankYouTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="color: #2c3e50; font-size: 32px; margin: 0;">Thank You!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 24px; margin: 0 0 20px 0;">Dear {{first_name}},</h2>
                            <p style="color: #666666; font-size: 18px; line-height: 1.6; margin: 0 0 25px 0;">
                                We wanted to take a moment to express our heartfelt gratitude for your continued support and trust in {{company_name}}.
                            </p>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Your partnership means the world to us, and we're committed to providing you with exceptional service and value.
                            </p>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #f39c12; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: 500; display: inline-block;">Continue Our Journey</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getProposalTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Follow-up</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 30px; background-color: #6c5ce7; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; font-size: 24px; margin: 0; text-align: center;">Following Up on Our Proposal</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 20px; margin: 0 0 20px 0;">Hi {{first_name}},</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                I wanted to follow up on the proposal we sent last week regarding {{project_name}}.
                            </p>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Do you have any questions about our approach or timeline? I'm here to address any concerns and help move this project forward.
                            </p>
                            <div style="background-color: #f1f2f6; padding: 20px; border-radius: 8px; margin: 25px 0;">
                                <h3 style="color: #2c3e50; font-size: 18px; margin: 0 0 10px 0;">Quick Recap:</h3>
                                <ul style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>{{deliverable_1}}</li>
                                    <li>{{deliverable_2}}</li>
                                    <li>{{deliverable_3}}</li>
                                </ul>
                            </div>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #6c5ce7; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: 500; display: inline-block;">Schedule Discussion</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getSurveyTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Survey</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 30px; background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; font-size: 26px; margin: 0; text-align: center;">Help Us Improve!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 22px; margin: 0 0 20px 0;">Hi {{first_name}},</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Your feedback is incredibly valuable to us! Would you mind taking 2 minutes to share your thoughts about your experience with {{company_name}}?
                            </p>
                            <div style="background-color: #dff9fb; padding: 20px; border-radius: 8px; margin: 25px 0;">
                                <h3 style="color: #2c3e50; font-size: 18px; margin: 0 0 15px 0;">What we'd love to know:</h3>
                                <ul style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>How satisfied are you with our service?</li>
                                    <li>What features do you find most valuable?</li>
                                    <li>Any suggestions for improvement?</li>
                                </ul>
                            </div>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #0984e3; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 30px; font-weight: bold; display: inline-block;">Take 2-Minute Survey</a>
                            </div>
                            <p style="color: #999999; font-size: 14px; text-align: center; margin: 20px 0 0 0;">
                                Thank you for helping us serve you better!
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getReEngagementTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Miss You</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="color: #2c3e50; font-size: 32px; margin: 0;">We Miss You!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 24px; margin: 0 0 20px 0;">Hi {{first_name}},</h2>
                            <p style="color: #666666; font-size: 18px; line-height: 1.6; margin: 0 0 25px 0;">
                                It's been a while since we last heard from you, and we wanted to reach out to see how you're doing.
                            </p>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                We've been busy improving our platform with exciting new features that we think you'll love:
                            </p>
                            <div style="background-color: #f8f9fa; padding: 25px; border-radius: 8px; margin: 25px 0;">
                                <ul style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>Enhanced dashboard with real-time analytics</li>
                                    <li>Mobile app for on-the-go management</li>
                                    <li>AI-powered insights and recommendations</li>
                                    <li>New integrations with popular tools</li>
                                </ul>
                            </div>
                            <div style="text-align: center; margin: 35px 0;">
                                <a href="#" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 30px; font-weight: bold; font-size: 16px; display: inline-block;">Welcome Back - 30% Off</a>
                            </div>
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 0; text-align: center;">
                                Use code COMEBACK30 - valid for the next 7 days
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function getMonthlyReportTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 30px; background-color: #34495e; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; font-size: 26px; margin: 0; text-align: center;">Monthly Report</h1>
                            <p style="color: #bdc3c7; font-size: 16px; margin: 10px 0 0 0; text-align: center;">{{current_date}}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2c3e50; font-size: 22px; margin: 0 0 20px 0;">Hello {{first_name}},</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Here's your monthly performance summary with key metrics and insights.
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                                <tr>
                                    <td width="45%" style="padding: 15px; background-color: #ecf0f1; border-radius: 5px;">
                                        <h4 style="color: #2c3e50; font-size: 14px; margin: 0 0 5px 0; text-transform: uppercase;">Total Contacts</h4>
                                        <p style="color: #27ae60; font-size: 24px; font-weight: bold; margin: 0;">{{total_contacts}}</p>
                                    </td>
                                    <td width="10%"></td>
                                    <td width="45%" style="padding: 15px; background-color: #ecf0f1; border-radius: 5px;">
                                        <h4 style="color: #2c3e50; font-size: 14px; margin: 0 0 5px 0; text-transform: uppercase;">Campaigns Sent</h4>
                                        <p style="color: #3498db; font-size: 24px; font-weight: bold; margin: 0;">{{campaigns_sent}}</p>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin: 30px 0;">
                                <h3 style="color: #2c3e50; font-size: 18px; margin: 0 0 15px 0;">Key Highlights</h3>
                                <ul style="color: #666666; font-size: 16px; line-height: 1.8; margin: 0; padding-left: 20px;">
                                    <li>Email open rate increased by 15%</li>
                                    <li>Added 25 new qualified leads</li>
                                    <li>Completed 3 successful campaigns</li>
                                    <li>Customer engagement up 23%</li>
                                </ul>
                            </div>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="#" style="background-color: #34495e; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: 500; display: inline-block;">View Full Report</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
