@extends('layouts.app')

@section('title', 'Backup Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-hdd text-primary me-2"></i>
                        Backup Management
                    </h1>
                    <p class="text-muted">Manage system backups and restore points</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                        <i class="fas fa-plus"></i> Create Backup
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="scheduleBackup('daily')"><i class="fas fa-clock"></i> Schedule Daily</a></li>
                            <li><a class="dropdown-item" href="#" onclick="scheduleBackup('weekly')"><i class="fas fa-calendar-week"></i> Schedule Weekly</a></li>
                            <li><a class="dropdown-item" href="#" onclick="scheduleBackup('monthly')"><i class="fas fa-calendar-alt"></i> Schedule Monthly</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="cleanupOldBackups()"><i class="fas fa-broom"></i> Cleanup Old</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Backups</h5>
                            <h3 class="mb-0" id="total-backups">{{ $stats['total_backups'] }}</h3>
                        </div>
                        <i class="fas fa-database fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Successful</h5>
                            <h3 class="mb-0" id="successful-backups">{{ $stats['successful_backups'] }}</h3>
                            <small class="opacity-75">{{ $additionalStats['success_rate'] }}% success rate</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Failed</h5>
                            <h3 class="mb-0" id="failed-backups">{{ $stats['failed_backups'] }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Size</h5>
                            <h3 class="mb-0" id="total-size">{{ formatBytes($stats['total_size'] ?? 0) }}</h3>
                            @if(($additionalStats['avg_size'] ?? 0) > 0)
                                <small class="opacity-75">Avg: {{ formatBytes($additionalStats['avg_size']) }}</small>
                            @endif
                        </div>
                        <i class="fas fa-hdd fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3" id="filter-form">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                   placeholder="Search by name, description...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                <option value="">All Types</option>
                                <option value="full" {{ request('type') == 'full' ? 'selected' : '' }}>Full</option>
                                <option value="database" {{ request('type') == 'database' ? 'selected' : '' }}>Database</option>
                                <option value="files" {{ request('type') == 'files' ? 'selected' : '' }}>Files</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Backups Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Backup History
                    </h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshTable()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="bulkDelete()" disabled id="bulk-delete-btn">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="backups-table">
                        @include('admin.backups.table', ['backups' => $backups])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Recent Activity</h5>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        @foreach($recentActivity as $backup)
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <span class="badge bg-{{ $backup->status_badge_class }}">
                                        <i class="{{ $backup->status_icon }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $backup->name }}</h6>
                                    <small class="text-muted">
                                        {{ $backup->type }} backup • {{ $backup->created_at->diffForHumans() }}
                                        @if($backup->creator)
                                            • by {{ $backup->creator->name }}
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    @if($backup->status === 'completed')
                                        <span class="badge bg-light text-dark">{{ $backup->formatted_file_size }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center mb-0">No recent backup activity</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.backups.store') }}" method="POST" id="create-backup-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Backup Name</label>
                        <input type="text" class="form-control" name="name" required 
                               placeholder="backup_{{ now()->format('Y_m_d_H_i_s') }}" 
                               value="backup_{{ now()->format('Y_m_d_H_i_s') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" 
                                  placeholder="Optional description for this backup"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Backup Type</label>
                        <select class="form-select" name="type" required>
                            <option value="full">Full Backup (Database + Files)</option>
                            <option value="database">Database Only</option>
                            <option value="files">Files Only</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Full backups may take longer to complete depending on your data size.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play"></i> Create Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form onsubmit="performCleanup(event)">
                <div class="modal-header">
                    <h5 class="modal-title">Cleanup Old Backups</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Keep backups for the last</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="days_to_keep" value="30" min="1" max="365" required>
                            <span class="input-group-text">days</span>
                        </div>
                        <div class="form-text">Backups older than this will be permanently deleted</div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action cannot be undone. Please make sure you want to delete old backups.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-broom"></i> Cleanup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Helper function for formatting bytes
@if (!function_exists('formatBytes'))
function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
@endif

// Refresh table data
function refreshTable() {
    const params = new URLSearchParams(window.location.search);
    
    fetch(`{{ route('admin.backups.index') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('backups-table').innerHTML = data.backups;
        
        // Update stats
        document.getElementById('total-backups').textContent = data.stats.total_backups;
        document.getElementById('successful-backups').textContent = data.stats.successful_backups;
        document.getElementById('failed-backups').textContent = data.stats.failed_backups;
        document.getElementById('total-size').textContent = formatBytes(data.stats.total_size);
        
        showToast('Table refreshed successfully', 'success');
    })
    .catch(error => {
        console.error('Error refreshing table:', error);
        showToast('Failed to refresh table', 'error');
    });
}

// Schedule backup
function scheduleBackup(frequency) {
    if (!confirm(`Create a ${frequency} scheduled backup?`)) return;
    
    fetch('{{ route('admin.backups.scheduled') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ frequency: frequency })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => refreshTable(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error scheduling backup:', error);
        showToast('Failed to schedule backup', 'error');
    });
}

// Cleanup old backups
function cleanupOldBackups() {
    const modal = new bootstrap.Modal(document.getElementById('cleanupModal'));
    modal.show();
}

function performCleanup(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('{{ route('admin.backups.cleanup') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('cleanupModal')).hide();
            setTimeout(() => refreshTable(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error cleaning up backups:', error);
        showToast('Failed to cleanup backups', 'error');
    });
}

// Bulk delete
function bulkDelete() {
    const checked = document.querySelectorAll('input[name="selected_backups[]"]:checked');
    if (checked.length === 0) {
        showToast('Please select backups to delete', 'warning');
        return;
    }
    
    if (!confirm(`Delete ${checked.length} selected backup(s)?`)) return;
    
    const ids = Array.from(checked).map(cb => cb.value);
    
    fetch('{{ route('admin.backups.bulk-action') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: 'delete',
            backup_ids: ids
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const successful = data.results.filter(r => r.success).length;
            showToast(`Successfully deleted ${successful} backup(s)`, 'success');
            setTimeout(() => refreshTable(), 1000);
        } else {
            showToast('Bulk delete failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting backups:', error);
        showToast('Failed to delete backups', 'error');
    });
}

// Toggle select all
function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('input[name="selected_backups[]"]');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

// Update bulk action buttons
function updateBulkActions() {
    const checked = document.querySelectorAll('input[name="selected_backups[]"]:checked');
    const bulkBtn = document.getElementById('bulk-delete-btn');
    
    bulkBtn.disabled = checked.length === 0;
}

// Auto-refresh every 30 seconds
setInterval(refreshTable, 30000);

// Toast notification helper
function showToast(message, type = 'info') {
    // Create toast element
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type}" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Add to toast container (create if doesn't exist)
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    container.insertAdjacentHTML('beforeend', toastHtml);
    
    // Show toast
    const toastEl = container.lastElementChild;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    // Remove from DOM after hidden
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

// Form submission handling
document.getElementById('create-backup-form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Add change listeners to checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.name === 'selected_backups[]') {
            updateBulkActions();
        }
    });
});
</script>
@endpush
