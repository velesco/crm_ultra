<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Models\SmtpConfig;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailCampaign>
 */
class EmailCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $campaignNames = [
            'Welcome New Users', 'Monthly Newsletter', 'Product Launch Announcement',
            'Holiday Special Offer', 'Webinar Invitation', 'Customer Feedback Survey',
            'Industry Report Release', 'Feature Update Notification', 'Re-engagement Campaign',
            'Black Friday Deals', 'Year End Review', 'New Partnership Announcement'
        ];

        $subjects = [
            'Welcome to {{company}}, {{first_name}}!',
            'Don\'t miss out on our latest updates',
            'Exclusive offer just for you, {{first_name}}',
            'Join us for an exciting webinar',
            'Your monthly report is ready',
            'New features that will boost your productivity',
            '🚀 Something amazing is coming your way',
            'Last chance to grab this deal',
            'We\'d love your feedback, {{first_name}}',
            'Breaking: Industry insights you need to know'
        ];

        $status = fake()->randomElement(['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled', 'failed']);
        
        $totalRecipients = fake()->numberBetween(10, 1000);
        $sentCount = $status === 'sent' ? $totalRecipients : 
                    ($status === 'sending' ? fake()->numberBetween(1, $totalRecipients) : 0);
        
        $deliveredCount = $sentCount > 0 ? fake()->numberBetween(0, $sentCount) : 0;
        $openedCount = $deliveredCount > 0 ? fake()->numberBetween(0, $deliveredCount) : 0;
        $clickedCount = $openedCount > 0 ? fake()->numberBetween(0, $openedCount) : 0;
        $bouncedCount = $sentCount > 0 ? fake()->numberBetween(0, intval($sentCount * 0.05)) : 0;
        $failedCount = $sentCount - $deliveredCount - $bouncedCount;

        return [
            'name' => fake()->randomElement($campaignNames),
            'subject' => fake()->randomElement($subjects),
            'content' => $this->generateEmailContent(),
            'template_id' => fake()->boolean(70) ? EmailTemplate::factory() : null,
            'smtp_config_id' => SmtpConfig::factory(),
            'status' => $status,
            'scheduled_at' => $status === 'scheduled' ? fake()->dateTimeBetween('now', '+1 month') : null,
            'sent_at' => in_array($status, ['sent', 'sending']) ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'total_recipients' => $totalRecipients,
            'sent_count' => $sentCount,
            'delivered_count' => $deliveredCount,
            'opened_count' => $openedCount,
            'clicked_count' => $clickedCount,
            'bounced_count' => $bouncedCount,
            'failed_count' => $failedCount,
            'settings' => [
                'send_immediately' => fake()->boolean(30),
                'track_opens' => fake()->boolean(90),
                'track_clicks' => fake()->boolean(85),
                'unsubscribe_link' => true,
                'personalization_enabled' => fake()->boolean(80),
                'priority' => fake()->randomElement(['low', 'normal', 'high']),
                'timezone' => fake()->randomElement(['UTC', 'America/New_York', 'Europe/London']),
                'send_rate_limit' => fake()->numberBetween(50, 500), // emails per hour
                'retry_failed' => fake()->boolean(70),
                'max_retries' => fake()->numberBetween(2, 5),
            ],
            'created_by' => User::factory(),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the campaign is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'scheduled_at' => null,
            'sent_at' => null,
            'sent_count' => 0,
            'delivered_count' => 0,
            'opened_count' => 0,
            'clicked_count' => 0,
            'bounced_count' => 0,
            'failed_count' => 0,
        ]);
    }

    /**
     * Indicate that the campaign is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'scheduled_at' => fake()->dateTimeBetween('+1 hour', '+1 month'),
            'sent_at' => null,
            'sent_count' => 0,
            'delivered_count' => 0,
            'opened_count' => 0,
            'clicked_count' => 0,
            'bounced_count' => 0,
            'failed_count' => 0,
        ]);
    }

    /**
     * Indicate that the campaign has been sent.
     */
    public function sent(): static
    {
        return $this->state(function (array $attributes) {
            $totalRecipients = $attributes['total_recipients'] ?? fake()->numberBetween(100, 1000);
            $sentCount = $totalRecipients;
            $deliveredCount = fake()->numberBetween(intval($sentCount * 0.85), intval($sentCount * 0.98));
            $openedCount = fake()->numberBetween(intval($deliveredCount * 0.15), intval($deliveredCount * 0.35));
            $clickedCount = fake()->numberBetween(0, intval($openedCount * 0.25));
            $bouncedCount = fake()->numberBetween(0, intval($sentCount * 0.03));
            $failedCount = $sentCount - $deliveredCount - $bouncedCount;

            return [
                'status' => 'sent',
                'sent_at' => fake()->dateTimeBetween('-2 months', '-1 day'),
                'scheduled_at' => null,
                'total_recipients' => $totalRecipients,
                'sent_count' => $sentCount,
                'delivered_count' => $deliveredCount,
                'opened_count' => $openedCount,
                'clicked_count' => $clickedCount,
                'bounced_count' => $bouncedCount,
                'failed_count' => $failedCount,
            ];
        });
    }

    /**
     * Indicate that the campaign is currently sending.
     */
    public function sending(): static
    {
        return $this->state(function (array $attributes) {
            $totalRecipients = $attributes['total_recipients'] ?? fake()->numberBetween(100, 1000);
            $sentCount = fake()->numberBetween(1, intval($totalRecipients * 0.7));
            $deliveredCount = fake()->numberBetween(0, $sentCount);

            return [
                'status' => 'sending',
                'sent_at' => fake()->dateTimeBetween('-2 hours', 'now'),
                'total_recipients' => $totalRecipients,
                'sent_count' => $sentCount,
                'delivered_count' => $deliveredCount,
                'opened_count' => fake()->numberBetween(0, intval($deliveredCount * 0.2)),
                'clicked_count' => fake()->numberBetween(0, intval($deliveredCount * 0.05)),
                'bounced_count' => fake()->numberBetween(0, intval($sentCount * 0.02)),
                'failed_count' => fake()->numberBetween(0, intval($sentCount * 0.05)),
            ];
        });
    }

    /**
     * Create a high-performing campaign.
     */
    public function highPerforming(): static
    {
        return $this->state(function (array $attributes) {
            $totalRecipients = $attributes['total_recipients'] ?? fake()->numberBetween(500, 2000);
            $sentCount = $totalRecipients;
            $deliveredCount = fake()->numberBetween(intval($sentCount * 0.95), $sentCount);
            $openedCount = fake()->numberBetween(intval($deliveredCount * 0.35), intval($deliveredCount * 0.55));
            $clickedCount = fake()->numberBetween(intval($openedCount * 0.15), intval($openedCount * 0.35));

            return [
                'status' => 'sent',
                'sent_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
                'total_recipients' => $totalRecipients,
                'sent_count' => $sentCount,
                'delivered_count' => $deliveredCount,
                'opened_count' => $openedCount,
                'clicked_count' => $clickedCount,
                'bounced_count' => fake()->numberBetween(0, intval($sentCount * 0.01)),
                'failed_count' => $sentCount - $deliveredCount,
            ];
        });
    }

    /**
     * Create a newsletter campaign.
     */
    public function newsletter(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Monthly Newsletter - ' . fake()->monthName() . ' ' . fake()->year(),
            'subject' => fake()->randomElement([
                'Your monthly update from {{company}}',
                '{{company}} Newsletter - {{current_date}}',
                'What\'s new this month, {{first_name}}?',
                'Monthly insights and updates'
            ]),
            'settings' => array_merge($attributes['settings'] ?? [], [
                'track_opens' => true,
                'track_clicks' => true,
                'personalization_enabled' => true,
                'priority' => 'normal',
            ]),
        ]);
    }

    /**
     * Create a promotional campaign.
     */
    public function promotional(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement([
                'Summer Sale Campaign',
                'Black Friday Deals',
                'Holiday Special Offer',
                'Flash Sale Alert',
                'Exclusive Discount'
            ]),
            'subject' => fake()->randomElement([
                '🔥 Limited time offer - 50% off!',
                'Don\'t miss out, {{first_name}} - Sale ends soon!',
                'Exclusive deal just for you',
                'Last chance - Save big today!'
            ]),
            'settings' => array_merge($attributes['settings'] ?? [], [
                'track_opens' => true,
                'track_clicks' => true,
                'priority' => 'high',
                'send_immediately' => fake()->boolean(60),
            ]),
        ]);
    }

    /**
     * Create a welcome campaign.
     */
    public function welcome(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Welcome Email Campaign',
            'subject' => 'Welcome to {{company}}, {{first_name}}!',
            'settings' => array_merge($attributes['settings'] ?? [], [
                'track_opens' => true,
                'track_clicks' => true,
                'personalization_enabled' => true,
                'send_immediately' => true,
                'priority' => 'high',
            ]),
        ]);
    }

    /**
     * Generate sample email content.
     */
    protected function generateEmailContent(): string
    {
        $templates = [
            $this->getWelcomeContent(),
            $this->getNewsletterContent(),
            $this->getPromotionalContent(),
            $this->getAnnouncementContent(),
        ];

        return fake()->randomElement($templates);
    }

    protected function getWelcomeContent(): string
    {
        return <<<HTML
<h1>Welcome to {{company}}, {{first_name}}!</h1>
<p>We're thrilled to have you join our community of innovative professionals.</p>
<p>Here's what you can expect:</p>
<ul>
<li>Regular updates on industry trends</li>
<li>Exclusive access to our resources</li>
<li>Personalized recommendations</li>
<li>24/7 customer support</li>
</ul>
<p><a href="#" style="background-color: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Get Started</a></p>
<p>Welcome aboard!</p>
HTML;
    }

    protected function getNewsletterContent(): string
    {
        return <<<HTML
<h1>{{company}} Newsletter</h1>
<h2>Hi {{first_name}},</h2>
<p>Here's what's happening this month:</p>

<h3>🚀 New Features</h3>
<p>We've launched several exciting features that will help streamline your workflow.</p>

<h3>📊 Industry Insights</h3>
<p>Check out our latest research on market trends and best practices.</p>

<h3>🎯 Success Story</h3>
<p>Learn how {{company}} helped increase productivity by 40% for our clients.</p>

<p><a href="#" style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Read More</a></p>
HTML;
    }

    protected function getPromotionalContent(): string
    {
        return <<<HTML
<h1>🔥 Special Offer Just for You!</h1>
<h2>Hi {{first_name}},</h2>
<p>We have an exclusive deal that you won't want to miss!</p>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
<h3 style="color: #dc3545; font-size: 2em;">50% OFF</h3>
<p>Limited time offer - expires in 48 hours!</p>
</div>

<p>This is our way of saying thank you for being a valued member of our community.</p>

<p><a href="#" style="background-color: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 1.1em;">Claim Your Discount</a></p>

<p><small>Use code SAVE50 at checkout. Offer valid until {{offer_expiry}}.</small></p>
HTML;
    }

    protected function getAnnouncementContent(): string
    {
        return <<<HTML
<h1>Exciting News from {{company}}!</h1>
<h2>Hello {{first_name}},</h2>
<p>We're excited to share some important updates with you.</p>

<h3>🎉 Product Launch</h3>
<p>After months of development, we're proud to announce the launch of our newest product.</p>

<h3>🏆 Award Recognition</h3>
<p>We're honored to have received industry recognition for our innovative solutions.</p>

<h3>🤝 New Partnerships</h3>
<p>We've formed strategic partnerships that will bring you even more value.</p>

<p><a href="#" style="background-color: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Learn More</a></p>

<p>Thank you for your continued support!</p>
HTML;
    }
}
