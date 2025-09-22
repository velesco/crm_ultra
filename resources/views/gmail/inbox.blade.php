@extends('layouts.app')

@section('title', 'Gmail Unified Inbox')
@section('page-title', 'Gmail Inbox')

@push('styles')
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Custom button styles to match design */
.btn {
    @apply inline-flex items-center justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2;
}
.btn-primary {
    @apply bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500;
}
.btn-secondary {
    @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
}
.btn-sm {
    @apply px-3 py-2 text-xs;
}

/* Custom toast styles */
.toast {
    @apply fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform;
}
.toast-success { @apply bg-green-500 text-white; }
.toast-error { @apply bg-red-500 text-white; }
.toast-info { @apply bg-blue-500 text-white; }

/* Loading spinner */
.spinner {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush

@section('header-actions')
<div class="flex items-center space-x-3">
    <!-- Account Selector -->
    <select id="accountFilter" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
        <option value="">All Accounts</option>
        @foreach($googleAccounts as $account)
            <option value="{{ $account->id }}">{{ $account->email }}</option>
        @endforeach
    </select>

    <!-- Quick Actions -->
    <button onclick="refreshInbox()" 
            id="refreshBtn"
            class="btn btn-secondary btn-sm relative">
        <i class="fas fa-sync-alt mr-2" id="refreshIcon"></i>
        <span id="refreshText">Refresh</span>
        <div id="loadingSpinner" class="hidden absolute inset-0 flex items-center justify-center">
            <svg class="spinner h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </button>

    <a href="{{ route('gmail.oauth.index') }}" 
       class="btn btn-primary btn-sm">
        <i class="fas fa-cog mr-2"></i>
        Gmail Settings
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Sync Progress Indicator (hidden by default) -->
    <div id="syncProgressBar" class="hidden mb-4">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-900 dark:text-white">Syncing Gmail accounts...</span>
                <span class="text-xs text-gray-500 dark:text-gray-400" id="syncProgressText">0%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-indigo-500 h-2 rounded-full transition-all duration-300" id="syncProgressFill" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Inbox Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-envelope text-indigo-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Emails</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['total_emails'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-envelope-open text-yellow-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Unread</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['unread_emails'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-star text-amber-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Starred</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['starred_emails'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Connected Accounts</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $googleAccounts->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6">
        <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
            <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-4">
                <!-- Search -->
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search emails..." 
                           class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>

                <!-- Filters -->
                <select id="statusFilter" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                    <option value="">All Status</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                    <option value="starred">Starred</option>
                    <option value="important">Important</option>
                </select>

                <select id="labelFilter" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800">
                    <option value="">All Labels</option>
                    <option value="INBOX">Inbox</option>
                    <option value="SENT">Sent</option>
                    <option value="DRAFT">Drafts</option>
                </select>
            </div>

            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <button onclick="markSelectedAsRead()" class="btn btn-sm btn-secondary w-full sm:w-auto">
                    <i class="fas fa-envelope-open mr-1"></i> 
                    <span class="hidden sm:inline">Mark Read</span>
                    <span class="sm:hidden">Read</span>
                </button>
                <button onclick="starSelected()" class="btn btn-sm btn-secondary w-full sm:w-auto">
                    <i class="fas fa-star mr-1"></i> 
                    <span class="hidden sm:inline">Star</span>
                    <span class="sm:hidden">★</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Emails List -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Gmail Messages
                </h3>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" 
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="selectAll" class="text-sm text-gray-500 dark:text-gray-400">Select All</label>
                </div>
            </div>
        </div>

        <div id="emailsList" class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($emails as $email)
                <div class="email-item px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700 {{ $email->is_read ? '' : 'bg-blue-50 dark:bg-blue-900/20' }}" 
                     data-email-id="{{ $email->id }}">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" class="email-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                               value="{{ $email->id }}">
                        
                        <!-- Star Button -->
                        <button onclick="toggleStar({{ $email->id }})" 
                                class="star-btn text-gray-400 hover:text-amber-500 {{ $email->is_starred ? 'text-amber-500' : '' }}">
                            <i class="fas fa-star"></i>
                        </button>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <!-- Gmail Account Badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                        {{ $email->googleAccount->email }}
                                    </span>
                                    
                                    <!-- From -->
                                    <div class="flex flex-col sm:flex-row sm:items-center">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-48">
                                            {{ $email->from_name ?: $email->from_email }}
                                        </p>
                                        @if($email->from_name)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 sm:ml-2">
                                                &lt;{{ $email->from_email }}&gt;
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    @if($email->has_attachments)
                                        <i class="fas fa-paperclip text-gray-400"></i>
                                    @endif
                                    @if($email->is_important)
                                        <i class="fas fa-exclamation text-red-500"></i>
                                    @endif
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $email->date_received->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white {{ $email->is_read ? 'font-normal' : 'font-semibold' }}">
                                    {{ $email->subject ?: '(no subject)' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 truncate">
                                    {{ $email->snippet }}
                                </p>
                            </div>

                            <!-- Labels -->
                            @if($email->labels && count($email->labels) > 0)
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach(array_slice($email->labels, 0, 3) as $label)
                                        @if(!in_array($label, ['INBOX', 'UNREAD', 'SENT', 'DRAFT']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $label }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No emails found</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        @if($googleAccounts->isEmpty())
                            Connect your Gmail account to start syncing emails.
                        @else
                            Try adjusting your filters or wait for the next sync.
                        @endif
                    </p>
                    @if($googleAccounts->isEmpty())
                        <div class="mt-6">
                            <a href="{{ route('gmail.oauth.index') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>
                                Connect Gmail Account
                            </a>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($emails->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $emails->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Email Detail Modal -->
<div id="emailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div id="emailModalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CSRF token if not already set
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        const metaTag = document.createElement('meta');
        metaTag.name = 'csrf-token';
        metaTag.content = '{{ csrf_token() }}';
        document.head.appendChild(metaTag);
    }
    
    // Email item click handler with error checking
    document.querySelectorAll('.email-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            // Don't trigger on checkbox, star button clicks
            if (e.target.type === 'checkbox' || e.target.closest('.star-btn') || e.target.closest('button')) {
                return;
            }
            
            const emailId = this.dataset.emailId;
            if (emailId) {
                openEmailModal(emailId);
            } else {
                console.error('Email ID not found for email item');
                showToast('Error: Cannot open email details.', 'error');
            }
        });
    });

    // Filter handlers
    document.getElementById('accountFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('labelFilter').addEventListener('change', applyFilters);
    
    // Search handler with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
});

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.email-checkbox');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = selectAll.checked;
    });
}

