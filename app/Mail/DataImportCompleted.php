<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataImportCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $hasErrors = $this->emailData['has_errors'] ?? false;
        $fileName = $this->emailData['file_name'] ?? 'Unknown file';

        $subject = $hasErrors
            ? "Import Completed with Errors - {$fileName}"
            : "Import Completed Successfully - {$fileName}";

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.data-import-completed',
            with: [
                'userName' => $this->emailData['user_name'],
                'fileName' => $this->emailData['file_name'],
                'importType' => $this->emailData['import_type'],
                'results' => $this->emailData['results'],
                'hasErrors' => $this->emailData['has_errors'],
                'dashboardUrl' => $this->emailData['dashboard_url'],
                'companyName' => config('app.name'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Set the queue for this mailable.
     */
    public function viaQueue(): string
    {
        return 'emails';
    }

    /**
     * Determine the time at which the job should be processed.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }
}
