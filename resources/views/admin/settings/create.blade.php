@extends('layouts.app')

@section('title', 'Create System Setting')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <!-- Breadcrumb -->
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Admin
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('admin.settings.index') }}" class="ml-1 text-gray-400 hover:text-blue-500 md:ml-2 transition-colors duration-200">System Settings</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-gray-500 md:ml-2">Create Setting</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">Create System Setting</h1>
                    <p class="text-gray-400">Add a new system configuration setting</p>
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.settings.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Settings
            </a>
        </div>
    </div>

    <div class="flex justify-center">
        <div class="w-full max-w-4xl">
            <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h6 class="text-white font-semibold">Setting Information</h6>
                </div>
                
                <form method="POST" action="{{ route('admin.settings.store') }}" id="settingForm">
                    @csrf
                    
                    <div class="p-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="key" class="block text-sm font-medium text-gray-300 mb-2">
                                    Setting Key <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('key') border-red-500 @enderror" 
                                       id="key" 
                                       name="key" 
                                       value="{{ old('key') }}"
                                       placeholder="e.g., app.max_upload_size"
                                       pattern="^[a-z0-9._]+$"
                                       required>
                                <p class="mt-1 text-sm text-gray-400">Use lowercase letters, numbers, dots, and underscores only</p>
                                @error('key')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="label" class="block text-sm font-medium text-gray-300 mb-2">
                                    Display Label <span class="text-red-400">*</span>
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('label') border-red-500 @enderror" 
                                       id="label" 
                                       name="label" 
                                       value="{{ old('label') }}"
                                       placeholder="e.g., Maximum Upload Size"
                                       required>
                                <p class="mt-1 text-sm text-gray-400">Human-readable name for this setting</p>
                                @error('label')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="group" class="block text-sm font-medium text-gray-300 mb-2">
                                    Group <span class="text-red-400">*</span>
                                </label>
                                <select class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('group') border-red-500 @enderror" 
                                        id="group" 
                                        name="group" 
                                        required>
                                    <option value="">Select a group...</option>
                                    @foreach($groups as $key => $name)
                                        <option value="{{ $key }}" {{ (old('group', $defaultGroup) === $key) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-400">Category to organize this setting</p>
                                @error('group')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-300 mb-2">
                                    Data Type <span class="text-red-400">*</span>
                                </label>
                                <select class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('type') border-red-500 @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="">Select data type...</option>
                                    <option value="string" {{ old('type') === 'string' ? 'selected' : '' }}>String</option>
                                    <option value="integer" {{ old('type') === 'integer' ? 'selected' : '' }}>Integer</option>
                                    <option value="boolean" {{ old('type') === 'boolean' ? 'selected' : '' }}>Boolean</option>
                                    <option value="json" {{ old('type') === 'json' ? 'selected' : '' }}>JSON</option>
                                    <option value="text" {{ old('type') === 'text' ? 'selected' : '' }}>Text (Long)</option>
                                    <option value="encrypted" {{ old('type') === 'encrypted' ? 'selected' : '' }}>Encrypted</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-400">How the value should be stored and processed</p>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Value Input -->
                        <div class="mb-6">
                            <label for="value" class="block text-sm font-medium text-gray-300 mb-2">Value</label>
                            
                            <!-- String/Integer/Encrypted input -->
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('value') border-red-500 @enderror" 
                                   id="value-text" 
                                   name="value" 
                                   value="{{ old('value') }}"
                                   placeholder="Enter setting value">
                            
                            <!-- Boolean input -->
                            <select class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('value') border-red-500 @enderror" 
                                    id="value-boolean" 
                                    name="value" 
                                    style="display: none;">
                                <option value="1" {{ old('value') === '1' ? 'selected' : '' }}>True</option>
                                <option value="0" {{ old('value') === '0' ? 'selected' : '' }}>False</option>
                            </select>
                            
                            <!-- Text/JSON input -->
                            <textarea class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm @error('value') border-red-500 @enderror" 
                                      id="value-textarea" 
                                      name="value" 
                                      rows="4" 
                                      style="display: none;"
                                      placeholder="Enter setting value...">{{ old('value') }}</textarea>
                            
                            <p class="mt-1 text-sm text-gray-400" id="value-help">The default or initial value for this setting</p>
                            @error('value')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                            <textarea class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Describe what this setting controls...">{{ old('description') }}</textarea>
                            <p class="mt-1 text-sm text-gray-400">Explain what this setting does and how it affects the system</p>
                            @error('description')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Advanced Options -->
                        <div class="bg-gray-700 rounded-lg border border-gray-600 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-600">
                                <button type="button" 
                                        class="w-full flex items-center justify-between text-left text-white font-medium focus:outline-none hover:text-blue-400 transition-colors duration-200" 
                                        onclick="toggleAdvanced()"
                                        id="advanced-toggle-btn">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 transform transition-transform duration-200" id="advanced-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        Advanced Options
                                    </span>
                                </button>
                            </div>
                            <div class="hidden" id="advancedOptions">
                                <div class="p-4 space-y-4">
                                    <!-- Validation Rules -->
                                    <div>
                                        <label for="validation_rules" class="block text-sm font-medium text-gray-300 mb-2">Validation Rules (JSON)</label>
                                        <textarea class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm @error('validation_rules') border-red-500 @enderror" 
                                                  id="validation_rules" 
                                                  name="validation_rules" 
                                                  rows="3"
                                                  placeholder='{"required": true, "min": 1, "max": 100}'>{{ old('validation_rules') }}</textarea>
                                        <p class="mt-1 text-sm text-gray-400">Laravel validation rules in JSON format</p>
                                        @error('validation_rules')
                                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Options for select/radio inputs -->
                                    <div>
                                        <label for="options" class="block text-sm font-medium text-gray-300 mb-2">Options (JSON)</label>
                                        <textarea class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm @error('options') border-red-500 @enderror" 
                                                  id="options" 
                                                  name="options" 
                                                  rows="3"
                                                  placeholder='{"option1": "Label 1", "option2": "Label 2"}'>{{ old('options') }}</textarea>
                                        <p class="mt-1 text-sm text-gray-400">Key-value pairs for select/radio options</p>
                                        @error('options')
                                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Sort Order -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                                            <input type="number" 
                                                   class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('sort_order') border-red-500 @enderror" 
                                                   id="sort_order" 
                                                   name="sort_order" 
                                                   value="{{ old('sort_order', 0) }}"
                                                   min="0">
                                            <p class="mt-1 text-sm text-gray-400">Lower numbers appear first</p>
                                            @error('sort_order')
                                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Checkboxes -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700" 
                                                       type="checkbox" 
                                                       id="is_public" 
                                                       name="is_public" 
                                                       value="1" 
                                                       {{ old('is_public') ? 'checked' : '' }}>
                                                <label for="is_public" class="ml-3 block text-sm font-medium text-gray-300">
                                                    Public Setting
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-400">Can be accessed by non-admin users</p>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700" 
                                                       type="checkbox" 
                                                       id="is_encrypted" 
                                                       name="is_encrypted" 
                                                       value="1" 
                                                       {{ old('is_encrypted') ? 'checked' : '' }}>
                                                <label for="is_encrypted" class="ml-3 block text-sm font-medium text-gray-300">
                                                    Encrypt Value
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-400">Store value encrypted in database</p>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700" 
                                                       type="checkbox" 
                                                       id="requires_restart" 
                                                       name="requires_restart" 
                                                       value="1" 
                                                       {{ old('requires_restart') ? 'checked' : '' }}>
                                                <label for="requires_restart" class="ml-3 block text-sm font-medium text-gray-300">
                                                    Requires Restart
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-400">Changes require cache clear</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-700 border-t border-gray-600">
                        <div class="flex justify-between items-center">
                            <a href="{{ route('admin.settings.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </a>
                            <div class="flex items-center space-x-3">
                                <button type="submit" name="action" value="save" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    Create Setting
                                </button>
                                <button type="submit" name="action" value="save_and_add" 
                                        class="inline-flex items-center px-4 py-2 border border-blue-600 rounded-lg text-blue-400 hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create & Add Another
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const valueText = document.getElementById('value-text');
    const valueBoolean = document.getElementById('value-boolean');
    const valueTextarea = document.getElementById('value-textarea');
    const valueHelp = document.getElementById('value-help');
    
    // Handle data type changes
    typeSelect.addEventListener('change', function() {
        const type = this.value;
        
        // Hide all value inputs
        valueText.style.display = 'none';
        valueBoolean.style.display = 'none';
        valueTextarea.style.display = 'none';
        
        // Reset names to prevent multiple submissions
        valueText.name = '';
        valueBoolean.name = '';
        valueTextarea.name = '';
        
        // Show appropriate input and set name
        switch (type) {
            case 'boolean':
                valueBoolean.style.display = 'block';
                valueBoolean.name = 'value';
                valueHelp.textContent = 'Select true or false for this boolean setting';
                break;
                
            case 'json':
                valueTextarea.style.display = 'block';
                valueTextarea.name = 'value';
                valueTextarea.placeholder = '{"key": "value", "array": [1, 2, 3]}';
                valueHelp.textContent = 'Enter valid JSON data';
                break;
                
            case 'text':
                valueTextarea.style.display = 'block';
                valueTextarea.name = 'value';
                valueTextarea.placeholder = 'Enter long text content...';
                valueHelp.textContent = 'Enter multi-line text content';
                break;
                
            case 'integer':
                valueText.style.display = 'block';
                valueText.name = 'value';
                valueText.type = 'number';
                valueText.placeholder = 'Enter a number';
                valueHelp.textContent = 'Enter a numeric value';
                break;
                
            case 'encrypted':
                valueText.style.display = 'block';
                valueText.name = 'value';
                valueText.type = 'password';
                valueText.placeholder = 'Enter sensitive data';
                valueHelp.textContent = 'This value will be encrypted when stored';
                break;
                
            default: // string
                valueText.style.display = 'block';
                valueText.name = 'value';
                valueText.type = 'text';
                valueText.placeholder = 'Enter setting value';
                valueHelp.textContent = 'Enter a text value for this setting';
        }
    });
    
    // Trigger initial type change
    if (typeSelect.value) {
        typeSelect.dispatchEvent(new Event('change'));
    }
    
    // Auto-generate label from key
    document.getElementById('key').addEventListener('input', function() {
        const labelField = document.getElementById('label');
        if (!labelField.value) {
            const key = this.value;
            const label = key.split('.').pop() // Get last part after dots
                            .split('_').map(word => 
                                word.charAt(0).toUpperCase() + word.slice(1)
                            ).join(' ');
            labelField.value = label;
        }
    });
    
    // Validate JSON fields
    const jsonFields = ['validation_rules', 'options'];
    jsonFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('blur', function() {
                if (this.value.trim()) {
                    try {
                        JSON.parse(this.value);
                        this.classList.remove('border-red-500');
                        const feedback = this.parentNode.querySelector('.json-error');
                        if (feedback) {
                            feedback.remove();
                        }
                    } catch (e) {
                        this.classList.add('border-red-500');
                        let feedback = this.parentNode.querySelector('.json-error');
                        if (!feedback) {
                            feedback = document.createElement('p');
                            feedback.className = 'mt-1 text-sm text-red-400 json-error';
                            this.parentNode.appendChild(feedback);
                        }
                        feedback.textContent = 'Invalid JSON format: ' + e.message;
                    }
                } else {
                    this.classList.remove('border-red-500');
                    const feedback = this.parentNode.querySelector('.json-error');
                    if (feedback) {
                        feedback.remove();
                    }
                }
            });
        }
    });
    
    // Form submission handling for "Create & Add Another"
    document.getElementById('settingForm').addEventListener('submit', function(e) {
        const action = e.submitter.value;
        if (action === 'save_and_add') {
            // Store form data in session storage to persist values
            const formData = new FormData(this);
            const data = {};
            for (let [key, value] of formData.entries()) {
                if (key !== '_token' && key !== 'key' && key !== 'label' && key !== 'value') {
                    data[key] = value;
                }
            }
            sessionStorage.setItem('settingFormDefaults', JSON.stringify(data));
        }
    });
    
    // Restore form defaults if available
    const savedDefaults = sessionStorage.getItem('settingFormDefaults');
    if (savedDefaults) {
        try {
            const defaults = JSON.parse(savedDefaults);
            Object.keys(defaults).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = defaults[key] === '1';
                    } else {
                        field.value = defaults[key];
                    }
                }
            });
            // Clear after using
            sessionStorage.removeItem('settingFormDefaults');
        } catch (e) {
            // Ignore invalid JSON
        }
    }
});

function toggleAdvanced() {
    const options = document.getElementById('advancedOptions');
    const arrow = document.getElementById('advanced-arrow');
    
    if (options.classList.contains('hidden')) {
        options.classList.remove('hidden');
        arrow.classList.add('rotate-90');
    } else {
        options.classList.add('hidden');
        arrow.classList.remove('rotate-90');
    }
}

// Toast notification helper
function showToast(message, type = 'info') {
    const colors = {
        'success': 'bg-green-600',
        'error': 'bg-red-600',
        'warning': 'bg-yellow-600',
        'info': 'bg-blue-600'
    };
    
    const toastHtml = `
        <div class="fixed top-4 right-4 z-50 ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg animate-fade-in-down" role="alert">
            <span>${message}</span>
            <button type="button" class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.remove();">&times;</button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    setTimeout(function() {
        const toasts = document.querySelectorAll('.fixed.top-4.right-4');
        toasts.forEach(toast => toast.remove());
    }, 5000);
}
</script>
@endpush

@push('styles')
<style>
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -100%, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}
.animate-fade-in-down {
    animation: fadeInDown 0.5s ease-out;
}
</style>
@endpush
