<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\SmsMessage;
use App\Models\SmsProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use Vonage\Client as VonageClient;
use Vonage\Client\Credentials\Basic as VonageBasic;
use Vonage\SMS\Message\SMS;

class SmsService
{
    public function send(SmsProvider $provider, string $to, string $message, ?Contact $contact = null)
    {
        if (! $provider->canSend()) {
            return [
                'success' => false,
                'message' => 'Provider cannot send SMS (limit reached or inactive)',
            ];
        }

        try {
            $result = $this->sendBySmsProvider($provider, $to, $message);

            if ($result['success']) {
                // Find or create contact if not provided
                if (! $contact) {
                    $contact = $this->findOrCreateContact($to);
                }

                // Create SMS message record
                $smsMessage = SmsMessage::create([
                    'contact_id' => $contact->id,
                    'provider_id' => $provider->id,
                    'to_number' => $to,
                    'message' => $message,
                    'status' => 'sent',
                    'external_id' => $result['external_id'] ?? null,
                    'cost' => $result['cost'] ?? $provider->cost_per_sms,
                    'metadata' => $result['metadata'] ?? [],
                ]);

                // Update provider stats
                $provider->incrementSent();

                // Create communication record
                $this->createCommunication($contact, $message, $smsMessage->id);

                return [
                    'success' => true,
                    'sms_message' => $smsMessage,
                    'external_id' => $result['external_id'] ?? null,
                ];
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('SMS send error: '.$e->getMessage(), [
                'provider_id' => $provider->id,
                'to' => $to,
                'message' => substr($message, 0, 100),
            ]);

            return [
                'success' => false,
                'message' => 'Error sending SMS: '.$e->getMessage(),
            ];
        }
    }

    protected function sendBySmsProvider(SmsProvider $provider, string $to, string $message)
    {
        switch ($provider->provider) {
            case 'twilio':
                return $this->sendViaTwilio($provider, $to, $message);
            case 'vonage':
                return $this->sendViaVonage($provider, $to, $message);
            case 'orange':
                return $this->sendViaOrange($provider, $to, $message);
            case 'custom':
                return $this->sendViaCustomProvider($provider, $to, $message);
            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported SMS provider: '.$provider->provider,
                ];
        }
    }

