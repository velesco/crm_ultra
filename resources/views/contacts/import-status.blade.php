@extends('layouts.app')

@section('title', 'Import Status')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Import Status
                    </h5>
                    <a href="{{ route('contacts.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-users me-1"></i> View Contacts
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Import Details -->
                            <div class="d-flex align-items-center mb-4">
                                <div class="me-3">
                                    @if($import->status === 'completed')
                                        <div class="bg-success rounded-circle d-flex align-items-center justify-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-check text-white fa-2x"></i>
                                        </div>
                                    @elseif($import->status === 'failed')
                                        <div class="bg-danger rounded-circle d-flex align-items-center justify-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-times text-white fa-2x"></i>
                                        </div>
                                    @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-spinner fa-spin text-white fa-2x"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="mb-1">
                                        @if($import->status === 'completed')
                                            Import Completed Successfully
                                        @elseif($import->status === 'failed')
                                            Import Failed
                                        @else
                                            Import in Progress
                                        @endif
                                    </h4>
                                    <p class="text-muted mb-0">
                                        Started {{ $import->created_at->diffForHumans() }}
                                        @if($import->completed_at)
                                            • Completed {{ $import->completed_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            @if($import->status === 'processing')
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Progress</span>
                                    <span class="text-muted" id="progressText">{{ $import->progress ?? 0 }}%</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: {{ $import->progress ?? 0 }}%"
                                         id="progressBar">
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Statistics -->
                            <div class="row mb-4">
                                <div class="col-sm-3">
                                    <div class="text-center">
                                        <div class="text-2xl font-weight-bold text-primary">{{ $import->total_records ?? 0 }}</div>
                                        <div class="text-muted small">Total Records</div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="text-center">
                                        <div class="text-2xl font-weight-bold text-success">{{ $import->processed_records ?? 0 }}</div>
                                        <div class="text-muted small">Processed</div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="text-center">
                                        <div class="text-2xl font-weight-bold text-warning">{{ $import->skipped_records ?? 0 }}</div>
                                        <div class="text-muted small">Skipped</div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="text-center">
                                        <div class="text-2xl font-weight-bold text-danger">{{ $import->failed_records ?? 0 }}</div>
                                        <div class="text-muted small">Failed</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Messages -->
                            @if($import->status === 'failed' && $import->error_message)
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">Import Error</h6>
                                <p class="mb-0">{{ $import->error_message }}</p>
                            </div>
                            @endif

                            <!-- Import Errors Log -->
                            @if($import->errors && count($import->errors) > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Import Errors ({{ count($import->errors) }})</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Row</th>
                                                    <th>Error</th>
                                                    <th>Data</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($import->errors as $error)
                                                <tr>
                                                    <td>{{ $error['row'] ?? 'N/A' }}</td>
                                                    <td class="text-danger">{{ $error['message'] ?? 'Unknown error' }}</td>
                                                    <td>
                                                        @if(isset($error['data']))
                                                            <small class="text-muted">{{ Str::limit(json_encode($error['data']), 100) }}</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <!-- Import Details -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Import Details</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row small">
                                        <dt class="col-5">File:</dt>
                                        <dd class="col-7">{{ basename($import->file_path) }}</dd>
                                        
                                        <dt class="col-5">Type:</dt>
                                        <dd class="col-7">{{ ucfirst($import->type) }}</dd>
                                        
                                        <dt class="col-5">Status:</dt>
                                        <dd class="col-7">
                                            <span class="badge bg-{{ $import->status === 'completed' ? 'success' : ($import->status === 'failed' ? 'danger' : 'primary') }}">
                                                {{ ucfirst($import->status) }}
                                            </span>
                                        </dd>
                                        
                                        @if(isset($import->options['segment_id']) && $import->options['segment_id'])
                                        <dt class="col-5">Segment:</dt>
                                        <dd class="col-7">
                                            @php
                                                $segment = App\Models\ContactSegment::find($import->options['segment_id']);
                                            @endphp
                                            {{ $segment ? $segment->name : 'Unknown' }}
                                        </dd>
                                        @endif
                                        
                                        <dt class="col-5">Skip Duplicates:</dt>
                                        <dd class="col-7">{{ isset($import->options['skip_duplicates']) && $import->options['skip_duplicates'] ? 'Yes' : 'No' }}</dd>
                                        
                                        <dt class="col-5">Update Existing:</dt>
                                        <dd class="col-7">{{ isset($import->options['update_existing']) && $import->options['update_existing'] ? 'Yes' : 'No' }}</dd>
                                        
                                        <dt class="col-5">Started:</dt>
                                        <dd class="col-7">{{ $import->created_at->format('M j, Y g:i A') }}</dd>
                                        
                                        @if($import->completed_at)
                                        <dt class="col-5">Completed:</dt>
                                        <dd class="col-7">{{ $import->completed_at->format('M j, Y g:i A') }}</dd>
                                        
                                        <dt class="col-5">Duration:</dt>
                                        <dd class="col-7">{{ $import->created_at->diffForHumans($import->completed_at, true) }}</dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Actions</h6>
                                </div>
                                <div class="card-body">
                                    @if($import->status === 'completed')
                                        <a href="{{ route('contacts.index') }}" class="btn btn-primary btn-sm w-100 mb-2">
                                            <i class="fas fa-users me-1"></i> View Imported Contacts
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('contacts.import') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                        <i class="fas fa-upload me-1"></i> Import More Contacts
                                    </a>
                                    
                                    @if($import->status === 'processing')
                                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="refreshStatus()">
                                            <i class="fas fa-refresh me-1"></i> Refresh Status
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($import->status === 'processing')
@push('scripts')
<script>
// Auto-refresh for processing imports
let refreshInterval;

function refreshStatus() {
    fetch(`{{ route('contacts.import.status', $import) }}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status !== 'processing') {
            // Import completed, refresh the page
            window.location.reload();
        } else {
            // Update progress
            if (data.progress) {
                document.getElementById('progressBar').style.width = data.progress + '%';
                document.getElementById('progressText').textContent = data.progress + '%';
            }
        }
    })
    .catch(error => {
        console.error('Status refresh failed:', error);
    });
}

// Auto refresh every 5 seconds for processing imports
if ('{{ $import->status }}' === 'processing') {
    refreshInterval = setInterval(refreshStatus, 5000);
}

// Stop refreshing when page is hidden
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        if (refreshInterval) clearInterval(refreshInterval);
    } else {
        if ('{{ $import->status }}' === 'processing') {
            refreshInterval = setInterval(refreshStatus, 5000);
        }
    }
});
</script>
@endpush
@endif

@endsection