function getSelectedEmails() {
    const checkboxes = document.querySelectorAll('.email-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value).filter(id => id && id.trim() !== '');
}

function markSelectedAsRead() {
    const selectedIds = getSelectedEmails();
    
    if (selectedIds.length === 0) {
        showToast('Please select emails to mark as read.', 'error');
        return;
    }
    
    // Validate CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken || !csrfToken.content) {
        showToast('Security token missing. Please refresh the page.', 'error');
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner spinner mr-1"></i> Processing...';
    
    // Make AJAX call to mark as read
    fetch('/api/gmail/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ email_ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Marked ${data.processed} emails as read`, 'success');
            
            // Update UI for selected emails
            selectedIds.forEach(emailId => {
                const emailItem = document.querySelector(`[data-email-id="${emailId}"]`);
                if (emailItem) {
                    emailItem.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                }
            });
            
            // Reset button
            button.disabled = false;
            button.innerHTML = originalText;
            
            // Clear selection
            document.getElementById('selectAll').checked = false;
            toggleSelectAll();
        } else {
            showToast('Error: ' + data.message, 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while marking emails as read.', 'error');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function starSelected() {
    const selectedIds = getSelectedEmails();
    
    if (selectedIds.length === 0) {
        showToast('Please select emails to star.', 'error');
        return;
    }
    
    // Validate CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken || !csrfToken.content) {
        showToast('Security token missing. Please refresh the page.', 'error');
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner spinner mr-1"></i> Processing...';
    
    // Make AJAX call to star emails
    fetch('/api/gmail/star', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ email_ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Updated ${data.processed} emails`, 'success');
            
            // Reset button
            button.disabled = false;
            button.innerHTML = originalText;
            
            // Clear selection and refresh to show changes
            document.getElementById('selectAll').checked = false;
            toggleSelectAll();
            
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + data.message, 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while starring emails.', 'error');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function toggleStar(emailId) {
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
            // Update star visual state
            const starBtn = document.querySelector(`[data-email-id="${emailId}"] .star-btn`);
            if (data.starred) {
                starBtn.classList.add('text-amber-500');
                starBtn.classList.remove('text-gray-400');
            } else {
                starBtn.classList.remove('text-amber-500');
                starBtn.classList.add('text-gray-400');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function openEmailModal(emailId) {
    // Show modal
    document.getElementById('emailModal').classList.remove('hidden');
    
    // Load email content
    fetch(`/api/gmail/${emailId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('emailModalContent').innerHTML = data.html;
                
                // Mark as read if unread
                if (!data.email.is_read) {
                    markAsRead(emailId);
                }
            } else {
                document.getElementById('emailModalContent').innerHTML = `
                    <div class="text-center p-8">
                        <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Error Loading Email</h3>
                        <p class="text-gray-500">${data.message}</p>
                        <button onclick="closeEmailModal()" class="mt-4 btn btn-primary">Close</button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('emailModalContent').innerHTML = `
                <div class="text-center p-8">
                    <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Network Error</h3>
                    <p class="text-gray-500">Failed to load email content.</p>
                    <button onclick="closeEmailModal()" class="mt-4 btn btn-primary">Close</button>
                </div>
            `;
        });
}

function closeEmailModal() {
    document.getElementById('emailModal').classList.add('hidden');
}

function markAsRead(emailId) {
    fetch(`/api/gmail/${emailId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
}

function applyFilters() {
    const params = new URLSearchParams();
    
    const account = document.getElementById('accountFilter').value;
    const status = document.getElementById('statusFilter').value;
    const label = document.getElementById('labelFilter').value;
    const search = document.getElementById('searchInput').value;
    
    if (account) params.append('account', account);
    if (status) params.append('status', status);
    if (label) params.append('label', label);
    if (search) params.append('search', search);
    
    // Reload page with filters
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}

function refreshInbox() {
    const refreshBtn = document.getElementById('refreshBtn');
    const refreshIcon = document.getElementById('refreshIcon');
    const refreshText = document.getElementById('refreshText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    // Show loading state
    refreshBtn.disabled = true;
    refreshIcon.classList.add('hidden');
    refreshText.textContent = 'Syncing...';
    loadingSpinner.classList.remove('hidden');
    
    // Trigger sync for all accounts
    fetch('/api/gmail/sync-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Gmail sync initiated successfully!', 'success');
            
            // Show progress bar and simulate progress
            showSyncProgress();
            
            // Reload after progress is complete
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else {
            showToast('Sync failed: ' + data.message, 'error');
            resetRefreshButton();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while refreshing inbox.', 'error');
        resetRefreshButton();
    });
}

function resetRefreshButton() {
    const refreshBtn = document.getElementById('refreshBtn');
    const refreshIcon = document.getElementById('refreshIcon');
    const refreshText = document.getElementById('refreshText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    refreshBtn.disabled = false;
    refreshIcon.classList.remove('hidden');
    refreshText.textContent = 'Refresh';
    loadingSpinner.classList.add('hidden');
}

// Toast notification function - Updated with CSS classes
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const typeClass = type === 'success' ? 'toast-success' : type === 'error' ? 'toast-error' : 'toast-info';
    
    toast.className = `toast ${typeClass} translate-x-full`;
    
    if (typeof message === 'string') {
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
    } else {
        toast.innerHTML = message; // For HTML content like keyboard shortcuts
    }
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after delay (longer for HTML content)
    const delay = typeof message === 'string' ? 3000 : 5000;
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, delay);
}

// Show sync progress
function showSyncProgress() {
    const progressBar = document.getElementById('syncProgressBar');
    const progressFill = document.getElementById('syncProgressFill');
    const progressText = document.getElementById('syncProgressText');
    
    progressBar.classList.remove('hidden');
    
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 30 + 5; // Random progress increment
        
        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
            
            setTimeout(() => {
                progressBar.classList.add('hidden');
                progressFill.style.width = '0%';
                progressText.textContent = '0%';
            }, 500);
        }
        
        progressFill.style.width = progress + '%';
        progressText.textContent = Math.round(progress) + '%';
    }, 200);
}

// Close modal when clicking outside
document.getElementById('emailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEmailModal();
    }
});

