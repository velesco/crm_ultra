@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 mb-2">
                    <a href="{{ route('google-sheets.index') }}" 
                       class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <i class="fas fa-table text-green-600 mr-3"></i>
                        {{ $googleSheet->name }}
                    </h1>
                    @if($googleSheet->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                            <i class="fas fa-check-circle mr-2"></i>
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                            <i class="fas fa-pause-circle mr-2"></i>
                            Inactive
                        </span>
                    @endif
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('google-sheets.edit', $googleSheet) }}" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Edit
                    </a>
                    @if($googleSheet->is_active && $googleSheet->oauth_tokens)
                        <form method="POST" action="{{ route('google-sheets.sync', $googleSheet) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Sync Now
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @if($googleSheet->description)
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $googleSheet->description }}
                </p>
            @endif
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Integration Overview -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                        Integration Overview
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Google Spreadsheet</h3>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-table text-green-600"></i>
                                <span class="text-gray-900 dark:text-white font-mono text-sm">
                                    {{ Str::limit($googleSheet->spreadsheet_id, 20) }}...
                                </span>
                                <a href="https://docs.google.com/spreadsheets/d/{{ $googleSheet->spreadsheet_id }}/edit" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    <i class="fas fa-external-link-alt text-sm"></i>
                                </a>
                            </div>
                            @if($googleSheet->sheet_name)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Sheet: {{ $googleSheet->sheet_name }}
                                </p>
                            @endif
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Sync Configuration</h3>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <i class="fas fa-exchange-alt text-purple-600 mr-2"></i>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ ucfirst(str_replace('_', ' ', $googleSheet->sync_direction)) }}
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-orange-600 mr-2"></i>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ ucfirst($googleSheet->sync_frequency) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Authentication Status</h3>
                            @if($googleSheet->oauth_tokens)
                                <div class="flex items-center text-green-600 dark:text-green-400">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span>Connected to Google</span>
                                </div>
                            @else
                                <div class="flex items-center text-red-600 dark:text-red-400">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <span>Not authenticated</span>
                                </div>
                            @endif
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Created</h3>
                            <div class="text-gray-900 dark:text-white">
                                {{ $googleSheet->created_at->format('M j, Y') }}
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    by {{ $googleSheet->user->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Field Mapping -->
                @if($googleSheet->field_mapping && count($googleSheet->field_mapping) > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                Field Mapping
                            </h2>
                            <button onclick="previewSpreadsheetData()" 
                                    class="px-3 py-1.5 bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:hover:bg-green-800 text-green-700 dark:text-green-300 text-sm rounded-lg transition-colors duration-200">
                                <i class="fas fa-eye mr-1"></i>
                                Preview Data
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            CRM Field
                                        </th>
                                        <th class="text-center py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            
                                        </th>
                                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Google Sheets Column
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($googleSheet->field_mapping as $crmField => $sheetColumn)
                                        <tr>
                                            <td class="py-3 px-4">
                                                <div class="flex items-center">
                                                    <i class="fas fa-user text-blue-600 mr-2"></i>
                                                    <span class="text-gray-900 dark:text-white font-medium">
                                                        {{ ucfirst(str_replace('_', ' ', $crmField)) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <i class="fas fa-arrow-right text-gray-400"></i>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex items-center">
                                                    <i class="fas fa-table text-green-600 mr-2"></i>
                                                    <span class="text-gray-900 dark:text-white font-mono">
                                                        {{ $sheetColumn }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Recent Sync Logs -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Recent Sync Activity
                        </h2>
                        <a href="{{ route('google-sheets.sync-logs', $googleSheet) }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm">
                            View All Logs →
                        </a>
                    </div>
                    
                    @if($recentSyncLogs && $recentSyncLogs->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentSyncLogs as $log)
                                <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        @if($log->status === 'completed')
                                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                                            </div>
                                        @elseif($log->status === 'failed')
                                            <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                                <i class="fas fa-times text-red-600 dark:text-red-400"></i>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                                <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
                                            </div>
                                        @endif
                                        
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ ucfirst($log->direction) }} Sync
                                                </span>
                                                @if($log->status === 'completed')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                        Success
                                                    </span>
                                                @elseif($log->status === 'failed')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                        Failed
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                        {{ ucfirst($log->status) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                @if($log->contacts_processed > 0)
                                                    {{ number_format($log->contacts_processed) }} contacts processed
                                                @endif
                                                @if($log->started_at)
                                                    • {{ $log->started_at->diffForHumans() }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($log->error_message)
                                        <button onclick="showErrorDetails('{{ addslashes($log->error_message) }}')" 
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                            <i class="fas fa-exclamation-circle"></i>
                                            View Error
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-sync-alt text-gray-400 text-2xl mb-3"></i>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Sync Activity</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                This integration hasn't been synced yet.
                            </p>
                            @if($googleSheet->is_active && $googleSheet->oauth_tokens)
                                <form method="POST" action="{{ route('google-sheets.sync', $googleSheet) }}" class="mt-4">
                                    @csrf
                                    <button type="submit" 
                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200">
                                        <i class="fas fa-sync-alt mr-2"></i>
                                        Start First Sync
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar Stats -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistics</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-sm"></i>
                                </div>
                                <span class="text-gray-600 dark:text-gray-400">Successful Syncs</span>
                            </div>
                            <span class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ $stats['successful_syncs'] ?? 0 }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-sm"></i>
                                </div>
                                <span class="text-gray-600 dark:text-gray-400">Failed Syncs</span>
                            </div>
                            <span class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ $stats['failed_syncs'] ?? 0 }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-users text-blue-600 dark:text-blue-400 text-sm"></i>
                                </div>
                                <span class="text-gray-600 dark:text-gray-400">Contacts Synced</span>
                            </div>
                            <span class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ number_format($stats['total_contacts_synced'] ?? 0) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-clock text-purple-600 dark:text-purple-400 text-sm"></i>
                                </div>
                                <span class="text-gray-600 dark:text-gray-400">Last Sync</span>
                            </div>
                            <span class="text-sm text-gray-900 dark:text-white">
                                @if($stats['last_sync_at'])
                                    {{ \Carbon\Carbon::parse($stats['last_sync_at'])->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    
                    <div class="space-y-3">
                        @if($googleSheet->is_active && $googleSheet->oauth_tokens)
                            <form method="POST" action="{{ route('google-sheets.sync', $googleSheet) }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Sync Now
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('google-sheets.edit', $googleSheet) }}" 
                           class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Integration
                        </a>
                        
                        <button onclick="testConnection()" 
                                class="w-full px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                            <i class="fas fa-plug mr-2"></i>
                            Test Connection
                        </button>
                        
                        <a href="{{ route('google-sheets.sync-logs', $googleSheet) }}" 
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 flex items-center justify-center">
                            <i class="fas fa-history mr-2"></i>
                            View All Logs
                        </a>
                    </div>
                </div>

                <!-- Integration Health -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Integration Health</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Status</span>
                            @if($googleSheet->is_active)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                    <i class="fas fa-pause-circle mr-1"></i>
                                    Inactive
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Authentication</span>
                            @if($googleSheet->oauth_tokens)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Connected
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Disconnected
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Field Mapping</span>
                            @if($googleSheet->field_mapping && count($googleSheet->field_mapping) > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ count($googleSheet->field_mapping) }} fields
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Not configured
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testConnection() {
    const button = event.target;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
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

function previewSpreadsheetData() {
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

function showErrorDetails(errorMessage) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-2xl max-h-96 overflow-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sync Error Details</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <pre class="text-sm text-red-800 dark:text-red-300 whitespace-pre-wrap">${errorMessage}</pre>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function showPreviewModal(data, headers) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    
    let tableHtml = '<table class="min-w-full border-collapse border border-gray-300"><thead><tr>';
    headers.forEach(header => {
        tableHtml += `<th class="border border-gray-300 px-4 py-2 bg-gray-100 text-left">${header}</th>`;
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
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-6xl max-h-96 overflow-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Spreadsheet Preview (First 10 rows)</h3>
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
</script>
@endpush
@endsection
