@extends('layouts.app')

@section('title', 'Backup Details - ' . $backup->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">
                        <i class="{{ $backup->type_icon }} text-{{ $backup->status === 'completed' ? 'success' : ($backup->status === 'failed' ? 'danger' : 'warning') }} me-2"></i>
                        {{ $backup->name }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">Admin</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.backups.index') }}">Backups</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $backup->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <div class="btn-group me-2">
                        @if($backup->status === 'completed' && $backup->file_path)
                            <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-success">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#restoreModal">
                                <i class="fas fa-undo"></i> Restore
                            </button>
                        @endif
                    </div>
                    <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Backup Information
                    </h5>
                    <span class="badge bg-{{ $backup->status_badge_class }} fs-6">
                        <i class="{{ $backup->status_icon }}"></i>
                        {{ ucwords(str_replace('_', ' ', $backup->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Name:</dt>
                                <dd class="col-sm-8">{{ $backup->name }}</dd>
                                
                                <dt class="col-sm-4">Type:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-light text-dark border">
                                        <i class="{{ $backup->type_icon }}"></i>
                                        {{ ucfirst($backup->type) }} Backup
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Size:</dt>
                                <dd class="col-sm-8">
                                    @if($backup->file_size)
                                        <span class="fw-semibold">{{ $backup->formatted_file_size }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">Duration:</dt>
                                <dd class="col-sm-8">
                                    @if($backup->formatted_duration)
                                        {{ $backup->formatted_duration }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Created:</dt>
                                <dd class="col-sm-8">
                                    {{ $backup->created_at->format('M j, Y H:i') }}
                                    <br>
                                    <small class="text-muted">{{ $backup->created_at->diffForHumans() }}</small>
                                </dd>
                                
                                <dt class="col-sm-4">Started:</dt>
                                <dd class="col-sm-8">
                                    @if($backup->started_at)
                                        {{ $backup->started_at->format('H:i:s') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">Completed:</dt>
                                <dd class="col-sm-8">
                                    @if($backup->completed_at)
                                        {{ $backup->completed_at->format('H:i:s') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </dd>
                                
                                <dt class="col-sm-4">Created by:</dt>
                                <dd class="col-sm-8">
                                    @if($backup->creator)
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($backup->creator->name) }}&size=24&background=007bff&color=fff" 
                                                 class="rounded-circle me-2" width="24" height="24" alt="Avatar">
                                            {{ $backup->creator->name }}
                                        </div>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($backup->description)
                        <div class="mt-3">
                            <h6>Description:</h6>
                            <p class="text-muted mb-0">{{ $backup->description }}</p>
                        </div>
                    @endif
                    
                    @if($backup->status === 'failed' && $backup->error_message)
                        <div class="mt-3">
                            <h6 class="text-danger">Error Details:</h6>
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $backup->error_message }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Validation Results -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt"></i> Backup Validation
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="validateBackup()">
                        <i class="fas fa-sync"></i> Re-validate
                    </button>
                </div>
                <div class="card-body">
                    <div id="validation-results">
                        @if($validation['valid'])
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Valid:</strong> {{ $validation['message'] }}
                            </div>
                        @else
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Invalid:</strong> {{ $validation['error'] }}
                            </div>
                        @endif
                    </div>

                    @if($backup->status === 'completed')
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <i class="fas fa-file-archive fa-2x text-primary mb-2"></i>
                                    <h6>File Integrity</h6>
                                    <span class="badge bg-{{ $validation['valid'] ? 'success' : 'danger' }}">
                                        {{ $validation['valid'] ? 'Valid' : 'Invalid' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <i class="fas fa-hdd fa-2x text-info mb-2"></i>
                                    <h6>File Exists</h6>
                                    <span class="badge bg-{{ $backup->fileExists() ? 'success' : 'danger' }}">
                                        {{ $backup->fileExists() ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <i class="fas fa-balance-scale fa-2x text-warning mb-2"></i>
                                    <h6>Size Match</h6>
                                    @php
                                        $actualSize = $backup->getActualFileSize();
                                        $sizeMatch = $actualSize === $backup->file_size;
                                    @endphp
                                    <span class="badge bg-{{ $sizeMatch ? 'success' : 'warning' }}">
                                        {{ $sizeMatch ? 'Match' : 'Different' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Backup Contents (for completed backups) -->
            @if($backup->status === 'completed' && $backup->metadata)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Backup Contents
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($backup->type === 'full' || $backup->type === 'database')
                                <div class="col-md-6">
                                    <h6><i class="fas fa-database"></i> Database</h6>
                                    <ul class="list-unstyled ms-3">
                                        <li><i class="fas fa-check text-success"></i> All tables and data</li>
                                        <li><i class="fas fa-check text-success"></i> Indexes and constraints</li>
                                        <li><i class="fas fa-check text-success"></i> Stored procedures</li>
                                        <li><i class="fas fa-check text-success"></i> User permissions</li>
                                    </ul>
                                </div>
                            @endif
                            
                            @if($backup->type === 'full' || $backup->type === 'files')
                                <div class="col-md-6">
                                    <h6><i class="fas fa-folder"></i> Files</h6>
                                    <ul class="list-unstyled ms-3">
                                        <li><i class="fas fa-check text-success"></i> Application files</li>
                                        <li><i class="fas fa-check text-success"></i> Configuration files</li>
                                        <li><i class="fas fa-check text-success"></i> Storage files</li>
                                        <li><i class="fas fa-check text-success"></i> View templates</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                        
                        @if(isset($backup->metadata['includes']))
                            <div class="mt-3">
                                <h6>Included Files:</h6>
                                <div class="row">
                                    @foreach($backup->metadata['includes'] as $key => $file)
                                        <div class="col-md-6">
                                            <span class="badge bg-light text-dark me-2">{{ ucfirst($key) }}:</span>
                                            <code>{{ $file }}</code>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($backup->status === 'completed' && $backup->file_path)
                            <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-success">
                                <i class="fas fa-download"></i> Download Backup
                            </a>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#restoreModal">
                                <i class="fas fa-undo"></i> Restore System
                            </button>
                            <button type="button" class="btn btn-info" onclick="validateBackup()">
                                <i class="fas fa-shield-alt"></i> Validate Integrity
                            </button>
                        @endif
                        
                        @if($backup->canBeDeleted())
                            <button type="button" class="btn btn-danger" onclick="deleteBackup()">
                                <i class="fas fa-trash"></i> Delete Backup
                            </button>
                        @endif
                        
                        <a href="{{ route('admin.backups.create') }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> Create New Backup
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-server"></i> System Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($backup->metadata)
                        <dl class="row small">
                            @if(isset($backup->metadata['laravel_version']))
                                <dt class="col-6">Laravel:</dt>
                                <dd class="col-6">{{ $backup->metadata['laravel_version'] }}</dd>
                            @endif
                            
                            @if(isset($backup->metadata['php_version']))
                                <dt class="col-6">PHP:</dt>
                                <dd class="col-6">{{ $backup->metadata['php_version'] }}</dd>
                            @endif
                            
                            @if(isset($backup->metadata['database']))
                                <dt class="col-6">Database:</dt>
                                <dd class="col-6">{{ $backup->metadata['database'] }}</dd>
                            @endif
                        </dl>
                    @endif

                    <div class="mt-3">
                        <h6 class="small">Backup Health:</h6>
                        <div class="progress mb-2" style="height: 6px;">
                            @php
                                $healthScore = $backup->status === 'completed' ? ($validation['valid'] ? 100 : 50) : 0;
                                $healthColor = $healthScore >= 80 ? 'success' : ($healthScore >= 50 ? 'warning' : 'danger');
                            @endphp
                            <div class="progress-bar bg-{{ $healthColor }}" style="width: {{ $healthScore }}%"></div>
                        </div>
                        <small class="text-muted">Health Score: {{ $healthScore }}%</small>
                    </div>
                </div>
            </div>

            <!-- Related Backups -->
            @if($relatedBackups->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-layer-group"></i> Related {{ ucfirst($backup->type) }} Backups
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($relatedBackups as $related)
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-{{ $related->status_badge_class }} me-2">
                                    <i class="{{ $related->status_icon }}"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <a href="{{ route('admin.backups.show', $related) }}" class="text-decoration-none small">
                                        {{ Str::limit($related->name, 25) }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ $related->created_at->format('M j, H:i') }}
                                    </div>
                                </div>
                                @if($related->file_size)
                                    <small class="text-muted">{{ formatBytes($related->file_size) }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.backups.restore', $backup) }}" method="POST" id="restore-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-undo"></i> Restore from Backup
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h6 class="alert-heading">Critical Warning</h6>
                                <p class="mb-0">
                                    This operation will completely overwrite your current system with backup data.
                                    <strong>This action cannot be undone!</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Backup Information:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Name:</strong> {{ $backup->name }}</li>
                                <li><strong>Type:</strong> {{ ucfirst($backup->type) }}</li>
                                <li><strong>Size:</strong> {{ $backup->formatted_file_size }}</li>
                                <li><strong>Created:</strong> {{ $backup->created_at->format('M j, Y H:i') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Restore Options:</h6>
                            @if($backup->type === 'full')
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="restore_database" id="restore_database" checked>
                                    <label class="form-check-label" for="restore_database">
                                        <i class="fas fa-database"></i> Restore Database
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="restore_files" id="restore_files">
                                    <label class="form-check-label" for="restore_files">
                                        <i class="fas fa-folder"></i> Restore Files
                                    </label>
                                </div>
                            @elseif($backup->type === 'database')
                                <input type="hidden" name="restore_database" value="1">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    This backup contains database data only.
                                </div>
                            @elseif($backup->type === 'files')
                                <input type="hidden" name="restore_files" value="1">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    This backup contains files only.
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="confirmation" id="confirmation" required>
                            <label class="form-check-label fw-semibold" for="confirmation">
                                I understand this will overwrite current system data and cannot be undone
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <h6><i class="fas fa-clock"></i> Estimated Restore Time</h6>
                        <p class="mb-0 small">
                            Database restore: ~2-5 minutes<br>
                            Files restore: ~5-15 minutes<br>
                            System will be unavailable during restore process.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="restore-submit">
                        <i class="fas fa-undo"></i> Begin Restore
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Validate backup integrity
function validateBackup() {
    const resultsDiv = document.getElementById('validation-results');
    resultsDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Validating backup...</div>';
    
    fetch(`{{ route('admin.backups.validate', $backup) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        let html;
        if (data.valid) {
            html = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Valid:</strong> ${data.message}
                </div>
            `;
        } else {
            html = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Invalid:</strong> ${data.error}
                </div>
            `;
        }
        resultsDiv.innerHTML = html;
    })
    .catch(error => {
        console.error('Validation error:', error);
        resultsDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error:</strong> Failed to validate backup
            </div>
        `;
    });
}

// Delete backup
function deleteBackup() {
    if (!confirm(`Are you sure you want to delete backup "{{ $backup->name }}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('admin.backups.destroy', $backup) }}';
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
        alert('Please confirm that you understand this action');
        return;
    }
    
    const submitBtn = document.getElementById('restore-submit');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...';
    
    // Show warning one more time
    if (!confirm('FINAL WARNING: This will overwrite your current system. Are you absolutely sure?')) {
        e.preventDefault();
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-undo"></i> Begin Restore';
    }
});

// Auto-refresh backup status if in progress
@if($backup->status === 'in_progress')
    setInterval(function() {
        location.reload();
    }, 10000); // Refresh every 10 seconds
@endif
</script>
@endpush
