<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $company = fake()->company();
        $domain = strtolower(str_replace([' ', '\'', '.', '&', ','], ['', '', '', '', ''], $company)) . '.com';
        
        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName . '.' . $lastName . '@' . $domain),
            'phone' => '+1-' . fake()->numberBetween(200, 999) . '-' . fake()->numberBetween(100, 999) . '-' . fake()->numberBetween(1000, 9999),
            'whatsapp' => fake()->boolean(60) ? ('+1-' . fake()->numberBetween(200, 999) . '-' . fake()->numberBetween(100, 999) . '-' . fake()->numberBetween(1000, 9999)) : null,
            'company' => $company,
            'position' => fake()->jobTitle(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => fake()->randomElement([
                'USA', 'Canada', 'UK', 'Germany', 'France', 'Australia', 
                'Netherlands', 'Spain', 'Italy', 'Sweden', 'Norway'
            ]),
            'tags' => fake()->randomElements([
                'lead', 'prospect', 'customer', 'vip', 'newsletter-subscriber',
                'webinar-attendee', 'trial-user', 'enterprise', 'small-business',
                'startup', 'agency', 'consultant', 'referral-source', 'cold-lead',
                'warm-lead', 'qualified', 'decision-maker', 'influencer'
            ], fake()->numberBetween(1, 4)),
            'notes' => fake()->boolean(70) ? fake()->paragraph(2) : null,
            'custom_fields' => [
                'company_size' => fake()->randomElement([
                    '1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'
                ]),
                'industry' => fake()->randomElement([
                    'Technology', 'Healthcare', 'Finance', 'Education', 'Manufacturing',
                    'Retail', 'Real Estate', 'Marketing', 'Consulting', 'E-commerce',
                    'Non-profit', 'Government', 'Transportation', 'Energy', 'Media'
                ]),
                'budget' => fake()->randomElement([
                    'Under $1,000', '$1,000-5,000', '$5,000-10,000', '$10,000-25,000',
                    '$25,000-50,000', '$50,000-100,000', '$100,000+', 'Not specified'
                ]),
                'interest_level' => fake()->randomElement(['low', 'medium', 'high']),
                'lead_score' => fake()->numberBetween(0, 100),
                'preferred_contact_method' => fake()->randomElement(['email', 'phone', 'whatsapp', 'no_preference']),
                'timezone' => fake()->randomElement([
                    'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
                    'Europe/London', 'Europe/Paris', 'Europe/Berlin', 'Asia/Tokyo', 'Australia/Sydney'
                ]),
                'language' => fake()->randomElement(['en', 'es', 'fr', 'de', 'it', 'pt', 'nl']),
                'acquisition_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'last_interaction' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
            ],
            'status' => fake()->randomElement(['active', 'inactive', 'prospect', 'customer']),
            'source' => fake()->randomElement([
                'website', 'referral', 'google-ads', 'facebook-ads', 'linkedin',
                'twitter', 'instagram', 'conference', 'webinar', 'cold-outreach',
                'partner', 'organic', 'direct', 'email-campaign', 'content-marketing'
            ]),
            'created_by' => User::factory(),
            'assigned_to' => fake()->boolean(60) ? User::factory() : null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }

    /**
     * Indicate that the contact is a VIP customer.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => array_merge($attributes['tags'] ?? [], ['vip', 'customer']),
            'status' => 'customer',
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'budget' => fake()->randomElement(['$25,000-50,000', '$50,000-100,000', '$100,000+']),
                'interest_level' => 'high',
                'lead_score' => fake()->numberBetween(80, 100),
            ]),
        ]);
    }

    /**
     * Indicate that the contact is from a technology company.
     */
    public function tech(): static
    {
        return $this->state(fn (array $attributes) => [
            'company' => fake()->randomElement([
                'TechCorp Solutions', 'InnovaTech Inc', 'Digital Dynamics', 'CodeCraft LLC',
                'DataStream Technologies', 'CloudFirst Systems', 'AI Solutions Group',
                'CyberTech Innovations', 'NextGen Software', 'SmartTech Partners'
            ]),
            'position' => fake()->randomElement([
                'CTO', 'VP of Engineering', 'Software Engineer', 'Product Manager',
                'DevOps Engineer', 'Data Scientist', 'Tech Lead', 'System Administrator'
            ]),
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'industry' => 'Technology',
                'budget' => fake()->randomElement(['$10,000-25,000', '$25,000-50,000', '$50,000-100,000']),
            ]),
            'tags' => array_merge($attributes['tags'] ?? [], ['tech-lead', 'saas-user']),
        ]);
    }

    /**
     * Indicate that the contact is a small business owner.
     */
    public function smallBusiness(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => fake()->randomElement([
                'Owner', 'Founder', 'CEO', 'Managing Director', 'President'
            ]),
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'company_size' => fake()->randomElement(['1-10', '11-50']),
                'budget' => fake()->randomElement(['Under $1,000', '$1,000-5,000', '$5,000-10,000']),
            ]),
            'tags' => array_merge($attributes['tags'] ?? [], ['small-business', 'owner']),
        ]);
    }

    /**
     * Indicate that the contact is an enterprise client.
     */
    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => fake()->randomElement([
                'VP of Sales', 'Director of Marketing', 'Chief Marketing Officer',
                'VP of Operations', 'Enterprise Account Manager', 'Strategic Partner Manager'
            ]),
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'company_size' => fake()->randomElement(['501-1000', '1000+']),
                'budget' => fake()->randomElement(['$50,000-100,000', '$100,000+']),
                'interest_level' => 'high',
            ]),
            'tags' => array_merge($attributes['tags'] ?? [], ['enterprise', 'decision-maker']),
        ]);
    }

    /**
     * Indicate that the contact is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'last_interaction' => fake()->dateTimeBetween('-1 year', '-6 months')->format('Y-m-d H:i:s'),
            ]),
            'updated_at' => fake()->dateTimeBetween('-6 months', '-3 months'),
        ]);
    }

    /**
     * Indicate that the contact is a newsletter subscriber.
     */
    public function newsletterSubscriber(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => array_merge($attributes['tags'] ?? [], ['newsletter-subscriber']),
            'source' => fake()->randomElement(['website', 'content-marketing', 'organic']),
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'preferred_contact_method' => 'email',
            ]),
        ]);
    }

    /**
     * Create a contact from a specific country.
     */
    public function fromCountry(string $country): static
    {
        $timezoneMap = [
            'USA' => ['America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles'],
            'UK' => ['Europe/London'],
            'Germany' => ['Europe/Berlin'],
            'France' => ['Europe/Paris'],
            'Canada' => ['America/Toronto', 'America/Vancouver'],
            'Australia' => ['Australia/Sydney', 'Australia/Melbourne'],
        ];

        return $this->state(fn (array $attributes) => [
            'country' => $country,
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'timezone' => fake()->randomElement($timezoneMap[$country] ?? ['UTC']),
            ]),
        ]);
    }

    /**
     * Create a contact with a specific lead score range.
     */
    public function withLeadScore(int $min = 0, int $max = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'lead_score' => fake()->numberBetween($min, $max),
            ]),
        ]);
    }

    /**
     * Create a contact with recent activity.
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'custom_fields' => array_merge($attributes['custom_fields'] ?? [], [
                'last_interaction' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s'),
            ]),
            'updated_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
