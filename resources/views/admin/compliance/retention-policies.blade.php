@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-1 text-gray-800">Data Retention Policies</h2>
                    <p class="text-muted mb-0">Manage automated data cleanup and retention periods for compliance</p>
                </div>
                <div>
                    <a href="{{ route('admin.compliance.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_policies'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_policies'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Auto Delete</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['auto_delete_policies'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['overdue_executions'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">To Delete</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['records_to_delete']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Last Run</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        @if($stats['last_execution'])
                            {{ $stats['last_execution']->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Execution Status Alert -->
    @if($stats['overdue_executions'] > 0)
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Attention:</strong> {{ $stats['overdue_executions'] }} retention policies have overdue executions. 
            Data may be retained longer than specified.
            <button id="executeOverdueBtn" class="btn btn-sm btn-outline-warning ml-2">
                Execute Overdue Policies
            </button>
        </div>
    @endif

    <!-- Next Execution Info -->
    @if($stats['next_execution'])
        <div class="alert alert-info mb-4">
            <i class="fas fa-clock mr-2"></i>
            <strong>Next execution:</strong> {{ $stats['next_execution']->diffForHumans() }}
        </div>
    @endif

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>
                Filters & Search
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.compliance.retention-policies') }}">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Policy name, description...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="data_type" class="form-label">Data Type</label>
                        <select class="form-control" id="data_type" name="data_type">
                            <option value="">All Data Types</option>
                            @foreach(\App\Models\DataRetentionPolicy::getDataTypes() as $key => $label)
                                <option value="{{ $key }}" {{ request('data_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('admin.compliance.retention-policies') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-success" id="createPolicyBtn">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Retention Policies Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clock mr-2"></i>
                Retention Policies
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" id="bulkExecuteBtn" disabled>
                    <i class="fas fa-play mr-1"></i>
                    Execute Selected
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="exportBtn">
                    <i class="fas fa-download mr-1"></i>
                    Export CSV
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($retentionPolicies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Policy Name</th>
                                <th>Data Type</th>
                                <th>Retention Period</th>
                                <th>Status</th>
                                <th>Affected Records</th>
                                <th>Last Executed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($retentionPolicies as $policy)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="policy-checkbox" value="{{ $policy->id }}"
                                               {{ $policy->is_active ? '' : 'disabled' }}>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas {{ $policy->getDataTypeIcon() }} mr-2 text-muted"></i>
                                            <div>
                                                <div class="font-weight-medium">{{ $policy->name }}</div>
                                                @if($policy->description)
                                                    <small class="text-muted">{{ Str::limit($policy->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light">
                                            {{ \App\Models\DataRetentionPolicy::getDataTypes()[$policy->data_type] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-medium">{{ $policy->getRetentionPeriodHuman() }}</span>
                                        <br>
                                        <small class="text-muted">{{ $policy->retention_period_days }} days</small>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge badge-{{ $policy->is_active ? 'success' : 'secondary' }}">
                                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($policy->auto_delete)
                                                <br><span class="badge badge-warning badge-sm">Auto Delete</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $affectedCount = $policy->getAffectedRecordsCount();
                                        @endphp
                                        @if($affectedCount > 0)
                                            <span class="text-danger font-weight-medium">
                                                {{ number_format($affectedCount) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">records to delete</small>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($policy->last_executed_at)
                                            <div>
                                                <div class="font-weight-medium">{{ $policy->last_executed_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $policy->last_executed_at->diffForHumans() }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Never executed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" onclick="viewPolicy({{ $policy->id }})">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    View Details
                                                </a>
                                                @if($policy->is_active)
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="#" onclick="executePolicy({{ $policy->id }})">
                                                        <i class="fas fa-play mr-2"></i>
                                                        Execute Now
                                                    </a>
                                                    @if($policy->getAffectedRecordsCount() > 0)
                                                        <a class="dropdown-item" href="#" onclick="previewDeletion({{ $policy->id }})">
                                                            <i class="fas fa-list mr-2"></i>
                                                            Preview Deletion
                                                        </a>
                                                    @endif
                                                @endif
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="#" onclick="editPolicy({{ $policy->id }})">
                                                    <i class="fas fa-edit mr-2"></i>
                                                    Edit Policy
                                                </a>
                                                <a class="dropdown-item" href="#" onclick="togglePolicy({{ $policy->id }}, {{ $policy->is_active ? 'false' : 'true' }})">
                                                    <i class="fas fa-{{ $policy->is_active ? 'pause' : 'play' }} mr-2"></i>
                                                    {{ $policy->is_active ? 'Deactivate' : 'Activate' }}
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#" onclick="deletePolicy({{ $policy->id }})">
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
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $retentionPolicies->firstItem() ?? 0 }} to {{ $retentionPolicies->lastItem() ?? 0 }} 
                            of {{ $retentionPolicies->total() }} entries
                        </div>
                        {{ $retentionPolicies->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No Retention Policies Found</h5>
                    <p class="text-muted mb-3">
                        @if(request()->hasAny(['search', 'data_type', 'status']))
                            Try adjusting your filters to see more results.
                        @else
                            Create retention policies to automatically manage data cleanup and compliance.
                        @endif
                    </p>
                    <button class="btn btn-primary" id="createFirstPolicyBtn">
                        <i class="fas fa-plus mr-2"></i>
                        Create First Policy
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- View Policy Modal -->
<div class="modal fade" id="viewPolicyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock mr-2"></i>
                    Policy Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="policyDetails">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Policy Modal -->
<div class="modal fade" id="createPolicyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>
                    Create Retention Policy
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">Create a new data retention policy to automatically manage data cleanup based on age and criteria.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Policy creation functionality would be implemented here with a comprehensive form.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Create Policy</button>
            </div>
        </div>
    </div>
</div>

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
            bulkBtn.innerHTML = `<i class="fas fa-play mr-1"></i>Execute Selected (${selected})`;
        }
    }

    // Create policy buttons
    const createBtns = ['createPolicyBtn', 'createFirstPolicyBtn'];
    createBtns.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.addEventListener('click', () => $('#createPolicyModal').modal('show'));
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
                alert('Execute overdue policies functionality would be implemented here');
                location.reload();
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
                alert('Bulk policy execution functionality would be implemented here');
                location.reload();
            }
        });
    }
});

function viewPolicy(policyId) {
    document.getElementById('policyDetails').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    $('#viewPolicyModal').modal('show');
    
    // This would typically make an AJAX call to get policy details
    setTimeout(() => {
        document.getElementById('policyDetails').innerHTML = 
            '<p>Detailed policy information would be loaded here via AJAX.</p>';
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
                alert(`Policy executed successfully. ${data.deleted_count || 0} records deleted.`);
                location.reload();
            } else {
                alert('Error executing policy: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error executing policy: ' + error.message);
        });
    }
}

function previewDeletion(policyId) {
    alert('Preview deletion functionality would show which records would be affected');
}

function editPolicy(policyId) {
    alert('Policy editing functionality would be implemented here');
}

function togglePolicy(policyId, activate) {
    const action = activate === 'true' ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this policy?`)) {
        alert(`Policy ${action} functionality would be implemented here`);
        location.reload();
    }
}

function deletePolicy(policyId) {
    if (confirm('Are you sure you want to delete this policy? This action cannot be undone.')) {
        alert('Policy deletion functionality would be implemented here');
        location.reload();
    }
}
</script>
@endpush

<style>
.gap-2 > * + * {
    margin-left: 0.5rem;
}

.badge-sm {
    font-size: 0.75em;
}
</style>
@endsection