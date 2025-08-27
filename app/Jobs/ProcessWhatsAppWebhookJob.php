<?php

namespace App\Jobs;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSession;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessWhatsAppWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job should run.
     */
    public $timeout = 60;

    /**
     * The webhook payload.
     */
    protected array $payload;

    /**
     * The webhook type.
     */
    protected string $webhookType;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload, string $webhookType = 'message')
    {
        $this->payload = $payload;
        $this->webhookType = $webhookType;
        $this->onQueue('whatsapp-webhooks');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Processing WhatsApp webhook", [
                'type' => $this->webhookType,
                'payload_keys' => array_keys($this->payload)
            ]);

            switch ($this->webhookType) {
                case 'message':
                    $this->processMessageWebhook();
                    break;

                case 'status':
                    $this->processStatusWebhook();
                    break;

                case 'session_status':
                    $this->processSessionStatusWebhook();
                    break;

                case 'qr_code':
                    $this->processQrCodeWebhook();
                    break;

                default:
                    Log::warning("Unknown WhatsApp webhook type: {$this->webhookType}");
                    break;
            }

        } catch (Exception $e) {
            Log::error("ProcessWhatsAppWebhookJob failed: " . $e->getMessage(), [
                'webhook_type' => $this->webhookType,
                'payload' => $this->payload
            ]);
            throw $e;
        }
    }

    /**
     * Process incoming message webhook.
     */
    protected function processMessageWebhook(): void
    {
        $sessionId = $this->payload['session_id'] ?? null;
        $from = $this->payload['from'] ?? null;
        $messageBody = $this->payload['body'] ?? '';
        $messageId = $this->payload['id'] ?? null;
        $timestamp = $this->payload['timestamp'] ?? time();
        $messageType = $this->payload['type'] ?? 'text';

        if (!$sessionId || !$from || !$messageId) {
            Log::warning("Invalid message webhook payload - missing required fields");
            return;
        }

        // Find the WhatsApp session
        $session = WhatsAppSession::where('session_id', $sessionId)->first();
        if (!$session) {
            Log::warning("WhatsApp session not found: {$sessionId}");
            return;
        }

        // Clean phone number (remove WhatsApp suffix if present)
        $cleanPhone = str_replace('@c.us', '', $from);

        // Find or create contact
        $contact = $this->findOrCreateContact($cleanPhone);

        // Check if message already exists
        $existingMessage = WhatsAppMessage::where('message_id', $messageId)->first();
        if ($existingMessage) {
            Log::debug("WhatsApp message {$messageId} already exists, skipping");
            return;
        }

        // Create WhatsApp message record
        $message = WhatsAppMessage::create([
            'session_id' => $session->id,
            'contact_id' => $contact ? $contact->id : null,
            'message_id' => $messageId,
            'phone_number' => $cleanPhone,
            'direction' => 'inbound',
            'type' => $messageType,
            'content' => $messageBody,
            'status' => 'received',
            'timestamp' => date('Y-m-d H:i:s', $timestamp),
            'raw_payload' => $this->payload,
        ]);

        // Update contact last contacted
        if ($contact) {
            $contact->update(['last_contacted_at' => now()]);
        }

        // Update session statistics
        $session->increment('messages_received');
        $session->update(['last_activity_at' => now()]);

        Log::info("Processed inbound WhatsApp message {$messageId} from {$cleanPhone}");
    }

    /**
     * Process message status webhook (sent, delivered, read, failed).
     */
    protected function processStatusWebhook(): void
    {
        $messageId = $this->payload['id'] ?? null;
        $status = $this->payload['status'] ?? null;
        $timestamp = $this->payload['timestamp'] ?? time();

        if (!$messageId || !$status) {
            Log::warning("Invalid status webhook payload - missing required fields");
            return;
        }

        // Find the message
        $message = WhatsAppMessage::where('message_id', $messageId)->first();
        if (!$message) {
            Log::warning("WhatsApp message not found for status update: {$messageId}");
            return;
        }

        // Update message status
        $statusField = null;
        switch ($status) {
            case 'sent':
                $statusField = 'sent_at';
                break;
            case 'delivered':
                $statusField = 'delivered_at';
                break;
            case 'read':
                $statusField = 'read_at';
                break;
            case 'failed':
                $message->update([
                    'status' => 'failed',
                    'error_message' => $this->payload['error'] ?? 'Message failed to send'
                ]);
                Log::info("WhatsApp message {$messageId} marked as failed");
                return;
        }

        if ($statusField) {
            $message->update([
                'status' => $status,
                $statusField => date('Y-m-d H:i:s', $timestamp)
            ]);

            Log::debug("WhatsApp message {$messageId} status updated to {$status}");
        }

        // Update session statistics based on status
        if ($message->session) {
            switch ($status) {
                case 'delivered':
                    $message->session->increment('messages_delivered');
                    break;
                case 'read':
                    $message->session->increment('messages_read');
                    break;
                case 'failed':
                    $message->session->increment('messages_failed');
                    break;
            }
        }
    }

    /**
     * Process session status webhook (connected, disconnected, etc.).
     */
    protected function processSessionStatusWebhook(): void
    {
        $sessionId = $this->payload['session_id'] ?? null;
        $status = $this->payload['status'] ?? null;
        $timestamp = $this->payload['timestamp'] ?? time();

        if (!$sessionId || !$status) {
            Log::warning("Invalid session status webhook payload - missing required fields");
            return;
        }

        // Find the session
        $session = WhatsAppSession::where('session_id', $sessionId)->first();
        if (!$session) {
            Log::warning("WhatsApp session not found: {$sessionId}");
            return;
        }

        // Update session status
        $previousStatus = $session->status;
        $session->update([
            'status' => $status,
            'last_activity_at' => date('Y-m-d H:i:s', $timestamp)
        ]);

        // Additional actions based on status change
        switch ($status) {
            case 'connected':
                if ($previousStatus !== 'connected') {
                    $session->update(['connected_at' => now()]);
                    Log::info("WhatsApp session {$sessionId} connected");
                }
                break;

            case 'disconnected':
                if ($previousStatus === 'connected') {
                    $session->update(['disconnected_at' => now()]);
                    Log::info("WhatsApp session {$sessionId} disconnected");
                }
                break;

            case 'expired':
                $session->update([
                    'qr_code' => null,
                    'disconnected_at' => now()
                ]);
                Log::info("WhatsApp session {$sessionId} expired");
                break;

            case 'error':
                $errorMessage = $this->payload['error'] ?? 'Unknown error';
                $session->update(['last_error' => $errorMessage]);
                Log::error("WhatsApp session {$sessionId} error: {$errorMessage}");
                break;
        }
    }

    /**
     * Process QR code webhook.
     */
    protected function processQrCodeWebhook(): void
    {
        $sessionId = $this->payload['session_id'] ?? null;
        $qrCode = $this->payload['qr_code'] ?? null;

        if (!$sessionId || !$qrCode) {
            Log::warning("Invalid QR code webhook payload - missing required fields");
            return;
        }

        // Find the session
        $session = WhatsAppSession::where('session_id', $sessionId)->first();
        if (!$session) {
            Log::warning("WhatsApp session not found: {$sessionId}");
            return;
        }

        // Update session with QR code
        $session->update([
            'qr_code' => $qrCode,
            'status' => 'waiting_for_scan',
            'qr_generated_at' => now()
        ]);

        Log::info("QR code generated for WhatsApp session {$sessionId}");
    }

    /**
     * Find or create contact by phone number.
     */
    protected function findOrCreateContact(string $phone): ?Contact
    {
        // Try to find existing contact by phone
        $contact = Contact::where('phone', $phone)->first();

        if (!$contact) {
            // Try to find by phone with different formatting
            $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
            $contact = Contact::where(function ($query) use ($cleanPhone, $phone) {
                $query->whereRaw("REGEXP_REPLACE(phone, '[^0-9+]', '') = ?", [$cleanPhone])
                      ->orWhere('phone', 'LIKE', "%{$phone}%");
            })->first();
        }

        // Create new contact if not found
        if (!$contact) {
            try {
                $contact = Contact::create([
                    'phone' => $phone,
                    'contact_source' => 'whatsapp',
                    'is_active' => true,
                    'is_unsubscribed' => false,
                ]);

                Log::info("Created new contact from WhatsApp: {$phone}");
            } catch (Exception $e) {
                Log::error("Failed to create contact from WhatsApp: " . $e->getMessage());
                return null;
            }
        }

        return $contact;
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("ProcessWhatsAppWebhookJob permanently failed: " . $exception->getMessage(), [
            'webhook_type' => $this->webhookType,
            'payload' => $this->payload
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // 30 seconds, 1 minute, 2 minutes
    }
}
