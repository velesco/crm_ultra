@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-check-circle text-success me-2"></i>
                Consent Management
            </h1>
            <p class="text-muted mb-0">Manage user consents and privacy preferences</p>
        </div>
        
        <div class="btn-group">
            <a href="{{ route('admin.compliance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createConsentModal">
                <i class="fas fa-plus me-2"></i>Record Consent
            </button>
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-cog me-2"></i>Actions
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="exportConsents('csv')">
                    <i class="fas fa-file-csv me-2"></i>Export to CSV
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="exportConsents('json')">
                    <i class="fas fa-file-code me-2"></i>Export to JSON
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="bulkWithdraw()">
                    <i class="fas fa-times-circle me-2"></i>Bulk Withdraw
                </a></li>
            </ul>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Consents
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['given'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Withdrawn Consents
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['withdrawn'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Expired Consents
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expired'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Records
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.compliance.consent-management') }}" id="filtersForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\ConsentLog::getStatuses() as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Consent Type</label>
                            <select name="consent_type" class="form-control">
                                <option value="">All Types</option>
                                @foreach(\App\Models\ConsentLog::getConsentTypes() as $key => $label)
                                    <option value="{{ $key }}" {{ request('consent_type') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Contact name/email" value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.compliance.consent-management') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Consent Logs Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Consent Records ({{ $consentLogs->total() }})
            </h6>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($consentLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Contact</th>
                                <th>Consent Type</th>
                                <th>Status</th>
                                <th>Legal Basis</th>
                                <th>Source</th>
                                <th>Given Date</th>
                                <th>Withdrawn Date</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consentLogs as $consent)
                            <tr class="consent-row" data-id="{{ $consent->id }}">
                                <td>
                                    <input type="checkbox" class="consent-checkbox" value="{{ $consent->id }}">
                                </td>
                                <td>
                                    @if($consent->contact)
                                        <div>
                                            <strong>{{ $consent->contact->first_name }} {{ $consent->contact->last_name }}</strong><br>
                                            <small class="text-muted">{{ $consent->contact->email }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Contact not found</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-{{ $consent->getConsentTypeIcon() }} me-2 text-primary"></i>
                                        <span class="badge bg-light text-dark">
                                            {{ str_replace('_', ' ', ucwords($consent->consent_type)) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $consent->getStatusBadgeClass() }}">
                                        {{ ucfirst($consent->status) }}
                                    </span>
                                    @if($consent->isActive())
                                        <br><small class="text-success">Active</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ ucwords(str_replace('_', ' ', $consent->legal_basis)) }}</small>
                                </td>
                                <td>
                                    <small>{{ ucfirst($consent->source) }}</small>
                                </td>
                                <td>
                                    @if($consent->given_at)
                                        <small class="text-muted">
                                            {{ $consent->given_at->format('M j, Y') }}<br>
                                            {{ $consent->given_at->format('H:i') }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($consent->withdrawn_at)
                                        <small class="text-muted">
                                            {{ $consent->withdrawn_at->format('M j, Y') }}<br>
                                            {{ $consent->withdrawn_at->format('H:i') }}
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                onclick="viewConsent({{ $consent->id }})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($consent->status === 'given')
                                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                                    onclick="withdrawConsent({{ $consent->id }})" title="Withdraw">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        @if($consent->contact)
                                            <a href="{{ route('contacts.show', $consent->contact) }}" 
                                               class="btn btn-outline-secondary btn-sm" title="View Contact">
                                                <i class="fas fa-user"></i>
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
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        Showing {{ $consentLogs->firstItem() }} to {{ $consentLogs->lastItem() }} 
                        of {{ $consentLogs->total() }} results
                    </div>
                    {{ $consentLogs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-5x text-gray-300 mb-4"></i>
                    <h4 class="text-gray-500">No Consent Records Found</h4>
                    <p class="text-gray-400">No consent records match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Consent Modal -->
<div class="modal fade" id="createConsentModal" tabindex="-1" aria-labelledby="createConsentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createConsentModalLabel">
                    <i class="fas fa-plus me-2"></i>Record New Consent
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createConsentForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="contactSelect">Contact *</label>
                                <select class="form-control" id="contactSelect" name="contact_id" required>
                                    <option value="">Search and select contact...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="consentType">Consent Type *</label>
                                <select class="form-control" id="consentType" name="consent_type" required>
                                    <option value="">Select consent type</option>
                                    @foreach(\App\Models\ConsentLog::getConsentTypes() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="legalBasis">Legal Basis *</label>
                                <select class="form-control" id="legalBasis" name="legal_basis" required>
                                    @foreach(\App\Models\ConsentLog::getLegalBasisTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ $key === 'consent' ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="source">Source *</label>
                                <select class="form-control" id="source" name="source" required>
                                    @foreach(\App\Models\ConsentLog::getSourceTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ $key === 'manual' ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label for="purpose">Purpose</label>
                                <input type="text" class="form-control" id="purpose" name="purpose" 
                                       placeholder="Purpose for processing personal data">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="retentionPeriod">Retention Period (days)</label>
                                <input type="number" class="form-control" id="retentionPeriod" name="retention_period" 
                                       placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Additional notes about this consent..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitConsent()">
                    <i class="fas fa-save me-2"></i>Record Consent
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Withdraw Consent Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Withdraw Consent
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="withdrawForm">
                    <input type="hidden" id="withdrawConsentId">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to withdraw this consent? This action cannot be undone.
                    </div>
                    <div class="form-group">
                        <label for="withdrawNotes">Withdrawal Reason</label>
                        <textarea class="form-control" id="withdrawNotes" name="notes" rows="3" 
                                  placeholder="Reason for withdrawing consent..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitWithdraw()">
                    <i class="fas fa-times me-2"></i>Withdraw Consent
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
            $('#createConsentModal').modal('hide');
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
    $('#withdrawModal').modal('show');
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
            $('#withdrawModal').modal('hide');
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
    // Implementation for viewing consent details
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
    
    // Implementation for bulk withdrawal
    showToast('info', 'Bulk withdrawal feature coming soon');
}

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

// Initialize contact search
document.addEventListener('DOMContentLoaded', function() {
    // Initialize contact select (would typically use Select2 or similar)
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

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.consent-row:hover {
    background-color: #f8f9fc;
}

.btn-group-vertical .btn {
    margin-bottom: 2px;
}

.table th {
    border-top: none;
    vertical-align: middle;
}

.badge {
    font-size: 0.75em;
}
</style>
@endpush
