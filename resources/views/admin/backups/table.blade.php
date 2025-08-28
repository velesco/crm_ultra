<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th width="40">
                    <input type="checkbox" class="form-check-input" id="select-all" onchange="toggleSelectAll()">
                </th>
                <th>Backup</th>
                <th>Type</th>
                <th>Status</th>
                <th>Size</th>
                <th>Duration</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($backups as $backup)
                <tr class="{{ $backup->status === 'failed' ? 'table-danger' : '' }}">
                    <td>
                        @if($backup->canBeDeleted())
                            <input type="checkbox" class="form-check-input" name="selected_backups[]" value="{{ $backup->id }}">
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <i class="{{ $backup->type_icon }} text-{{ $backup->status === 'completed' ? 'success' : ($backup->status === 'failed' ? 'danger' : 'warning') }}"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    <a href="{{ route('admin.backups.show', $backup) }}" class="text-decoration-none">
                                        {{ $backup->name }}
                                    </a>
                                </h6>
                                @if($backup->description)
                                    <small class="text-muted">{{ Str::limit($backup->description, 50) }}</small>
                                @endif
                                @if($backup->creator)
                                    <small class="d-block text-muted">by {{ $backup->creator->name }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="{{ $backup->type_icon }}"></i>
                            {{ ucfirst($backup->type) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $backup->status_badge_class }}">
                            <i class="{{ $backup->status_icon }}"></i>
                            {{ ucwords(str_replace('_', ' ', $backup->status)) }}
                        </span>
                        @if($backup->status === 'failed' && $backup->error_message)
                            <button type="button" class="btn btn-sm btn-link p-0 ms-1" 
                                    data-bs-toggle="popover" 
                                    data-bs-trigger="hover" 
                                    data-bs-content="{{ $backup->error_message }}"
                                    data-bs-title="Error Details">
                                <i class="fas fa-info-circle text-danger"></i>
                            </button>
                        @endif
                    </td>
                    <td>
                        @if($backup->file_size)
                            <span class="fw-semibold">{{ $backup->formatted_file_size }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($backup->formatted_duration)
                            <span class="text-muted">{{ $backup->formatted_duration }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">{{ $backup->created_at->format('M j, Y') }}</span>
                            <small class="text-muted">{{ $backup->created_at->format('H:i') }}</small>
                            <small class="text-muted">{{ $backup->created_at->diffForHumans() }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                                    data-bs-toggle="dropdown">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.backups.show', $backup) }}">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </li>
                                
                                @if($backup->status === 'completed' && $backup->file_path)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.backups.download', $backup) }}">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item" onclick="validateBackup({{ $backup->id }})">
                                            <i class="fas fa-check-circle"></i> Validate
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-warning" 
                                                onclick="showRestoreModal({{ $backup->id }}, '{{ $backup->name }}')">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                    </li>
                                @endif
                                
                                @if($backup->canBeDeleted())
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger" 
                                                onclick="deleteBackup({{ $backup->id }}, '{{ $backup->name }}')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-database fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No backups found</h5>
                        <p class="text-muted">Create your first backup to get started</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                            <i class="fas fa-plus"></i> Create Backup
                        </button>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($backups instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="d-flex justify-content-between align-items-center px-3 py-2">
        <div class="text-muted">
            Showing {{ $backups->firstItem() ?? 0 }} to {{ $backups->lastItem() ?? 0 }} of {{ $backups->total() }} results
        </div>
        {{ $backups->links() }}
    </div>
@endif

<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="restore-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Restore from Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This will overwrite your current system with the backup data. 
                        This action cannot be undone!
                    </div>
                    
                    <div class="mb-3">
                        <h6>Backup: <span id="restore-backup-name"></span></h6>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Restore Options:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="restore_database" id="restore_database" checked>
                            <label class="form-check-label" for="restore_database">
                                <i class="fas fa-database"></i> Restore Database
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="restore_files" id="restore_files">
                            <label class="form-check-label" for="restore_files">
                                <i class="fas fa-folder"></i> Restore Files
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="confirmation" id="confirmation" required>
                            <label class="form-check-label" for="confirmation">
                                I understand this will overwrite current data
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Restore System
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize popovers
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Validate backup
function validateBackup(backupId) {
    fetch(`{{ url('admin/backups') }}/${backupId}/validate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            showToast('Backup validation successful', 'success');
        } else {
            showToast(`Backup validation failed: ${data.error}`, 'error');
        }
    })
    .catch(error => {
        console.error('Error validating backup:', error);
        showToast('Failed to validate backup', 'error');
    });
}

// Show restore modal
function showRestoreModal(backupId, backupName) {
    document.getElementById('restore-backup-name').textContent = backupName;
    document.getElementById('restore-form').action = `{{ url('admin/backups') }}/${backupId}/restore`;
    
    const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
    modal.show();
}

// Delete backup
function deleteBackup(backupId, backupName) {
    if (!confirm(`Are you sure you want to delete backup "${backupName}"?`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ url('admin/backups') }}/${backupId}`;
    form.innerHTML = `
        @csrf
        @method('DELETE')
    `;
    
    document.body.appendChild(form);
    form.submit();
}

// Handle restore form submission
document.getElementById('restore-form').addEventListener('submit', function(e) {
    if (!document.getElementById('confirmation').checked) {
        e.preventDefault();
        showToast('Please confirm that you understand this action', 'warning');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...';
});
</script>
