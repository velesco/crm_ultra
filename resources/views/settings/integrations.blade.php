@extends('layouts.app')

@section('title', 'Integrations Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Integrations Management</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage your external service integrations and API connections</p>
        </div>
        <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Integration
        </button>
    </div>

    <!-- Integration Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Email Services -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Email Services</h3>
                </div>
                
                <div class="space-y-3">
                    <!-- SMTP Configurations -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">SMTP Servers</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $smtpCount ?? 0 }} configured</p>
                            </div>
                        </div>
                        <a href="{{ route('smtp-configs.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Configure</a>
                    </div>

                    <!-- SendGrid -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">SendGrid</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Not connected</p>
                            </div>
                        </div>
                        <button class="text-gray-600 hover:text-gray-700 text-sm font-medium">Connect</button>
                    </div>

                    <!-- Mailgun -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Mailgun</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Not connected</p>
                            </div>
                        </div>
                        <button class="text-gray-600 hover:text-gray-700 text-sm font-medium">Connect</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMS Services -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">SMS Services</h3>
                </div>
                
                <div class="space-y-3">
                    <!-- Custom SMS Providers -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-{{ ($integrations['sms']['status'] ?? 'inactive') === 'active' ? 'green' : 'gray' }}-500 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Custom SMS Server</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ ($integrations['sms']['status'] ?? 'inactive') === 'active' ? 'Connected' : 'Not connected' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('sms.providers.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Configure</a>
                    </div>

                    <!-- Vonage -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-{{ $vonageStatus === 'active' ? 'green' : 'gray' }}-500 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Vonage (Nexmo)</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $vonageStatus === 'active' ? 'Connected' : 'Not connected' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('sms.providers.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Configure</a>
                    </div>

                    <!-- Orange SMS -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-{{ $orangeStatus === 'active' ? 'green' : 'gray' }}-500 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Orange SMS</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $orangeStatus === 'active' ? 'Connected' : 'Not connected' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('sms.providers.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Configure</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Integrations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Other Services</h3>
                </div>
                
                <div class="space-y-3">
                    <!-- WhatsApp -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-{{ $whatsappStatus === 'connected' ? 'green' : 'gray' }}-500 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">WhatsApp</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $whatsappStatus === 'connected' ? 'Connected' : 'Not connected' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('whatsapp.sessions.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Configure</a>
                    </div>

                    <!-- Google Sheets -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-{{ $googleSheetsCount > 0 ? 'green' : 'gray' }}-500 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Google Sheets</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $googleSheetsCount ?? 0 }} integrations</p>
                            </div>
                        </div>
                        <a href="{{ route('google-sheets.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Configure</a>
                    </div>

                    <!-- Google OAuth -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-{{ $googleOauthStatus === 'configured' ? 'green' : 'gray' }}-500 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Google OAuth</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $googleOauthStatus === 'configured' ? 'Configured' : 'Not configured' }}</p>
                            </div>
                        </div>
                        <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">Configure</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Webhook Configuration -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Webhook Configuration</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Configure webhook endpoints for external service integrations</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- SMS Webhooks -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">SMS Delivery Webhooks</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Twilio Webhook</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ url('/webhooks/sms/twilio') }}</p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-500 transition-colors" onclick="copyToClipboard('{{ url('/webhooks/sms/twilio') }}')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Vonage Webhook</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ url('/webhooks/sms/vonage') }}</p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-500 transition-colors" onclick="copyToClipboard('{{ url('/webhooks/sms/vonage') }}')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Webhooks -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">WhatsApp Webhooks</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Message Webhook</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ url('/webhooks/whatsapp/message') }}</p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-500 transition-colors" onclick="copyToClipboard('{{ url('/webhooks/whatsapp/message') }}')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Status Webhook</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ url('/webhooks/whatsapp/status') }}</p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-500 transition-colors" onclick="copyToClipboard('{{ url('/webhooks/whatsapp/status') }}')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Endpoints -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">API Endpoints</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Available API endpoints for external integrations</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">GET</span>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Contacts API</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">{{ url('/api/contacts') }}</p>
                    </div>
                    <a href="{{ route('settings.api-keys') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">API Keys</a>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">POST</span>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Send Email API</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">{{ url('/api/email/send') }}</p>
                    </div>
                    <a href="{{ route('settings.api-keys') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">API Keys</a>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">POST</span>
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Send SMS API</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">{{ url('/api/sms/send') }}</p>
                    </div>
                    <a href="{{ route('settings.api-keys') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">API Keys</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toast.textContent = 'Webhook URL copied to clipboard!';
        document.body.appendChild(toast);
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    });
}
</script>
@endsection
