<!-- Email Detail Modal Content -->
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
        {{ $email->subject ?: '(no subject)' }}
    </h2>
    <button onclick="closeEmailModal()" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times text-xl"></i>
    </button>
</div>

<!-- Email Header -->
<div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
                <!-- Gmail Account Badge -->
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                    {{ $email->googleAccount->email }}
                </span>
                
                <!-- Status Badges -->
                @if($email->is_starred)
                    <span class="inline-flex items-center text-amber-500">
                        <i class="fas fa-star mr-1"></i> Starred
                    </span>
                @endif
                
                @if($email->is_important)
                    <span class="inline-flex items-center text-red-500">
                        <i class="fas fa-exclamation mr-1"></i> Important
                    </span>
                @endif
                
                @if($email->has_attachments)
                    <span class="inline-flex items-center text-gray-500">
                        <i class="fas fa-paperclip mr-1"></i> {{ $attachments->count() }} attachment(s)
                    </span>
                @endif
            </div>
            
            <div class="space-y-1">
                <div class="flex items-center">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-16">From:</span>
                    <span class="text-sm text-gray-900 dark:text-white">
                        {{ $email->from_name ? $email->from_name . ' <' . $email->from_email . '>' : $email->from_email }}
                    </span>
                </div>
                
                @if($email->to_recipients && count($email->to_recipients) > 0)
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-16">To:</span>
                        <span class="text-sm text-gray-900 dark:text-white">
                            {{ implode(', ', $email->to_recipients) }}
                        </span>
                    </div>
                @endif
                
                @if($email->cc_recipients && count($email->cc_recipients) > 0)
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-16">CC:</span>
                        <span class="text-sm text-gray-900 dark:text-white">
                            {{ implode(', ', $email->cc_recipients) }}
                        </span>
                    </div>
                @endif
                
                <div class="flex items-center">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-16">Date:</span>
                    <span class="text-sm text-gray-900 dark:text-white">
                        {{ $email->date_sent ? $email->date_sent->format('M j, Y \a\t g:i A') : 'Unknown' }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="ml-4 flex space-x-2">
            <button onclick="replyToEmail({{ $email->id }})" 
                    class="btn btn-sm btn-secondary">
                <i class="fas fa-reply mr-1"></i> Reply
            </button>
            <button onclick="forwardEmail({{ $email->id }})" 
                    class="btn btn-sm btn-secondary">
                <i class="fas fa-share mr-1"></i> Forward
            </button>
        </div>
    </div>
</div>

<!-- Labels -->
@if($email->labels && count($email->labels) > 0)
    <div class="mb-4">
        <div class="flex flex-wrap gap-2">
            @foreach($email->labels as $label)
                @if(!in_array($label, ['INBOX', 'UNREAD', 'SENT', 'DRAFT']))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ $label }}
                    </span>
                @endif
            @endforeach
        </div>
    </div>
@endif

