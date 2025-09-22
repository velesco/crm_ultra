@extends('layouts.app')

@section('title', 'Create Application Setting')
@section('page-title', 'Create Application Setting')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.form-input {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}
.form-label {
    @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1;
}
.form-help {
    @apply mt-1 text-sm text-gray-500 dark:text-gray-400;
}
.form-error {
    @apply mt-1 text-sm text-red-600 dark:text-red-400;
}
</style>
@endpush

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('admin.app-settings.index') }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Settings
    </a>
</div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                <i class="fas fa-plus mr-2 text-indigo-500"></i>
                Create New Setting
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Add a new application setting that can be managed through the interface.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.app-settings.store') }}" class="px-6 py-4 space-y-6">
            @csrf

            <!-- Key -->
            <div>
                <label for="key" class="form-label">
                    Setting Key <span class="text-red-500">*</span>
                </label>
                <input type="text" id="key" name="key" value="{{ old('key') }}" required
                       class="form-input" placeholder="e.g., google.client_id">
                <p class="form-help">
                    Unique identifier for the setting. Use dots for grouping (e.g., category.subcategory.key)
                </p>
                @error('key')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Label -->
            <div>
                <label for="label" class="form-label">
                    Label <span class="text-red-500">*</span>
                </label>
                <input type="text" id="label" name="label" value="{{ old('label') }}" required
                       class="form-input" placeholder="e.g., Google Client ID">
                <p class="form-help">Human-readable label for the setting</p>
                @error('label')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="form-label">
                    Category <span class="text-red-500">*</span>
                </label>
                <select id="category" name="category" class="form-input" required>
                    <option value="">Select Category</option>
                    <option value="google" {{ old('category') === 'google' ? 'selected' : '' }}>Google</option>
                    <option value="sms" {{ old('category') === 'sms' ? 'selected' : '' }}>SMS</option>
                    <option value="whatsapp" {{ old('category') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ old('category') === 'email' ? 'selected' : '' }}>Email</option>
                    <option value="database" {{ old('category') === 'database' ? 'selected' : '' }}>Database</option>
                    <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>General</option>
                </select>
                <p class="form-help">Category to group related settings</p>
                @error('category')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="form-label">
                    Type <span class="text-red-500">*</span>
                </label>
                <select id="type" name="type" class="form-input" onchange="handleTypeChange()" required>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
                <p class="form-help">Data type for the setting value</p>
                @error('type')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Value -->
            <div>
                <label for="value" class="form-label">Value</label>
                <div id="value-input-container">
                    <input type="text" id="value" name="value" value="{{ old('value') }}"
                           class="form-input" placeholder="Enter the setting value">
                </div>
                <p class="form-help">Default value for the setting</p>
                @error('value')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="3" 
                          class="form-input" placeholder="Describe what this setting controls">{{ old('description') }}</textarea>
                <p class="form-help">Optional description explaining the setting's purpose</p>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- .env Sync -->
            <div class="flex items-start">
                <input type="hidden" name="is_env_synced" value="0">
                <input type="checkbox" id="is_env_synced" name="is_env_synced" value="1"
                       {{ old('is_env_synced') ? 'checked' : '' }}
                       onchange="handleEnvSyncChange()"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1">
                <div class="ml-3">
                    <label for="is_env_synced" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sync to .env file
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Automatically update the .env file when this setting changes
                    </p>
                </div>
            </div>

            <!-- .env Key -->
            <div id="env-key-container" class="hidden">
                <label for="env_key" class="form-label">.env Key</label>
                <input type="text" id="env_key" name="env_key" value="{{ old('env_key') }}"
                       class="form-input" placeholder="e.g., GOOGLE_CLIENT_ID">
                <p class="form-help">Environment variable name (will be auto-generated if empty)</p>
                @error('env_key')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="form-label">Sort Order</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                       class="form-input">
                <p class="form-help">Order for displaying settings within the category (0 = first)</p>
                @error('sort_order')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.app-settings.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-save mr-2"></i>
                    Create Setting
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function handleTypeChange() {
    const type = document.getElementById('type').value;
    const valueContainer = document.getElementById('value-input-container');
    const currentValue = document.getElementById('value') ? document.getElementById('value').value : '';
    
    let inputHtml = '';
    
    switch(type) {
        case 'boolean':
            inputHtml = `
                <div class="flex items-center">
                    <input type="hidden" name="value" value="0">
                    <input type="checkbox" id="value" name="value" value="1" 
                           ${currentValue === '1' || currentValue === 'true' ? 'checked' : ''}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="value" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Enable this setting</label>
                </div>
            `;
            break;
        case 'integer':
            inputHtml = `<input type="number" id="value" name="value" value="${currentValue}" class="form-input" placeholder="Enter a number">`;
            break;
        case 'json':
            inputHtml = `<textarea id="value" name="value" rows="4" class="form-input font-mono text-sm" placeholder="Enter valid JSON">${currentValue}</textarea>`;
            break;
        case 'encrypted':
            inputHtml = `<input type="password" id="value" name="value" value="${currentValue}" class="form-input" placeholder="Enter encrypted value">`;
            break;
        default:
            inputHtml = `<input type="text" id="value" name="value" value="${currentValue}" class="form-input" placeholder="Enter the setting value">`;
    }
    
    valueContainer.innerHTML = inputHtml;
}

function handleEnvSyncChange() {
    const checkbox = document.getElementById('is_env_synced');
    const container = document.getElementById('env-key-container');
    
    if (checkbox.checked) {
        container.classList.remove('hidden');
        // Auto-generate env key from setting key
        const settingKey = document.getElementById('key').value;
        if (settingKey && !document.getElementById('env_key').value) {
            document.getElementById('env_key').value = settingKey.toUpperCase().replace(/[.-]/g, '_');
        }
    } else {
        container.classList.add('hidden');
    }
}

// Auto-generate env key when setting key changes
document.getElementById('key').addEventListener('input', function() {
    const envSynced = document.getElementById('is_env_synced').checked;
    const envKeyInput = document.getElementById('env_key');
    
    if (envSynced && this.value && !envKeyInput.value) {
        envKeyInput.value = this.value.toUpperCase().replace(/[.-]/g, '_');
    }
});

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    handleTypeChange();
    handleEnvSyncChange();
});
</script>
@endpush