// Enhanced keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Don't trigger shortcuts when typing in input fields
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
        return;
    }
    
    switch(e.key) {
        case 'Escape':
            closeEmailModal();
            break;
        case 'r':
        case 'R':
            if (e.ctrlKey || e.metaKey) return; // Don't conflict with browser refresh
            e.preventDefault();
            refreshInbox();
            showToast('Refreshing inbox...', 'info');
            break;
        case 'a':
        case 'A':
            if (e.ctrlKey || e.metaKey) return; // Don't conflict with select all
            e.preventDefault();
            const selectAll = document.getElementById('selectAll');
            selectAll.checked = !selectAll.checked;
            toggleSelectAll();
            showToast(selectAll.checked ? 'All emails selected' : 'Selection cleared', 'info');
            break;
        case 's':
        case 'S':
            if (e.ctrlKey || e.metaKey) return; // Don't conflict with save
            e.preventDefault();
            starSelected();
            break;
        case 'u':
        case 'U':
            e.preventDefault();
            markSelectedAsRead();
            break;
        case '/':
            e.preventDefault();
            const searchInput = document.getElementById('searchInput');
            searchInput.focus();
            break;
    }
});

// Show keyboard shortcuts help
function showKeyboardShortcuts() {
    const shortcuts = [
        { key: 'R', action: 'Refresh inbox' },
        { key: 'A', action: 'Select/deselect all emails' },
        { key: 'S', action: 'Star selected emails' },
        { key: 'U', action: 'Mark selected as read' },
        { key: '/', action: 'Focus search' },
        { key: 'Esc', action: 'Close modal' }
    ];
    
    let shortcutsHtml = shortcuts.map(s => 
        `<div class="flex justify-between py-1">
            <span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">${s.key}</span>
            <span class="text-sm">${s.action}</span>
        </div>`
    ).join('');
    
    showToast(`<div class="text-left">
        <div class="font-semibold mb-2">Keyboard Shortcuts:</div>
        ${shortcutsHtml}
    </div>`, 'info');
}

// Add keyboard shortcuts info to help
document.addEventListener('DOMContentLoaded', function() {
    // Add help icon somewhere visible
    const helpButton = document.createElement('button');
    helpButton.innerHTML = '<i class="fas fa-keyboard"></i>';
    helpButton.className = 'fixed bottom-4 right-4 bg-gray-600 text-white p-2 rounded-full shadow-lg hover:bg-gray-700 transition-colors z-40';
    helpButton.title = 'Show keyboard shortcuts';
    helpButton.onclick = showKeyboardShortcuts;
    document.body.appendChild(helpButton);
});
</script>
@endpush
