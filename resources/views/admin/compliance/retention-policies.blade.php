@extends('layouts.app')

@section('title', 'Data Retention Policies')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Data Retention Policies</h1>
                    <p class="text-gray-600 mt-1">Manage automated data cleanup and retention periods for compliance</p>
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
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_policies'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                <div class="text-xs font-medium text-green-600 uppercase mb-1">Active</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['active_policies'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
                <div class="text-xs font-medium text-yellow-600 uppercase mb-1">Auto Delete</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['auto_delete_policies'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
                <div class="text-xs font-medium text-red-600 uppercase mb-1">Overdue</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['overdue_executions'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-cyan-500 p-4">
                <div class="text-xs font-medium text-cyan-600 uppercase mb-1">To Delete</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['records_to_delete']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-500 p-4">
                <div class="text-xs font-medium text-gray-600 uppercase mb-1">Last Run</div>
                <div class="text-xl font-bold text-gray-900">
                    @if($stats['last_execution'])
                        {{ $stats['last_execution']->diffForHumans() }}
                    @else
                        Never
                    @endif
                </div>
            </div>
        </div>

        <!-- Execution Status Alert -->
        @if($stats['overdue_executions'] > 0)
            <div class="mb-8">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-yellow-800">
                                <strong>Attention:</strong> {{ $stats['overdue_executions'] }} retention policies have overdue executions. 
                                Data may be retained longer than specified.
                            </p>
                        </div>
                        <button id="executeOverdueBtn" 
                                class="ml-4 inline-flex items-center px-3 py-1.5 text-sm bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700">
                            Execute Overdue Policies
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Next Execution Info -->
        @if($stats['next_execution'])
            <div class="mb-8">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-3"></i>
                        <span class="text-sm font-medium text-blue-800">
                            <strong>Next execution:</strong> {{ $stats['next_execution']->diffForHumans() }}
                        </span>
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
                <form method="GET" action="{{ route('admin.compliance.retention-policies') }}">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input type="text" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   id="search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Policy name, description...">
                        </div>
                        <div>
                            <label for="data_type" class="block text-sm font-medium text-gray-700 mb-2">Data Type</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    id="data_type" name="data_type">
                                <option value="">All Data Types</option>
                                @foreach(\App\Models\DataRetentionPolicy::getDataTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ request('data_type') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    id="status" name="status">
                                <option value="">All</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <div class="w-full space-x-2 flex">
                                <button type="submit" 
                                        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('admin.compliance.retention-policies') }}" 
                                   class="px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <div class="flex items-end">
                            <button type="button" id="createPolicyBtn"
                                    class="w-full px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <i class="fas fa-plus mr-2"></i>Create Policy
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Retention Policies Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                        Retention Policies
                    </h3>
                    <div class="flex space-x-2">
                        <button id="bulkExecuteBtn" disabled
                                class="inline-flex items-center px-3 py-2 text-sm bg-blue-50 text-blue-600 font-medium rounded-lg hover:bg-blue-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-play mr-2"></i>
                            Execute Selected
                        </button>
                        <button id="exportBtn"
                                class="inline-flex items-center px-3 py-2 text-sm bg-gray-50 text-gray-600 font-medium rounded-lg hover:bg-gray-100">
                            <i class="fas fa-download mr-2"></i>
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>
            
            @if($retentionPolicies->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll" 
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Policy Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retention Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Affected Records</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Executed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($retentionPolicies as $policy)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="policy-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                               value="{{ $policy->id }}"
                                               {{ $policy->is_active ? '' : 'disabled' }}>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="fas {{ $policy->getDataTypeIcon() }} mr-3 text-gray-400"></i>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $policy->name }}</div>
                                                @if($policy->description)
                                                    <div class="text-sm text-gray-500">{{ Str::limit($policy->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ \App\Models\DataRetentionPolicy::getDataTypes()[$policy->data_type] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $policy->getRetentionPeriodHuman() }}</div>
                                        <div class="text-sm text-gray-500">{{ $policy->retention_period_days }} days</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $policy->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($policy->auto_delete)
                                                <div>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Auto Delete
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $affectedCount = $policy->getAffectedRecordsCount();
                                        @endphp
                                        @if($affectedCount > 0)
                                            <div class="text-sm font-medium text-red-600">
                                                {{ number_format($affectedCount) }}
                                            </div>
                                            <div class="text-sm text-gray-500">records to delete</div>
                                        @else
                                            <span class="text-gray-500">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($policy->last_executed_at)
                                            <div class="text-sm text-gray-900">{{ $policy->last_executed_at->format('M d, Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $policy->last_executed_at->diffForHumans() }}</div>
                                        @else
                                            <span class="text-gray-500 text-sm">Never executed</span>
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
                                                <a href="#" onclick="viewPolicy({{ $policy->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    View Details
                                                </a>
                                                @if($policy->is_active)
                                                    <div class="border-t border-gray-100"></div>
                                                    <a href="#" onclick="executePolicy({{ $policy->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-play mr-2"></i>
                                                        Execute Now
                                                    </a>
                                                    @if($policy->getAffectedRecordsCount() > 0)
                                                        <a href="#" onclick="previewDeletion({{ $policy->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <i class="fas fa-list mr-2"></i>
                                                            Preview Deletion
                                                        </a>
                                                    @endif
                                                @endif
                                                <div class="border-t border-gray-100"></div>
                                                <a href="#" onclick="editPolicy({{ $policy->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <i class="fas fa-edit mr-2"></i>
                                                    Edit Policy
                                                </a>
                                                <a href="#" onclick="togglePolicy({{ $policy->id }}, {{ $policy->is_active ? 'false' : 'true' }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <i class="fas fa-{{ $policy->is_active ? 'pause' : 'play' }} mr-2"></i>
                                                    {{ $policy->is_active ? 'Deactivate' : 'Activate' }}
                                                </a>
                                                <div class="border-t border-gray-100"></div>
                                                <a href="#" onclick="deletePolicy({{ $policy->id }})" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                                    <i class="fas fa-trash mr-2"></i>
                                                    Delete Policy
                                                </a>
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
                            Showing {{ $retentionPolicies->firstItem() ?? 0 }} to {{ $retentionPolicies->lastItem() ?? 0 }} 
                            of {{ $retentionPolicies->total() }} entries
                        </div>
                        <div class="flex-1 flex justify-center">
                            {{ $retentionPolicies->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-clock text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Retention Policies Found</h3>
                    <p class="text-gray-500 mb-6">
                        @if(request()->hasAny(['search', 'data_type', 'status']))
                            Try adjusting your filters to see more results.
                        @else
                            Create retention policies to automatically manage data cleanup and compliance.
                        @endif
                    </p>
                    <button id="createFirstPolicyBtn"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Create First Policy
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- View Policy Modal -->
<div id="viewPolicyModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                        <i class="fas fa-clock mr-2 text-blue-600"></i>
                        Policy Details
                    </h3>
                    <button type="button" onclick="closePolicyModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="policyDetails" class="mt-4">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Policy Modal -->
<div id="createPolicyModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="create-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="create-modal-title">
                        <i class="fas fa-plus mr-2 text-green-600"></i>
                        Create Retention Policy
                    </h3>
                    <button type="button" onclick="closeCreateModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="mt-4">
                    <p class="text-gray-500 mb-4">Create a new data retention policy to automatically manage data cleanup based on age and criteria.</p>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                            <span class="text-sm text-blue-800">Policy creation functionality would be implemented here with a comprehensive form.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-between">
                <button type="button" onclick="closeCreateModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Cancel
                </button>
                <button type="button" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Create Policy
                </button>
            </div>
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
            const checkboxes = document.querySelectorAll('.policy-checkbox:not([disabled])');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }

    // Individual checkboxes
    document.querySelectorAll('.policy-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButton);
    });

    function updateBulkButton() {
        const selected = document.querySelectorAll('.policy-checkbox:checked').length;
        const bulkBtn = document.getElementById('bulkExecuteBtn');
        if (bulkBtn) {
            bulkBtn.disabled = selected === 0;
            bulkBtn.innerHTML = `<i class="fas fa-play mr-2"></i>Execute Selected (${selected})`;
        }
    }

    // Create policy buttons
    const createBtns = ['createPolicyBtn', 'createFirstPolicyBtn'];
    createBtns.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.addEventListener('click', () => showCreateModal());
        }
    });

    // Export functionality
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'csv');
            window.location.href = window.location.pathname + '?' + params.toString();
        });
    }

    // Execute overdue policies
    const executeOverdueBtn = document.getElementById('executeOverdueBtn');
    if (executeOverdueBtn) {
        executeOverdueBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to execute all overdue retention policies? This will permanently delete data and cannot be undone.')) {
                showToast('info', 'Execute overdue policies functionality would be implemented here');
                setTimeout(() => location.reload(), 2000);
            }
        });
    }

    // Bulk execute
    const bulkExecuteBtn = document.getElementById('bulkExecuteBtn');
    if (bulkExecuteBtn) {
        bulkExecuteBtn.addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.policy-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selected.length === 0) return;
            
            if (confirm(`Are you sure you want to execute ${selected.length} selected policies? This will permanently delete data and cannot be undone.`)) {
                showToast('info', 'Bulk policy execution functionality would be implemented here');
                setTimeout(() => location.reload(), 2000);
            }
        });
    }
});

