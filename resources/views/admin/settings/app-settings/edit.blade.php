@extends('layouts.app')

@section('title', 'Edit Application Setting')
@section('page-title', 'Edit Application Setting')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.setting-form {
    transition: all 0.2s ease-in-out;
}
.form-group {
    @apply mb-6;
}
.form-label {
    @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2;
}
.form-input {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}
.form-textarea {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}
.form-select {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}
.form-checkbox {
    @apply rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50;
}
.help-text {
    @apply text-sm text-gray-500 dark:text-gray-400 mt-1;
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
    
    <a href="{{ route('admin.app-settings.show', $appSetting) }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-eye mr-2"></i>
        View Setting
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg setting-form">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                <i class="fas fa-edit mr-2 text-indigo-500"></i>
                Edit Setting: {{ $appSetting->label }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Update the application setting configuration and values.
            </p>
        </div>

        <form action="{{ route('admin.app-settings.update', $appSetting) }}" method="POST" class="px-6 py-6">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Validation Error!</strong>
                    <ul class="mt-2 text-sm">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Basic Information -->
                <div class="lg:col-span-2">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Basic Information
                    </h4>
                </div>

                <!-- Key (Read-only) -->
                <div class="form-group">
                    <label class="form-label">
                        Setting Key
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           value="{{ $appSetting->key }}" 
                           disabled
                           class="form-input bg-gray-100 dark:bg-gray-600 cursor-not-allowed">
                    <p class="help-text">
                        <i class="fas fa-lock mr-1"></i>
                        The setting key cannot be modified after creation.
                    </p>
                </div>

                <!-- Label -->
                <div class="form-group">
                    <label for="label" class="form-label">
                        Display Label
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="label" 
                           name="label" 
                           value="{{ old('label', $appSetting->label) }}"
                           class="form-input"
                           required>
                    <p class="help-text">Human-readable name for this setting.</p>
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label for="category" class="form-label">
                        Category
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="category" name="category" class="form-select" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category', $appSetting->category) === $cat ? 'selected' : '' }}>
                                {{ ucfirst($cat) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="help-text">Category for organizing settings.</p>
                </div>

                <!-- Type -->
                <div class="form-group">
                    <label for="type" class="form-label">
                        Data Type
                        <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" class="form-select" required onchange="toggleTypeOptions()">
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ old('type', $appSetting->type) === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="help-text">The data type for validation and storage.</p>
                </div>

                <!-- Description -->
                <div class="form-group lg:col-span-2">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3" 
                              class="form-textarea"
                              placeholder="Optional description for this setting">{{ old('description', $appSetting->description) }}</textarea>
                    <p class="help-text">Brief description of what this setting controls.</p>
                </div>

                <!-- Value Configuration -->
                <div class="lg:col-span-2">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                        <i class="fas fa-sliders-h mr-2 text-green-500"></i>
                        Value Configuration
                    </h4>
                </div>

                <!-- Current Value -->
                <div class="form-group lg:col-span-2" id="valueField">
                    <label for="value" class="form-label">Current Value</label>
                    
                    <!-- String/Integer/Float Value -->
                    <div id="stringValue" class="value-input">
                        <input type="text" 
                               id="value" 
                               name="value" 
                               value="{{ old('value', $appSetting->type === 'encrypted' ? '' : $appSetting->value) }}"
                               placeholder="{{ $appSetting->type === 'encrypted' ? 'Enter new value to change (current value is encrypted)' : 'Enter value' }}"
                               class="form-input">
                    </div>
                    
                    <!-- Boolean Value -->
                    <div id="booleanValue" class="value-input hidden">
                        <div class="flex items-center">
                            <input type="hidden" name="value" value="0">
                            <input type="checkbox" 
                                   id="booleanCheck" 
                                   name="value" 
                                   value="1"
                                   {{ old('value', $appSetting->value) ? 'checked' : '' }}
                                   class="form-checkbox">
                            <label for="booleanCheck" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                Enable this setting
                            </label>
                        </div>
                    </div>
                    
                    <!-- JSON Value -->
                    <div id="jsonValue" class="value-input hidden">
                        <textarea id="jsonTextarea" 
                                  name="value" 
                                  rows="6"
                                  class="form-textarea font-mono text-sm"
                                  placeholder="Enter valid JSON">{{ old('value', $appSetting->type === 'json' ? (is_array($appSetting->value) ? json_encode($appSetting->value, JSON_PRETTY_PRINT) : $appSetting->value) : '') }}</textarea>
                    </div>
                    
                    <p class="help-text">
                        @if($appSetting->type === 'encrypted')
                            <i class="fas fa-shield-alt mr-1 text-yellow-500"></i>
                            This value is encrypted in the database. Leave empty to keep current value.
                        @else
                            Current value for this setting.
                        @endif
                    </p>
                </div>

                <!-- Sort Order -->
                <div class="form-group">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           value="{{ old('sort_order', $appSetting->sort_order ?? 0) }}"
                           min="0"
                           class="form-input">
                    <p class="help-text">Order for displaying this setting (lower numbers first).</p>
                </div>

                <!-- Environment Sync -->
                <div class="lg:col-span-2">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                        <i class="fas fa-sync mr-2 text-purple-500"></i>
                        Environment Synchronization
                    </h4>
                </div>

                <!-- Sync to .env -->
                <div class="form-group lg:col-span-2">
                    <div class="flex items-start">
                        <input type="hidden" name="is_env_synced" value="0">
                        <input type="checkbox" 
                               id="is_env_synced" 
                               name="is_env_synced" 
                               value="1"
                               {{ old('is_env_synced', $appSetting->is_env_synced) ? 'checked' : '' }}
                               class="form-checkbox mt-1"
                               onchange="toggleEnvKey()">
                        <div class="ml-3">
                            <label for="is_env_synced" class="form-label mb-1">
                                Synchronize to .env file
                            </label>
                            <p class="help-text">
                                When enabled, changes to this setting will automatically update the .env file.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Environment Key -->
                <div class="form-group lg:col-span-2" id="envKeyField" style="{{ old('is_env_synced', $appSetting->is_env_synced) ? '' : 'display: none;' }}">
                    <label for="env_key" class="form-label">
                        Environment Variable Name
                    </label>
                    <input type="text" 
                           id="env_key" 
                           name="env_key" 
                           value="{{ old('env_key', $appSetting->env_key) }}"
                           pattern="[A-Z0-9_]+"
                           placeholder="EXAMPLE_ENV_KEY"
                           class="form-input font-mono">
                    <p class="help-text">
                        <i class="fas fa-code mr-1"></i>
                        The environment variable name in .env file (uppercase, underscores only).
                        Leave empty to auto-generate from key.
                    </p>
                </div>

                <!-- Advanced Options -->
                <div class="lg:col-span-2">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                        <i class="fas fa-cogs mr-2 text-orange-500"></i>
                        Advanced Options
                    </h4>
                </div>

                <!-- Validation Rules -->
                <div class="form-group lg:col-span-2">
                    <label for="validation_rules_text" class="form-label">Validation Rules</label>
                    <input type="text" 
                           id="validation_rules_text" 
                           name="validation_rules_text" 
                           value="{{ old('validation_rules_text', is_array($appSetting->validation_rules) ? implode('|', $appSetting->validation_rules) : '') }}"
                           placeholder="required|string|max:255"
                           class="form-input font-mono">
                    <p class="help-text">
                        Laravel validation rules separated by pipes (|). Example: required|string|max:255
                    </p>
                </div>

                <!-- Options (for select fields) -->
                <div class="form-group lg:col-span-2">
                    <label for="options_text" class="form-label">Options (for select fields)</label>
                    <textarea id="options_text" 
                              name="options_text" 
                              rows="3"
                              class="form-textarea font-mono text-sm"
                              placeholder='{"value1": "Label 1", "value2": "Label 2"}'>{{ old('options_text', is_array($appSetting->options) ? json_encode($appSetting->options, JSON_PRETTY_PRINT) : '') }}</textarea>
                    <p class="help-text">
                        JSON object with options for select fields. Format: {"value": "label"}
                    </p>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="button" 
                        onclick="history.back()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>

                <div class="flex items-center space-x-3">
                    <button type="button"
                            onclick="testConnection()"
                            id="testConnectionBtn"
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:border-yellow-900 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-plug mr-2"></i>
                        Test Connection
                    </button>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-save mr-2"></i>
                        Update Setting
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle type-specific input fields
function toggleTypeOptions() {
    const type = document.getElementById('type').value;
    const valueInputs = document.querySelectorAll('.value-input');
    
    // Hide all value inputs
    valueInputs.forEach(input => input.classList.add('hidden'));
    
    // Show appropriate input based on type
    switch(type) {
        case 'boolean':
            document.getElementById('booleanValue').classList.remove('hidden');
            break;
        case 'json':
            document.getElementById('jsonValue').classList.remove('hidden');
            break;
        default:
            document.getElementById('stringValue').classList.remove('hidden');
    }
    
    // Update input type for number fields
    const stringInput = document.getElementById('value');
    if (type === 'integer') {
        stringInput.type = 'number';
        stringInput.step = '1';
    } else if (type === 'float') {
        stringInput.type = 'number';
        stringInput.step = 'any';
    } else if (type === 'encrypted') {
        stringInput.type = 'password';
    } else {
        stringInput.type = 'text';
        stringInput.removeAttribute('step');
    }
}

// Toggle env key field
function toggleEnvKey() {
    const isChecked = document.getElementById('is_env_synced').checked;
    const envKeyField = document.getElementById('envKeyField');
    
    if (isChecked) {
        envKeyField.style.display = 'block';
    } else {
        envKeyField.style.display = 'none';
    }
}

// Test connection for specific providers
function testConnection() {
    const category = document.getElementById('category').value;
    const button = document.getElementById('testConnectionBtn');
    
    // Show loading state
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
    
    fetch(`{{ route('admin.app-settings.test-connection', ':provider') }}`.replace(':provider', category), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            key: '{{ $appSetting->key }}',
            value: getSettingValue()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Connection test successful!', 'success');
        } else {
            showToast('Connection test failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Connection test error.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Get current setting value from form
function getSettingValue() {
    const type = document.getElementById('type').value;
    
    switch(type) {
        case 'boolean':
            return document.getElementById('booleanCheck').checked ? '1' : '0';
        case 'json':
            return document.getElementById('jsonTextarea').value;
        default:
            return document.getElementById('value').value;
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
    toast.className = `fixed top-4 right-4 z-50 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after delay
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Initialize form state
document.addEventListener('DOMContentLoaded', function() {
    toggleTypeOptions();
    toggleEnvKey();
});

// Handle validation rules input
document.getElementById('validation_rules_text').addEventListener('input', function() {
    const value = this.value.trim();
    if (value) {
        // Create hidden field for validation rules array
        let hiddenField = document.querySelector('input[name="validation_rules"]');
        if (!hiddenField) {
            hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = 'validation_rules';
            this.parentNode.appendChild(hiddenField);
        }
        hiddenField.value = JSON.stringify(value.split('|').filter(rule => rule.trim()));
    }
});

// Handle options input
document.getElementById('options_text').addEventListener('input', function() {
    const value = this.value.trim();
    if (value) {
        try {
            const options = JSON.parse(value);
            // Create hidden field for options
            let hiddenField = document.querySelector('input[name="options"]');
            if (!hiddenField) {
                hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.name = 'options';
                this.parentNode.appendChild(hiddenField);
            }
            hiddenField.value = JSON.stringify(options);
        } catch (e) {
            // Invalid JSON, don't update hidden field
        }
    }
});
</script>
@endpush
