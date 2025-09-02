@extends('layouts.app')

@section('title', 'Consent Management')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        Consent Management
                    </h1>
                    <p class="text-gray-600 mt-1">Manage user consents and privacy preferences</p>
                </div>
                
                <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
                    <a href="{{ route('admin.compliance.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <button type="button" onclick="showCreateModal()" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <i class="fas fa-plus mr-2"></i>Record Consent
                    </button>
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-cogs mr-2"></i>Actions
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 transform scale-95" 
                             x-transition:enter-end="opacity-100 transform scale-100" 
                             x-transition:leave="transition ease-in duration-75" 
                             x-transition:leave-start="opacity-100 transform scale-100" 
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 top-12 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <a href="#" onclick="exportConsents('csv')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-file-csv mr-2"></i>Export to CSV
                            </a>
                            <a href="#" onclick="exportConsents('json')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-file-code mr-2"></i>Export to JSON
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <a href="#" onclick="bulkWithdraw()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-times-circle mr-2"></i>Bulk Withdraw
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-medium text-green-600 uppercase mb-1">
                                Active Consents
                            </div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['given'] }}</div>
                        </div>
                        <i class="fas fa-check-circle text-2xl text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-medium text-yellow-600 uppercase mb-1">
                                Withdrawn Consents
                            </div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['withdrawn'] }}</div>
                        </div>
                        <i class="fas fa-times-circle text-2xl text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border-l-4 border-red-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-medium text-red-600 uppercase mb-1">
                                Expired Consents
                            </div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['expired'] }}</div>
                        </div>
                        <i class="fas fa-exclamation-triangle text-2xl text-gray-300"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-medium text-blue-600 uppercase mb-1">
                                Total Records
                            </div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                        </div>
                        <i class="fas fa-database text-2xl text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-500"></i>Filters
                </h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('admin.compliance.consent-management') }}" id="filtersForm">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\ConsentLog::getStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Consent Type</label>
                            <select name="consent_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Types</option>
                                @foreach(\App\Models\ConsentLog::getConsentTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ request('consent_type') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Contact name/email"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex items-end">
                            <div class="w-full space-y-2">
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-search mr-2"></i>Apply
                                </button>
                                <a href="{{ route('admin.compliance.consent-management') }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    <i class="fas fa-times mr-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Consent Logs Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Consent Records ({{ $consentLogs->total() }})
                    </h3>
                    <button type="button" onclick="refreshTable()" 
                            class="inline-flex items-center px-3 py-2 text-sm bg-blue-50 text-blue-600 font-medium rounded-lg hover:bg-blue-100">
                        <i class="fas fa-sync mr-2"></i>Refresh
                    </button>
                </div>
            </div>
            
            @if($consentLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" 
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
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
                            @foreach($consentLogs as $consent)
                            <tr class="consent-row hover:bg-gray-50" data-id="{{ $consent->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="consent-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                           value="{{ $consent->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consent->contact)
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $consent->contact->first_name }} {{ $consent->contact->last_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $consent->contact->email }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-500 text-sm">Contact not found</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-{{ $consent->getConsentTypeIcon() }} mr-2 text-blue-500"></i>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ str_replace('_', ' ', ucwords($consent->consent_type)) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match($consent->getStatusBadgeClass()) {
                                            'success' => 'bg-green-100 text-green-800',
                                            'warning' => 'bg-yellow-100 text-yellow-800',
                                            'danger' => 'bg-red-100 text-red-800',
                                            'info' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ ucfirst($consent->status) }}
                                    </span>
                                    @if($consent->isActive())
                                        <div class="text-xs text-green-600 mt-1">Active</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs text-gray-600">{{ ucwords(str_replace('_', ' ', $consent->legal_basis)) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs text-gray-600">{{ ucfirst($consent->source) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consent->given_at)
                                        <div class="text-sm text-gray-900">{{ $consent->given_at->format('M j, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $consent->given_at->format('H:i') }}</div>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($consent->withdrawn_at)
                                        <div class="text-sm text-gray-900">{{ $consent->withdrawn_at->format('M j, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $consent->withdrawn_at->format('H:i') }}</div>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-1">
                                        <button type="button" onclick="viewConsent({{ $consent->id }})" 
                                                class="inline-flex items-center px-2 py-1 text-xs bg-blue-50 text-blue-700 rounded hover:bg-blue-100" 
                                                title="View Details">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </button>
                                        @if($consent->status === 'given')
                                            <button type="button" onclick="withdrawConsent({{ $consent->id }})" 
                                                    class="inline-flex items-center px-2 py-1 text-xs bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100" 
                                                    title="Withdraw">
                                                <i class="fas fa-times mr-1"></i>Withdraw
                                            </button>
                                        @endif
                                        @if($consent->contact)
                                            <a href="{{ route('contacts.show', $consent->contact) }}" 
                                               class="inline-flex items-center px-2 py-1 text-xs bg-gray-50 text-gray-700 rounded hover:bg-gray-100" 
                                               title="View Contact">
                                                <i class="fas fa-user mr-1"></i>Contact
                                            </a>
                                        @endif
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
                            Showing {{ $consentLogs->firstItem() }} to {{ $consentLogs->lastItem() }} 
                            of {{ $consentLogs->total() }} results
                        </div>
                        <div class="flex-1 flex justify-center">
                            {{ $consentLogs->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-check-circle text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Consent Records Found</h3>
                    <p class="text-gray-500">No consent records match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Consent Modal -->
<div id="createConsentModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                        <i class="fas fa-plus mr-2 text-green-600"></i>
                        Record New Consent
                    </h3>
                    <button type="button" onclick="closeCreateModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="createConsentForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contactSelect" class="block text-sm font-medium text-gray-700 mb-2">Contact *</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                    id="contactSelect" name="contact_id" required>
                                <option value="">Search and select contact...</option>
                            </select>
                        </div>
                        <div>
                            <label for="consentType" class="block text-sm font-medium text-gray-700 mb-2">Consent Type *</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                    id="consentType" name="consent_type" required>
                                <option value="">Select consent type</option>
                                @foreach(\App\Models\ConsentLog::getConsentTypes() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="legalBasis" class="block text-sm font-medium text-gray-700 mb-2">Legal Basis *</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                    id="legalBasis" name="legal_basis" required>
                                @foreach(\App\Models\ConsentLog::getLegalBasisTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ $key === 'consent' ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700 mb-2">Source *</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                    id="source" name="source" required>
                                @foreach(\App\Models\ConsentLog::getSourceTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ $key === 'manual' ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   id="purpose" name="purpose" 
                                   placeholder="Purpose for processing personal data">
                        </div>
                        <div>
                            <label for="retentionPeriod" class="block text-sm font-medium text-gray-700 mb-2">Retention Period (days)</label>
                            <input type="number" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   id="retentionPeriod" name="retention_period" 
                                   placeholder="Optional">
                        </div>
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Additional notes about this consent..."></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-between">
                <button type="button" onclick="closeCreateModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Cancel
                </button>
                <button type="button" onclick="submitConsent()" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-save mr-2"></i>Record Consent
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Withdraw Consent Modal -->
<div id="withdrawModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="withdraw-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="withdraw-modal-title">
                        <i class="fas fa-times-circle mr-2 text-yellow-600"></i>
                        Withdraw Consent
                    </h3>
                    <button type="button" onclick="closeWithdrawModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="withdrawForm">
                    <input type="hidden" id="withdrawConsentId">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                            <span class="text-sm text-yellow-800">Are you sure you want to withdraw this consent? This action cannot be undone.</span>
                        </div>
                    </div>
                    <div>
                        <label for="withdrawNotes" class="block text-sm font-medium text-gray-700 mb-2">Withdrawal Reason</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" 
                                  id="withdrawNotes" name="notes" rows="3" 
                                  placeholder="Reason for withdrawing consent..."></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-between">
                <button type="button" onclick="closeWithdrawModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Cancel
                </button>
                <button type="button" onclick="submitWithdraw()" 
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <i class="fas fa-times mr-2"></i>Withdraw Consent
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedConsents = [];

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.consent-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedConsents();
}

function updateSelectedConsents() {
    const checkboxes = document.querySelectorAll('.consent-checkbox:checked');
    selectedConsents = Array.from(checkboxes).map(cb => cb.value);
}

function showCreateModal() {
    document.getElementById('createConsentModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createConsentModal').classList.add('hidden');
    document.getElementById('createConsentForm').reset();
}

function submitConsent() {
    const form = document.getElementById('createConsentForm');
    const formData = new FormData(form);
    
    fetch('{{ route("admin.compliance.create-consent-log") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCreateModal();
            showToast('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('error', data.message || 'Error recording consent');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Network error occurred');
    });
}

function withdrawConsent(consentId) {
    document.getElementById('withdrawConsentId').value = consentId;
    document.getElementById('withdrawModal').classList.remove('hidden');
}

function closeWithdrawModal() {
    document.getElementById('withdrawModal').classList.add('hidden');
    document.getElementById('withdrawForm').reset();
}

function submitWithdraw() {
    const consentId = document.getElementById('withdrawConsentId').value;
    const notes = document.getElementById('withdrawNotes').value;
    
    fetch(`/admin/compliance/consent-logs/${consentId}/withdraw`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeWithdrawModal();
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

function viewConsent(consentId) {
    showToast('info', 'Consent details view coming soon');
}

function exportConsents(format) {
    const url = new URL('{{ route("admin.compliance.export-data") }}');
    url.searchParams.append('type', 'consent_logs');
    url.searchParams.append('format', format);
    
    // Add current filters
    const form = new FormData(document.getElementById('filtersForm'));
    for (let [key, value] of form.entries()) {
        if (value) url.searchParams.append(key, value);
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Export started. Download will begin shortly.');
                window.location.href = data.download_url;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Export failed');
        });
}

function refreshTable() {
    location.reload();
}

function bulkWithdraw() {
    updateSelectedConsents();
    
    if (selectedConsents.length === 0) {
        showToast('warning', 'Please select at least one consent record');
        return;
    }
    
    showToast('info', 'Bulk withdrawal feature coming soon');
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

// Initialize contact search and event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize contact select
    const contactSelect = document.getElementById('contactSelect');
    
    // Load contacts via AJAX - simplified for demo
    fetch('/api/contacts/search')
        .then(response => response.json())
        .then(data => {
            data.forEach(contact => {
                const option = document.createElement('option');
                option.value = contact.id;
                option.textContent = `${contact.first_name} ${contact.last_name} (${contact.email})`;
                contactSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading contacts:', error));
    
    // Update selected consents when checkboxes change
    document.querySelectorAll('.consent-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedConsents);
    });
});

// Auto-refresh every 3 minutes
setInterval(refreshTable, 180000);
</script>
@endpush