function showCreateModal() {
    document.getElementById('createPolicyModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createPolicyModal').classList.add('hidden');
}

function closePolicyModal() {
    document.getElementById('viewPolicyModal').classList.add('hidden');
}

function viewPolicy(policyId) {
    document.getElementById('policyDetails').innerHTML = 
        '<div class="text-center py-4"><i class="fas fa-spinner animate-spin text-gray-400"></i> Loading...</div>';
    
    document.getElementById('viewPolicyModal').classList.remove('hidden');
    
    // This would typically make an AJAX call to get policy details
    setTimeout(() => {
        document.getElementById('policyDetails').innerHTML = 
            '<p class="text-gray-600">Detailed policy information would be loaded here via AJAX.</p>';
    }, 500);
}

function executePolicy(policyId) {
    if (confirm('Are you sure you want to execute this retention policy? This will permanently delete data and cannot be undone.')) {
        // Submit execution request
        fetch(`/admin/compliance/execute-retention-policy/${policyId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', `Policy executed successfully. ${data.deleted_count || 0} records deleted.`);
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', 'Error executing policy: ' + data.message);
            }
        })
        .catch(error => {
            showToast('error', 'Error executing policy: ' + error.message);
        });
    }
}

function previewDeletion(policyId) {
    showToast('info', 'Preview deletion functionality would show which records would be affected');
}

function editPolicy(policyId) {
    showToast('info', 'Policy editing functionality would be implemented here');
}

function togglePolicy(policyId, activate) {
    const action = activate === 'true' ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this policy?`)) {
        showToast('info', `Policy ${action} functionality would be implemented here`);
        setTimeout(() => location.reload(), 2000);
    }
}

function deletePolicy(policyId) {
    if (confirm('Are you sure you want to delete this policy? This action cannot be undone.')) {
        showToast('info', 'Policy deletion functionality would be implemented here');
        setTimeout(() => location.reload(), 2000);
    }
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
</script>
@endpush
