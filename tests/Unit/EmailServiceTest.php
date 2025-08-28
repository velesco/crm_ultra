<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\SmtpConfig;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmailService $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailService = app(EmailService::class);
    }

    public function test_can_create_email_campaign()
    {
        $user = User::factory()->create();
        $smtpConfig = SmtpConfig::factory()->create(['created_by' => $user->id]);

        $campaignData = [
            'name' => 'Test Campaign',
            'subject' => 'Test Subject',
            'content' => 'Test Content',
            'smtp_config_id' => $smtpConfig->id,
            'scheduled_at' => now()->addHour(),
            'settings' => [
                'track_opens' => true,
                'track_clicks' => true,
            ],
        ];

        $this->actingAs($user);

        $campaign = $this->emailService->createCampaign($campaignData);

        $this->assertInstanceOf(EmailCampaign::class, $campaign);
        $this->assertEquals('Test Campaign', $campaign->name);
        $this->assertEquals('Test Subject', $campaign->subject);
        $this->assertEquals('draft', $campaign->status);
        $this->assertEquals($user->id, $campaign->created_by);
    }

    public function test_can_add_contacts_to_campaign()
    {
        $user = User::factory()->create();
        $campaign = EmailCampaign::factory()->create(['created_by' => $user->id]);
        $contacts = Contact::factory(3)->create();

        $contactIds = $contacts->pluck('id')->toArray();

        $result = $this->emailService->addContactsToCampaign($campaign, $contactIds);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['added']);
        $this->assertEquals(3, $result['total_recipients']);
        $this->assertEquals(3, $campaign->fresh()->total_recipients);
        $this->assertEquals(3, $campaign->contacts()->count());
    }

    public function test_prevents_duplicate_contacts_in_campaign()
    {
        $user = User::factory()->create();
        $campaign = EmailCampaign::factory()->create(['created_by' => $user->id]);
        $contacts = Contact::factory(3)->create();

        $contactIds = $contacts->pluck('id')->toArray();

        // Add contacts first time
        $this->emailService->addContactsToCampaign($campaign, $contactIds);

        // Try to add same contacts again
        $result = $this->emailService->addContactsToCampaign($campaign, $contactIds);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['added']); // No new contacts added
        $this->assertEquals(3, $result['total_recipients']); // Total remains same
    }

    public function test_personalize_content_replaces_variables()
    {
        $contact = Contact::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'company' => 'Test Company',
        ]);

        $content = 'Hello {{first_name}} {{last_name}} from {{company}}!';

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->emailService);
        $method = $reflection->getMethod('personalizeContent');
        $method->setAccessible(true);

        $result = $method->invoke($this->emailService, $content, $contact);

        $this->assertEquals('Hello John Doe from Test Company!', $result);
    }

    public function test_generate_preview_personalizes_content()
    {
        $user = User::factory()->create();
        $campaign = EmailCampaign::factory()->create([
            'subject' => 'Hello {{first_name}}!',
            'content' => 'Dear {{first_name}}, welcome to {{company}}!',
            'created_by' => $user->id,
        ]);

        $contact = Contact::factory()->create([
            'first_name' => 'Jane',
            'company' => 'Awesome Corp',
        ]);

        $preview = $this->emailService->generatePreview($campaign, $contact);

        $this->assertEquals('Hello Jane!', $preview['subject']);
        $this->assertStringContains('Dear Jane, welcome to Awesome Corp!', $preview['content']);
    }

    public function test_duplicate_campaign_copies_all_data()
    {
        $user = User::factory()->create();
        $template = EmailTemplate::factory()->create();
        $smtpConfig = SmtpConfig::factory()->create();

        $originalCampaign = EmailCampaign::factory()->create([
            'name' => 'Original Campaign',
            'subject' => 'Original Subject',
            'content' => 'Original Content',
            'template_id' => $template->id,
            'smtp_config_id' => $smtpConfig->id,
            'settings' => ['track_opens' => true],
            'created_by' => $user->id,
        ]);

        $contacts = Contact::factory(2)->create();
        $originalCampaign->contacts()->attach($contacts->pluck('id'));

        $this->actingAs($user);

        $duplicatedCampaign = $this->emailService->duplicateCampaign($originalCampaign);

        $this->assertNotEquals($originalCampaign->id, $duplicatedCampaign->id);
        $this->assertEquals('Original Campaign - Copy', $duplicatedCampaign->name);
        $this->assertEquals($originalCampaign->subject, $duplicatedCampaign->subject);
        $this->assertEquals($originalCampaign->content, $duplicatedCampaign->content);
        $this->assertEquals($originalCampaign->template_id, $duplicatedCampaign->template_id);
        $this->assertEquals($originalCampaign->smtp_config_id, $duplicatedCampaign->smtp_config_id);
        $this->assertEquals('draft', $duplicatedCampaign->status);
        $this->assertEquals(2, $duplicatedCampaign->contacts()->count());
    }

    public function test_get_campaign_stats_calculates_rates_correctly()
    {
        $campaign = EmailCampaign::factory()->create([
            'total_recipients' => 1000,
            'sent_count' => 950,
            'delivered_count' => 900,
            'opened_count' => 360,
            'clicked_count' => 72,
            'bounced_count' => 30,
            'failed_count' => 20,
        ]);

        $stats = $this->emailService->getCampaignStats($campaign);

        $this->assertEquals(1000, $stats['total_recipients']);
        $this->assertEquals(950, $stats['sent_count']);
        $this->assertEquals(360, $stats['opened_count']);
        $this->assertEquals(72, $stats['clicked_count']);

        // Test calculated rates
        $this->assertEquals(37.89, $stats['open_rate']); // 360/950 * 100
        $this->assertEquals(7.58, $stats['click_rate']); // 72/950 * 100
        $this->assertEquals(3.16, $stats['bounce_rate']); // 30/950 * 100
    }

    public function test_send_campaign_validates_status()
    {
        $user = User::factory()->create();
        $campaign = EmailCampaign::factory()->sent()->create(['created_by' => $user->id]);

        $result = $this->emailService->sendCampaign($campaign);

        $this->assertFalse($result['success']);
        $this->assertStringContains('cannot be sent from current status', $result['message']);
    }

    public function test_send_campaign_checks_smtp_config()
    {
        $user = User::factory()->create();
        $inactiveSmtpConfig = SmtpConfig::factory()->create([
            'is_active' => false,
            'created_by' => $user->id,
        ]);

        $campaign = EmailCampaign::factory()->draft()->create([
            'smtp_config_id' => $inactiveSmtpConfig->id,
            'created_by' => $user->id,
        ]);

        $contacts = Contact::factory(2)->create();
        $campaign->contacts()->attach($contacts->pluck('id'), ['status' => 'pending']);

        $result = $this->emailService->sendCampaign($campaign);

        $this->assertFalse($result['success']);
        $this->assertStringContains('SMTP configuration is not active', $result['message']);
    }

    public function test_pause_campaign_changes_status()
    {
        $user = User::factory()->create();
        $campaign = EmailCampaign::factory()->sending()->create(['created_by' => $user->id]);

        $result = $this->emailService->pauseCampaign($campaign);

        $this->assertTrue($result['success']);
        $this->assertEquals('paused', $campaign->fresh()->status);
    }

    public function test_resume_campaign_changes_status()
    {
        $user = User::factory()->create();
        $campaign = EmailCampaign::factory()->create([
            'status' => 'paused',
            'created_by' => $user->id,
        ]);

        $result = $this->emailService->resumeCampaign($campaign);

        $this->assertTrue($result['success']);
        $this->assertEquals('sending', $campaign->fresh()->status);
    }

    public function test_cancel_campaign_prevents_invalid_status()
    {
        $user = User::factory()->create();
        $campaign = EmailCampaign::factory()->sent()->create(['created_by' => $user->id]);

        $result = $this->emailService->cancelCampaign($campaign);

        $this->assertFalse($result['success']);
        $this->assertStringContains('cannot be cancelled from current status', $result['message']);
    }

    public function test_replace_variables_handles_missing_values()
    {
        $variables = [
            'first_name' => 'John',
            'last_name' => null,
            'company' => 'Test Corp',
        ];

        $content = 'Hello {{first_name}} {{last_name}} from {{company}} and {{missing_var}}!';

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->emailService);
        $method = $reflection->getMethod('replaceVariables');
        $method->setAccessible(true);

        $result = $method->invoke($this->emailService, $content, $variables);

        $this->assertEquals('Hello John  from Test Corp and {{missing_var}}!', $result);
    }

    public function test_add_tracking_elements_includes_pixel_and_unsubscribe()
    {
        $content = '<html><body><p>Test email content</p></body></html>';
        $trackingId = 'test-tracking-id';

        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->emailService);
        $method = $reflection->getMethod('addTrackingElements');
        $method->setAccessible(true);

        $result = $method->invoke($this->emailService, $content, $trackingId);

        $this->assertStringContains('track/open/'.$trackingId, $result);
        $this->assertStringContains('unsubscribe/'.$trackingId, $result);
        $this->assertStringContains('width="1" height="1"', $result); // tracking pixel
    }
}
