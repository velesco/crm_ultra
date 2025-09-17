@extends('layouts.app')

@section('title', 'Team Gmail Management')

@section('content')
<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Team Gmail Management</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Manage Gmail accounts, permissions, and team visibility settings</p>
        </div>
        
        <div class="flex space-x-4">
            <button onclick="exportTeamSettings()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Settings
            </button>
            <button onclick="openTeamSettingsModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Team Settings
            </button>
        </div>
    </div>

    <!-- Team Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_accounts'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Accounts</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_accounts'] }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Active Accounts</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h3a1 1 0 011 1v2h4a1 1 0 011 1v2a1 1 0 01-1 1h-1v9a2 2 0 01-2 2H8a2 2 0 01-2-2V8H5a1 1 0 01-1-1V5a1 1 0 011-1h2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_emails']) }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Emails</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM3 12h12a9 9 0 11-9 9H3V12z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['unread_emails']) }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Unread Emails</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Gmail Accounts -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-8">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Team Gmail Accounts</h2>
                <div class="flex items-center space-x-4">
                    <select id="visibilityFilter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="">All Visibility</option>
                        <option value="private">Private</option>
                        <option value="team">Team</option>
                        <option value="public">Public</option>
                    </select>
                    <select id="statusFilter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="token_expired">Needs Auth</option>
                        <option value="disconnected">Disconnected</option>
                    </select>
                </div>
            </div>

            @if($teamAccounts->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No team Gmail accounts</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">Team members haven't connected any Gmail accounts yet or haven't made them visible to the team.</p>
                </div>
            @else
                <!-- Accounts List -->
                <div class="space-y-4" id="accountsList">
                    @foreach($teamAccounts as $account)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6" 
                         data-visibility="{{ $account->visibility }}" 
                         data-status="{{ $account->status }}">
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
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Owner: {{ $account->user->name }}</span>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $account->visibility === 'team' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                               ($account->visibility === 'public' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200') }}">
                                            {{ ucfirst($account->visibility) }}
                                        </span>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $account->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                               ($account->status === 'token_expired' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                            {{ $account->status === 'token_expired' ? 'Needs Auth' : ucfirst($account->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Actions -->
                            <div class="flex items-center space-x-2">
                                @if($account->user_id === auth()->id() || auth()->user()->can('manage-team', $currentTeam))
                                    <button onclick="openVisibilityModal({{ $account->id }}, '{{ $account->visibility }}')" 
                                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Change Visibility">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    
                                    <button onclick="openPermissionsModal({{ $account->id }})" 
                                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Manage Permissions">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </button>
                                @endif

                                <button onclick="viewAccountDetails({{ $account->id }})" 
                                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="View Details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Account Stats -->
                        <div class="mt-4 grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($account->emails()->count()) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total Emails</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($account->getUnreadEmailsCount()) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Unread</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ $account->last_sync_at ? $account->last_sync_at->diffForHumans() : 'Never' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Last Sync</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Team Members -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Team Members & Gmail Access</h2>
            
            <div class="space-y-4">
                @foreach($teamMembers as $member)
                <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-medium text-sm">{{ substr($member->name, 0, 2) }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->googleAccounts->count() }} accounts</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->googleAccounts->where('status', 'active')->count() }} active</p>
                        </div>
                        
                        @if($member->id !== auth()->id() && auth()->user()->can('manage-team', $currentTeam))
                            <button onclick="manageMemberAccess({{ $member->id }})" 
                                    class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md">
                                Manage Access
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modals will be added via JavaScript -->
@endsection

@push('scripts')
<script>
// Filter functionality
document.getElementById('visibilityFilter').addEventListener('change', filterAccounts);
document.getElementById('statusFilter').addEventListener('change', filterAccounts);

function filterAccounts() {
    const visibilityFilter = document.getElementById('visibilityFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    document.querySelectorAll('#accountsList > div').forEach(account => {
        const visibility = account.dataset.visibility;
        const status = account.dataset.status;
        
        const showVisibility = !visibilityFilter || visibility === visibilityFilter;
        const showStatus = !statusFilter || status === statusFilter;
        
        account.style.display = (showVisibility && showStatus) ? 'block' : 'none';
    });
}

// Team management functions
function openVisibilityModal(accountId, currentVisibility) {
    // Implementation for visibility modal
    console.log('Open visibility modal for account', accountId, 'current:', currentVisibility);
}

function openPermissionsModal(accountId) {
    // Implementation for permissions modal
    console.log('Open permissions modal for account', accountId);
}

function viewAccountDetails(accountId) {
    // Implementation for account details
    console.log('View details for account', accountId);
}

function openTeamSettingsModal() {
    // Implementation for team settings modal
    console.log('Open team settings modal');
}

function exportTeamSettings() {
    fetch('/api/gmail/team/export-settings', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `team-gmail-settings-${new Date().getTime()}.json`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    })
    .catch(error => {
        console.error('Error exporting settings:', error);
        window.CRM.showToast('Failed to export settings', 'error');
    });
}

function manageMemberAccess(memberId) {
    // Implementation for member access management
    console.log('Manage access for member', memberId);
}
</script>
@endpush
