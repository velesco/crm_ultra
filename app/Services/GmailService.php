<?php

namespace App\Services;

use App\Models\GoogleAccount;
use App\Models\Email;
use App\Models\EmailAttachment;
use App\Models\SyncLog;
use App\Models\Contact;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class GmailService
{
    private $client;
    private $service;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes([
            Gmail::GMAIL_READONLY,
            Gmail::GMAIL_SEND,
            Gmail::GMAIL_MODIFY,
        ]);
        $this->client->setAccessType('offline');
    }

    /**
     * Initialize Gmail service with authenticated account.
     */
    public function initializeWithAccount(GoogleAccount $account): bool
    {
        try {
            if ($account->isTokenExpired()) {
                $this->refreshToken($account);
            }

            $this->client->setAccessToken([
                'access_token' => $account->access_token,
                'refresh_token' => $account->refresh_token,
                'expires_in' => 3600,
                'created' => time(),
            ]);

            $this->service = new Gmail($this->client);

            return true;

        } catch (Exception $e) {
            Log::error('Gmail service initialization failed', [
                'google_account_id' => $account->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Sync Gmail messages for an account (incremental sync).
     */
    public function syncMessages(GoogleAccount $account, int $maxResults = 100): array
    {
        if (!$this->initializeWithAccount($account)) {
            return [
                'success' => false,
                'message' => 'Failed to initialize Gmail service',
            ];
        }

        $syncLog = SyncLog::createForSync($account->id, $account->user_id, 'gmail_sync');

        try {
            $syncSettings = $account->sync_settings ?? [];
            $query = $this->buildSyncQuery($account, $syncSettings);

            // Get message list with query
            $listParams = [
                'q' => $query,
                'maxResults' => $maxResults,
            ];

            // For incremental sync, use lastSyncAt as starting point
            if ($account->last_sync_at && !$this->isInitialSync($account)) {
                $lastSyncTimestamp = $account->last_sync_at->timestamp;
                $listParams['q'] .= " after:{$lastSyncTimestamp}";
            }

            Log::info('Gmail sync starting', [
                'account_id' => $account->id,
                'query' => $listParams['q'],
                'maxResults' => $maxResults
            ]);

            $messagesList = $this->service->users_messages->listUsersMessages('me', $listParams);
            $messages = $messagesList->getMessages() ?? [];

            $processedCount = 0;
            $createdCount = 0;
            $updatedCount = 0;
            $failedCount = 0;

            foreach ($messages as $messageRef) {
                try {
                    $result = $this->syncSingleMessage($account, $messageRef->getId());
                    
                    if ($result['success']) {
                        $processedCount++;
                        if ($result['action'] === 'created') {
                            $createdCount++;
                        } elseif ($result['action'] === 'updated') {
                            $updatedCount++;
                        }
                    } else {
                        $failedCount++;
                    }

                } catch (Exception $e) {
                    $failedCount++;
                    Log::warning('Failed to sync message', [
                        'account_id' => $account->id,
                        'message_id' => $messageRef->getId(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $syncLog->updateCounts($processedCount, $createdCount, $updatedCount, $failedCount);
            $syncLog->markAsSuccess("Synced {$processedCount} messages");

            // Update account's last sync time
            $account->update(['last_sync_at' => now()]);

            return [
                'success' => true,
                'message' => "Gmail sync completed successfully",
                'processed' => $processedCount,
                'created' => $createdCount,
                'updated' => $updatedCount,
                'failed' => $failedCount,
            ];

        } catch (Exception $e) {
            Log::error('Gmail sync error', [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);

            $syncLog->markAsFailed('Gmail sync failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gmail sync failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync a single message by Gmail ID.
     */
    public function syncSingleMessage(GoogleAccount $account, string $gmailId): array
    {
        try {
            // Check if message already exists
            $existingEmail = Email::where('google_account_id', $account->id)
                                 ->where('gmail_id', $gmailId)
                                 ->first();

            // Get full message from Gmail API
            $message = $this->service->users_messages->get('me', $gmailId, ['format' => 'full']);

            // Parse message data
            $emailData = $this->parseMessage($message, $account);

            if ($existingEmail) {
                // Update existing email
                $existingEmail->update($emailData);

                // Sync attachments if needed
                if ($emailData['has_attachments']) {
                    $this->syncMessageAttachments($existingEmail, $message);
                }

                return [
                    'success' => true,
                    'action' => 'updated',
                    'email_id' => $existingEmail->id,
                ];
            } else {
                // Create new email
                $email = Email::create($emailData);

                // Sync attachments if present
                if ($emailData['has_attachments']) {
                    $this->syncMessageAttachments($email, $message);
                }

                // Auto-create contacts if enabled
                if ($account->sync_settings['auto_create_contacts'] ?? false) {
                    $this->autoCreateContacts($email, $account);
                }

                return [
                    'success' => true,
                    'action' => 'created',
                    'email_id' => $email->id,
                ];
            }

        } catch (Exception $e) {
            Log::error('Failed to sync single message', [
                'account_id' => $account->id,
                'gmail_id' => $gmailId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse Gmail message into Email model data.
     */
    private function parseMessage(Message $message, GoogleAccount $account): array
    {
        $headers = [];
        $payload = $message->getPayload();

        if ($payload && $payload->getHeaders()) {
            foreach ($payload->getHeaders() as $header) {
                $headers[strtolower($header->getName())] = $header->getValue();
            }
        }

        // Extract body content
        $bodyHtml = '';
        $bodyText = '';
        $this->extractBody($payload, $bodyHtml, $bodyText);

        // Parse recipients
        $toRecipients = $this->parseRecipients($headers['to'] ?? '');
        $ccRecipients = $this->parseRecipients($headers['cc'] ?? '');
        $bccRecipients = $this->parseRecipients($headers['bcc'] ?? '');

        // Parse from header
        $from = $this->parseFromHeader($headers['from'] ?? '');

        return [
            'google_account_id' => $account->id,
            'thread_id' => $message->getThreadId(),
            'message_id' => $headers['message-id'] ?? null,
            'gmail_id' => $message->getId(),
            'subject' => $headers['subject'] ?? '(no subject)',
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
            'from_email' => $from['email'],
            'from_name' => $from['name'],
            'to_recipients' => $toRecipients,
            'cc_recipients' => $ccRecipients,
            'bcc_recipients' => $bccRecipients,
            'date_sent' => $this->parseDate($headers['date'] ?? null),
            'date_received' => now(),
            'labels' => $message->getLabelIds() ?? [],
            'is_read' => !in_array('UNREAD', $message->getLabelIds() ?? []),
            'is_starred' => in_array('STARRED', $message->getLabelIds() ?? []),
            'is_important' => in_array('IMPORTANT', $message->getLabelIds() ?? []),
            'is_draft' => in_array('DRAFT', $message->getLabelIds() ?? []),
            'is_sent' => in_array('SENT', $message->getLabelIds() ?? []),
            'is_trash' => in_array('TRASH', $message->getLabelIds() ?? []),
            'is_spam' => in_array('SPAM', $message->getLabelIds() ?? []),
            'size_bytes' => $message->getSizeEstimate(),
            'snippet' => $message->getSnippet(),
            'headers' => $headers,
            'has_attachments' => $this->hasAttachments($payload),
            'in_reply_to' => $headers['in-reply-to'] ?? null,
            'references' => isset($headers['references']) ? explode(' ', $headers['references']) : [],
        ];
    }

    /**
     * Extract body content from message payload.
     */
    private function extractBody($payload, &$bodyHtml, &$bodyText): void
    {
        if (!$payload) return;

        $mimeType = $payload->getMimeType();
        $body = $payload->getBody();

        if ($body && $body->getData()) {
            $decodedBody = base64_decode(str_replace(['-', '_'], ['+', '/'], $body->getData()));

            if ($mimeType === 'text/plain') {
                $bodyText = $decodedBody;
            } elseif ($mimeType === 'text/html') {
                $bodyHtml = $decodedBody;
            }
        }

        // Handle multipart messages
        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                $this->extractBody($part, $bodyHtml, $bodyText);
            }
        }
    }

    /**
     * Check if message has attachments.
     */
    private function hasAttachments($payload): bool
    {
        if (!$payload) return false;

        $filename = $payload->getFilename();
        if ($filename && $filename !== '') {
            return true;
        }

        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($this->hasAttachments($part)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Parse recipient string into array.
     */
    private function parseRecipients(string $recipients): array
    {
        if (empty($recipients)) return [];

        $parsed = [];
        $addresses = explode(',', $recipients);

        foreach ($addresses as $address) {
            $address = trim($address);
            if ($address) {
                $parsed[] = $address;
            }
        }

        return $parsed;
    }

    /**
     * Parse from header into name and email.
     */
    private function parseFromHeader(string $from): array
    {
        if (empty($from)) {
            return ['name' => '', 'email' => ''];
        }

        // Pattern: "Name" <email@domain.com> or just email@domain.com
        if (preg_match('/^"?([^"]*)"?\s*<([^>]+)>$/', $from, $matches)) {
            return [
                'name' => trim($matches[1]),
                'email' => trim($matches[2])
            ];
        } elseif (preg_match('/^([^<]*)<([^>]+)>$/', $from, $matches)) {
            return [
                'name' => trim($matches[1]),
                'email' => trim($matches[2])
            ];
        } else {
            // Just email
            return [
                'name' => '',
                'email' => trim($from)
            ];
        }
    }

    /**
     * Parse date header.
     */
    private function parseDate(?string $dateString): ?Carbon
    {
        if (!$dateString) return null;

        try {
            return Carbon::parse($dateString);
        } catch (Exception $e) {
            Log::warning('Failed to parse email date', [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Sync message attachments.
     */
    private function syncMessageAttachments(Email $email, Message $message): void
    {
        try {
            $this->processAttachments($message->getPayload(), $email, $message->getId());
        } catch (Exception $e) {
            Log::error('Failed to sync attachments', [
                'email_id' => $email->id,
                'gmail_id' => $message->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process attachments from message payload.
     */
    private function processAttachments($payload, Email $email, string $gmailId): void
    {
        if (!$payload) return;

        $filename = $payload->getFilename();
        $attachmentId = $payload->getBody()?->getAttachmentId();

        if ($filename && $attachmentId) {
            // Check if attachment already exists
            $existingAttachment = EmailAttachment::where('email_id', $email->id)
                                                ->where('gmail_attachment_id', $attachmentId)
                                                ->first();

            if (!$existingAttachment) {
                $this->downloadAndStoreAttachment($email, $gmailId, $attachmentId, $filename, $payload);
            }
        }

        // Process nested parts
        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                $this->processAttachments($part, $email, $gmailId);
            }
        }
    }

    /**
     * Download and store attachment.
     */
    private function downloadAndStoreAttachment(Email $email, string $gmailId, string $attachmentId, string $filename, $payload): void
    {
        try {
            // Get attachment from Gmail API
            $attachment = $this->service->users_messages_attachments->get('me', $gmailId, $attachmentId);
            $data = $attachment->getData();
            
            if (!$data) return;

            // Decode attachment data
            $decodedData = base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
            
            // Generate unique filename
            $hash = hash('sha256', $decodedData);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $storedFilename = $hash . '.' . $extension;
            $storagePath = 'email_attachments/' . date('Y/m');
            $fullPath = $storagePath . '/' . $storedFilename;

            // Store file if not exists (deduplication by hash)
            if (!Storage::exists($fullPath)) {
                Storage::put($fullPath, $decodedData);
            }

            // Create attachment record
            EmailAttachment::create([
                'email_id' => $email->id,
                'gmail_attachment_id' => $attachmentId,
                'filename' => $filename,
                'original_filename' => $filename,
                'mime_type' => $payload->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => strlen($decodedData),
                'storage_path' => $fullPath,
                'storage_disk' => 'local',
                'file_hash' => $hash,
                'is_downloaded' => true,
                'downloaded_at' => now(),
                'is_inline' => false,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to download attachment', [
                'email_id' => $email->id,
                'attachment_id' => $attachmentId,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Auto-create contacts from email recipients.
     */
    private function autoCreateContacts(Email $email, GoogleAccount $account): void
    {
        try {
            $emailAddresses = array_merge(
                [$email->from_email],
                $email->to_recipients ?? [],
                $email->cc_recipients ?? [],
                $email->bcc_recipients ?? []
            );

            foreach (array_unique(array_filter($emailAddresses)) as $emailAddress) {
                if (empty($emailAddress) || $emailAddress === $account->email) {
                    continue;
                }

                // Check if contact already exists
                $existingContact = Contact::where('email', $emailAddress)
                                        ->orWhere('user_id', $account->user_id)
                                        ->first();

                if (!$existingContact) {
                    // Extract name from email headers if available
                    $name = $this->extractNameFromEmail($emailAddress, $email);
                    $nameParts = $this->splitName($name);

                    Contact::create([
                        'user_id' => $account->user_id,
                        'first_name' => $nameParts['first_name'],
                        'last_name' => $nameParts['last_name'],
                        'email' => $emailAddress,
                        'source' => 'gmail_auto',
                        'tags' => ['gmail-auto-created'],
                        'notes' => "Auto-created from Gmail message: {$email->subject}",
                        'custom_fields' => [
                            'gmail_thread_id' => $email->thread_id,
                            'created_from_email_id' => $email->id,
                        ]
                    ]);
                }
            }

        } catch (Exception $e) {
            Log::error('Failed to auto-create contacts', [
                'email_id' => $email->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Extract name from email address based on email context.
     */
    private function extractNameFromEmail(string $emailAddress, Email $email): string
    {
        // If this is the from address, use from_name
        if ($emailAddress === $email->from_email && $email->from_name) {
            return $email->from_name;
        }

        // Try to extract from the username part of email
        $localPart = explode('@', $emailAddress)[0];
        $name = str_replace(['.', '_', '-'], ' ', $localPart);
        return ucwords($name);
    }

    /**
     * Split full name into first and last name.
     */
    private function splitName(string $fullName): array
    {
        $nameParts = explode(' ', trim($fullName));
        
        if (count($nameParts) === 1) {
            return [
                'first_name' => $nameParts[0],
                'last_name' => ''
            ];
        }

        return [
            'first_name' => $nameParts[0],
            'last_name' => implode(' ', array_slice($nameParts, 1))
        ];
    }

    /**
     * Build Gmail query for sync.
     */
    private function buildSyncQuery(GoogleAccount $account, array $syncSettings): string
    {
        $queryParts = [];

        // Exclude spam and trash by default
        $queryParts[] = '-in:spam';
        $queryParts[] = '-in:trash';

        // Include specific folders based on settings
        $folders = [];
        if ($syncSettings['sync_inbox'] ?? true) {
            $folders[] = 'in:inbox';
        }
        if ($syncSettings['sync_sent'] ?? true) {
            $folders[] = 'in:sent';
        }
        if ($syncSettings['sync_drafts'] ?? false) {
            $folders[] = 'in:draft';
        }

        if (!empty($folders)) {
            $queryParts[] = '(' . implode(' OR ', $folders) . ')';
        }

        // For initial sync, limit by days back
        if ($this->isInitialSync($account)) {
            $daysBack = $syncSettings['days_back_initial_sync'] ?? 30;
            $sinceDate = now()->subDays($daysBack)->format('Y/m/d');
            $queryParts[] = "after:{$sinceDate}";
        }

        return implode(' ', $queryParts);
    }

    /**
     * Check if this is an initial sync.
     */
    private function isInitialSync(GoogleAccount $account): bool
    {
        return !$account->last_sync_at;
    }

    /**
     * Refresh access token.
     */
    private function refreshToken(GoogleAccount $account): bool
    {
        try {
            if (!$account->refresh_token) {
                throw new Exception('No refresh token available');
            }

            $this->client->refreshToken($account->refresh_token);
            $token = $this->client->getAccessToken();

            $account->update([
                'access_token' => $token['access_token'],
                'token_expires_at' => isset($token['expires_in']) 
                    ? now()->addSeconds($token['expires_in']) 
                    : null,
                'status' => 'active'
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Token refresh failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);

            $account->update(['status' => 'token_expired']);
            return false;
        }
    }

    /**
     * Send email via Gmail API.
     */
    public function sendEmail(GoogleAccount $account, array $emailData): array
    {
        if (!$this->initializeWithAccount($account)) {
            return [
                'success' => false,
                'message' => 'Failed to initialize Gmail service',
            ];
        }

        try {
            // Build email message
            $message = $this->buildEmailMessage($emailData);
            
            // Send via Gmail API
            $sentMessage = $this->service->users_messages->send('me', $message);

            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'gmail_id' => $sentMessage->getId(),
            ];

        } catch (Exception $e) {
            Log::error('Gmail send error', [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build Gmail message from email data.
     */
    private function buildEmailMessage(array $emailData): Gmail\Message
    {
        $boundary = uniqid();
        
        $headers = [
            'To' => $emailData['to'],
            'Subject' => $emailData['subject'],
            'MIME-Version' => '1.0',
            'Content-Type' => "multipart/alternative; boundary=\"{$boundary}\"",
        ];

        if (isset($emailData['cc'])) {
            $headers['Cc'] = $emailData['cc'];
        }

        if (isset($emailData['bcc'])) {
            $headers['Bcc'] = $emailData['bcc'];
        }

        // Build email content
        $content = "";
        
        // Add headers
        foreach ($headers as $key => $value) {
            $content .= "{$key}: {$value}\r\n";
        }
        
        $content .= "\r\n";
        
        // Add body parts
        $content .= "--{$boundary}\r\n";
        $content .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $content .= $emailData['body_text'] ?? strip_tags($emailData['body_html'] ?? '') . "\r\n";
        
        if (isset($emailData['body_html'])) {
            $content .= "--{$boundary}\r\n";
            $content .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $content .= $emailData['body_html'] . "\r\n";
        }
        
        $content .= "--{$boundary}--";

        // Encode message
        $encodedMessage = base64_encode($content);
        $encodedMessage = str_replace(['+', '/', '='], ['-', '_', ''], $encodedMessage);

        $message = new Gmail\Message();
        $message->setRaw($encodedMessage);

        return $message;
    }

    /**
     * Mark message as read/unread.
     */
    public function markAsRead(GoogleAccount $account, string $gmailId, bool $read = true): bool
    {
        if (!$this->initializeWithAccount($account)) {
            return false;
        }

        try {
            $modify = new Gmail\ModifyMessageRequest();
            
            if ($read) {
                $modify->setRemoveLabelIds(['UNREAD']);
            } else {
                $modify->setAddLabelIds(['UNREAD']);
            }

            $this->service->users_messages->modify('me', $gmailId, $modify);

            // Update local database
            Email::where('google_account_id', $account->id)
                 ->where('gmail_id', $gmailId)
                 ->update(['is_read' => $read]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to mark message as read/unread', [
                'account_id' => $account->id,
                'gmail_id' => $gmailId,
                'read' => $read,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get Gmail account profile.
     */
    public function getProfile(GoogleAccount $account): ?array
    {
        if (!$this->initializeWithAccount($account)) {
            return null;
        }

        try {
            $profile = $this->service->users->getProfile('me');

            return [
                'email_address' => $profile->getEmailAddress(),
                'messages_total' => $profile->getMessagesTotal(),
                'threads_total' => $profile->getThreadsTotal(),
                'history_id' => $profile->getHistoryId(),
            ];

        } catch (Exception $e) {
            Log::error('Failed to get Gmail profile', [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }
}
