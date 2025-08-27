@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('google-sheets.index') }}" 
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-plus text-green-600 mr-3"></i>
                    Create Google Sheets Integration
                </h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                Set up a new integration to sync your CRM contacts with Google Sheets
            </p>
        </div>

        <form method="POST" action="{{ route('google-sheets.store') }}" class="space-y-8">
            @csrf

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
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="e.g., My CRM Contacts"
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
                               value="{{ old('description') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="Optional description">
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Google Sheets Configuration -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-table text-green-600 dark:text-green-400"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Google Sheets Configuration</h2>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="spreadsheet_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Google Spreadsheet ID *
                        </label>
                        <input type="text" 
                               id="spreadsheet_id" 
                               name="spreadsheet_id" 
                               value="{{ old('spreadsheet_id') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                               placeholder="1AbC-DefGhI2JkLmN3OpQrS4TuVwX5YzA6BcD7EfG8HiJ"
                               required>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Copy the spreadsheet ID from the Google Sheets URL: https://docs.google.com/spreadsheets/d/<strong>SPREADSHEET_ID</strong>/edit
                        </p>
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
                                   value="{{ old('sheet_name', 'Sheet1') }}"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   placeholder="Sheet1">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Leave blank to use the first sheet
                            </p>
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
                                   value="{{ old('header_row', 1) }}"
                                   min="1"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Row number containing column headers
                            </p>
                            @error('header_row')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
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
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                required>
                            <option value="">Select sync direction</option>
                            <option value="to_sheets" {{ old('sync_direction') == 'to_sheets' ? 'selected' : '' }}>
                                CRM → Google Sheets
                            </option>
                            <option value="from_sheets" {{ old('sync_direction') == 'from_sheets' ? 'selected' : '' }}>
                                Google Sheets → CRM
                            </option>
                            <option value="bidirectional" {{ old('sync_direction') == 'bidirectional' ? 'selected' : '' }}>
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
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                required>
                            <option value="">Select frequency</option>
                            <option value="manual" {{ old('sync_frequency') == 'manual' ? 'selected' : '' }}>
                                Manual Only
                            </option>
                            <option value="hourly" {{ old('sync_frequency') == 'hourly' ? 'selected' : '' }}>
                                Every Hour
                            </option>
                            <option value="daily" {{ old('sync_frequency') == 'daily' ? 'selected' : '' }}>
                                Daily
                            </option>
                            <option value="weekly" {{ old('sync_frequency') == 'weekly' ? 'selected' : '' }}>
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
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Activate integration immediately
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        You can enable/disable the integration later if needed
                    </p>
                </div>
            </div>

            <!-- Authentication Notice -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                            Authentication Required
                        </h3>
                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                            <p>
                                After creating the integration, you'll be redirected to Google to authorize access to your spreadsheet.
                                Make sure you're signed in to the correct Google account.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('google-sheets.index') }}" 
                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Create Integration
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus first input
    document.getElementById('name').focus();

    // Spreadsheet ID validation helper
    const spreadsheetInput = document.getElementById('spreadsheet_id');
    
    spreadsheetInput.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value && !isValidSpreadsheetId(value)) {
            this.classList.add('border-red-500');
            showSpreadsheetIdError('Invalid spreadsheet ID format');
        } else {
            this.classList.remove('border-red-500');
            hideSpreadsheetIdError();
        }
    });

    function isValidSpreadsheetId(id) {
        // Google Sheets ID pattern: alphanumeric, hyphens, underscores, 44+ chars
        return /^[a-zA-Z0-9\-_]{30,}$/.test(id);
    }

    function showSpreadsheetIdError(message) {
        let errorEl = document.getElementById('spreadsheet_id_error');
        if (!errorEl) {
            errorEl = document.createElement('p');
            errorEl.id = 'spreadsheet_id_error';
            errorEl.className = 'text-red-500 text-sm mt-1';
            spreadsheetInput.parentNode.appendChild(errorEl);
        }
        errorEl.textContent = message;
    }

    function hideSpreadsheetIdError() {
        const errorEl = document.getElementById('spreadsheet_id_error');
        if (errorEl) {
            errorEl.remove();
        }
    }
});
</script>
@endpush
@endsection
