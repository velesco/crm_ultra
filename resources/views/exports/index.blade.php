@extends('layouts.app')

@section('title', 'Export Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">🚀 Export Management</h1>
                    <p class="text-muted mb-0">Create, manage, and schedule data exports</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('exports.scheduled') }}" class="btn btn-outline-primary">
                        <i class="fas fa-clock me-2"></i>Scheduled Exports
                    </a>
                    @can('create', App\Models\ExportRequest::class)
                        <a href="{{ route('exports.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Export
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-download text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $stats['total_exports'] }}</h5>
                            <p class="text-muted mb-0 small">Total Exports</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $stats['completed_exports'] }}</h5>
                            <p class="text-muted mb-0 small">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-clock text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $stats['pending_exports'] + $stats['scheduled_exports'] }}</h5>
                            <p class="text-muted mb-0 small">Pending/Scheduled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-hdd text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ formatBytes($stats['total_file_size']) }}</h5>
                            <p class="text-muted mb-0 small">Total Size</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <form method="GET" id="filterForm" class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search exports..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="data_type" class="form-select">
                                <option value="">All Data Types</option>
                                <option value="contacts" {{ request('data_type') == 'contacts' ? 'selected' : '' }}>Contacts</option>
                                <option value="email_campaigns" {{ request('data_type') == 'email_campaigns' ? 'selected' : '' }}>Email Campaigns</option>
                                <option value="sms_messages" {{ request('data_type') == 'sms_messages' ? 'selected' : '' }}>SMS Messages</option>
                                <option value="whatsapp_messages" {{ request('data_type') == 'whatsapp_messages' ? 'selected' : '' }}>WhatsApp Messages</option>
                                <option value="revenue" {{ request('data_type') == 'revenue' ? 'selected' : '' }}>Revenue Data</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="format" class="form-select">
                                <option value="">All Formats</option>
                                <option value="csv" {{ request('format') == 'csv' ? 'selected' : '' }}>CSV</option>
                                <option value="xlsx" {{ request('format') == 'xlsx' ? 'selected' : '' }}>Excel</option>
                                <option value="json" {{ request('format') == 'json' ? 'selected' : '' }}>JSON</option>
                                <option value="pdf" {{ request('format') == 'pdf' ? 'selected' : '' }}>PDF</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('exports.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Exports Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Export Requests</h5>
                        @can('bulkAction', App\Models\ExportRequest::class)
                            <div class="dropdown">
                                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" 
                                        id="bulkActionsDropdown" data-bs-toggle="dropdown" disabled>
                                    Bulk Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="bulkAction('start')">
                                        <i class="fas fa-play me-2"></i>Start Selected
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="bulkAction('cancel')">
                                        <i class="fas fa-stop me-2"></i>Cancel Selected
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="bulkAction('delete')">
                                        <i class="fas fa-trash me-2"></i>Delete Selected
                                    </a></li>
                                </ul>
                            </div>
                        @endcan
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($exports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        @can('bulkAction', App\Models\ExportRequest::class)
                                            <th width="40">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                        @endcan
                                        <th>Name</th>
                                        <th>Data Type</th>
                                        <th>Format</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>File Size</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exports as $export)
                                        <tr>
                                            @can('bulkAction', App\Models\ExportRequest::class)
                                                <td>
                                                    <input type="checkbox" class="form-check-input export-checkbox" 
                                                           value="{{ $export->id }}">
                                                </td>
                                            @endcan
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="fas fa-{{ $export->status_icon }} text-{{ $export->status_color }}"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $export->name }}</h6>
                                                        @if($export->description)
                                                            <small class="text-muted">{{ Str::limit($export->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    {{ $export->formatted_data_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    {{ $export->formatted_format }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $export->status_color }} bg-opacity-10 text-{{ $export->status_color }}">
                                                    {{ ucfirst($export->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($export->status === 'processing' && $export->progress)
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: {{ $export->progress }}%"></div>
                                                    </div>
                                                    <small class="text-muted">{{ $export->progress }}%</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($export->file_size)
                                                    {{ $export->formatted_file_size }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $export->created_at->format('M j, Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $export->created_at->format('g:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-link btn-sm text-muted p-0" type="button" 
                                                            data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        @can('view', $export)
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('exports.show', $export) }}">
                                                                    <i class="fas fa-eye me-2"></i>View Details
                                                                </a>
                                                            </li>
                                                        @endcan
                                                        
                                                        @can('download', $export)
                                                            @if($export->canDownload())
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('exports.download', $export) }}">
                                                                        <i class="fas fa-download me-2"></i>Download
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endcan
                                                        
                                                        @can('process', $export)
                                                            @if($export->canStart())
                                                                <li>
                                                                    <form method="POST" action="{{ route('exports.start', $export) }}" 
                                                                          style="display: inline;">
                                                                        @csrf
                                                                        <button type="submit" class="dropdown-item">
                                                                            <i class="fas fa-play me-2"></i>Start Export
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                        @endcan
                                                        
                                                        @can('cancel', $export)
                                                            @if($export->canCancel())
                                                                <li>
                                                                    <form method="POST" action="{{ route('exports.cancel', $export) }}" 
                                                                          style="display: inline;">
                                                                        @csrf
                                                                        <button type="submit" class="dropdown-item text-warning">
                                                                            <i class="fas fa-stop me-2"></i>Cancel Export
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                        @endcan
                                                        
                                                        @can('duplicate', $export)
                                                            <li>
                                                                <form method="POST" action="{{ route('exports.duplicate', $export) }}" 
                                                                      style="display: inline;">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fas fa-copy me-2"></i>Duplicate
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endcan
                                                        
                                                        @can('update', $export)
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('exports.edit', $export) }}">
                                                                    <i class="fas fa-edit me-2"></i>Edit
                                                                </a>
                                                            </li>
                                                        @endcan
                                                        
                                                        @can('delete', $export)
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form method="POST" action="{{ route('exports.destroy', $export) }}" 
                                                                      style="display: inline;" 
                                                                      onsubmit="return confirm('Are you sure you want to delete this export?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="fas fa-trash me-2"></i>Delete
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($exports->hasPages())
                            <div class="card-footer bg-white border-0 py-3">
                                {{ $exports->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-download fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No exports found</h5>
                            <p class="text-muted">Create your first export to get started.</p>
                            @can('create', App\Models\ExportRequest::class)
                                <a href="{{ route('exports.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create Export
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if($recent_activity->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($recent_activity as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-{{ $activity->status_color }}"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ $activity->name }}</h6>
                                        <p class="text-muted mb-1">{{ $activity->formatted_data_type }} • {{ $activity->formatted_format }}</p>
                                        <small class="text-muted">{{ $activity->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

.timeline-marker::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 100%;
    width: 2px;
    height: 2rem;
    background: #dee2e6;
    transform: translateX(-50%);
}

.timeline-item:last-child .timeline-marker::before {
    display: none;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.export-checkbox');
    const bulkDropdown = document.getElementById('bulkActionsDropdown');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDropdown();
        });
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkDropdown);
    });

    function updateBulkDropdown() {
        const checkedCount = document.querySelectorAll('.export-checkbox:checked').length;
        if (bulkDropdown) {
            bulkDropdown.disabled = checkedCount === 0;
        }
    }

    // Auto-refresh for processing exports
    const processingExports = document.querySelectorAll('tr[data-status="processing"]');
    if (processingExports.length > 0) {
        setInterval(() => {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    }
});

function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.export-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one export.');
        return;
    }

    const exportIds = Array.from(checkedBoxes).map(cb => cb.value);
    const actionText = action.charAt(0).toUpperCase() + action.slice(1);

    if (confirm(`Are you sure you want to ${action} ${exportIds.length} export(s)?`)) {
        fetch('{{ route("exports.bulk") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                action: action,
                export_ids: exportIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error occurred'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the request.');
        });
    }
}
</script>
@endpush
@endsection
