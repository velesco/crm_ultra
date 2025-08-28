@extends('layouts.app')

@section('title', 'Scheduled Exports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">⏰ Scheduled Exports</h1>
                    <p class="text-muted mb-0">Manage scheduled and recurring export tasks</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('exports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>All Exports
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

    <!-- Scheduled Exports -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Upcoming Scheduled Exports</h5>
                </div>
                
                <div class="card-body p-0">
                    @if($scheduled_exports->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Export Name</th>
                                        <th>Data Type</th>
                                        <th>Format</th>
                                        <th>Scheduled For</th>
                                        <th>Frequency</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scheduled_exports as $export)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        @if($export->isRecurring())
                                                            <i class="fas fa-sync text-info" title="Recurring Export"></i>
                                                        @else
                                                            <i class="fas fa-clock text-warning" title="One-time Scheduled"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">{{ $export->name }}</h6>
                                                        @if($export->description)
                                                            <small class="text-muted">{{ Str::limit($export->description, 40) }}</small>
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
                                                <div>
                                                    <div class="fw-bold">{{ $export->scheduled_for->format('M j, Y') }}</div>
                                                    <small class="text-muted">{{ $export->scheduled_for->format('g:i A') }}</small>
                                                    <br>
                                                    <small class=\"badge bg-warning bg-opacity-10 text-warning\">\n                                                        {{ $export->scheduled_for->diffForHumans() }}\n                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($export->recurring_frequency)
                                                    <span class="badge bg-success bg-opacity-10 text-success">
                                                        <i class="fas fa-sync me-1"></i>
                                                        {{ ucfirst($export->recurring_frequency) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">One-time</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $export->status_color }} bg-opacity-10 text-{{ $export->status_color }}">
                                                    <i class="fas fa-{{ $export->status_icon }} me-1"></i>
                                                    {{ ucfirst($export->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $export->createdBy->name }}
                                                    <br>
                                                    <small class="text-muted">{{ $export->created_at->format('M j') }}</small>
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
                                                        
                                                        @can('update', $export)
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('exports.edit', $export) }}">
                                                                    <i class="fas fa-edit me-2"></i>Edit Schedule
                                                                </a>
                                                            </li>
                                                        @endcan
                                                        
                                                        @can('process', $export)
                                                            @if($export->canStart())
                                                                <li>
                                                                    <form method="POST" action="{{ route('exports.start', $export) }}" 
                                                                          style="display: inline;">
                                                                        @csrf
                                                                        <button type="submit" class="dropdown-item">
                                                                            <i class="fas fa-play me-2"></i>Run Now
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
                                                        
                                                        @can('delete', $export)
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form method="POST" action="{{ route('exports.destroy', $export) }}" 
                                                                      style="display: inline;" 
                                                                      onsubmit="return confirm('Are you sure you want to delete this scheduled export?')">
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
                        @if($scheduled_exports->hasPages())
                            <div class="card-footer bg-white border-0 py-3">
                                {{ $scheduled_exports->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No scheduled exports</h5>
                            <p class="text-muted">You don't have any exports scheduled for the future.</p>
                            @can('create', App\Models\ExportRequest::class)
                                <a href="{{ route('exports.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Schedule Export
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Tips -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <h5 class="text-info mb-3">
                        <i class="fas fa-lightbulb me-2"></i>Scheduling Tips
                    </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="fw-bold">One-time Schedules</h6>
                            <p class="text-muted small mb-0">
                                Perfect for monthly reports, end-of-quarter data exports, or specific date requirements.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold">Recurring Exports</h6>
                            <p class="text-muted small mb-0">
                                Ideal for daily backups, weekly reports, or monthly analytics that you need consistently.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold">Time Zones</h6>
                            <p class="text-muted small mb-0">
                                All scheduled times are in your local timezone. Make sure to account for daylight saving changes.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 30 seconds to keep schedule times updated
    setInterval(function() {
        // Only refresh if we're still on the same page
        if (window.location.pathname.includes('scheduled')) {
            location.reload();
        }
    }, 30000);
    
    // Update relative times every minute
    setInterval(function() {
        const timeElements = document.querySelectorAll('.badge.bg-warning .text-warning');
        timeElements.forEach(element => {
            // This would need server-side support to update the "diffForHumans" text
            // For now, we'll just refresh the page every 30 seconds
        });
    }, 60000);
});
</script>
@endpush
@endsection
