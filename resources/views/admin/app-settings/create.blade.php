@extends('layouts.app')

@section('title', 'Create App Setting')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create App Setting
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new application configuration setting</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('admin.app-settings.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Validation errors occurred:</h3>
                <ul class="mt-1 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Create Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Setting Configuration</h3>
        </div>
        
        <form method="POST" action="{{ route('admin.app-settings.store') }}" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Key -->
                <div class="md:col-span-2">
                    <label for="key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Setting Key <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="key" 
                           name="key" 
                           value="{{ old('key') }}" 
                           placeholder="e.g., google.client_id, app.name" 
                           required 
                           pattern="[a-z0-9._-]+"
                           class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unique identifier using only lowercase letters, numbers, dots, dashes, and underscores</p>
                </div>

                <!-- Label -->
                <div>
                    <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Display Label <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="label" 
                           name="label" 
                           value="{{ old('label') }}" 
                           placeholder="e.g., Google Client ID, Application Name" 
                           required 
                           maxlength="255"
                           class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category" 
                            name="category" 
                            required 
                            class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a category</option>
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Data Type <span class="text-red-500">*</span>
                    </label>
                    <select id="type" 
                            name="type" 
                            required 
                            class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select data type</option>
                        @foreach($types as $type)
                        <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Order -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Sort Order
                    </label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           value="{{ old('sort_order', 0) }}" 
                           min="0"
                           class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Lower numbers appear first in the list</p>
                </div>

                <!-- Value -->
                <div class="md:col-span-2">
                    <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Default Value
                    </label>
                    <div id="value-input-container">
                        <!-- Will be populated by JavaScript based on type selection -->
                        <input type="text" 
                               id="value" 
                               name="value" 
                               value="{{ old('value') }}" 
                               placeholder="Enter default value..." 
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Describe what this setting controls..." 
                              class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                </div>

                <!-- Environment Sync -->
                <div class="md:col-span-2">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   id="is_env_synced" 
                                   name="is_env_synced" 
                                   value="1" 
                                   {{ old('is_env_synced') ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_env_synced" class="font-medium text-gray-700 dark:text-gray-300">Sync to Environment File</label>
                            <p class="text-gray-500 dark:text-gray-400">Automatically update the .env file when this setting changes</p>
                        </div>
                    </div>
                </div>

                <!-- Environment Key -->
                <div class="md:col-span-2" id="env-key-container" style="display: none;">
                    <label for="env_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Environment Variable Name
                    </label>
                    <input type="text" 
                           id="env_key" 
                           name="env_key" 
                           value="{{ old('env_key') }}" 
                           placeholder="e.g., GOOGLE_CLIENT_ID, APP_NAME" 
                           pattern="[A-Z0-9_]+"
                           class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Use uppercase letters, numbers and underscores only. Leave empty to auto-generate from key.</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.app-settings.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Create Setting
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Handle type change to update input field
    $('#type').on('change', function() {
        updateValueInput();
    });

    // Handle env sync checkbox
    $('#is_env_synced').on('change', function() {
        if ($(this).is(':checked')) {
            $('#env-key-container').show();
            generateEnvKey();
        } else {
            $('#env-key-container').hide();
        }
    });

    // Auto-generate env key from setting key
    $('#key').on('input', function() {
        if ($('#is_env_synced').is(':checked')) {
            generateEnvKey();
        }
    });

    // Initialize on page load
    if ($('#is_env_synced').is(':checked')) {
        $('#env-key-container').show();
    }
    updateValueInput();

    function updateValueInput() {
        const type = $('#type').val();
        const container = $('#value-input-container');
        const currentValue = $('#value').val();
        
        let inputHtml = '';
        
        switch (type) {
            case 'boolean':
                inputHtml = '<select id="value" name="value" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">' +
                    '<option value="1"' + (currentValue === '1' ? ' selected' : '') + '>True</option>' +
                    '<option value="0"' + (currentValue === '0' || currentValue === '' ? ' selected' : '') + '>False</option>' +
                '</select>';
                break;
                
            case 'integer':
                inputHtml = '<input type="number" id="value" name="value" value="' + currentValue + '" placeholder="Enter integer value..." class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">';
                break;
                
            case 'float':
                inputHtml = '<input type="number" id="value" name="value" value="' + currentValue + '" step="0.01" placeholder="Enter decimal value..." class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">';
                break;
                
            case 'json':
                inputHtml = '<textarea id="value" name="value" rows="4" placeholder="Enter JSON data..." class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm">' + currentValue + '</textarea>' +
                    '<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enter valid JSON format</p>';
                break;
                
            case 'encrypted':
                inputHtml = '<input type="password" id="value" name="value" value="' + currentValue + '" placeholder="Enter sensitive value (will be encrypted)..." class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">' +
                    '<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This value will be encrypted when stored</p>';
                break;
                
            default: // string
                inputHtml = '<input type="text" id="value" name="value" value="' + currentValue + '" placeholder="Enter text value..." class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">';
        }
        
        container.html(inputHtml);
    }

    function generateEnvKey() {
        const key = $('#key').val();
        if (key && $('#env_key').val() === '') {
            const envKey = key.toUpperCase().replace(/[.-]/g, '_');
            $('#env_key').val(envKey);
        }
    }
});
</script>
@endsection
