@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('google-sheets.show', $googleSheet) }}" 
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-edit text-blue-600 mr-3"></i>
                    Edit Integration
                </h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                Modify settings for "{{ $googleSheet->name }}"
            </p>
        </div>

        <form method="POST" action="{{ route('google-sheets.update', $googleSheet) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Integration Details -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Integration Details</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Integration Name *
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $googleSheet->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <input type="text" 
                               id="description" 
                               name="description" 
                               value="{{ old('description', $googleSheet->description) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Google Sheets Configuration -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-table text-green-600 dark:text-green-400"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Google Sheets Configuration</h2>
                    </div>
                    <button type="button" 
                            onclick="testConnection()" 
                            class="px-3 py-1.5 bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:hover:bg-green-800 text-green-700 dark:text-green-300 text-sm rounded-lg transition-colors duration-200">
                        <i class="fas fa-plug mr-1"></i>
                        Test Connection
                    </button>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="spreadsheet_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Google Spreadsheet ID *
                        </label>
                        <input type="text" 
                               id="spreadsheet_id" 
                               name="spreadsheet_id" 
                               value="{{ old('spreadsheet_id', $googleSheet->spreadsheet_id) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                               required>
                        @error('spreadsheet_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sheet_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sheet Name
                            </label>
                            <input type="text" 
                                   id="sheet_name" 
                                   name="sheet_name" 
                                   value="{{ old('sheet_name', $googleSheet->sheet_name) }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('sheet_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="header_row" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Header Row Number
                            </label>
                            <input type="number" 
                                   id="header_row" 
                                   name="header_row" 
                                   value="{{ old('header_row', $googleSheet->header_row ?? 1) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('header_row')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field Mapping -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-exchange-alt text-orange-600 dark:text-orange-400"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Field Mapping</h2>
                    </div>
                    <button type="button" 
                            onclick="loadSpreadsheetPreview()" 
                            class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 text-sm rounded-lg transition-colors duration-200">
                        <i class="fas fa-eye mr-1"></i>
                        Preview Data
                    </button>
                </div>

                <div id="field-mapping-container">
                    @if($googleSheet->field_mapping)
                        @foreach($googleSheet->field_mapping as $crmField => $sheetColumn)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 field-mapping-row">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        CRM Field
                                    </label>
                                    <select name="field_mapping[{{ $loop->index }}][crm_field]" 
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="">Select CRM Field</option>
                                        <option value="first_name" {{ $crmField == 'first_name' ? 'selected' : '' }}>First Name</option>
                                        <option value="last_name" {{ $crmField == 'last_name' ? 'selected' : '' }}>Last Name</option>
                                        <option value="email" {{ $crmField == 'email' ? 'selected' : '' }}>Email</option>
                                        <option value="phone" {{ $crmField == 'phone' ? 'selected' : '' }}>Phone</option>
                                        <option value="company" {{ $crmField == 'company' ? 'selected' : '' }}>Company</option>
                                        <option value="job_title" {{ $crmField == 'job_title' ? 'selected' : '' }}>Job Title</option>
                                        <option value="address" {{ $crmField == 'address' ? 'selected' : '' }}>Address</option>
                                        <option value="city" {{ $crmField == 'city' ? 'selected' : '' }}>City</option>
                                        <option value="state" {{ $crmField == 'state' ? 'selected' : '' }}>State</option>
                                        <option value="postal_code" {{ $crmField == 'postal_code' ? 'selected' : '' }}>Postal Code</option>
                                        <option value="country" {{ $crmField == 'country' ? 'selected' : '' }}>Country</option>
                                    </select>
                                </div>
                                <div class="flex">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Sheet Column
                                        </label>
                                        <input type="text" 
                                               name="field_mapping[{{ $loop->index }}][sheet_column]" 
                                               value="{{ $sheetColumn }}"
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                               placeholder="A, B, C or column name">
                                    </div>
                                    <div class="ml-2 flex items-end">
                                        <button type="button" 
                                                onclick="removeFieldMapping(this)" 
                                                class="px-3 py-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" 
                        onclick="addFieldMapping()" 
                        class="mt-4 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Add Field Mapping
                </button>
            </div>

            <!-- Sync Configuration -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-sync-alt text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Sync Configuration</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="sync_direction" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sync Direction *
                        </label>
                        <select id="sync_direction" 
                                name="sync_direction" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                required>
                            <option value="to_sheets" {{ old('sync_direction', $googleSheet->sync_direction) == 'to_sheets' ? 'selected' : '' }}>
                                CRM → Google Sheets
                            </option>
                            <option value="from_sheets" {{ old('sync_direction', $googleSheet->sync_direction) == 'from_sheets' ? 'selected' : '' }}>
                                Google Sheets → CRM
                            </option>
                            <option value="bidirectional" {{ old('sync_direction', $googleSheet->sync_direction) == 'bidirectional' ? 'selected' : '' }}>
                                Bidirectional
                            </option>
                        </select>
                        @error('sync_direction')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sync_frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sync Frequency *
                        </label>
                        <select id="sync_frequency" 
                                name="sync_frequency" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                required>
                            <option value="manual" {{ old('sync_frequency', $googleSheet->sync_frequency) == 'manual' ? 'selected' : '' }}>
                                Manual Only
                            </option>
                            <option value="hourly" {{ old('sync_frequency', $googleSheet->sync_frequency) == 'hourly' ? 'selected' : '' }}>
                                Every Hour
                            </option>
                            <option value="daily" {{ old('sync_frequency', $googleSheet->sync_frequency) == 'daily' ? 'selected' : '' }}>
                                Daily
                            </option>
                            <option value="weekly" {{ old('sync_frequency', $googleSheet->sync_frequency) == 'weekly' ? 'selected' : '' }}>
                                Weekly
                            </option>
                        </select>
                        @error('sync_frequency')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $googleSheet->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Keep integration active
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Disable to temporarily pause all syncing
                    </p>
                </div>
            </div>

            <!-- OAuth Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-key text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Authentication Status</h2>
                    </div>
                    @if($googleSheet->oauth_tokens)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                            <i class="fas fa-check-circle mr-2"></i>
                            Connected
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Not Connected
                        </span>
                    @endif
                </div>

                <div class="space-y-4">
                    @if($googleSheet->oauth_tokens)
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-400 mt-0.5 mr-3"></i>
                                <div>
                                    <p class="text-sm text-green-800 dark:text-green-300 font-medium">
                                        Successfully authenticated with Google
                                    </p>
                                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                                        Integration has access to your Google Sheets
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 flex space-x-3">
                                <form method="POST" action="{{ route('google-sheets.refresh-tokens', $googleSheet) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors duration-200">
                                        <i class="fas fa-sync-alt mr-1"></i>
                                        Refresh Tokens
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('google-sheets.revoke-access', $googleSheet) }}" 
                                      class="inline" 
                                      onsubmit="return confirm('Are you sure you want to revoke access? This will disable the integration.')">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition-colors duration-200">
                                        <i class="fas fa-times mr-1"></i>
                                        Revoke Access
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-red-400 mt-0.5 mr-3"></i>
                                <div>
                                    <p class="text-sm text-red-800 dark:text-red-300 font-medium">
                                        Google authentication required
                                    </p>
                                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                                        You need to authorize access to your Google Sheets to enable syncing
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('google-sheets.authorize', $googleSheet) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition-colors duration-200">
                                    <i class="fab fa-google mr-2"></i>
                                    Authorize Google Access
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('google-sheets.show', $googleSheet) }}" 
                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Update Integration
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let fieldMappingIndex = {{ $googleSheet->field_mapping ? count($googleSheet->field_mapping) : 0 }};

