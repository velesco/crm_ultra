@extends('layouts.app')

@section('title', 'Data Requests Management')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Data Requests Management</h1>
                    <p class="text-gray-600 mt-1">Manage GDPR data export and deletion requests from users</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.compliance.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
                <div class="text-xs font-medium text-blue-600 uppercase mb-1">Total</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_requests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-cyan-500 p-4">
                <div class="text-xs font-medium text-cyan-600 uppercase mb-1">Export</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['export_requests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
                <div class="text-xs font-medium text-yellow-600 uppercase mb-1">Delete</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['delete_requests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-500 p-4">
                <div class="text-xs font-medium text-gray-600 uppercase mb-1">Pending</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['pending_requests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                <div class="text-xs font-medium text-green-600 uppercase mb-1">Completed</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['completed_requests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
                <div class="text-xs font-medium text-red-600 uppercase mb-1">Overdue</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['overdue_requests'] }}</div>
            </div>
        </div>

        <!-- Processing Time Alert -->
        @if($stats['avg_processing_time'] > 48)
            <div class="mb-8">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">
                                <strong>Attention:</strong> Average processing time is {{ number_format($stats['avg_processing_time'], 1) }} hours. 
                                GDPR requires processing within 30 days (720 hours).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-500"></i>
                    Filters & Search
                </h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('admin.compliance.data-requests') }}">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   id="search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Contact name, email...">
                        </div>
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Request Type</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    id="type" name="type">
                                <option value="">All Types</option>
                                @foreach(\App\Models\DataRequest::getRequestTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    id="status" name="status">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\DataRequest::getStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                            <input type="date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   id="date_from" name="date_from" 
                                   value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                            <input type="date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   id="date_to" name="date_to" 
                                   value="{{ request('date_to') }}">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('admin.compliance.data-requests') }}" 
                               class="px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Requests Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-file-text mr-2 text-blue-500"></i>
                        Data Requests
                    </h3>
                    <div class="flex space-x-2">
                        <button id="bulkProcessBtn" disabled
                                class="inline-flex items-center px-3 py-2 text-sm bg-blue-50 text-blue-600 font-medium rounded-lg hover:bg-blue-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-play mr-2"></i>
                            Process Selected
                        </button>
                        <button id="exportBtn"
                                class="inline-flex items-center px-3 py-2 text-sm bg-gray-50 text-gray-600 font-medium rounded-lg hover:bg-gray-100">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>
            
            @if($dataRequests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll" 
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processing Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($dataRequests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="request-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                               value="{{ $request->id }}"
                                               {{ $request->canBeProcessed() ? '' : 'disabled' }}>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($request->contact)
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium mr-3">
                                                    {{ strtoupper(substr($request->contact->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $request->contact->full_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $request->contact->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-gray-400 text-white rounded-full flex items-center justify-center text-sm font-medium mr-3">
                                                    ?
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $request->full_name ?? 'Unknown' }}</div>
                                                    <div class="text-sm text-gray-500">{{ $request->email }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="fas {{ $request->getRequestTypeIcon() }} mr-2 text-gray-400"></i>
                                            <span class="text-sm text-gray-900">{{ ucfirst($request->request_type) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = match($request->getStatusBadgeClass()) {
                                                'success' => 'bg-green-100 text-green-800',
                                                'warning' => 'bg-yellow-100 text-yellow-800',
                                                'danger' => 'bg-red-100 text-red-800',
                                                'info' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                        @if($request->isExpired())
                                            <div class="text-xs text-red-600 mt-1">Expired</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->created_at->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $request->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($request->status === 'completed' && $request->processed_at && $request->completed_at)
                                            @php
                                                $processingHours = $request->processed_at->diffInHours($request->completed_at);
                                                $timeClass = $processingHours > 48 ? 'bg-red-100 text-red-800' : ($processingHours > 24 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800');
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $timeClass }}">
                                                {{ $processingHours }}h
                                            </span>
                                        @elseif($request->processed_at)
                                            @php
                                                $processingHours = $request->processed_at->diffInHours(now());
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $processingHours }}h ongoing
                                            </span>
                                        @else
                                            @php
                                                $waitingHours = $request->created_at->diffInHours(now());
                                                $waitClass = $waitingHours > 168 ? 'bg-red-100 text-red-800' : ($waitingHours > 48 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $waitClass }}">
                                                {{ $waitingHours }}h waiting
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div x-show="open" @click.away="open = false" 
                                                 x-transition:enter="transition ease-out duration-200" 
                                                 x-transition:enter-start="opacity-0 transform scale-95" 
                                                 x-transition:enter-end="opacity-100 transform scale-100" 
                                                 x-transition:leave="transition ease-in duration-75" 
                                                 x-transition:leave-start="opacity-100 transform scale-100" 
                                                 x-transition:leave-end="opacity-0 transform scale-95"
                                                 class="absolute right-0 top-10 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                                <a href="#" onclick="viewRequest({{ $request->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    View Details
                                                </a>
                                                @if($request->canBeProcessed())
                                                    <div class="border-t border-gray-100"></div>
                                                    <a href="#" onclick="processRequest({{ $request->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-play mr-2"></i>
                                                        Process Request
                                                    </a>
                                                @endif
                                                @if($request->request_type === 'export' && $request->status === 'completed' && $request->file_path)
                                                    <a href="{{ route('admin.compliance.download-export', $request) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-download mr-2"></i>
                                                        Download Export
                                                    </a>
                                                @endif
                                                @if($request->status === 'pending')
                                                    <div class="border-t border-gray-100"></div>
                                                    <a href="#" onclick="rejectRequest({{ $request->id }})" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                                        <i class="fas fa-times mr-2"></i>
                                                        Reject Request
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-white">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ $dataRequests->firstItem() ?? 0 }} to {{ $dataRequests->lastItem() ?? 0 }} 
                            of {{ $dataRequests->total() }} entries
                        </div>
                        <div class="flex-1 flex justify-center">
                            {{ $dataRequests->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-file-text text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Data Requests Found</h3>
                    <p class="text-gray-500">
                        @if(request()->hasAny(['search', 'type', 'status', 'date_from', 'date_to']))
                            Try adjusting your filters to see more results.
                        @else
                            Data requests will appear here when users submit GDPR requests.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.request-checkbox:not([disabled])');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }

    // Individual checkboxes
    document.querySelectorAll('.request-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButton);
    });

    function updateBulkButton() {
        const selected = document.querySelectorAll('.request-checkbox:checked').length;
        const bulkBtn = document.getElementById('bulkProcessBtn');
        if (bulkBtn) {
            bulkBtn.disabled = selected === 0;
            bulkBtn.innerHTML = `<i class="fas fa-play mr-2"></i>Process Selected (${selected})`;
        }
    }

    // Export functionality
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'csv');
            window.location.href = window.location.pathname + '?' + params.toString();
        });
    }

    // Bulk process
    const bulkProcessBtn = document.getElementById('bulkProcessBtn');
    if (bulkProcessBtn) {
        bulkProcessBtn.addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.request-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selected.length === 0) return;
            
            if (confirm(`Are you sure you want to process ${selected.length} selected requests? This action cannot be undone.`)) {
                showToast('Bulk processing functionality would be implemented here', 'info');
            }
        });
    }
});

function viewRequest(requestId) {
    showToast('Request details view would be implemented here', 'info');
}

function processRequest(requestId) {
    if (confirm('Are you sure you want to process this data request? This action cannot be undone.')) {
        showToast('Request processing functionality would be implemented here', 'info');
        setTimeout(() => location.reload(), 1000);
    }
}

function rejectRequest(requestId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason && reason.trim()) {
        showToast('Request rejection functionality would be implemented here', 'info');
        setTimeout(() => location.reload(), 1000);
    }
}

function showToast(message, type = 'info') {
    const toastClasses = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'info': 'bg-blue-500',
        'warning': 'bg-yellow-500'
    }[type] || 'bg-blue-500';
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${toastClasses} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    toast.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}
</script>
@endpush
