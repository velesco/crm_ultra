<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class DataImportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public array $importData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $importData)
    {
        $this->importData = $importData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Add mail channel if email notifications are enabled and user preferences allow
        if (config('crm.notifications.import_completion.email_enabled', true)) {
            $channels[] = 'mail';
        }
        
        // Add broadcast channel for real-time notifications
        if (config('crm.notifications.import_completion.broadcast_enabled', true)) {
            $channels[] = 'broadcast';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $results = $this->importData['results'];
        $hasErrors = $this->importData['has_errors'];
        $fileName = $this->importData['file_name'];
        $importType = $this->importData['import_type'];

        $subject = $hasErrors 
            ? "Data Import Completed with Errors - {$fileName}"
            : "Data Import Completed Successfully - {$fileName}";

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your {$importType} import has been completed.");

        if (!$hasErrors) {
            $mailMessage->line("✅ Import completed successfully!");
        } else {
            $mailMessage->line("⚠️ Import completed with some errors that need your attention.");
        }

        // Add statistics
        if (isset($results['total_rows'])) {
            $mailMessage->line("📊 **Import Statistics:**");
            $mailMessage->line("• Total rows processed: {$results['total_rows']}");
            
            if (isset($results['created_contacts'])) {
                $mailMessage->line("• Contacts created: {$results['created_contacts']}");
            }
            
            if (isset($results['updated_contacts'])) {
                $mailMessage->line("• Contacts updated: {$results['updated_contacts']}");
            }
            
            if (isset($results['failed_rows']) && $results['failed_rows'] > 0) {
                $mailMessage->line("• Failed rows: {$results['failed_rows']}");
            }
        }

        $mailMessage->action('View Import Details', route('data.history'))
                    ->line('You can review the complete import results and any errors in your dashboard.');

        if ($hasErrors) {
            $mailMessage->line('Please check the import history for detailed error information and consider re-importing the failed records.');
        }

        return $mailMessage;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $results = $this->importData['results'];
        $hasErrors = $this->importData['has_errors'];
        $fileName = $this->importData['file_name'];
        $importType = $this->importData['import_type'];

        return [
            'type' => 'data_import_completed',
            'title' => $hasErrors ? 'Import Completed with Errors' : 'Import Completed Successfully',
            'message' => "Your {$importType} import '{$fileName}' has been completed" . 
                        ($hasErrors ? ' with some errors.' : ' successfully.'),
            'data' => [
                'import_type' => $importType,
                'file_name' => $fileName,
                'has_errors' => $hasErrors,
                'results' => $results,
                'completed_at' => $this->importData['completed_at'],
            ],
            'action_url' => route('data.history'),
            'action_text' => 'View Details',
            'icon' => $hasErrors ? 'exclamation-triangle' : 'check-circle',
            'color' => $hasErrors ? 'warning' : 'success',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Determine which queues should be used for each notification channel.
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications',
            'database' => 'notifications',
            'broadcast' => 'notifications',
        ];
    }

    /**
     * Determine the time at which the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        // Always send database notifications
        if ($channel === 'database') {
            return true;
        }

        // Check user preferences for email notifications
        if ($channel === 'mail') {
            return $notifiable->notification_preferences['import_completion_email'] ?? true;
        }

        // Check if broadcast notifications are enabled
        if ($channel === 'broadcast') {
            return $notifiable->notification_preferences['import_completion_browser'] ?? true;
        }

        return true;
    }
}
