@extends('layouts.app')

@section('title', 'Gmail Integration')

@section('content')
<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Gmail Integration</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Connect your Gmail accounts for unified inbox and automatic contact creation</p>
        </div>
        
        <button onclick="openConnectModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Connect Gmail Account
        </button>
    </div>

    <!-- Connected Accounts -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Connected Gmail Accounts</h2>
            
            @if($googleAccounts->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Gmail accounts connected</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">Connect your Gmail account to enable unified inbox, automatic contact creation, and seamless email management.</p>
                    <button onclick="openConnectModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                        Connect Gmail Account
                    </button>
                </div>
            @else
                <!-- Connected Accounts List -->
                <div class="space-y-4">
                    @foreach($googleAccounts as $account)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6" id="account-{{ $account->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Account Avatar -->
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.283 10.356h-8.327v3.451h4.792c-.446 2.193-2.313 3.453-4.792 3.453a5.27 5.27 0 0 1-5.279-5.28 5.27 5.27 0 0 1 5.279-5.279c1.259 0 2.397.447 3.29 1.178l2.6-2.599c-1.584-1.381-3.615-2.233-5.89-2.233a8.908 8.908 0 0 0-8.934 8.934 8.907 8.907 0 0 0 8.934 8.934c4.467 0 8.529-3.249 8.529-8.934 0-.528-.081-1.097-.202-1.625z"/>
                                    </svg>
                                </div>
                                
                                <!-- Account Info -->
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $account->email }}</h3>
                                    <div class="flex items-center space-x-4 mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($account->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($account->status === 'token_expired') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                            @if($account->status === 'active')
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                                                </svg>
                                                Active
                                            @elseif($account->status === 'token_expired')
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"></path>
                                                </svg>
                                                Token Expired
                                            @else
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"></path>
                                                </svg>
                                                Disconnected
                                            @endif
                                        </span>
                                        
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            Visibility: {{ ucfirst($account->visibility) }}
                                        </span>
                                        
                                        @if($account->last_sync_at)
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Last sync: {{ $account->last_sync_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Account Actions -->
                            <div class="flex items-center space-x-3">
                                @if($account->status === 'active')
                                    <button onclick="loadAccountStats({{ $account->id }})" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                        View Stats
                                    </button>
                                    <button onclick="openSyncSettings({{ $account->id }})" class="text-gray-600 hover:text-gray-700 font-medium text-sm">
                                        Settings
                                    </button>
                                    <button onclick="disconnectAccount({{ $account->id }})" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                        Disconnect
                                    </button>
                                @elseif($account->status === 'token_expired')
                                    <a href="{{ route('gmail.oauth.reconnect', $account) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        Reconnect
                                    </a>
                                    <button onclick="disconnectAccount({{ $account->id }})" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                        Remove
                                    </button>
                                @else
                                    <a href="{{ route('gmail.oauth.reconnect', $account) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        Reconnect
                                    </a>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Account Statistics (Initially Hidden) -->
                        <div id="stats-{{ $account->id }}" class="mt-6 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white" id="total-emails-{{ $account->id }}">-</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Emails</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white" id="unread-emails-{{ $account->id }}">-</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Unread Emails</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white" id="sync-frequency-{{ $account->id }}">-</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Sync Frequency (min)</div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white" id="auto-sync-{{ $account->id }}">-</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Auto Sync</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Connect Gmail Modal -->
<div id="connectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Connect Gmail Account</h3>
                <button onclick="closeConnectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('gmail.oauth.connect') }}" method="GET" id="connectForm">
                <div class="space-y-4">
                    @if(auth()->user()->teams ?? false)
                    <div>
                        <label for="team_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Team (Optional)
                        </label>
                        <select name="team_id" id="team_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Personal Account</option>
                            @foreach(auth()->user()->teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div>
                        <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Account Visibility
                        </label>
                        <select name="visibility" id="visibility" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="private">Private (Only me)</option>
                            <option value="team">Team Visible</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.283 10.356h-8.327v3.451h4.792c-.446 2.193-2.313 3.453-4.792 3.453a5.27 5.27 0 0 1-5.279-5.28 5.27 5.27 0 0 1 5.279-5.279c1.259 0 2.397.447 3.29 1.178l2.6-2.599c-1.584-1.381-3.615-2.233-5.89-2.233a8.908 8.908 0 0 0-8.934 8.934 8.907 8.907 0 0 0 8.934 8.934c4.467 0 8.529-3.249 8.529-8.934 0-.528-.081-1.097-.202-1.625z"/>
                        </svg>
                        Connect with Google
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sync Settings Modal -->
<div id="syncSettingsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Sync Settings</h3>
                <button onclick="closeSyncSettingsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="syncSettingsForm">
                <input type="hidden" id="settingsAccountId" value="">
                
                <div class="space-y-6">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" id="autoSyncEnabled" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enable automatic sync</span>
                        </label>
                    </div>
                    
                    <div>
                        <label for="syncFrequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sync Frequency (minutes)
                        </label>
                        <select id="syncFrequency" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="5">Every 5 minutes</option>
                            <option value="15">Every 15 minutes</option>
                            <option value="30">Every 30 minutes</option>
                            <option value="60">Every hour</option>
                            <option value="240">Every 4 hours</option>
                        </select>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Sync Options</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" id="syncInbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Sync inbox messages</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="syncSent" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Sync sent messages</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="syncDrafts" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Sync draft messages</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="autoCreateContacts" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Automatically create contacts from emails</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label for="daysBackSync" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Initial sync period (days)
                        </label>
                        <select id="daysBackSync" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="7">Last 7 days</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="365">Last year</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium">
                        Save Settings
                    </button>
                    <button type="button" onclick="closeSyncSettingsModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 px-4 rounded-lg font-medium">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openConnectModal() {
    document.getElementById('connectModal').classList.remove('hidden');
}

function closeConnectModal() {
    document.getElementById('connectModal').classList.add('hidden');
}

function openSyncSettings(accountId) {
    document.getElementById('settingsAccountId').value = accountId;
    
    // Load current settings
    fetch(`/gmail-oauth/${accountId}/status`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('autoSyncEnabled').checked = data.auto_sync_enabled;
            document.getElementById('syncFrequency').value = data.sync_frequency;
            
            // Load sync settings if available
            if (data.sync_settings) {
                document.getElementById('syncInbox').checked = data.sync_settings.sync_inbox || false;
                document.getElementById('syncSent').checked = data.sync_settings.sync_sent || false;
                document.getElementById('syncDrafts').checked = data.sync_settings.sync_drafts || false;
                document.getElementById('autoCreateContacts').checked = data.sync_settings.auto_create_contacts || false;
                document.getElementById('daysBackSync').value = data.sync_settings.days_back_initial_sync || 30;
            }
            
            document.getElementById('syncSettingsModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading settings:', error);
            showToast('Failed to load sync settings', 'error');
        });
}

function closeSyncSettingsModal() {
    document.getElementById('syncSettingsModal').classList.add('hidden');
}

function loadAccountStats(accountId) {
    const statsDiv = document.getElementById(`stats-${accountId}`);
    
    if (statsDiv.classList.contains('hidden')) {
        // Show and load stats
        fetch(`/gmail-oauth/${accountId}/status`)
            .then(response => response.json())
            .then(data => {
                document.getElementById(`total-emails-${accountId}`).textContent = data.total_emails || 0;
                document.getElementById(`unread-emails-${accountId}`).textContent = data.unread_emails || 0;
                document.getElementById(`sync-frequency-${accountId}`).textContent = data.sync_frequency || '-';
                document.getElementById(`auto-sync-${accountId}`).textContent = data.auto_sync_enabled ? 'Enabled' : 'Disabled';
                
                statsDiv.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error loading stats:', error);
                showToast('Failed to load account statistics', 'error');
            });
    } else {
        // Hide stats
        statsDiv.classList.add('hidden');
    }
}

function disconnectAccount(accountId) {
    if (!confirm('Are you sure you want to disconnect this Gmail account? This will stop all syncing but preserve existing data.')) {
        return;
    }
    
    fetch(`/gmail-oauth/${accountId}/disconnect`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.error || 'Failed to disconnect account', 'error');
        }
    })
    .catch(error => {
        console.error('Error disconnecting account:', error);
        showToast('Failed to disconnect account', 'error');
    });
}

// Handle sync settings form submission
document.getElementById('syncSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const accountId = document.getElementById('settingsAccountId').value;
    const formData = {
        auto_sync_enabled: document.getElementById('autoSyncEnabled').checked,
        sync_frequency_minutes: parseInt(document.getElementById('syncFrequency').value),
        sync_settings: {
            sync_inbox: document.getElementById('syncInbox').checked,
            sync_sent: document.getElementById('syncSent').checked,
            sync_drafts: document.getElementById('syncDrafts').checked,
            auto_create_contacts: document.getElementById('autoCreateContacts').checked,
            days_back_initial_sync: parseInt(document.getElementById('daysBackSync').value)
        }
    };
    
    fetch(`/gmail-oauth/${accountId}/sync-settings`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeSyncSettingsModal();
        } else {
            showToast(data.error || 'Failed to update sync settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating settings:', error);
        showToast('Failed to update sync settings', 'error');
    });
});

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-600 text-white' :
        type === 'error' ? 'bg-red-600 text-white' :
        'bg-blue-600 text-white'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endpush
@endsection