<!-- Attachments -->
@if($attachments->isNotEmpty())
    <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <i class="fas fa-paperclip mr-1"></i> Attachments ({{ $attachments->count() }})
        </h4>
        <div class="space-y-2">
            @foreach($attachments as $attachment)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <i class="{{ $attachment->getIcon() }} text-gray-400"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $attachment->filename }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $attachment->getFormattedSize() }} • {{ $attachment->mime_type }}
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        @if($attachment->fileExists())
                            <a href="{{ $attachment->getDownloadUrl() }}" 
                               download="{{ $attachment->filename }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        @else
                            <button onclick="downloadAttachment({{ $attachment->id }})" 
                                    class="text-indigo-600 hover:text-indigo-800 text-sm">
                                <i class="fas fa-cloud-download-alt mr-1"></i> Download from Gmail
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Email Thread/Conversation -->
@if($threadEmails->count() > 1)
    <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            <i class="fas fa-comments mr-1"></i> Conversation ({{ $threadEmails->count() }} messages)
        </h4>
        <div class="space-y-2 max-h-40 overflow-y-auto">
            @foreach($threadEmails as $threadEmail)
                <div class="flex items-center justify-between p-2 {{ $threadEmail->id === $email->id ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'bg-gray-50 dark:bg-gray-700' }} rounded">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full {{ $threadEmail->id === $email->id ? 'bg-indigo-500' : 'bg-gray-400' }}"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-300">
                            {{ $threadEmail->from_name ?: $threadEmail->from_email }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $threadEmail->date_sent ? $threadEmail->date_sent->format('M j, g:i A') : '' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Email Body -->
<div class="border-t border-gray-200 dark:border-gray-600 pt-4">
    <div class="prose prose-sm dark:prose-invert max-w-none">
        @if($email->body_html)
            <div class="email-html-content">
                {!! $email->body_html !!}
            </div>
        @elseif($email->body_text)
            <div class="email-text-content whitespace-pre-wrap font-mono text-sm">
                {{ $email->body_text }}
            </div>
        @else
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <i class="fas fa-file-alt text-2xl mb-2"></i>
                <p>No email content available</p>
            </div>
        @endif
    </div>
</div>

<!-- Email Actions Footer -->
<div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
    <div class="flex space-x-4">
        <button onclick="toggleEmailStar({{ $email->id }})" 
                class="flex items-center text-sm {{ $email->is_starred ? 'text-amber-500' : 'text-gray-500' }} hover:text-amber-500">
            <i class="fas fa-star mr-1"></i>
            {{ $email->is_starred ? 'Starred' : 'Star' }}
        </button>
        
        <button onclick="markEmailAsUnread({{ $email->id }})" 
                class="flex items-center text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-envelope mr-1"></i>
            Mark as Unread
        </button>
        
        <button onclick="archiveEmail({{ $email->id }})" 
                class="flex items-center text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-archive mr-1"></i>
            Archive
        </button>
    </div>
    
    <div class="flex space-x-2">
        <button onclick="replyToEmail({{ $email->id }})" 
                class="btn btn-primary btn-sm">
            <i class="fas fa-reply mr-2"></i>
            Reply
        </button>
        
        <button onclick="forwardEmail({{ $email->id }})" 
                class="btn btn-secondary btn-sm">
            <i class="fas fa-share mr-2"></i>
            Forward
        </button>
    </div>
</div>

<!-- Email Actions JavaScript -->
<script>
function toggleEmailStar(emailId) {
    fetch(`/api/gmail/${emailId}/toggle-star`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the star button in modal
            const starButton = event.target.closest('button');
            if (data.starred) {
                starButton.classList.remove('text-gray-500');
                starButton.classList.add('text-amber-500');
                starButton.innerHTML = '<i class="fas fa-star mr-1"></i> Starred';
            } else {
                starButton.classList.remove('text-amber-500');
                starButton.classList.add('text-gray-500');
                starButton.innerHTML = '<i class="fas fa-star mr-1"></i> Star';
            }
            
            // Also update the star in the inbox list if visible
            const inboxStarBtn = document.querySelector(`[data-email-id="${emailId}"] .star-btn`);
            if (inboxStarBtn) {
                if (data.starred) {
                    inboxStarBtn.classList.add('text-amber-500');
                    inboxStarBtn.classList.remove('text-gray-400');
                } else {
                    inboxStarBtn.classList.remove('text-amber-500');
                    inboxStarBtn.classList.add('text-gray-400');
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update star status');
    });
}

function markEmailAsUnread(emailId) {
    fetch(`/api/gmail/${emailId}/mark-unread`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEmailModal();
            // Refresh the page to show updated status
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark as unread');
    });
}

function archiveEmail(emailId) {
    if (confirm('Archive this email?')) {
        fetch(`/api/gmail/${emailId}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEmailModal();
                // Remove from inbox list
                const emailRow = document.querySelector(`[data-email-id="${emailId}"]`);
                if (emailRow) {
                    emailRow.remove();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to archive email');
        });
    }
}

function replyToEmail(emailId) {
    // TODO: Implement reply functionality
    alert('Reply functionality will be implemented in the next phase');
}

function forwardEmail(emailId) {
    // TODO: Implement forward functionality
    alert('Forward functionality will be implemented in the next phase');
}

function downloadAttachment(attachmentId) {
    // Show loading state
    event.target.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Downloading...';
    
    fetch(`/api/gmail/attachments/${attachmentId}/download`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to download URL
            window.location.href = data.download_url;
            event.target.innerHTML = '<i class="fas fa-download mr-1"></i> Download';
        } else {
            alert('Error: ' + data.message);
            event.target.innerHTML = '<i class="fas fa-cloud-download-alt mr-1"></i> Download from Gmail';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to download attachment');
        event.target.innerHTML = '<i class="fas fa-cloud-download-alt mr-1"></i> Download from Gmail';
    });
}
</script>

<!-- Email Content Styling -->
<style>
.email-html-content {
    /* Scope email HTML content styles */
    line-height: 1.6;
}

.email-html-content img {
    max-width: 100%;
    height: auto;
}

.email-html-content table {
    border-collapse: collapse;
    width: 100%;
}

.email-html-content blockquote {
    border-left: 4px solid #e5e7eb;
    margin: 1em 0;
    padding-left: 1em;
    color: #6b7280;
}

.email-text-content {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 1rem;
}

.dark .email-text-content {
    background-color: #374151;
    border-color: #4b5563;
    color: #d1d5db;
}

/* Remove potentially dangerous styles from email content */
.email-html-content * {
    position: static !important;
    z-index: auto !important;
}
</style>
