@extends('layouts.app')

@section('title', 'App Settings Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    App Settings Management
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage application configuration and environment settings</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <a href="{{ route('admin.app-settings.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Setting
                </a>
                <button type="button" id="initializeDefaults" class="inline-flex items-center px-4 py-2 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Initialize Defaults
                </button>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Category Filters -->
    <div class="mb-6">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.app-settings.index', ['category' => 'all']) }}" 
               class="inline-flex items-center px-4 py-2 rounded-md font-medium transition-colors {{ $category === 'all' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                All Categories
            </a>
            @foreach($categories as $cat => $label)
            <a href="{{ route('admin.app-settings.index', ['category' => $cat]) }}" 
               class="inline-flex items-center px-4 py-2 rounded-md font-medium transition-colors {{ $category === $cat ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    <!-- Settings by Category -->
    @if($settings->isNotEmpty())
        @foreach($settings as $categoryKey => $categorySettings)
        <div class="mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $categories[$categoryKey] ?? ucfirst($categoryKey) }}
                        </h3>
                        <button type="button" class="bulk-update-btn inline-flex items-center px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors" data-category="{{ $categoryKey }}">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </div>
                
                <form class="bulk-form" data-category="{{ $categoryKey }}">
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($categorySettings as $setting)
                        <div class="px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <label class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $setting->label }}
                                        </label>
                                        @if($setting->is_env_synced)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded">
                                            ENV
                                        </span>
                                        @endif
                                        @if($setting->is_encrypted)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded">
                                            ENCRYPTED
                                        </span>
                                        @endif
                                    </div>
                                    
                                    @if($setting->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $setting->description }}</p>
                                    @endif
                                    
                                    <div class="max-w-md">
                                        @if($setting->type === 'boolean')
                                        <select name="settings[{{ $setting->key }}]" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            <option value="1" {{ $setting->value ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ !$setting->value ? 'selected' : '' }}>No</option>
                                        </select>
                                        @elseif($setting->type === 'json' || $setting->type === 'array')
                                        <textarea name="settings[{{ $setting->key }}]" rows="3" placeholder="Enter JSON data..." class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                        @elseif($setting->is_encrypted)
                                        <input type="password" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" placeholder="Enter {{ strtolower($setting->label) }}..." class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        @else
                                        <input type="{{ $setting->type === 'integer' || $setting->type === 'float' ? 'number' : 'text' }}" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" placeholder="Enter {{ strtolower($setting->label) }}..." class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-sm" {{ $setting->type === 'float' ? 'step=0.01' : '' }}>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4">
                                    <a href="{{ route('admin.app-settings.show', $setting->id) }}" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.app-settings.edit', $setting->id) }}" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.app-settings.destroy', $setting->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this setting?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8">
            <div class="text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Settings Found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">No application settings have been configured yet.</p>
                <div class="flex justify-center space-x-3">
                    <a href="{{ route('admin.app-settings.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add First Setting
                    </a>
                    <button type="button" id="initializeDefaultsEmpty" class="inline-flex items-center px-4 py-2 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Initialize Default Settings
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize defaults
    $('#initializeDefaults, #initializeDefaultsEmpty').on('click', function() {
        if (confirm('This will create default settings for all categories. Are you sure?')) {
            $.ajax({
                url: '{{ route("admin.app-settings.initialize-defaults") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
                }
            });
        }
    });

    // Bulk update settings
    $('.bulk-update-btn').on('click', function() {
        const category = $(this).data('category');
        const form = $(`.bulk-form[data-category="${category}"]`);
        const formData = new FormData(form[0]);
        formData.append('category', category);
        
        // Show loading state
        $(this).prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...');
        
        $.ajax({
            url: '{{ route("admin.app-settings.bulk-update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Reset button
                $(`.bulk-update-btn[data-category="${category}"]`).prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Save Changes');
                
                // Show success message
                showNotification('Settings updated successfully!', 'success');
            },
            error: function(xhr) {
                // Reset button
                $(`.bulk-update-btn[data-category="${category}"]`).prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Save Changes');
                
                showNotification('Error: ' + (xhr.responseJSON?.message || 'An error occurred'), 'error');
            }
        });
    });

    function showNotification(message, type) {
        let bgColor, iconColor, textColor, iconPath;
        
        if (type === 'success') {
            bgColor = 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
            iconColor = 'text-green-400';
            textColor = 'text-green-800 dark:text-green-200';
            iconPath = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
        } else {
            bgColor = 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
            iconColor = 'text-red-400';
            textColor = 'text-red-800 dark:text-red-200';
            iconPath = 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        }
        
        const notification = $('<div class="mb-6 ' + bgColor + ' border rounded-md p-4" style="position: fixed; top: 20px; right: 20px; z-index: 1000; max-width: 400px;">' +
            '<div class="flex">' +
                '<svg class="w-5 h-5 ' + iconColor + ' mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + iconPath + '"></path>' +
                '</svg>' +
                '<p class="text-sm ' + textColor + '">' + message + '</p>' +
            '</div>' +
        '</div>');
        
        $('body').append(notification);
        setTimeout(function() {
            notification.fadeOut(function() {
                notification.remove();
            });
        }, 5000);
    }
});
</script>
@endsection
