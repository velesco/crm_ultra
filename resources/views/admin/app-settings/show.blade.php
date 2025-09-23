@extends('layouts.app')

@section('title', 'App Setting Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    App Setting Details
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Configuration setting: <strong>{{ $appSetting->key }}</strong></p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <a href="{{ route('admin.app-settings.edit', $appSetting->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Setting
                </a>
                <a href="{{ route('admin.app-settings.index', ['category' => $appSetting->category]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Setting Details -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $appSetting->label }}</h3>
                <div class="flex items-center space-x-2">
                    @if($appSetting->is_env_synced)
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        ENV SYNCED
                    </span>
                    @endif
                    @if($appSetting->is_encrypted)
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        ENCRYPTED
                    </span>
                    @endif
                    @if($appSetting->is_active)
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded">
                        ACTIVE
                    </span>
                    @else
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 rounded">
                        INACTIVE
                    </span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Basic Information -->
                <div class="space-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Setting Key</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded">{{ $appSetting->key }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Display Label</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appSetting->label }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                {{ ucfirst($appSetting->category) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data Type</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ ucfirst($appSetting->type) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sort Order</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appSetting->sort_order ?? 0 }}</dd>
                    </div>
                </div>

                <!-- Value and Configuration -->
                <div class="space-y-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Value</dt>
                        <dd class="mt-1">
                            @if($appSetting->is_encrypted)
                            <div class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded border-l-4 border-yellow-400">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <span class="font-mono">••••••••</span>
                                    <span class="ml-2">(encrypted)</span>
                                </div>
                            </div>
                            @elseif($appSetting->type === 'json')
                            <pre class="text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded overflow-x-auto">{{ is_array($appSetting->value) ? json_encode($appSetting->value, JSON_PRETTY_PRINT) : $appSetting->value }}</pre>
                            @elseif($appSetting->type === 'boolean')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $appSetting->value ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $appSetting->value ? 'True' : 'False' }}
                            </span>
                            @else
                            <span class="text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded break-all">{{ $appSetting->value ?: '(empty)' }}</span>
                            @endif
                        </dd>
                    </div>

                    @if($appSetting->is_env_synced && $appSetting->env_key)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Environment Variable</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded">{{ $appSetting->env_key }}</dd>
                    </div>
                    @endif

                    @if($appSetting->description)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $appSetting->description }}</dd>
                    </div>
                    @endif

                    @if($appSetting->validation_rules)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Validation Rules</dt>
                        <dd class="mt-1">
                            <div class="bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded">
                                @foreach($appSetting->validation_rules as $rule)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded mr-1 mb-1">{{ $rule }}</span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                    @endif

                    @if($appSetting->options)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Options</dt>
                        <dd class="mt-1">
                            <pre class="text-xs text-gray-900 dark:text-white font-mono bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded overflow-x-auto">{{ json_encode($appSetting->options, JSON_PRETTY_PRINT) }}</pre>
                        </dd>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Metadata -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Metadata</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ $appSetting->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white">{{ $appSetting->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Setting ID</dt>
                        <dd class="mt-1 text-gray-900 dark:text-white font-mono">#{{ $appSetting->id }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
            <div class="flex space-x-2">
                <a href="{{ route('admin.app-settings.edit', $appSetting->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Setting
                </a>
                @if($appSetting->is_env_synced)
                <button type="button" id="testConnection" class="inline-flex items-center px-4 py-2 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Test Configuration
                </button>
                @endif
            </div>
            
            <form method="POST" action="{{ route('admin.app-settings.destroy', $appSetting->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this setting? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete Setting
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#testConnection').on('click', function() {
        const button = $(this);
        const originalText = button.html();
        
        // Show loading state
        button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Testing...');
        
        $.ajax({
            url: '{{ route("admin.app-settings.test-connection", $appSetting->category) }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                button.prop('disabled', false).html(originalText);
                showNotification(response.message || 'Connection test completed successfully!', 'success');
            },
            error: function(xhr) {
                button.prop('disabled', false).html(originalText);
                const message = xhr.responseJSON?.message || 'Connection test failed';
                showNotification(message, 'error');
            }
        });
    });

    function showNotification(message, type) {
        const bgColor = type === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
        const iconColor = type === 'success' ? 'text-green-400' : 'text-red-400';
        const textColor = type === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200';
        const iconPath = type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        
        const notification = $(`
            <div class="mb-6 ${bgColor} border rounded-md p-4" style="position: fixed; top: 20px; right: 20px; z-index: 1000; max-width: 400px;">
                <div class="flex">
                    <svg class="w-5 h-5 ${iconColor} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"></path>
                    </svg>
                    <p class="text-sm ${textColor}">${message}</p>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        setTimeout(() => {
            notification.fadeOut(() => notification.remove());
        }, 5000);
    }
});
</script>
@endsection
