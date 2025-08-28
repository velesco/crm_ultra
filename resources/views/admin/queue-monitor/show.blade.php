@extends('layouts.app')

@section('title', 'Job Details')
@section('page-title', 'Job Details')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.queue-monitor.index') }}">Queue Monitor</a></li>
        <li class="breadcrumb-item active">Job Details</li>
    </ol>
</nav>
@endsection

@push('styles')
<style>
    .job-detail-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        color: white;
        margin-bottom: 30px;
    }
    .job-status-badge {
        font-size: 1rem;
        padding: 0.5rem 1rem;
        border-radius: 25px;
    }
    .json-viewer {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        max-height: 400px;
        overflow-y: auto;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }
    .exception-viewer {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 8px;
        padding: 15px;
        max-height: 300px;
        overflow-y: auto;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        color: #721c24;
    }
    .info-table th {
        background-color: #f8f9fa;
        border-top: none;
        width: 150px;
    }
    .action-buttons {
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Job Header Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card job-detail-card">
                <div class="card-body text-center">
                    <i class="fas fa-cog fa-3x mb-3 opacity-75"></i>
                    <h2 class="mb-2">{{ class_basename($jobDetails['name'] ?? 'Unknown Job') }}</h2>
                    <p class="mb-3 opacity-75">Job ID: {{ $jobDetails['id'] }}</p>
                    <span class="job-status-badge badge bg-{{ $jobDetails['status'] === 'completed' ? 'success' : ($jobDetails['status'] === 'failed' ? 'danger' : 'warning') }}">
                        {{ ucfirst($jobDetails['status'] ?? 'pending') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Information -->
    <div class="row mb-4">
        <!-- Basic Information -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped info-table mb-0">
                        <tbody>
                            <tr>
                                <th>Job ID</th>
                                <td>{{ $jobDetails['id'] }}</td>
                            </tr>
                            <tr>
                                <th>Job Name</th>
                                <td>
                                    <code>{{ $jobDetails['name'] ?? 'Unknown' }}</code>
                                </td>
                            </tr>
                            <tr>
                                <th>Queue</th>
                                <td>
                                    <span class="badge bg-primary">{{ $jobDetails['queue'] ?? 'default' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-{{ $jobDetails['status'] === 'completed' ? 'success' : ($jobDetails['status'] === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($jobDetails['status'] ?? 'pending') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Attempts</th>
                                <td>
                                    <span class="badge bg-info">{{ $jobDetails['attempts'] ?? 0 }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Timing Information -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Timing Information</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped info-table mb-0">
                        <tbody>
                            <tr>
                                <th>Started At</th>
                                <td>
                                    @if($jobDetails['started_at'])
                                        {{ \Carbon\Carbon::parse($jobDetails['started_at'])->format('Y-m-d H:i:s') }}
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($jobDetails['started_at'])->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Not started</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Finished At</th>
                                <td>
                                    @if($jobDetails['finished_at'])
                                        {{ \Carbon\Carbon::parse($jobDetails['finished_at'])->format('Y-m-d H:i:s') }}
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($jobDetails['finished_at'])->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Not finished</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Failed At</th>
                                <td>
                                    @if($jobDetails['failed_at'])
                                        {{ \Carbon\Carbon::parse($jobDetails['failed_at'])->format('Y-m-d H:i:s') }}
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($jobDetails['failed_at'])->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Not failed</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Runtime</th>
                                <td>
                                    @if($jobDetails['runtime'])
                                        <span class="badge bg-success">{{ $jobDetails['runtime'] }}ms</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($jobDetails['status'] === 'failed')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-tools me-2"></i>Actions</h5>
                    <div class="d-flex action-buttons">
                        <button type="button" class="btn btn-success" onclick="retryJob('{{ $jobDetails['id'] }}')">
                            <i class="fas fa-redo me-2"></i>Retry Job
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deleteJob('{{ $jobDetails['id'] }}')">
                            <i class="fas fa-trash me-2"></i>Delete Job
                        </button>
                        <a href="{{ route('admin.queue-monitor.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Monitor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.queue-monitor.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Monitor
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payload Information -->
    @if($jobDetails['payload'])
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-code me-2"></i>Job Payload</h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('payloadContent')">
                        <i class="fas fa-copy me-1"></i>Copy
                    </button>
                </div>
                <div class="card-body">
                    <div id="payloadContent" class="json-viewer">
                        <pre>{{ json_encode($jobDetails['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Exception Information -->
    @if($jobDetails['exception'])
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Exception Details</h5>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="copyToClipboard('exceptionContent')">
                        <i class="fas fa-copy me-1"></i>Copy
                    </button>
                </div>
                <div class="card-body">
                    <div id="exceptionContent" class="exception-viewer">
                        <pre>{{ $jobDetails['exception'] }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Additional Information -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Job Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $jobDetails['attempts'] ?? 0 }}</h4>
                                <small class="text-muted">Total Attempts</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-success">{{ $jobDetails['runtime'] ? $jobDetails['runtime'] . 'ms' : 'N/A' }}</h4>
                                <small class="text-muted">Execution Time</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-info">{{ $jobDetails['queue'] ?? 'default' }}</h4>
                                <small class="text-muted">Queue Name</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-{{ $jobDetails['status'] === 'completed' ? 'success' : ($jobDetails['status'] === 'failed' ? 'danger' : 'warning') }}">
                                {{ ucfirst($jobDetails['status'] ?? 'pending') }}
                            </h4>
                            <small class="text-muted">Current Status</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent || element.innerText;
    
    navigator.clipboard.writeText(text).then(function() {
        showToast('Content copied to clipboard!', 'success');
    }, function(err) {
        console.error('Could not copy text: ', err);
        showToast('Failed to copy content', 'error');
    });
}

function retryJob(id) {
    if (!confirm('Are you sure you want to retry this job?')) {
        return;
    }
    
    fetch(`{{ route("admin.queue-monitor.retry", ":id") }}`.replace(':id', id), {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Job retried successfully', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("admin.queue-monitor.index") }}';
            }, 2000);
        } else {
            showToast(data.message || 'Failed to retry job', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to retry job', 'error');
        console.error(error);
    });
}

function deleteJob(id) {
    if (!confirm('Are you sure you want to delete this failed job? This action cannot be undone.')) {
        return;
    }
    
    fetch(`{{ route("admin.queue-monitor.delete", ":id") }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Job deleted successfully', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("admin.queue-monitor.index") }}';
            }, 2000);
        } else {
            showToast(data.message || 'Failed to delete job', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to delete job', 'error');
        console.error(error);
    });
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to toast container or create one
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    container.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}
</script>
@endpush