function addFieldMapping() {
    const container = document.getElementById('field-mapping-container');
    const newRow = document.createElement('div');
    newRow.className = 'grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 field-mapping-row';
    
    newRow.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                CRM Field
            </label>
            <select name="field_mapping[${fieldMappingIndex}][crm_field]" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Select CRM Field</option>
                <option value="first_name">First Name</option>
                <option value="last_name">Last Name</option>
                <option value="email">Email</option>
                <option value="phone">Phone</option>
                <option value="company">Company</option>
                <option value="job_title">Job Title</option>
                <option value="address">Address</option>
                <option value="city">City</option>
                <option value="state">State</option>
                <option value="postal_code">Postal Code</option>
                <option value="country">Country</option>
            </select>
        </div>
        <div class="flex">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Sheet Column
                </label>
                <input type="text" 
                       name="field_mapping[${fieldMappingIndex}][sheet_column]" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                       placeholder="A, B, C or column name">
            </div>
            <div class="ml-2 flex items-end">
                <button type="button" 
                        onclick="removeFieldMapping(this)" 
                        class="px-3 py-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    fieldMappingIndex++;
}

function removeFieldMapping(button) {
    button.closest('.field-mapping-row').remove();
}

function testConnection() {
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Testing...';
    button.disabled = true;
    
    fetch(`{{ route('google-sheets.test', $googleSheet) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Connection test successful!', 'success');
        } else {
            showNotification('Connection failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Connection test failed', 'error');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function loadSpreadsheetPreview() {
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Loading...';
    button.disabled = true;
    
    fetch(`{{ route('google-sheets.preview', $googleSheet) }}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showPreviewModal(data.data, data.headers);
        } else {
            showNotification('Failed to load preview: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Failed to load preview', 'error');
    })
    .finally(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function showNotification(message, type) {
    const color = type === 'success' ? 'green' : 'red';
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 bg-${color}-100 border border-${color}-300 text-${color}-700 rounded-lg z-50`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function showPreviewModal(data, headers) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    
    let tableHtml = '<table class="min-w-full border-collapse border border-gray-300"><thead><tr>';
    headers.forEach(header => {
        tableHtml += `<th class="border border-gray-300 px-4 py-2 bg-gray-100">${header}</th>`;
    });
    tableHtml += '</tr></thead><tbody>';
    
    data.slice(0, 10).forEach(row => {
        tableHtml += '<tr>';
        row.forEach(cell => {
            tableHtml += `<td class="border border-gray-300 px-4 py-2">${cell || ''}</td>`;
        });
        tableHtml += '</tr>';
    });
    tableHtml += '</tbody></table>';
    
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-4xl max-h-96 overflow-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Spreadsheet Preview</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="overflow-auto">
                ${tableHtml}
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}
</script>
@endpush
@endsection
