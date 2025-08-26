@extends('layouts.app')

@section('title', $smtpConfig->name . ' - SMTP Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('smtp-configs.index') }}" 
                       class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $smtpConfig->name }}</h1>
                        <div class="flex items-center gap-4 mt-2">
                            <p class="text-gray-600 dark:text-gray-400">{{ $smtpConfig->from_email }}</p>
                            @if($smtpConfig->is_active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="testConnection('{{ route('smtp-configs.test', $smtpConfig) }}')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Test Connection
                    </button>
                    <a href="{{ route('smtp-configs.edit', $smtpConfig) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Edit
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Configuration -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Server Details -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Server Configuration</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">SMTP Host</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $smtpConfig->host }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Port & Encryption</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $smtpConfig->port }} ({{ strtoupper($smtpConfig->encryption) }})</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Username</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $smtpConfig->username }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Provider</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-white capitalize">
                                {{ $smtpConfig->provider ?: 'Custom SMTP' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">From Name</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $smtpConfig->from_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Priority</label>
                            <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $smtpConfig->priority }}</p>
                        </div>
                    </div>
                </div>

                <!-- Usage Limits -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Usage Limits</h2>
                        <form method="POST" action="{{ route('smtp-configs.reset-counters', $smtpConfig) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200"
                                    onclick="return confirm('Are you sure you want to reset the counters?')">
                                Reset Counters
                            </button>
                        </form>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Daily Limit -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Daily Usage</label>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $smtpConfig->sent_today }} / {{ number_format($smtpConfig->daily_limit) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div class="bg-blue-600 dark:bg-blue-400 h-3 rounded-full transition-all duration-300" 
                                     style="width: {{ $smtpConfig->daily_limit > 0 ? min(($smtpConfig->sent_today / $smtpConfig->daily_limit) * 100, 100) : 0 }}%"></div>
                            </div>
                            @if($smtpConfig->sent_today >= $smtpConfig->daily_limit)
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">Daily limit reached</p>
                            @endif
                        </div>

                        <!-- Hourly Limit -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Hourly Usage</label>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $smtpConfig->sent_this_hour }} / {{ number_format($smtpConfig->hourly_limit) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                <div class="bg-green-600 dark:bg-green-400 h-3 rounded-full transition-all duration-300" 
                                     style="width: {{ $smtpConfig->hourly_limit > 0 ? min(($smtpConfig->sent_this_hour / $smtpConfig->hourly_limit) * 100, 100) : 0 }}%"></div>
                            </div>
                            @if($smtpConfig->sent_this_hour >= $smtpConfig->hourly_limit)
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">Hourly limit reached</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Recent Activity</h2>
                    
                    @if($smtpConfig->emailCampaigns && $smtpConfig->emailCampaigns->count() > 0)
                        <div class="space-y-4">
                            @foreach($smtpConfig->emailCampaigns->take(5) as $campaign)
                                <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div>
                                        <h3 class="font-medium text-gray-900 dark:text-white">{{ $campaign->name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $campaign->sent_count }} emails sent • {{ $campaign->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $campaign->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                               ($campaign->status === 'sending' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                            {{ ucfirst($campaign->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 8l7.89 7.89c.39.39 1.02.39 1.41 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <p class="mt-4 text-gray-500 dark:text-gray-400">No email campaigns have used this SMTP configuration yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Statistics -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistics</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Sent</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($smtpConfig->total_sent) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Sent Today</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($smtpConfig->sent_today) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Sent This Hour</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($smtpConfig->sent_this_hour) }}</span>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Last Used</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $smtpConfig->last_used_at ? $smtpConfig->last_used_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Created</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $smtpConfig->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('smtp-configs.toggle', $smtpConfig) }}" class="w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-full text-left px-3 py-2 text-sm rounded-md 
                                           {{ $smtpConfig->is_active ? 
                                              'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900' : 
                                              'text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900' }} 
                                           transition-colors">
                                {{ $smtpConfig->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('smtp-configs.duplicate', $smtpConfig) }}" class="w-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full text-left px-3 py-2 text-sm text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900 rounded-md transition-colors">
                                Duplicate Configuration
                            </button>
                        </form>

                        <hr class="border-gray-200 dark:border-gray-700">
                        
                        <form method="POST" action="{{ route('smtp-configs.destroy', $smtpConfig) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this SMTP configuration? This action cannot be undone.')" 
                              class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 rounded-md transition-colors">
                                Delete Configuration
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Health Status -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Health Status</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full {{ $smtpConfig->is_active ? 'bg-green-400' : 'bg-gray-400' }} mr-2"></div>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $smtpConfig->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Daily Limit</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full {{ $smtpConfig->sent_today >= $smtpConfig->daily_limit ? 'bg-red-400' : 'bg-green-400' }} mr-2"></div>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $smtpConfig->sent_today >= $smtpConfig->daily_limit ? 'Exceeded' : 'OK' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Hourly Limit</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full {{ $smtpConfig->sent_this_hour >= $smtpConfig->hourly_limit ? 'bg-red-400' : 'bg-green-400' }} mr-2"></div>
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $smtpConfig->sent_this_hour >= $smtpConfig->hourly_limit ? 'Exceeded' : 'OK' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Connection Modal -->
<div id="testModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full mx-4">
        <div class="flex items-center justify-center mb-4">
            <div id="testSpinner" class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 hidden"></div>
            <div id="testSuccess" class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center hidden">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div id="testError" class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center hidden">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>
        <p id="testMessage" class="text-center text-gray-600 dark:text-gray-400 mb-4">Testing SMTP connection...</p>
        <button id="testClose" onclick="closeTestModal()" class="w-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 py-2 px-4 rounded-lg hidden">
            Close
        </button>
    </div>
</div>

<script>
function testConnection(url) {
    document.getElementById('testModal').classList.remove('hidden');
    document.getElementById('testSpinner').classList.remove('hidden');
    document.getElementById('testSuccess').classList.add('hidden');
    document.getElementById('testError').classList.add('hidden');
    document.getElementById('testMessage').textContent = 'Testing SMTP connection...';
    document.getElementById('testClose').classList.add('hidden');

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('testSpinner').classList.add('hidden');
        
        if (data.success) {
            document.getElementById('testSuccess').classList.remove('hidden');
            document.getElementById('testMessage').textContent = data.message;
        } else {
            document.getElementById('testError').classList.remove('hidden');
            document.getElementById('testMessage').textContent = data.message;
        }
        
        document.getElementById('testClose').classList.remove('hidden');
    })
    .catch(error => {
        document.getElementById('testSpinner').classList.add('hidden');
        document.getElementById('testError').classList.remove('hidden');
        document.getElementById('testMessage').textContent = 'Connection test failed. Please try again.';
        document.getElementById('testClose').classList.remove('hidden');
    });
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}
</script>
@endsection
