<?php

namespace App\Providers;

use App\Events\CampaignSent;
use App\Events\ContactCreated;
use App\Events\ContactUpdated;
use App\Events\DataImportCompleted;
// Import CRM Events
use App\Events\EmailClicked;
use App\Events\EmailOpened;
use App\Events\SmsDelivered;
use App\Events\WhatsAppMessageReceived;
use App\Listeners\LogCommunication;
use App\Listeners\NotifyUserImportComplete;
use App\Listeners\RefreshContactSegments;
use App\Listeners\SendWelcomeEmail;
// Import CRM Listeners
use App\Listeners\UpdateContactActivity;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Laravel Default Events
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // WhatsApp Events
        WhatsAppMessageReceived::class => [
            UpdateContactActivity::class,
            LogCommunication::class,
        ],

        // Email Events
        EmailOpened::class => [
            UpdateContactActivity::class,
            LogCommunication::class,
        ],

        EmailClicked::class => [
            UpdateContactActivity::class,
            LogCommunication::class,
        ],

        // Contact Events
        ContactCreated::class => [
            SendWelcomeEmail::class,
            LogCommunication::class,
            RefreshContactSegments::class,
        ],

        ContactUpdated::class => [
            LogCommunication::class,
            RefreshContactSegments::class,
        ],

        // Campaign Events
        CampaignSent::class => [
            // Add campaign-specific listeners here if needed
        ],

        // SMS Events
        SmsDelivered::class => [
            UpdateContactActivity::class,
            LogCommunication::class,
        ],

        // Import Events
        DataImportCompleted::class => [
            NotifyUserImportComplete::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register custom event listeners
        $this->registerCustomEventListeners();
    }

    /**
     * Register custom event listeners for CRM functionality.
     */
    protected function registerCustomEventListeners(): void
    {
        // Model observers for automatic event firing
        Event::listen('eloquent.created: App\Models\Contact', function ($contact) {
            event(new ContactCreated($contact, request('source'), request()->all()));
        });

        Event::listen('eloquent.updated: App\Models\Contact', function ($contact) {
            event(new ContactUpdated($contact, $contact->getChanges(), $contact->getOriginal()));
        });

        // Global event listeners for debugging (only in development)
        if (app()->environment(['local', 'development'])) {
            Event::listen('*', function ($eventName, array $data) {
                if (str_starts_with($eventName, 'App\\Events\\')) {
                    \Log::debug('CRM Event Fired', [
                        'event' => $eventName,
                        'data' => array_map(function ($item) {
                            return is_object($item) ? get_class($item) : gettype($item);
                        }, $data),
                    ]);
                }
            });
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
