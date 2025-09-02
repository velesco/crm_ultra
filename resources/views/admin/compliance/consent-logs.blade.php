@extends('layouts.app')

@section('title', 'Consent Logs Management')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Consent Logs Management</h1>
                    <p class="text-gray-600 mt-1">Track and manage user consent across all communication channels</p>
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
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_consents'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                <div class="text-xs font-medium text-green-600 uppercase mb-1">Given</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['given_consents'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
                <div class="text-xs font-medium text-yellow-600 uppercase mb-1">Withdrawn</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['withdrawn_consents'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
                <div class="text-xs font-medium text-red-600 uppercase mb-1">Expired</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['expired_consents'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-cyan-500 p-4">
                <div class="text-xs font-medium text-cyan-600 uppercase mb-1">Email</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['email_consents'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-500 p-4">
                <div class="text-xs font-medium text-gray-600 uppercase mb-1">Marketing</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['marketing_consents'] }}</div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-500"></i>
                    Filters & Search
                </h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('admin.compliance.consent-logs') }}">
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
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    id="status" name="status">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\ConsentLog::getStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="consent_type" class="block text-sm font-medium text-gray-700 mb-2">Consent Type</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    id="consent_type" name="consent_type">
                                <option value="">All Types</option>
                                @foreach(\App\Models\ConsentLog::getConsentTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ request('consent_type') == $key ? 'selected' : '' }}>
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
                            <a href="{{ route('admin.compliance.consent-logs') }}" 
                               class="px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Consent Logs Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-list text-blue-500 mr-2"></i>
                        Consent Logs
                        @if($consentLogs->total() > 0)
                            <span class="ml-2 text-sm text-gray-500">({{ $consentLogs->total() }} total)</span>
                        @endif
                    </h3>
                    <div class="flex space-x-2">
                        <button onclick="exportLogs()" 
                                class="inline-flex items-center px-3 py-2 text-sm bg-green-50 text-green-600 font-medium rounded-lg hover:bg-green-100">
                            <i class="fas fa-download mr-2"></i>
                            Export
                        </button>
                        <button onclick="refreshTable()" 
                                class="inline-flex items-center px-3 py-2 text-sm bg-blue-50 text-blue-600 font-medium rounded-lg hover:bg-blue-100">
                            <i class="fas fa-sync mr-2"></i>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            @if($consentLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consent Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Legal Basis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Given Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Withdrawn Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($consentLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->contact)
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium mr-3">
                                                    {{ strtoupper(substr($log->contact->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $log->contact->full_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $log->contact->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-500 text-sm">Contact not found</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="fas fa-{{ $log->getConsentTypeIcon() }} mr-2 text-blue-500"></i>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ str_replace('_', ' ', ucwords($log->consent_type)) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = match($log->status) {
                                                'given' => 'bg-green-100 text-green-800',
                                                'withdrawn' => 'bg-yellow-100 text-yellow-800',
                                                'expired' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                        @if($log->isActive())
                                            <div class="text-xs text-green-600 mt-1">Active</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs text-gray-600">{{ ucwords(str_replace('_', ' ', $log->legal_basis)) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs text-gray-600">{{ ucfirst($log->source) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->given_at)
                                            <div class="text-sm text-gray-900">{{ $log->given_at->format('M j, Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $log->given_at->format('H:i') }}</div>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->withdrawn_at)
                                            <div class="text-sm text-gray-900">{{ $log->withdrawn_at->format('M j, Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $log->withdrawn_at->format('H:i') }}</div>
                                        @else
                                            <span class="text-gray-500">-</span>
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
                                                <a href="#" onclick="viewLog({{ $log->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    View Details
                                                </a>
                                                @if($log->contact)
                                                    <a href="{{ route('contacts.show', $log->contact) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-user mr-2"></i>
                                                        View Contact
                                                    </a>
                                                @endif
                                                @if($log->status === 'given')
                                                    <div class="border-t border-gray-100"></div>
                                                    <a href="#" onclick="withdrawConsent({{ $log->id }})" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                                        <i class="fas fa-times mr-2"></i>
                                                        Withdraw Consent
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
                            Showing {{ $consentLogs->firstItem() ?? 0 }} to {{ $consentLogs->lastItem() ?? 0 }} 
                            of {{ $consentLogs->total() }} entries
                        </div>
                        <div class="flex-1 flex justify-center">
                            {{ $consentLogs->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-list text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Consent Logs Found</h3>
                    <p class="text-gray-500">
                        @if(request()->hasAny(['search', 'status', 'consent_type', 'date_from', 'date_to']))
                            Try adjusting your filters to see more results.
                        @else
                            Consent logs will appear here when users provide or withdraw consent.
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
function viewLog(logId) {
    showToast('info', 'Consent log details view coming soon');
}

function withdrawConsent(logId) {
    if (confirm('Are you sure you want to withdraw this consent? This action cannot be undone.')) {
        fetch(`/admin/compliance/consent-logs/${logId}/withdraw`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ notes: 'Manual withdrawal from admin panel' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', data.message || 'Error withdrawing consent');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Network error occurred');
        });
    }
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = window.location.pathname + '?' + params.toString();
}

function refreshTable() {
    location.reload();
}

function showToast(type, message) {
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

// Auto-refresh every 3 minutes
setInterval(refreshTable, 180000);
</script>
@endpush
