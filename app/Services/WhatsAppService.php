<?php

namespace App\Services;

use App\Models\Communication;
use App\Models\Contact;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $serverUrl;

    protected $serverToken;

    protected $timeout;

    public function __construct()
    {
        $this->serverUrl = config('services.whatsapp.server_url', 'http://localhost:3001');
        $this->serverToken = config('services.whatsapp.api_token', '');
        $this->timeout = config('services.whatsapp.timeout', 30);
    }

    /**
     * Create a new WhatsApp session
     */
    public function createSession(array $data)
    {
        try {
            $sessionId = $data['session_name'] ?? uniqid('whatsapp_');

            // Create session in our database
            $session = WhatsAppSession::create([
                'session_name' => $sessionId,
                'api_endpoint' => $this->serverUrl,
                'webhook_url' => config('app.url').'/api/whatsapp/webhook',
                'settings' => $data['settings'] ?? [],
                'status' => 'initializing',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            // Create session in WhatsApp server
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->post($this->serverUrl.'/sessions', [
                    'sessionId' => $sessionId,
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp session created successfully: {$sessionId}");

                return [
                    'success' => true,
                    'session' => $session,
                    'message' => 'Session created successfully',
                ];
            } else {
                $session->delete();

                Log::error('Failed to create WhatsApp session on server', [
                    'session_id' => $sessionId,
                    'error' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to create session on WhatsApp server: '.$response->body(),
                ];
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp create session error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Error creating session: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get QR Code for session authentication
     */
    public function getQRCode($sessionId = null)
    {
        try {
            if (! $sessionId) {
                $session = WhatsAppSession::where('is_active', true)->first();
                if (! $session) {
                    return ['success' => false, 'error' => 'No active session found'];
                }
                $sessionId = $session->session_name;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($this->serverUrl.'/sessions/'.$sessionId);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['qrCode'])) {
                    // Cache QR code for 5 minutes
                    Cache::put("whatsapp_qr_{$sessionId}", $data['qrCode'], 300);

                    return [
                        'success' => true,
                        'qr_code' => $data['qrCode'],
                        'session_status' => $data['state']['status'] ?? 'qr_code',
                    ];
                }

                return [
                    'success' => true,
                    'session_status' => $data['state']['status'] ?? 'unknown',
                    'is_ready' => $data['isReady'] ?? false,
                ];
            }

            return [
                'success' => false,
                'error' => 'Could not get session status from server',
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp get QR error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error getting QR code: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage($to, $message, $type = 'text', $media = null)
    {
        try {
            $session = WhatsAppSession::where('is_active', true)->first();

            if (! $session) {
                return [
                    'success' => false,
                    'error' => 'No active WhatsApp session found',
                ];
            }

            // Format phone number for WhatsApp
            $formattedTo = $this->formatPhoneNumber($to);

            $payload = [
                'to' => $formattedTo,
                'message' => $message,
            ];

            if ($media && $type !== 'text') {
                $payload['media'] = $media;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->post($this->serverUrl.'/sessions/'.$session->session_name.'/send', $payload);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('WhatsApp message sent successfully', [
                    'session_id' => $session->session_name,
                    'to' => $formattedTo,
                    'message_length' => strlen($message),
                ]);

                return [
                    'success' => true,
                    'message_id' => $data['result']['messageId'] ?? null,
                    'timestamp' => $data['result']['timestamp'] ?? now(),
                ];
            }

            Log::error('Failed to send WhatsApp message', [
                'session_id' => $session->session_name,
                'to' => $formattedTo,
                'error' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send message: '.$response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp send message error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error sending message: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Send bulk WhatsApp messages
     */
    public function sendBulkMessages(array $messages)
    {
        try {
            $session = WhatsAppSession::where('is_active', true)->first();

            if (! $session) {
                return [
                    'success' => false,
                    'error' => 'No active WhatsApp session found',
                ];
            }

            // Format messages for bulk sending
            $formattedMessages = [];
            foreach ($messages as $msg) {
                $formattedMessages[] = [
                    'to' => $this->formatPhoneNumber($msg['to']),
                    'message' => $msg['message'],
                    'media' => $msg['media'] ?? null,
                ];
            }

            $response = Http::timeout(120) // Increased timeout for bulk operations
                ->withHeaders($this->getHeaders())
                ->post($this->serverUrl.'/sessions/'.$session->session_name.'/send-bulk', [
                    'messages' => $formattedMessages,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('WhatsApp bulk messages sent', [
                    'session_id' => $session->session_name,
                    'total_messages' => count($formattedMessages),
                    'results' => $data['results'] ?? [],
                ]);

                return [
                    'success' => true,
                    'results' => $data['results'] ?? [],
                ];
            }

            Log::error('Failed to send WhatsApp bulk messages', [
                'session_id' => $session->session_name,
                'error' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send bulk messages: '.$response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp send bulk messages error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error sending bulk messages: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get WhatsApp chats
     */
    public function getChats($sessionId = null)
    {
        try {
            if (! $sessionId) {
                $session = WhatsAppSession::where('is_active', true)->first();
                if (! $session) {
                    return ['success' => false, 'error' => 'No active session found'];
                }
                $sessionId = $session->session_name;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($this->serverUrl.'/sessions/'.$sessionId.'/chats');

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'chats' => $data['chats'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => 'Could not get chats from server',
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp get chats error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error getting chats: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get WhatsApp contacts
     */
    public function getContacts($sessionId = null)
    {
        try {
            if (! $sessionId) {
                $session = WhatsAppSession::where('is_active', true)->first();
                if (! $session) {
                    return ['success' => false, 'error' => 'No active session found'];
                }
                $sessionId = $session->session_name;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($this->serverUrl.'/sessions/'.$sessionId.'/contacts');

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'contacts' => $data['contacts'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => 'Could not get contacts from server',
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp get contacts error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error getting contacts: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get session status
     */
    public function getSessionStatus($sessionId = null)
    {
        try {
            if (! $sessionId) {
                $session = WhatsAppSession::where('is_active', true)->first();
                if (! $session) {
                    return ['success' => false, 'error' => 'No active session found'];
                }
                $sessionId = $session->session_name;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($this->serverUrl.'/sessions/'.$sessionId);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'status' => $data['state']['status'] ?? 'unknown',
                    'info' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => 'Could not get session status from server',
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp get session status error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error getting session status: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Disconnect WhatsApp session
     */
    public function disconnect($sessionId = null)
    {
        try {
            if (! $sessionId) {
                $session = WhatsAppSession::where('is_active', true)->first();
                if (! $session) {
                    return ['success' => false, 'error' => 'No active session found'];
                }
                $sessionId = $session->session_name;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->delete($this->serverUrl.'/sessions/'.$sessionId);

            if ($response->successful()) {
                // Update session in database
                WhatsAppSession::where('session_name', $sessionId)->update([
                    'is_active' => false,
                    'status' => 'disconnected',
                    'disconnected_at' => now(),
                ]);

                Log::info("WhatsApp session disconnected: {$sessionId}");

                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => 'Failed to disconnect session: '.$response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp disconnect error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error disconnecting session: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook from WhatsApp server
     */
    public function handleWebhook(array $data)
    {
        try {
            $event = $data['event'] ?? null;
            $sessionId = $data['session_id'] ?? null;
            $webhookData = $data['data'] ?? [];

            if (! $event || ! $sessionId) {
                Log::warning('Invalid webhook data received', $data);

                return ['success' => false, 'message' => 'Invalid webhook data'];
            }

            $session = WhatsAppSession::where('session_name', $sessionId)->first();

            if (! $session) {
                Log::warning("WhatsApp webhook received for unknown session: {$sessionId}");

                return ['success' => false, 'message' => 'Session not found'];
            }

            switch ($event) {
                case 'qr_generated':
                    $this->handleQRGenerated($session, $webhookData);
                    break;

                case 'ready':
                    $this->handleSessionReady($session, $webhookData);
                    break;

                case 'disconnected':
                    $this->handleSessionDisconnected($session, $webhookData);
                    break;

                case 'message_received':
                    $this->handleIncomingMessage($session, $webhookData);
                    break;

                case 'message_sent':
                    $this->handleOutgoingMessage($session, $webhookData);
                    break;

                case 'error':
                    $this->handleSessionError($session, $webhookData);
                    break;

                default:
                    Log::info("Unknown WhatsApp webhook event: {$event}", $data);
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error: '.$e->getMessage(), $data);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle QR code generated event
     */
    protected function handleQRGenerated(WhatsAppSession $session, array $data)
    {
        $qrCode = $data['qr_code'] ?? null;

        if ($qrCode) {
            $session->update([
                'status' => 'qr_code',
                'qr_code' => $qrCode,
                'last_activity_at' => now(),
            ]);

            // Cache QR code
            Cache::put("whatsapp_qr_{$session->session_name}", $qrCode, 300);

            Log::info("QR code generated for session: {$session->session_name}");

            // Broadcast QR code to frontend if needed
            // broadcast(new \App\Events\WhatsAppQRGenerated($session, $qrCode));
        }
    }

    /**
     * Handle session ready event
     */
    protected function handleSessionReady(WhatsAppSession $session, array $data)
    {
        $session->update([
            'status' => 'connected',
            'is_active' => true,
            'phone_number' => $data['phone_number'] ?? null,
            'connected_at' => now(),
            'last_activity_at' => now(),
            'qr_code' => null,
        ]);

        Log::info("WhatsApp session ready: {$session->session_name}");

        // Clear cached QR code
        Cache::forget("whatsapp_qr_{$session->session_name}");

        // Broadcast session ready event
        // broadcast(new \App\Events\WhatsAppSessionReady($session));
    }

    /**
     * Handle session disconnected event
     */
    protected function handleSessionDisconnected(WhatsAppSession $session, array $data)
    {
        $reason = $data['reason'] ?? 'Unknown';

        $session->update([
            'status' => 'disconnected',
            'is_active' => false,
            'disconnected_at' => now(),
            'last_activity_at' => now(),
            'disconnect_reason' => $reason,
        ]);

        Log::warning("WhatsApp session disconnected: {$session->session_name}, reason: {$reason}");

        // Broadcast session disconnected event
        // broadcast(new \App\Events\WhatsAppSessionDisconnected($session, $reason));
    }

    /**
     * Handle incoming message
     */
    protected function handleIncomingMessage(WhatsAppSession $session, array $data)
    {
        $from = $data['from'] ?? null;
        $message = $data['body'] ?? '';
        $messageType = $data['type'] ?? 'text';
        $messageId = $data['id'] ?? null;
        $timestamp = $data['timestamp'] ?? null;
        $hasMedia = $data['hasMedia'] ?? false;
        $mediaData = $data['media'] ?? null;

        if (! $from) {
            Log::warning('Received WhatsApp message without sender information');

            return;
        }

        // Update session activity
        $session->update(['last_activity_at' => now()]);

        // Find or create contact
        $contact = $this->findOrCreateContact($from);

        // Handle media
        $mediaUrl = null;
        $mediaType = null;

        if ($hasMedia && $mediaData) {
            $mediaType = $mediaData['mimetype'] ?? null;
            // Store media data if needed - you can implement media storage here
            // $mediaUrl = $this->storeMedia($mediaData, $contact->id);
        }

        // Create WhatsApp message record
        $whatsappMessage = WhatsAppMessage::create([
            'whats_app_session_id' => $session->id,
            'contact_id' => $contact->id,
            'phone_number' => $this->cleanPhoneNumber($from),
            'message' => $message,
            'message_type' => $messageType,
            'media_url' => $mediaUrl,
            'direction' => 'inbound',
            'status' => 'received',
            'whatsapp_message_id' => $messageId,
            'received_at' => $timestamp ? Carbon::createFromTimestamp($timestamp) : now(),
            'metadata' => $data,
        ]);

        // Create communication record
        $this->createCommunication($contact, $message, 'inbound', 'whatsapp', $whatsappMessage->id);

        // Update contact last contact date
        $contact->update(['last_contact_at' => now()]);

        Log::info("WhatsApp message received from {$from}", [
            'session_id' => $session->session_name,
            'contact_id' => $contact->id,
            'message_length' => strlen($message),
            'has_media' => $hasMedia,
        ]);

        // Broadcast message received event
        // broadcast(new \App\Events\WhatsAppMessageReceived($whatsappMessage));
    }

    /**
     * Handle outgoing message confirmation
     */
    protected function handleOutgoingMessage(WhatsAppSession $session, array $data)
    {
        $messageId = $data['id'] ?? null;
        $to = $data['to'] ?? null;
        $timestamp = $data['timestamp'] ?? null;

        if (! $messageId) {
            return;
        }

        // Update session activity
        $session->update(['last_activity_at' => now()]);

        // Find message by WhatsApp message ID or create if not exists
        $message = WhatsAppMessage::where('whatsapp_message_id', $messageId)->first();

        if ($message) {
            $message->update([
                'status' => 'sent',
                'sent_at' => $timestamp ? Carbon::createFromTimestamp($timestamp) : now(),
            ]);

            Log::info("WhatsApp message sent confirmation: {$messageId}");
        }
    }

    /**
     * Handle session error
     */
    protected function handleSessionError(WhatsAppSession $session, array $data)
    {
        $error = $data['error'] ?? 'Unknown error';

        $session->update([
            'status' => 'error',
            'last_error' => $error,
            'last_activity_at' => now(),
        ]);

        Log::error("WhatsApp session error: {$session->session_name}, error: {$error}");

        // Broadcast session error event
        // broadcast(new \App\Events\WhatsAppSessionError($session, $error));
    }

    /**
     * Find or create contact from phone number
     */
    protected function findOrCreateContact(string $phoneNumber)
    {
        $cleanPhone = $this->cleanPhoneNumber($phoneNumber);

        // Try to find existing contact
        $contact = Contact::where('phone', $cleanPhone)
            ->orWhere('whatsapp_number', $cleanPhone)
            ->first();

        if (! $contact) {
            // Extract name from WhatsApp ID if possible
            $name = 'WhatsApp Contact';
            if (strpos($phoneNumber, '@') !== false) {
                $parts = explode('@', $phoneNumber);
                $phone = $parts[0];
            } else {
                $phone = $cleanPhone;
            }

            $contact = Contact::create([
                'first_name' => $name,
                'phone' => $phone,
                'whatsapp_number' => $phone,
                'source' => 'whatsapp',
                'status' => 'active',
                'created_by' => 1, // System user
            ]);

            Log::info("New contact created from WhatsApp: {$phone}", [
                'contact_id' => $contact->id,
            ]);
        } else {
            // Update WhatsApp number if not set
            if (! $contact->whatsapp_number) {
                $contact->update(['whatsapp_number' => $cleanPhone]);
            }
        }

        return $contact;
    }

    /**
     * Create communication record
     */
    protected function createCommunication(Contact $contact, string $content, string $direction, string $type, ?int $externalId = null)
    {
        return Communication::create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id() ?? 1,
            'type' => $type,
            'direction' => $direction,
            'content' => $content,
            'status' => 'delivered',
            'external_id' => $externalId,
            'sent_at' => now(),
            'delivered_at' => $direction === 'inbound' ? now() : null,
        ]);
    }

    /**
     * Clean phone number (remove non-numeric characters)
     */
    protected function cleanPhoneNumber(string $phoneNumber)
    {
        // Remove @c.us suffix and other WhatsApp suffixes
        $phoneNumber = preg_replace('/@.*$/', '', $phoneNumber);

        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // Remove leading + if present
        return ltrim($cleaned, '+');
    }

    /**
     * Get headers for API requests
     */
    protected function getHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($this->serverToken) {
            $headers['Authorization'] = 'Bearer '.$this->serverToken;
        }

        return $headers;
    }

    /**
     * Get server health status
     */
    public function getServerHealth()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($this->serverUrl.'/health');

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'unknown',
                    'uptime' => $data['uptime'] ?? 0,
                    'sessions' => $data['sessions'] ?? 0,
                    'timestamp' => $data['timestamp'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => 'WhatsApp server not responding',
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp server health check failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'WhatsApp server health check failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get all sessions from server
     */
    public function getAllSessions()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->getHeaders())
                ->get($this->serverUrl.'/sessions');

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'sessions' => $data['sessions'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => 'Could not get sessions from server',
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp get all sessions error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error getting sessions: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Upload media file to WhatsApp server
     */
    public function uploadMedia($file)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Authorization' => 'Bearer '.$this->serverToken])
                ->attach('media', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($this->serverUrl.'/upload');

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'filename' => $data['filename'] ?? null,
                    'path' => $data['path'] ?? null,
                    'url' => $this->serverUrl.'/uploads/'.($data['filename'] ?? ''),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to upload media: '.$response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp upload media error: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error uploading media: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Validate webhook signature (if webhook secret is configured)
     */
    public function validateWebhookSignature(array $data, ?string $signature = null)
    {
        $webhookSecret = config('services.whatsapp.webhook_secret');

        if (! $webhookSecret || ! $signature) {
            return true; // Skip validation if not configured
        }

        $expectedSignature = hash_hmac('sha256', json_encode($data), $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Send a quick WhatsApp message to a single contact
     */
    public function sendQuickMessage(Contact $contact, string $message)
    {
        try {
            // Validate contact has phone number
            if (empty($contact->phone)) {
                return [
                    'success' => false,
                    'message' => 'Contact does not have a phone number'
                ];
            }

            // Get the first active WhatsApp session
            $session = WhatsAppSession::where('is_active', true)
                ->where('status', 'active')
                ->first();

            if (!$session) {
                return [
                    'success' => false,
                    'message' => 'No active WhatsApp session available'
                ];
            }

            // Format phone number (ensure it starts with country code)
            $phoneNumber = $this->formatPhoneNumber($contact->phone);

            // Send message using the existing sendMessage method
            $result = $this->sendMessage($session, $phoneNumber, $message);

            if ($result['success']) {
                // Create communication record
                \App\Models\Communication::create([
                    'contact_id' => $contact->id,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'whatsapp',
                    'direction' => 'outbound',
                    'content' => $message,
                    'status' => 'sent',
                    'external_id' => $result['message_id'] ?? null,
                    'sent_at' => now(),
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Quick WhatsApp Send Error: ' . $e->getMessage(), [
                'contact_id' => $contact->id,
                'contact_phone' => $contact->phone,
                'message' => $message,
                'error_trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send WhatsApp message: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber(string $phone)
    {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If phone doesn't start with country code, add default (40 for Romania)
        if (!str_starts_with($phone, '40') && !str_starts_with($phone, '+40')) {
            $phone = '40' . ltrim($phone, '0');
        }

        return $phone;
    }
}
