<?php

namespace App\Listeners;

use App\Events\DataImportCompleted;
use App\Models\User;
use App\Notifications\DataImportCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyUserImportComplete implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(DataImportCompleted $event): void
    {
        try {
            $user = $event->user;
            $results = $event->results;
            $fileName = $event->fileName;
            $importType = $event->importType;
            $hasErrors = $event->hasErrors;

            // Create notification data
            $notificationData = [
                'import_type' => $importType,
                'file_name' => $fileName,
                'has_errors' => $hasErrors,
                'results' => $results,
                'completed_at' => now()->toISOString(),
            ];

            // Send notification to the user
            $user->notify(new DataImportCompletedNotification($notificationData));

            // If there are errors, also log them for admin attention
            if ($hasErrors && !empty($results['errors'])) {
                $this->logImportErrors($user, $fileName, $results['errors']);
            }

            // Send success/failure email if configured
            if (config('crm.notifications.import_completion.email_enabled', true)) {
                $this->sendImportCompletionEmail($user, $notificationData);
            }

            // Send browser notification if user is online
            if (config('crm.notifications.import_completion.browser_enabled', true)) {
                $this->sendBrowserNotification($user, $notificationData);
            }

            Log::info('Import completion notification sent successfully', [
                'user_id' => $user->id,
                'import_type' => $importType,
                'file_name' => $fileName,
                'has_errors' => $hasErrors,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send import completion notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id,
                'import_type' => $event->importType,
                'file_name' => $event->fileName,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Log import errors for admin review.
     */
    private function logImportErrors(User $user, string $fileName, array $errors): void
    {
        Log::warning('Data import completed with errors', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'file_name' => $fileName,
            'error_count' => count($errors),
            'errors' => array_slice($errors, 0, 10), // Log first 10 errors
        ]);

        // Store import errors in database for admin review
        try {
            \DB::table('import_error_logs')->insert([
                'user_id' => $user->id,
                'file_name' => $fileName,
                'error_count' => count($errors),
                'errors' => json_encode($errors),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store import errors in database', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Send import completion email.
     */
    private function sendImportCompletionEmail(User $user, array $notificationData): void
    {
        try {
            $subject = $notificationData['has_errors'] 
                ? 'Data Import Completed with Errors'
                : 'Data Import Completed Successfully';

            $emailData = [
                'user_name' => $user->name,
                'file_name' => $notificationData['file_name'],
                'import_type' => $notificationData['import_type'],
                'results' => $notificationData['results'],
                'has_errors' => $notificationData['has_errors'],
                'dashboard_url' => route('data.history'),
            ];

            // You can create a dedicated email template for this
            \Mail::to($user->email)->send(new \App\Mail\DataImportCompleted($emailData));

        } catch (\Exception $e) {
            Log::error('Failed to send import completion email', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);
        }
    }

    /**
     * Send browser notification.
     */
    private function sendBrowserNotification(User $user, array $notificationData): void
    {
        try {
            $title = $notificationData['has_errors'] 
                ? 'Import Completed with Errors'
                : 'Import Completed Successfully';
                
            $message = "File '{$notificationData['file_name']}' has been processed. ";
            
            if (isset($notificationData['results']['created_contacts'])) {
                $message .= "{$notificationData['results']['created_contacts']} contacts created.";
            }

            // Broadcast to user's private channel for real-time notification
            broadcast(new \App\Events\ImportNotificationEvent($user->id, [
                'title' => $title,
                'message' => $message,
                'type' => $notificationData['has_errors'] ? 'warning' : 'success',
                'data' => $notificationData,
                'action_url' => route('data.history'),
            ]));

        } catch (\Exception $e) {
            Log::error('Failed to send browser notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Determine the queue priority based on import results.
     */
    public function viaQueue(): string
    {
        return 'notifications';
    }

    /**
     * Handle a job failure.
     */
    public function failed(DataImportCompleted $event, $exception): void
    {
        Log::error('NotifyUserImportComplete listener failed', [
            'user_id' => $event->user->id,
            'import_type' => $event->importType,
            'file_name' => $event->fileName,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Try to send a basic notification that the notification system failed
        try {
            $event->user->notify(new \App\Notifications\SystemErrorNotification([
                'error' => 'Failed to process import completion notification',
                'context' => "Import: {$event->fileName}",
            ]));
        } catch (\Exception $e) {
            Log::critical('Failed to send error notification after NotifyUserImportComplete failure', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