    protected function sendViaTwilio(SmsProvider $provider, string $to, string $message)
    {
        try {
            $twilio = new TwilioClient(
                decrypt($provider->api_key),
                decrypt($provider->api_secret)
            );

            $sms = $twilio->messages->create($to, [
                'from' => $provider->sender_id,
                'body' => $message,
            ]);

            return [
                'success' => true,
                'external_id' => $sms->sid,
                'cost' => $sms->price ? abs(floatval($sms->price)) : null,
                'metadata' => [
                    'sid' => $sms->sid,
                    'status' => $sms->status,
                    'price_unit' => $sms->priceUnit,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Twilio SMS error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Twilio error: '.$e->getMessage(),
            ];
        }
    }

    protected function sendViaVonage(SmsProvider $provider, string $to, string $message)
    {
        try {
            $basic = new VonageBasic(decrypt($provider->api_key), decrypt($provider->api_secret));
            $client = new VonageClient($basic);

            $sms = new SMS($to, $provider->sender_id, $message);
            $response = $client->sms()->send($sms);

            $smsMessage = $response->current();

            if ($smsMessage->getStatus() == 0) {
                return [
                    'success' => true,
                    'external_id' => $smsMessage->getMessageId(),
                    'cost' => $smsMessage->getMessagePrice(),
                    'metadata' => [
                        'message_id' => $smsMessage->getMessageId(),
                        'status' => $smsMessage->getStatus(),
                        'remaining_balance' => $smsMessage->getRemainingBalance(),
                        'message_price' => $smsMessage->getMessagePrice(),
                        'network' => $smsMessage->getNetwork(),
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'Vonage error: '.$smsMessage->getStatusText(),
            ];

        } catch (\Exception $e) {
            Log::error('Vonage SMS error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Vonage error: '.$e->getMessage(),
            ];
        }
    }

    protected function sendViaOrange(SmsProvider $provider, string $to, string $message)
    {
        try {
            // Orange API implementation
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.decrypt($provider->api_key),
                'Content-Type' => 'application/json',
            ])->post($provider->settings['api_url'] ?? 'https://api.orange.com/smsmessaging/v1/outbound/sms', [
                'outboundSMSMessageRequest' => [
                    'address' => [$to],
                    'senderAddress' => $provider->sender_id,
                    'outboundSMSTextMessage' => [
                        'message' => $message,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'external_id' => $data['outboundSMSMessageRequest']['resourceURL'] ?? null,
                    'metadata' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => 'Orange API error: '.$response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Orange SMS error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Orange error: '.$e->getMessage(),
            ];
        }
    }

    protected function sendViaCustomProvider(SmsProvider $provider, string $to, string $message)
    {
        try {
            $settings = $provider->settings ?? [];
            $apiUrl = $settings['api_url'] ?? null;
            $method = $settings['method'] ?? 'POST';
            $headers = $settings['headers'] ?? [];

            if (! $apiUrl) {
                return [
                    'success' => false,
                    'message' => 'Custom provider API URL not configured',
                ];
            }

            // Replace placeholders in API URL and payload
            $apiUrl = str_replace(['{to}', '{message}', '{sender}'], [$to, urlencode($message), $provider->sender_id], $apiUrl);

            $payload = $settings['payload'] ?? [];
            $payload = $this->replacePlaceholders($payload, [
                'to' => $to,
                'message' => $message,
                'sender' => $provider->sender_id,
                'api_key' => decrypt($provider->api_key),
                'api_secret' => decrypt($provider->api_secret ?? ''),
            ]);

            $request = Http::withHeaders($headers);

            if ($method === 'GET') {
                $response = $request->get($apiUrl, $payload);
            } else {
                $response = $request->post($apiUrl, $payload);
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'external_id' => $response->json()['id'] ?? null,
                    'metadata' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Custom provider error: '.$response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Custom SMS provider error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Custom provider error: '.$e->getMessage(),
            ];
        }
    }

    protected function replacePlaceholders(array $data, array $placeholders)
    {
        $json = json_encode($data);

        foreach ($placeholders as $key => $value) {
            $json = str_replace('{'.$key.'}', $value, $json);
        }

        return json_decode($json, true);
    }

    public function handleWebhook(SmsProvider $provider, array $data)
    {
        try {
            switch ($provider->provider) {
                case 'twilio':
                    return $this->handleTwilioWebhook($data);
                case 'vonage':
                    return $this->handleVonageWebhook($data);
                case 'orange':
                    return $this->handleOrangeWebhook($data);
                default:
                    return $this->handleCustomWebhook($provider, $data);
            }
        } catch (\Exception $e) {
            Log::error('SMS webhook error: '.$e->getMessage(), $data);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function handleTwilioWebhook(array $data)
    {
        $messageSid = $data['MessageSid'] ?? null;
        $messageStatus = $data['MessageStatus'] ?? null;

        if (! $messageSid) {
            return ['success' => false, 'message' => 'MessageSid not provided'];
        }

        $smsMessage = SmsMessage::where('external_id', $messageSid)->first();

        if (! $smsMessage) {
            return ['success' => false, 'message' => 'SMS message not found'];
        }

        switch ($messageStatus) {
            case 'delivered':
                $smsMessage->markAsDelivered();
                break;
            case 'failed':
            case 'undelivered':
                $smsMessage->markAsFailed($data['ErrorMessage'] ?? 'Delivery failed');
                break;
        }

        return ['success' => true];
    }

    protected function handleVonageWebhook(array $data)
    {
        $messageId = $data['messageId'] ?? null;
        $status = $data['status'] ?? null;

        if (! $messageId) {
            return ['success' => false, 'message' => 'messageId not provided'];
        }

        $smsMessage = SmsMessage::where('external_id', $messageId)->first();

        if (! $smsMessage) {
            return ['success' => false, 'message' => 'SMS message not found'];
        }

        switch ($status) {
            case 'delivered':
                $smsMessage->markAsDelivered();
                break;
            case 'failed':
            case 'rejected':
                $smsMessage->markAsFailed($data['err-code'] ?? 'Delivery failed');
                break;
        }

        return ['success' => true];
    }

    protected function handleOrangeWebhook(array $data)
    {
        // Orange webhook handling
        return ['success' => true];
    }

    protected function handleCustomWebhook(SmsProvider $provider, array $data)
    {
        // Custom webhook handling based on provider settings
        return ['success' => true];
    }

    public function testProvider(SmsProvider $provider)
    {
        try {
            switch ($provider->provider) {
                case 'twilio':
                    return $this->testTwilio($provider);
                case 'vonage':
                    return $this->testVonage($provider);
                case 'orange':
                    return $this->testOrange($provider);
                case 'custom':
                    return $this->testCustomProvider($provider);
                default:
                    return [
                        'success' => false,
                        'message' => 'Unsupported provider: '.$provider->provider,
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Test failed: '.$e->getMessage(),
            ];
        }
    }

    protected function testTwilio(SmsProvider $provider)
    {
        try {
            $twilio = new TwilioClient(
                decrypt($provider->api_key),
                decrypt($provider->api_secret)
            );

            // Test by fetching account information
            $account = $twilio->api->v2010->account->fetch();

            return [
                'success' => true,
                'message' => 'Twilio connection successful',
                'account_sid' => $account->sid,
                'account_name' => $account->friendlyName,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Twilio test failed: '.$e->getMessage(),
            ];
        }
    }

    protected function testVonage(SmsProvider $provider)
    {
        try {
            $basic = new VonageBasic(decrypt($provider->api_key), decrypt($provider->api_secret));
            $client = new VonageClient($basic);

            // Test by fetching account balance
            $balance = $client->account()->getBalance();

            return [
                'success' => true,
                'message' => 'Vonage connection successful',
                'balance' => $balance->getBalance(),
                'currency' => 'EUR',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Vonage test failed: '.$e->getMessage(),
            ];
        }
    }

    protected function testOrange(SmsProvider $provider)
    {
        // Orange test implementation
        return [
            'success' => true,
            'message' => 'Orange connection test not implemented yet',
        ];
    }

    protected function testCustomProvider(SmsProvider $provider)
    {
        // Custom provider test implementation
        return [
            'success' => true,
            'message' => 'Custom provider connection test not implemented yet',
        ];
    }

    protected function findOrCreateContact(string $phoneNumber)
    {
        // Clean phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);

        $contact = Contact::where('phone', $cleanPhone)->first();

        if (! $contact) {
            $contact = Contact::create([
                'first_name' => 'Unknown',
                'phone' => $cleanPhone,
                'source' => 'sms',
                'created_by' => 1, // System user
            ]);
        }

        return $contact;
    }

    protected function createCommunication(Contact $contact, string $content, int $smsMessageId)
    {
        return \App\Models\Communication::create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id() ?? 1,
            'type' => 'sms',
            'direction' => 'outbound',
            'content' => $content,
            'status' => 'sent',
            'external_id' => $smsMessageId,
            'sent_at' => now(),
        ]);
    }

    public function bulkSend(SmsProvider $provider, array $recipients, string $message)
    {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($recipients as $recipient) {
            $contact = is_array($recipient) ? $recipient['contact'] : null;
            $phoneNumber = is_array($recipient) ? $recipient['phone'] : $recipient;

            $result = $this->send($provider, $phoneNumber, $message, $contact);

            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'phone' => $phoneNumber,
                'success' => $result['success'],
                'message' => $result['message'] ?? null,
                'sms_id' => $result['sms_message']->id ?? null,
            ];
        }

        return $results;
    }

    /**
     * Send a quick SMS to a single contact
     */
    public function sendQuickSms(Contact $contact, string $message)
    {
        try {
            // Validate contact has phone number
            if (empty($contact->phone)) {
                return [
                    'success' => false,
                    'message' => 'Contact does not have a phone number'
                ];
            }

            // Get the first active SMS provider
            $provider = SmsProvider::where('is_active', true)
                ->orderBy('priority')
                ->first();

            if (!$provider) {
                return [
                    'success' => false,
                    'message' => 'No active SMS provider available'
                ];
            }

            // Use the existing send method
            return $this->send($provider, $contact->phone, $message, $contact);

        } catch (\Exception $e) {
            Log::error('Quick SMS Send Error: ' . $e->getMessage(), [
                'contact_id' => $contact->id,
                'contact_phone' => $contact->phone,
                'message' => $message,
                'error_trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ];
        }
    }
}
