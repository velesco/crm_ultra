@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-1 text-gray-800">Consent Logs Management</h2>
                    <p class="text-muted mb-0">Track and manage user consent across all communication channels</p>
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
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_consents'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Given</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['given_consents'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Withdrawn</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['withdrawn_consents'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expired</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['expired_consents'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Email</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['email_consents'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Marketing</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['marketing_consents'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i>
                Filters & Search
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.compliance.consent-logs') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Contact name, email...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\ConsentLog::getStatuses() as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="consent_type" class="form-label">Type</label>
                        <select class="form-control" id="consent_type" name="consent_type">
                            <option value="">All Types</option>
                            @foreach(\App\Models\ConsentLog::getConsentTypes() as $key => $label)
                                <option value="{{ $key }}" {{ request('consent_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('admin.compliance.consent-logs') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
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
                <i class="fas fa-shield-check mr-2"></i>
                Consent Records
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="exportBtn">
                    <i class="fas fa-download mr-1"></i>
                    Export CSV
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($consentLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Contact</th>
                                <th>Consent Type</th>
                                <th>Status</th>
                                <th>Legal Basis</th>
                                <th>Purpose</th>
                                <th>Given At</th>
                                <th>Source</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($consentLogs as $consent)
                                <tr>
                                    <td>
                                        @if($consent->contact)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2">
                                                    {{ strtoupper(substr($consent->contact->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-weight-medium">{{ $consent->contact->full_name }}</div>
                                                    <small class="text-muted">{{ $consent->contact->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Contact Deleted</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas {{ $consent->getConsentTypeIcon() }} mr-2 text-muted"></i>
                                            <span>{{ str_replace('_', ' ', ucwords($consent->consent_type)) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $consent->getStatusBadgeClass() }}">
                                            {{ ucfirst($consent->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-capitalize">{{ str_replace('_', ' ', $consent->legal_basis) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-capitalize">{{ $consent->purpose ?? '-' }}</span>
                                    </td>
                                    <td>
                                        @if($consent->given_at)
                                            <div>
                                                <div class="font-weight-medium">{{ $consent->given_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $consent->given_at->format('H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light">{{ ucfirst($consent->source) }}</span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" onclick="viewConsent({{ $consent->id }})">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    View Details
                                                </a>
                                                @if($consent->status === 'given')
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-warning" href="#" onclick="withdrawConsent({{ $consent->id }})">
                                                        <i class="fas fa-ban mr-2"></i>
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
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $consentLogs->firstItem() ?? 0 }} to {{ $consentLogs->lastItem() ?? 0 }} 
                            of {{ $consentLogs->total() }} entries
                        </div>
                        {{ $consentLogs->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-shield-check fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No Consent Records Found</h5>
                    <p class="text-muted mb-0">
                        @if(request()->hasAny(['search', 'status', 'consent_type', 'date_from', 'date_to']))
                            Try adjusting your filters to see more results.
                        @else
                            Consent records will appear here as users interact with your system.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- View Consent Modal -->
<div class="modal fade" id="viewConsentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-shield-check mr-2"></i>
                    Consent Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="consentDetails">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        const params = new URLSearchParams(window.location.search);
        params.set('export', 'csv');
        window.location.href = window.location.pathname + '?' + params.toString();
    });
});

function viewConsent(consentId) {
    // Load consent details via AJAX
    document.getElementById('consentDetails').innerHTML = 
        '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    $('#viewConsentModal').modal('show');
    
    // This would typically make an AJAX call to get consent details
    setTimeout(() => {
        document.getElementById('consentDetails').innerHTML = 
            '<p>Detailed consent information would be loaded here via AJAX.</p>';
    }, 500);
}

function withdrawConsent(consentId) {
    if (confirm('Are you sure you want to withdraw this consent? This action cannot be undone.')) {
        // Implementation for withdrawing consent would go here
        alert('Consent withdrawal functionality would be implemented here');
        location.reload();
    }
}
</script>
@endpush

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.gap-2 > * + * {
    margin-left: 0.5rem;
}
</style>
@endsection