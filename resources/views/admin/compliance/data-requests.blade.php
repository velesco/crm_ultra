@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-1 text-gray-800">Data Requests Management</h2>
                    <p class="text-muted mb-0">Manage GDPR data export and deletion requests from users</p>
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
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_requests'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Export</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['export_requests'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Delete</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['delete_requests'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Pending</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_requests'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_requests'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-3 col-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['overdue_requests'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Time Alert -->
    @if($stats['avg_processing_time'] > 48)
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Attention:</strong> Average processing time is {{ number_format($stats['avg_processing_time'], 1) }} hours. 
            GDPR requires processing within 30 days (720 hours).
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
            <form method="GET" action="{{ route('admin.compliance.data-requests') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Contact name, email...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="type" class="form-label">Request Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="">All Types</option>
                            @foreach(\App\Models\DataRequest::getRequestTypes() as $key => $label)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\DataRequest::getStatuses() as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
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
                        <a href="{{ route('admin.compliance.data-requests') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Requests Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-file-text mr-2"></i>
                Data Requests
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" id="bulkProcessBtn" disabled>
                    <i class="fas fa-play mr-1"></i>
                    Process Selected
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="exportBtn">
                    <i class="fas fa-download mr-1"></i>
                    Export CSV
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($dataRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Contact</th>
                                <th>Request Type</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Processing Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataRequests as $request)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="request-checkbox" value="{{ $request->id }}"
                                               {{ $request->canBeProcessed() ? '' : 'disabled' }}>
                                    </td>
                                    <td>
                                        @if($request->contact)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-2">
                                                    {{ strtoupper(substr($request->contact->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-weight-medium">{{ $request->contact->full_name }}</div>
                                                    <small class="text-muted">{{ $request->contact->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mr-2">
                                                    ?
                                                </div>
                                                <div>
                                                    <div class="font-weight-medium">{{ $request->full_name ?? 'Unknown' }}</div>
                                                    <small class="text-muted">{{ $request->email }}</small>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas {{ $request->getRequestTypeIcon() }} mr-2 text-muted"></i>
                                            <span>{{ ucfirst($request->request_type) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $request->getStatusBadgeClass() }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                        @if($request->isExpired())
                                            <br><small class="text-danger">Expired</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <div class="font-weight-medium">{{ $request->created_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($request->status === 'completed' && $request->processed_at && $request->completed_at)
                                            @php
                                                $processingHours = $request->processed_at->diffInHours($request->completed_at);
                                            @endphp
                                            <span class="badge badge-{{ $processingHours > 48 ? 'danger' : ($processingHours > 24 ? 'warning' : 'success') }}">
                                                {{ $processingHours }}h
                                            </span>
                                        @elseif($request->processed_at)
                                            @php
                                                $processingHours = $request->processed_at->diffInHours(now());
                                            @endphp
                                            <span class="badge badge-info">
                                                {{ $processingHours }}h ongoing
                                            </span>
                                        @else
                                            @php
                                                $waitingHours = $request->created_at->diffInHours(now());
                                            @endphp
                                            <span class="badge badge-{{ $waitingHours > 168 ? 'danger' : ($waitingHours > 48 ? 'warning' : 'secondary') }}">
                                                {{ $waitingHours }}h waiting
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" onclick="viewRequest({{ $request->id }})">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    View Details
                                                </a>
                                                @if($request->canBeProcessed())
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="#" onclick="processRequest({{ $request->id }})">
                                                        <i class="fas fa-play mr-2"></i>
                                                        Process Request
                                                    </a>
                                                @endif
                                                @if($request->request_type === 'export' && $request->status === 'completed' && $request->file_path)
                                                    <a class="dropdown-item" href="{{ route('admin.compliance.download-export', $request) }}">
                                                        <i class="fas fa-download mr-2"></i>
                                                        Download Export
                                                    </a>
                                                @endif
                                                @if($request->status === 'pending')
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#" onclick="rejectRequest({{ $request->id }})">
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
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $dataRequests->firstItem() ?? 0 }} to {{ $dataRequests->lastItem() ?? 0 }} 
                            of {{ $dataRequests->total() }} entries
                        </div>
                        {{ $dataRequests->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-text fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No Data Requests Found</h5>
                    <p class="text-muted mb-0">
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
            bulkBtn.innerHTML = `<i class="fas fa-play mr-1"></i>Process Selected (${selected})`;
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
                alert('Bulk processing functionality would be implemented here');
            }
        });
    }
});

function viewRequest(requestId) {
    alert('Request details view would be implemented here');
}

function processRequest(requestId) {
    if (confirm('Are you sure you want to process this data request? This action cannot be undone.')) {
        alert('Request processing functionality would be implemented here');
        location.reload();
    }
}

function rejectRequest(requestId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason && reason.trim()) {
        alert('Request rejection functionality would be implemented here');
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