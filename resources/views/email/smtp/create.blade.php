@extends('layouts.app')

@section('title', 'Add SMTP Configuration')

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
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Add SMTP Configuration</h1>
                    <p class="text-gray-600 dark:text-gray-400">Configure a new SMTP server for email delivery</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <!-- Display Errors and Success Messages -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-t-xl">
                    <div class="font-medium">There were some problems with your input:</div>
                    <ul class="mt-1 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-t-xl">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-t-xl">
                    {{ session('success') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('smtp-configs.store') }}" x-data="smtpForm()">
                @csrf
                
                <div class="p-6">
                    <!-- Provider Selection -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Choose Provider</label>
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
                                       value="{{ old('name') }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                       placeholder="My SMTP Server">
                                @error('name')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="from_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Email</label>
                                <input type="email" 
                                       name="from_email" 
                                       id="from_email" 
                                       value="{{ old('from_email') }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                       placeholder="noreply@example.com">
                                @error('from_email')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Name</label>
                                <input type="text" 
                                       name="from_name" 
                                       id="from_name" 
                                       value="{{ old('from_name') }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                       placeholder="Your Company Name">
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
                                       value="{{ old('host') }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                       placeholder="smtp.example.com">
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
                                           value="{{ old('port', 587) }}"
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
                                        <option value="tls" {{ old('encryption') === 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ old('encryption') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        <option value="none" {{ old('encryption') === 'none' ? 'selected' : '' }}>None</option>
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
                                       value="{{ old('username') }}"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                       placeholder="your-email@example.com">
                                @error('username')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                       placeholder="Your password or app password">
                                @error('password')
                                    <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
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
                                       value="{{ old('daily_limit', 500) }}"
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
                                       value="{{ old('hourly_limit', 50) }}"
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
                                       value="{{ old('priority', 10) }}"
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
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700">
                                <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Activate this SMTP configuration immediately
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-xl flex items-center justify-between">
                    <a href="{{ route('smtp-configs.index') }}" 
                       class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </a>
                    <div class="flex items-center gap-3">
                        <button type="button" 
                                @click="testConnection()"
                                class="px-4 py-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 border border-blue-600 dark:border-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900 transition-colors">
                            Test Connection
                        </button>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                            Save Configuration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function smtpForm() {
    return {
        provider: 'custom',
        host: '',
        port: 587,
        encryption: 'tls',
        daily_limit: 500,
        hourly_limit: 50,

        selectProvider(provider) {
            this.provider = provider;
            this.loadProviderSettings(provider);
        },

        loadProviderSettings(provider) {
            const settings = {
                gmail: {
                    host: 'smtp.gmail.com',
                    port: 587,
                    encryption: 'tls',
                    daily_limit: 500,
                    hourly_limit: 50
                },
                outlook: {
                    host: 'smtp-mail.outlook.com',
                    port: 587,
                    encryption: 'tls',
                    daily_limit: 300,
                    hourly_limit: 30
                },
                sendgrid: {
                    host: 'smtp.sendgrid.net',
                    port: 587,
                    encryption: 'tls',
                    daily_limit: 40000,
                    hourly_limit: 4000
                },
                custom: {
                    host: '',
                    port: 587,
                    encryption: 'tls',
                    daily_limit: 500,
                    hourly_limit: 50
                }
            };

            if (settings[provider]) {
                Object.assign(this, settings[provider]);
            }
        },

        testConnection() {
            // Basic validation
            if (!this.host || !this.port || !document.getElementById('username').value || !document.getElementById('password').value) {
                alert('Please fill in all server settings before testing the connection.');
                return;
            }

            alert('Connection test functionality will be implemented after saving the configuration.');
        }
    }
}

// Debug form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form is being submitted...');
            console.log('Form data:', new FormData(form));
            
            // Check if all required fields are filled
            const requiredFields = form.querySelectorAll('[required]');
            let hasEmptyRequired = false;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    console.log('Empty required field:', field.name || field.id);
                    hasEmptyRequired = true;
                }
            });
            
            if (hasEmptyRequired) {
                console.log('Form has empty required fields, submission may be blocked.');
            }
        });
    }
});
</script>
@endsection
