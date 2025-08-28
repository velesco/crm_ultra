@extends('layouts.app')

@section('title', 'Edit Export')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">✏️ Edit Export</h1>
                    <p class="text-muted mb-0">Modify export configuration</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('exports.show', $export) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Export
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($export->status !== 'pending')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Note:</strong> Only pending exports can be edited. This export has status: {{ ucfirst($export->status) }}.
        </div>
    @else
        <form method="POST" action="{{ route('exports.update', $export) }}" id="exportForm" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-gradient-primary text-white">
                            <h5 class="mb-0">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">Export Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $export->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $export->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Selection -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-gradient-info text-white">
                            <h5 class="mb-0">Data Selection</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="data_type" class="form-label">Data Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('data_type') is-invalid @enderror" 
                                            id="data_type" name="data_type" required>
                                        @foreach($data_types as $key => $value)
                                            <option value="{{ $key }}" {{ old('data_type', $export->data_type) == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('data_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="format" class="form-label">Export Format <span class="text-danger">*</span></label>
                                    <select class="form-select @error('format') is-invalid @enderror" 
                                            id="format" name="format" required>
                                        @foreach($format_types as $key => $value)
                                            <option value="{{ $key }}" {{ old('format', $export->format) == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Custom Query Section -->
                            @if($export->data_type === 'custom')
                                <div id="customQuerySection">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label for="custom_query" class="form-label">Custom SQL Query <span class="text-danger">*</span></label>
                                            <textarea class="form-control font-monospace @error('custom_query') is-invalid @enderror" 
                                                      id="custom_query" name="custom_query" rows="6">{{ old('custom_query', $export->custom_query) }}</textarea>
                                            @error('custom_query')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Filters and Options -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-gradient-success text-white">
                            <h5 class="mb-0">Filters and Options</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" class="form-control @error('filters.date_from') is-invalid @enderror" 
                                           id="date_from" name="filters[date_from]" 
                                           value="{{ old('filters.date_from', $export->filters['date_from'] ?? '') }}">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" class="form-control @error('filters.date_to') is-invalid @enderror" 
                                           id="date_to" name="filters[date_to]" 
                                           value="{{ old('filters.date_to', $export->filters['date_to'] ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="include_attachments" 
                                               name="include_attachments" value="1" 
                                               {{ old('include_attachments', $export->include_attachments) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="include_attachments">
                                            Include attachments (for supported data types)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @can('makePublic', App\Models\ExportRequest::class)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_public" 
                                                   name="is_public" value="1" 
                                                   {{ old('is_public', $export->is_public) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_public">
                                                Make this export public (visible to all team members)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endcan

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notify_on_completion" 
                                               name="notify_on_completion" value="1" 
                                               {{ old('notify_on_completion', $export->notify_on_completion) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify_on_completion">
                                            Notify me when export is complete
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduling -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-gradient-warning text-dark">
                            <h5 class="mb-0">Scheduling</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="scheduled_for" class="form-label">Schedule Date & Time</label>
                                    <input type="datetime-local" class="form-control @error('scheduled_for') is-invalid @enderror" 
                                           id="scheduled_for" name="scheduled_for" 
                                           value="{{ old('scheduled_for', $export->scheduled_for?->format('Y-m-d\TH:i')) }}">
                                    @error('scheduled_for')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Leave empty to run immediately</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="recurring_frequency" class="form-label">Recurring Frequency</label>
                                    <select class="form-select" id="recurring_frequency" name="recurring_frequency">
                                        <option value="">One-time export</option>
                                        @can('recurring', App\Models\ExportRequest::class)
                                            <option value="daily" {{ old('recurring_frequency', $export->recurring_frequency) == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ old('recurring_frequency', $export->recurring_frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ old('recurring_frequency', $export->recurring_frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        @endcan
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm position-sticky" style="top: 1rem;">
                        <div class="card-header bg-light border-0">
                            <h5 class="mb-0">Export Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Current Status:</strong>
                                <div>
                                    <span class="badge bg-{{ $export->status_color }} bg-opacity-10 text-{{ $export->status_color }}">
                                        {{ ucfirst($export->status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Created:</strong>
                                <div class="text-muted">{{ $export->created_at->format('M j, Y g:i A') }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Created By:</strong>
                                <div class="text-muted">{{ $export->createdBy->name }}</div>
                            </div>

                            @if($export->file_size)
                                <div class="mb-3">
                                    <strong>File Size:</strong>
                                    <div class="text-muted">{{ $export->formatted_file_size }}</div>
                                </div>
                            @endif

                            @if($export->download_count > 0)
                                <div class="mb-3">
                                    <strong>Downloads:</strong>
                                    <div class="text-muted">{{ $export->download_count }}</div>
                                </div>
                            @endif

                            <hr>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Update Export
                                </button>
                                <a href="{{ route('exports.show', $export) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.bg-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #138496);
}

.bg-gradient-success {
    background: linear-gradient(45deg, #28a745, #1e7e34);
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #ffc107, #e0a800);
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('exportForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }
});
</script>
@endpush
@endsection
