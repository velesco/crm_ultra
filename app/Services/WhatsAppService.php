<?php

namespace App\Services;

use App\Models\WhatsAppSession;
use App\Models\WhatsAppMessage;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function __construct()
    {
        //
    }

    public function createSession(array $data)
    {
        return WhatsAppSession::create([
            'session_name' => $data['session_name'],
            'api_endpoint' => $data['api_endpoint'],
            'api_key' => encrypt($data['api_key'] ?? null),
            'webhook_url' => $data['webhook_url'] ?? null,
            'settings' => $data['settings'] ?? [],
            'created_by' => auth()->id(),
        ]);
    }

    public function startSession(WhatsAppSession $session)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($session))
                ->post($session->api_endpoint . '/start-session', [
                    'session' => $session->session_name,
                    'webhook' => $session->webhook_url,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $session->updateStatus('connecting');
                
                return [
                    'success' => true,
                    'message' => 'Session started successfully',
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to start session: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp start session error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error starting session: ' . $e->getMessage()
            ];
        }
    }

    public function getQRCode(WhatsAppSession $session)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($session))
                ->get($session->api_endpoint . '/qr-code/' . $session->session_name);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['qr'])) {
                    $session->updateStatus('qr', $data['qr']);
                    
                    return [
                        'success' => true,
                        'qr_code' => $data['qr']
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'QR Code not available'
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp get QR error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error getting QR code: ' . $e->getMessage()
            ];
        }
    }

    public function sendMessage(WhatsAppSession $session, string $to, string $message, string $type = 'text', $media = null)
    {
        try {
            $payload = [
                'session' => $session->session_name,
                'to' => $to,
                'type' => $type,
            ];

            if ($type === 'text') {
                $payload['message'] = $message;
            } else {
                $payload['caption'] = $message;
                $payload['media'] = $media;
            }

            $response = Http::withHeaders($this->getHeaders($session))
                ->post($session->api_endpoint . '/send-message', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Find or create contact
                $contact = $this->findOrCreateContact($to);
                
                // Log the message
                $whatsappMessage = WhatsAppMessage::create([
                    'session_id' => $session->id,
                    'contact_id' => $contact->id,
                    'message_id' => $data['id'] ?? null,
                    'from_number' => $session->phone_number,
                    'to_number' => $to,
                    'message_type' => $type,
                    'content' => $message,
                    'media_url' => is_array($media) ? $media['url'] ?? null : $media,
                    'media_type' => $type !== 'text' ? $type : null,
                    'status' => 'sent',
                    'direction' => 'outbound',
                    'metadata' => $data
                ]);

                // Create communication record
                $this->createCommunication($contact, $message, 'outbound', $whatsappMessage->id);

                return [
                    'success' => true,
                    'message_id' => $data['id'] ?? null,
                    'whatsapp_message' => $whatsappMessage
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send message: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp send message error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error sending message: ' . $e->getMessage()
            ];
        }
    }

    public function handleWebhook(array $data)
    {
        try {
            $eventType = $data['event'] ?? null;
            $sessionName = $data['session'] ?? null;

            if (!$sessionName) {
                return ['success' => false, 'message' => 'Session not provided'];
            }

            $session = WhatsAppSession::where('session_name', $sessionName)->first();
            
            if (!$session) {
                return ['success' => false, 'message' => 'Session not found'];
            }

            switch ($eventType) {
                case 'qr':
                    $session->updateStatus('qr', $data['qr'] ?? null);
                    break;

                case 'ready':
                    $session->updateStatus('connected');
                    $session->update(['phone_number' => $data['phone'] ?? null]);
                    break;

                case 'disconnected':
                    $session->updateStatus('disconnected');
                    break;

                case 'message':
                    $this->handleIncomingMessage($session, $data['data']);
                    break;

                case 'message_ack':
                    $this->handleMessageAck($session, $data['data']);
                    break;
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error: ' . $e->getMessage(), $data);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function handleIncomingMessage(WhatsAppSession $session, array $messageData)
    {
        $from = $messageData['from'] ?? null;
        $message = $messageData['body'] ?? '';
        $messageType = $messageData['type'] ?? 'text';
        $messageId = $messageData['id'] ?? null;

        if (!$from) {
            return;
        }

        // Find or create contact
        $contact = $this->findOrCreateContact($from);

        // Create WhatsApp message record
        $whatsappMessage = WhatsAppMessage::create([
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'message_id' => $messageId,
            'from_number' => $from,
            'to_number' => $session->phone_number,
            'message_type' => $messageType,
            'content' => $message,
            'media_url' => $messageData['media_url'] ?? null,
            'media_type' => $messageType !== 'text' ? $messageType : null,
            'status' => 'delivered',
            'direction' => 'inbound',
            'metadata' => $messageData
        ]);

        // Create communication record
        $this->createCommunication($contact, $message, 'inbound', $whatsappMessage->id);

        // Broadcast to real-time listeners
        broadcast(new \App\Events\WhatsAppMessageReceived($whatsappMessage));
    }

    protected function handleMessageAck(WhatsAppSession $session, array $ackData)
    {
        $messageId = $ackData['id'] ?? null;
        $ack = $ackData['ack'] ?? null;

        if (!$messageId) {
            return;
        }

        $message = WhatsAppMessage::where('message_id', $messageId)->first();

        if (!$message) {
            return;
        }

        switch ($ack) {
            case 1: // Server received
                $message->update(['status' => 'sent']);
                break;
            case 2: // Delivered
                $message->markAsDelivered();
                break;
            case 3: // Read
                $message->markAsRead();
                break;
        }
    }

    protected function findOrCreateContact(string $phoneNumber)
    {
        // Clean phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        $contact = Contact::where('phone', $cleanPhone)
            ->orWhere('whatsapp', $cleanPhone)
            ->first();

        if (!$contact) {
            $contact = Contact::create([
                'first_name' => 'Unknown',
                'phone' => $cleanPhone,
                'whatsapp' => $cleanPhone,
                'source' => 'whatsapp',
                'created_by' => 1 // System user
            ]);
        }

        return $contact;
    }

    protected function createCommunication(Contact $contact, string $content, string $direction, int $whatsappMessageId)
    {
        return \App\Models\Communication::create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id() ?? 1,
            'type' => 'whatsapp',
            'direction' => $direction,
            'content' => $content,
            'status' => 'delivered',
            'external_id' => $whatsappMessageId,
            'sent_at' => now(),
            'delivered_at' => now()
        ]);
    }

    protected function getHeaders(WhatsAppSession $session)
    {
        $headers = ['Accept' => 'application/json'];
        
        if ($session->api_key) {
            $headers['Authorization'] = 'Bearer ' . decrypt($session->api_key);
        }

        return $headers;
    }

    public function stopSession(WhatsAppSession $session)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($session))
                ->post($session->api_endpoint . '/stop-session', [
                    'session' => $session->session_name
                ]);

            $session->updateStatus('disconnected');

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('WhatsApp stop session error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error stopping session: ' . $e->getMessage()
            ];
        }
    }

    public function getSessionStatus(WhatsAppSession $session)
    {
        try {
            $response = Http::withHeaders($this->getHeaders($session))
                ->get($session->api_endpoint . '/status/' . $session->session_name);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'disconnected';
                
                $session->updateStatus($status);
                
                return [
                    'success' => true,
                    'status' => $status,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Could not get session status'
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp get status error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error getting session status: ' . $e->getMessage()
            ];
        }
    }
}
