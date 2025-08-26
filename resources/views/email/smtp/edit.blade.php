@extends('layouts.app')

@section('title', 'Edit SMTP Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('smtp-configs.index') }}" 
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit SMTP Configuration</h1>
                    <p class="text-gray-600 dark:text-gray-400">Update {{ $smtpConfig->name }} settings</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <form method="POST" action="{{ route('smtp-configs.update', $smtpConfig) }}" x-data="smtpForm()">
                @csrf
                @method('PUT')
                
                <div class="p-6">
                    <!-- Provider Selection -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Provider</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <button type="button" 
                                    @click="selectProvider('gmail')"
                                    :class="provider === 'gmail' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900' : ''"
                                    class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Gmail</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Google Workspace</div>
                            </button>
                            <button type="button" 
                                    @click="selectProvider('outlook')"
                                    :class="provider === 'outlook' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900' : ''"
                                    class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Outlook</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Microsoft 365</div>
                            </button>
                            <button type="button" 
                                    @click="selectProvider('sendgrid')"
                                    :class="provider === 'sendgrid' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900' : ''"
                                    class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">SendGrid</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">High Volume</div>
                            </button>
                            <button type="button" 
                                    @click="selectProvider('custom')"
                                    :class="provider === 'custom' ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900' : ''"
                                    class="p-4 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Custom</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Other SMTP</div>
                            </button>
                        </div>
                        <input type="hidden" name="provider" :value="provider">
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Basic Information -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                                Basic Information
                            </h3>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Configuration Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name', $smtpConfig->name) }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('name')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Email</label>
                                <input type="email" 
                                       name="from_email" 
                                       id="from_email" 
                                       value="{{ old('from_email', $smtpConfig->from_email) }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('from_email')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Name</label>
                                <input type="text" 
                                       name="from_name" 
                                       id="from_name" 
                                       value="{{ old('from_name', $smtpConfig->from_name) }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('from_name')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Server Settings -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                                Server Settings
                            </h3>

                            <div>
                                <label for="host" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SMTP Host</label>
                                <input type="text" 
                                       name="host" 
                                       id="host" 
                                       x-model="host"
                                       value="{{ old('host', $smtpConfig->host) }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('host')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Port</label>
                                    <input type="number" 
                                           name="port" 
                                           id="port" 
                                           x-model="port"
                                           value="{{ old('port', $smtpConfig->port) }}"
                                           required
                                           min="1" 
                                           max="65535"
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    @error('port')
                                        <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Encryption</label>
                                    <select name="encryption" 
                                            id="encryption" 
                                            x-model="encryption"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                        <option value="tls" {{ old('encryption', $smtpConfig->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ old('encryption', $smtpConfig->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="none" {{ old('encryption', $smtpConfig->encryption) === 'none' ? 'selected' : '' }}>None</option>
                                    </select>
                                    @error('encryption')
                                        <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                                <input type="text" 
                                       name="username" 
                                       id="username" 
                                       value="{{ old('username', $smtpConfig->username) }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('username')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                       placeholder="Leave blank to keep current password">
                                @error('password')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank to keep current password</p>
                            </div>
                        </div>
                    </div>

                    <!-- Limits and Priority -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Limits & Settings</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="daily_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Daily Limit</label>
                                <input type="number" 
                                       name="daily_limit" 
                                       id="daily_limit" 
                                       x-model="daily_limit"
                                       value="{{ old('daily_limit', $smtpConfig->daily_limit) }}"
                                       required
                                       min="1" 
                                       max="50000"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('daily_limit')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="hourly_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hourly Limit</label>
                                <input type="number" 
                                       name="hourly_limit" 
                                       id="hourly_limit" 
                                       x-model="hourly_limit"
                                       value="{{ old('hourly_limit', $smtpConfig->hourly_limit) }}"
                                       required
                                       min="1" 
                                       max="5000"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('hourly_limit')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority (1-100)</label>
                                <input type="number" 
                                       name="priority" 
                                       id="priority" 
                                       value="{{ old('priority', $smtpConfig->priority) }}"
                                       required
                                       min="1" 
                                       max="100"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('priority')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Higher numbers = higher priority</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active" 
                                       {{ old('is_active', $smtpConfig->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Keep this SMTP configuration active
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Statistics -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Usage Statistics</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Sent Today</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $smtpConfig->sent_today }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Sent This Hour</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $smtpConfig->sent_this_hour }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Total Sent</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($smtpConfig->total_sent) }}</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Last Used</div>
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $smtpConfig->last_used_at ? $smtpConfig->last_used_at->diffForHumans() : 'Never' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('smtp-configs.index') }}" 
                           class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </a>
                        <a href="{{ route('smtp-configs.show', $smtpConfig) }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 px-4 py-2 rounded-lg border border-blue-600 dark:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900 transition-colors">
                            View Details
                        </a>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" 
                                onclick="testConnection('{{ route('smtp-configs.test', $smtpConfig) }}')"
                                class="px-4 py-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 border border-blue-600 dark:border-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900 transition-colors">
                            Test Connection
                        </button>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            Update Configuration
                        </button>
                    </div>
                </div>
            </form>
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
function smtpForm() {
    return {
        provider: '{{ old('provider', $smtpConfig->provider ?: 'custom') }}',
        host: '{{ old('host', $smtpConfig->host) }}',
        port: {{ old('port', $smtpConfig->port) }},
        encryption: '{{ old('encryption', $smtpConfig->encryption) }}',
        daily_limit: {{ old('daily_limit', $smtpConfig->daily_limit) }},
        hourly_limit: {{ old('hourly_limit', $smtpConfig->hourly_limit) }},

        selectProvider(provider) {
            this.provider = provider;
            // Don't auto-load settings on edit to preserve user data
        }
    }
}

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
