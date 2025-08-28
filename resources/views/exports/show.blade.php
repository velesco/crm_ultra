@extends('layouts.app')

@section('title', 'Export Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">📄 {{ $export->name }}</h1>
                    <p class="text-muted mb-0">{{ $export->formatted_data_type }} • {{ $export->formatted_format }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('exports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Exports
                    </a>
                    
                    @can('update', $export)
                        @if($export->status === 'pending')
                            <a href="{{ route('exports.edit', $export) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                        @endif
                    @endcan
                    
                    @can('duplicate', $export)
                        <form method="POST" action="{{ route('exports.duplicate', $export) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline-info">
                                <i class="fas fa-copy me-2"></i>Duplicate
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Export Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Export Status</h5>
                        <span class="badge bg-{{ $export->status_color }} bg-opacity-10 text-{{ $export->status_color }} px-3 py-2">
                            <i class="fas fa-{{ $export->status_icon }} me-1"></i>
                            {{ ucfirst($export->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($export->status === 'processing' && $export->progress)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Progress</span>
                                <span class="fw-bold">{{ $export->progress }}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: {{ $export->progress }}%"></div>
                            </div>
                            @if($export->status_message)
                                <div class="text-muted small mt-2">{{ $export->status_message }}</div>
                            @endif
                        </div>
                    @endif

                    @if($export->error_message)
                        <div class="alert alert-danger">
                            <strong>Error:</strong> {{ $export->error_message }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Created:</strong>
                                <div class="text-muted">{{ $export->created_at->format('M j, Y \a\t g:i A') }}</div>
                            </div>
                            @if($export->started_at)
                                <div class="mb-3">
                                    <strong>Started:</strong>
                                    <div class="text-muted">{{ $export->started_at->format('M j, Y \a\t g:i A') }}</div>
                                </div>
                            @endif
                            @if($export->completed_at)
                                <div class="mb-3">
                                    <strong>Completed:</strong>
                                    <div class="text-muted">{{ $export->completed_at->format('M j, Y \a\t g:i A') }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($export->processing_duration)
                                <div class="mb-3">
                                    <strong>Processing Time:</strong>
                                    <div class="text-muted">{{ $export->processing_duration }}</div>
                                </div>
                            @endif
                            @if($export->file_size)
                                <div class="mb-3">
                                    <strong>File Size:</strong>
                                    <div class="text-muted">{{ $export->formatted_file_size }}</div>
                                </div>
                            @endif
                            @if($export->download_count > 0)
                                <div class="mb-3">
                                    <strong>Downloads:</strong>
                                    <div class="text-muted">{{ $export->download_count }} times</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mt-3">
                        @can('download', $export)
                            @if($export->canDownload())
                                <a href="{{ route('exports.download', $export) }}" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Download File
                                </a>
                            @endif
                        @endcan

                        @can('process', $export)
                            @if($export->canStart())
                                <form method="POST" action="{{ route('exports.start', $export) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-play me-2"></i>Start Export
                                    </button>
                                </form>
                            @endif
                        @endcan

                        @can('cancel', $export)
                            @if($export->canCancel())
                                <form method="POST" action="{{ route('exports.cancel', $export) }}" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to cancel this export?')">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-stop me-2"></i>Cancel Export
                                    </button>
                                </form>
                            @endif
                        @endcan

                        @can('delete', $export)
                            <form method="POST" action="{{ route('exports.destroy', $export) }}" style="display: inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this export? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Export Configuration -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Configuration Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Data Type:</strong>
                                <div class="text-muted">{{ $export->formatted_data_type }}</div>
                            </div>
                            <div class="mb-3">
                                <strong>Format:</strong>
                                <div class="text-muted">{{ $export->formatted_format }}</div>
                            </div>
                            @if($export->description)
                                <div class="mb-3">
                                    <strong>Description:</strong>
                                    <div class="text-muted">{{ $export->description }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Visibility:</strong>
                                <div class="text-muted">
                                    @if($export->is_public)
                                        <i class="fas fa-globe text-success me-1"></i>Public
                                    @else
                                        <i class="fas fa-lock text-warning me-1"></i>Private
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Notifications:</strong>
                                <div class="text-muted">
                                    @if($export->notify_on_completion)
                                        <i class="fas fa-bell text-success me-1"></i>Enabled
                                    @else
                                        <i class="fas fa-bell-slash text-muted me-1"></i>Disabled
                                    @endif
                                </div>
                            </div>
                            @if($export->include_attachments)
                                <div class="mb-3">
                                    <strong>Attachments:</strong>
                                    <div class="text-muted">
                                        <i class="fas fa-paperclip text-info me-1"></i>Included
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($export->columns)
                        <div class="mb-3">
                            <strong>Selected Columns:</strong>
                            <div class="mt-2">
                                @foreach($export->columns as $column)
                                    <span class="badge bg-light text-dark me-1 mb-1">{{ $column }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($export->filters)
                        <div class="mb-3">
                            <strong>Applied Filters:</strong>
                            <div class="bg-light p-3 rounded mt-2">
                                <pre class="mb-0 small">{{ json_encode($export->filters, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif

                    @if($export->custom_query)
                        <div class="mb-3">
                            <strong>Custom Query:</strong>
                            <div class="bg-dark text-light p-3 rounded mt-2">
                                <pre class="mb-0 small text-light"><code>{{ $export->custom_query }}</code></pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Scheduling Information -->
            @if($export->isScheduled() || $export->isRecurring())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0">Scheduling Information</h5>
                    </div>
                    <div class="card-body">
                        @if($export->scheduled_for)
                            <div class="mb-3">
                                <strong>Scheduled For:</strong>
                                <div class="text-muted">{{ $export->scheduled_for->format('M j, Y \a\t g:i A') }}</div>
                            </div>
                        @endif
                        
                        @if($export->recurring_frequency)
                            <div class="mb-3">
                                <strong>Recurring Frequency:</strong>
                                <div class="text-muted">{{ ucfirst($export->recurring_frequency) }}</div>
                            </div>
                        @endif
                        
                        @if($export->isScheduled())
                            <div class="alert alert-info">
                                <i class="fas fa-clock me-2"></i>
                                This export is scheduled to run {{ $export->scheduled_for->diffForHumans() }}.
                            </div>
                        @endif
                        
                        @if($export->isRecurring())
                            <div class="alert alert-info">
                                <i class="fas fa-sync me-2"></i>
                                This is a recurring export that runs {{ $export->recurring_frequency }}.
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Creator Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">Created By</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="mb-1">{{ $export->createdBy->name }}</h6>
                            <p class="text-muted mb-0">{{ $export->createdBy->email }}</p>
                            <small class="text-muted">{{ $export->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Exports -->
            @if($related_exports->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0">Related Exports</h6>
                    </div>
                    <div class="card-body p-0">
                        @foreach($related_exports as $related)
                            <div class="d-flex align-items-center p-3 border-bottom">
                                <div class="me-3">
                                    <i class="fas fa-{{ $related->status_icon }} text-{{ $related->status_color }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="{{ route('exports.show', $related) }}" class="text-decoration-none">
                                            {{ Str::limit($related->name, 25) }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">{{ $related->created_at->format('M j, Y') }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $related->status_color }} bg-opacity-10 text-{{ $related->status_color }}">
                                        {{ ucfirst($related->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('exports.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Create New Export
                        </a>
                        
                        @can('duplicate', $export)
                            <form method="POST" action="{{ route('exports.duplicate', $export) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-info btn-sm w-100">
                                    <i class="fas fa-copy me-2"></i>Duplicate This Export
                                </button>
                            </form>
                        @endcan
                        
                        <a href="{{ route('exports.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list me-2"></i>View All Exports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($export->status === 'processing')
    @push('scripts')
    <script>
    // Auto-refresh for processing exports
    document.addEventListener('DOMContentLoaded', function() {
        let refreshInterval;
        
        function checkProgress() {
            fetch('{{ route("exports.progress", $export) }}')
                .then(response => response.json())
                .then(data => {
                    // Update progress bar
                    const progressBar = document.querySelector('.progress-bar');
                    const progressText = document.querySelector('.fw-bold');
                    const statusMessage = document.querySelector('.status-message');
                    
                    if (progressBar && data.progress) {
                        progressBar.style.width = data.progress + '%';
                        progressText.textContent = data.progress + '%';
                    }
                    
                    if (statusMessage && data.message) {
                        statusMessage.textContent = data.message;
                    }
                    
                    // If completed or failed, reload page
                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(refreshInterval);
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error checking progress:', error);
                });
        }
        
        // Check progress every 5 seconds
        refreshInterval = setInterval(checkProgress, 5000);
        
        // Clear interval when page is about to unload
        window.addEventListener('beforeunload', function() {
            clearInterval(refreshInterval);
        });
    });
    </script>
    @endpush
@endif
@endsection
