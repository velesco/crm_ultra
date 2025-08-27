<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public array $errorData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $errorData)
    {
        $this->errorData = $errorData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $error = $this->errorData['error'] ?? 'Unknown system error';
        $context = $this->errorData['context'] ?? 'No additional context';

        return (new MailMessage)
            ->subject('System Error Notification')
            ->error()
            ->greeting("Hello {$notifiable->name}!")
            ->line('A system error occurred that may have affected your recent activity.')
            ->line("**Error:** {$error}")
            ->line("**Context:** {$context}")
            ->line('Our technical team has been notified and is working to resolve this issue.')
            ->action('Contact Support', route('settings.index'))
            ->line('If you continue to experience issues, please contact our support team.');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'system_error',
            'title' => 'System Error',
            'message' => $this->errorData['error'] ?? 'A system error occurred',
            'data' => $this->errorData,
            'action_url' => route('settings.index'),
            'action_text' => 'View Settings',
            'icon' => 'exclamation-triangle',
            'color' => 'error',
        ];
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
}
