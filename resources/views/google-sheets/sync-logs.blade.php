@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="p-6 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-3 mb-2">
                <a href="{{ route('google-sheets.show', $googleSheet) }}" 
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-history text-blue-600 mr-3"></i>
                    Sync Logs
                </h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                Sync history for "{{ $googleSheet->name }}"
            </p>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <form method="GET" action="{{ route('google-sheets.sync-logs', $googleSheet) }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status
                        </label>
                        <select name="status" id="status" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">All Statuses</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label for="direction" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Direction
                        </label>
                        <select name="direction" id="direction" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">All Directions</option>
                            <option value="to_sheets" {{ request('direction') == 'to_sheets' ? 'selected' : '' }}>CRM → Google Sheets</option>
                            <option value="from_sheets" {{ request('direction') == 'from_sheets' ? 'selected' : '' }}>Google Sheets → CRM</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            From Date
                        </label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            To Date
                        </label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-filter mr-2"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('google-sheets.sync-logs', $googleSheet) }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Sync History ({{ $logs->total() }} records)
                </h2>
            </div>

            @if($logs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Direction & Details
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Statistics
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Duration
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Started At
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($logs as $log)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        @if($log->status === 'completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Success
                                            </span>
                                        @elseif($log->status === 'failed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Failed
                                            </span>
                                        @elseif($log->status === 'running')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                                Running
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                                                @if($log->direction === 'to_sheets')
                                                    <i class="fas fa-arrow-right text-blue-600 mr-2"></i>
                                                    <span>CRM → Google Sheets</span>
                                                @else
                                                    <i class="fas fa-arrow-left text-green-600 mr-2"></i>
                                                    <span>Google Sheets → CRM</span>
                                                @endif
                                            </div>
                                            @if($log->user)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Initiated by {{ $log->user->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1 text-sm">
                                            @if($log->contacts_processed > 0)
                                                <div class="flex items-center text-blue-600 dark:text-blue-400">
                                                    <i class="fas fa-users mr-1 text-xs"></i>
                                                    <span>{{ number_format($log->contacts_processed) }} processed</span>
                                                </div>
                                            @endif
                                            @if($log->contacts_added > 0)
                                                <div class="flex items-center text-green-600 dark:text-green-400">
                                                    <i class="fas fa-plus mr-1 text-xs"></i>
                                                    <span>{{ number_format($log->contacts_added) }} added</span>
                                                </div>
                                            @endif
                                            @if($log->contacts_updated > 0)
                                                <div class="flex items-center text-yellow-600 dark:text-yellow-400">
                                                    <i class="fas fa-edit mr-1 text-xs"></i>
                                                    <span>{{ number_format($log->contacts_updated) }} updated</span>
                                                </div>
                                            @endif
                                            @if($log->contacts_skipped > 0)
                                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                                    <i class="fas fa-forward mr-1 text-xs"></i>
                                                    <span>{{ number_format($log->contacts_skipped) }} skipped</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        @if($log->completed_at && $log->started_at)
                                            @php
                                                $duration = \Carbon\Carbon::parse($log->started_at)->diffInSeconds($log->completed_at);
                                                $minutes = floor($duration / 60);
                                                $seconds = $duration % 60;
                                            @endphp
                                            <div class="flex items-center">
                                                <i class="fas fa-stopwatch text-gray-400 mr-2"></i>
                                                <span>
                                                    @if($minutes > 0)
                                                        {{ $minutes }}m {{ $seconds }}s
                                                    @else
                                                        {{ $seconds }}s
                                                    @endif
                                                </span>
                                            </div>
                                        @elseif($log->status === 'running')
                                            <div class="flex items-center text-blue-600 dark:text-blue-400">
                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                <span>Running...</span>
                                            </div>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        <div class="space-y-1">
                                            <div>{{ $log->started_at->format('M j, Y') }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $log->started_at->format('H:i:s') }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $log->started_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            @if($log->error_message)
                                                <button onclick="showErrorModal('{{ addslashes($log->error_message) }}', '{{ $log->started_at->format('M j, Y H:i:s') }}')" 
                                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                                        title="View Error">
                                                    <i class="fas fa-exclamation-circle"></i>
                                                </button>
                                            @endif
                                            @if($log->sync_details)
                                                <button onclick="showDetailsModal({{ json_encode($log->sync_details) }}, '{{ $log->started_at->format('M j, Y H:i:s') }}')" 
                                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                        title="View Details">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-history text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Sync Logs Found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">
                        @if(request()->hasAny(['status', 'direction', 'date_from', 'date_to']))
                            No sync logs match your current filters. Try adjusting your search criteria.
                        @else
                            This integration hasn't been synced yet or logs are not available.
                        @endif
                    </p>
                    @if(request()->hasAny(['status', 'direction', 'date_from', 'date_to']))
                        <a href="{{ route('google-sheets.sync-logs', $googleSheet) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Clear All Filters
                        </a>
                    @else
                        <a href="{{ route('google-sheets.show', $googleSheet) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Integration
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function showErrorModal(errorMessage, timestamp) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-4xl w-full max-h-96 overflow-auto">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sync Error Details</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">${timestamp}</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-400 mt-0.5 mr-3 flex-shrink-0"></i>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-red-800 dark:text-red-300 mb-2">Error Message</h4>
                        <pre class="text-sm text-red-700 dark:text-red-400 whitespace-pre-wrap font-mono">${errorMessage}</pre>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

function showDetailsModal(details, timestamp) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    
    // Format the details for display
    let detailsHtml = '';
    if (typeof details === 'object') {
        detailsHtml = Object.entries(details).map(([key, value]) => {
            const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            return `
                <div class="flex justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                    <span class="font-medium text-gray-900 dark:text-white">${formattedKey}:</span>
                    <span class="text-gray-600 dark:text-gray-400">${value}</span>
                </div>
            `;
        }).join('');
    } else {
        detailsHtml = `<pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${JSON.stringify(details, null, 2)}</pre>`;
    }
    
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-4xl w-full max-h-96 overflow-auto">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sync Details</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">${timestamp}</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="space-y-2">
                    ${detailsHtml}
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// Auto-refresh running syncs every 30 seconds
setInterval(function() {
    const runningElements = document.querySelectorAll('.fa-spinner');
    if (runningElements.length > 0) {
        // Only reload if there are running syncs
        window.location.reload();
    }
}, 30000);
</script>
@endpush
@endsection
