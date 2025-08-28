@extends('layouts.app')

@section('title', 'Create Export')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">📤 Create New Export</h1>
                    <p class="text-muted mb-0">Configure and create a new data export</p>
                </div>
                <div>
                    <a href="{{ route('exports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Exports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('exports.store') }}" id="exportForm" class="needs-validation" novalidate>
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Step 1: Basic Information -->
                <div class="card border-0 shadow-sm mb-4" id="step1">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex align-items-center">
                            <div class="step-number me-3">1</div>
                            <div>
                                <h5 class="mb-0">Basic Information</h5>
                                <small class="opacity-75">Export name and description</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Export Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                <div class="form-text">Give your export a descriptive name</div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                <div class="form-text">Optional description of what this export contains</div>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Data Selection -->
                <div class="card border-0 shadow-sm mb-4" id="step2">
                    <div class="card-header bg-gradient-info text-white">
                        <div class="d-flex align-items-center">
                            <div class="step-number me-3">2</div>
                            <div>
                                <h5 class="mb-0">Data Selection</h5>
                                <small class="opacity-75">Choose what data to export</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_type" class="form-label">Data Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('data_type') is-invalid @enderror" 
                                        id="data_type" name="data_type" required>
                                    <option value="">Select data type...</option>
                                    @foreach($data_types as $key => $value)
                                        <option value="{{ $key }}" {{ old('data_type') == $key ? 'selected' : '' }}>
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
                                    <option value="">Select format...</option>
                                    @foreach($format_types as $key => $value)
                                        <option value="{{ $key }}" {{ old('format') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('format')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Column Selection (will be populated via AJAX) -->
                        <div id="columnSelection" style="display: none;">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Select Columns</label>
                                    <div class="form-text mb-2">Choose which columns to include in your export (leave empty to include all)</div>
                                    <div id="columnCheckboxes" class="row"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Query Section -->
                        <div id="customQuerySection" style="display: none;">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="custom_query" class="form-label">Custom SQL Query <span class="text-danger">*</span></label>
                                    <textarea class="form-control font-monospace @error('custom_query') is-invalid @enderror" 
                                              id="custom_query" name="custom_query" rows="6" 
                                              placeholder="SELECT * FROM contacts WHERE created_at >= '2024-01-01'">{{ old('custom_query') }}</textarea>
                                    <div class="form-text">
                                        <strong>Warning:</strong> Custom queries run directly against the database. 
                                        Only use SELECT statements. Available tables: contacts, email_campaigns, sms_messages, whatsapp_messages, revenue, system_logs.
                                    </div>
                                    @error('custom_query')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Filters and Options -->
                <div class="card border-0 shadow-sm mb-4" id="step3">
                    <div class="card-header bg-gradient-success text-white">
                        <div class="d-flex align-items-center">
                            <div class="step-number me-3">3</div>
                            <div>
                                <h5 class="mb-0">Filters and Options</h5>
                                <small class="opacity-75">Configure export filters and settings</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Date Range Filter -->
                            <div class="col-md-6 mb-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control @error('filters.date_from') is-invalid @enderror" 
                                       id="date_from" name="filters[date_from]" value="{{ old('filters.date_from') }}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control @error('filters.date_to') is-invalid @enderror" 
                                       id="date_to" name="filters[date_to]" value="{{ old('filters.date_to') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_attachments" 
                                           name="include_attachments" value="1" {{ old('include_attachments') ? 'checked' : '' }}>
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
                                               name="is_public" value="1" {{ old('is_public') ? 'checked' : '' }}>
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
                                           name="notify_on_completion" value="1" checked>
                                    <label class="form-check-label" for="notify_on_completion">
                                        Notify me when export is complete
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Scheduling -->
                <div class="card border-0 shadow-sm mb-4" id="step4">
                    <div class="card-header bg-gradient-warning text-dark">
                        <div class="d-flex align-items-center">
                            <div class="step-number me-3">4</div>
                            <div>
                                <h5 class="mb-0">Scheduling</h5>
                                <small class="opacity-75">When to run this export</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="schedule_type" class="form-label">Schedule Type</label>
                                <select class="form-select" id="schedule_type" name="schedule_type">
                                    <option value="immediate" {{ old('schedule_type', 'immediate') == 'immediate' ? 'selected' : '' }}>
                                        Run Immediately
                                    </option>
                                    @can('schedule', App\Models\ExportRequest::class)
                                        <option value="scheduled" {{ old('schedule_type') == 'scheduled' ? 'selected' : '' }}>
                                            Schedule for Later
                                        </option>
                                        @can('recurring', App\Models\ExportRequest::class)
                                            <option value="recurring" {{ old('schedule_type') == 'recurring' ? 'selected' : '' }}>
                                                Recurring Export
                                            </option>
                                        @endcan
                                    @endcan
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="scheduledForContainer" style="display: none;">
                                <label for="scheduled_for" class="form-label">Schedule Date & Time</label>
                                <input type="datetime-local" class="form-control @error('scheduled_for') is-invalid @enderror" 
                                       id="scheduled_for" name="scheduled_for" value="{{ old('scheduled_for') }}">
                                @error('scheduled_for')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row" id="recurringContainer" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="recurring_frequency" class="form-label">Frequency</label>
                                <select class="form-select" id="recurring_frequency" name="recurring_frequency">
                                    <option value="">Select frequency...</option>
                                    <option value="daily" {{ old('recurring_frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('recurring_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('recurring_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar with Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm position-sticky" style="top: 1rem;">
                    <div class="card-header bg-light border-0">
                        <h5 class="mb-0">Export Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="export-summary">
                            <div class="mb-3">
                                <strong>Name:</strong>
                                <div class="text-muted" id="summaryName">Not specified</div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Data Type:</strong>
                                <div class="text-muted" id="summaryDataType">Not selected</div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Format:</strong>
                                <div class="text-muted" id="summaryFormat">Not selected</div>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Schedule:</strong>
                                <div class="text-muted" id="summarySchedule">Run immediately</div>
                            </div>
                            
                            <div class="mb-3" id="summaryFilters" style="display: none;">
                                <strong>Filters:</strong>
                                <div class="text-muted" id="summaryFiltersContent"></div>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-rocket me-2"></i>Create Export
                            </button>
                            <a href="{{ route('exports.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Data Source Info -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-info text-white border-0">
                        <h6 class="mb-0">📊 Available Data</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="small">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Contacts:</span>
                                <strong>{{ number_format($data_sources['contacts']) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Email Campaigns:</span>
                                <strong>{{ number_format($data_sources['email_campaigns']) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>SMS Messages:</span>
                                <strong>{{ number_format($data_sources['sms_messages']) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>WhatsApp Messages:</span>
                                <strong>{{ number_format($data_sources['whatsapp_messages']) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Revenue Records:</span>
                                <strong>{{ number_format($data_sources['revenue']) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>System Logs:</span>
                                <strong>{{ number_format($data_sources['system_logs']) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.step-number {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.875rem;
}

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
    const dataTypeSelect = document.getElementById('data_type');
    const scheduleTypeSelect = document.getElementById('schedule_type');
    const customQuerySection = document.getElementById('customQuerySection');
    const columnSelection = document.getElementById('columnSelection');
    const scheduledForContainer = document.getElementById('scheduledForContainer');
    const recurringContainer = document.getElementById('recurringContainer');

    // Update summary when form fields change
    document.getElementById('name').addEventListener('input', updateSummary);
    dataTypeSelect.addEventListener('change', function() {
        updateSummary();
        handleDataTypeChange();
    });
    document.getElementById('format').addEventListener('change', updateSummary);
    scheduleTypeSelect.addEventListener('change', function() {
        updateSummary();
        handleScheduleTypeChange();
    });

    function updateSummary() {
        const name = document.getElementById('name').value || 'Not specified';
        const dataType = dataTypeSelect.options[dataTypeSelect.selectedIndex].text || 'Not selected';
        const format = document.getElementById('format').options[document.getElementById('format').selectedIndex].text || 'Not selected';
        const scheduleType = scheduleTypeSelect.value;
        
        document.getElementById('summaryName').textContent = name;
        document.getElementById('summaryDataType').textContent = dataType;
        document.getElementById('summaryFormat').textContent = format;
        
        let scheduleText = 'Run immediately';
        if (scheduleType === 'scheduled') {
            const scheduledFor = document.getElementById('scheduled_for').value;
            scheduleText = scheduledFor ? `Scheduled for ${new Date(scheduledFor).toLocaleString()}` : 'Schedule for later';
        } else if (scheduleType === 'recurring') {
            const frequency = document.getElementById('recurring_frequency').value;
            scheduleText = frequency ? `Recurring ${frequency}` : 'Recurring export';
        }
        document.getElementById('summarySchedule').textContent = scheduleText;
    }

    function handleDataTypeChange() {
        const dataType = dataTypeSelect.value;
        
        if (dataType === 'custom') {
            customQuerySection.style.display = 'block';
            columnSelection.style.display = 'none';
        } else if (dataType) {
            customQuerySection.style.display = 'none';
            loadColumns(dataType);
        } else {
            customQuerySection.style.display = 'none';
            columnSelection.style.display = 'none';
        }
    }

    function handleScheduleTypeChange() {
        const scheduleType = scheduleTypeSelect.value;
        
        scheduledForContainer.style.display = (scheduleType === 'scheduled' || scheduleType === 'recurring') ? 'block' : 'none';
        recurringContainer.style.display = (scheduleType === 'recurring') ? 'block' : 'none';
        
        if (scheduleType === 'scheduled' || scheduleType === 'recurring') {
            document.getElementById('scheduled_for').required = true;
        } else {
            document.getElementById('scheduled_for').required = false;
        }
        
        if (scheduleType === 'recurring') {
            document.getElementById('recurring_frequency').required = true;
        } else {
            document.getElementById('recurring_frequency').required = false;
        }
    }

    function loadColumns(dataType) {
        fetch(`{{ route('exports.columns', '') }}/${dataType}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('columnCheckboxes');
                container.innerHTML = '';
                
                if (data.columns && Object.keys(data.columns).length > 0) {
                    Object.entries(data.columns).forEach(([key, label]) => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 mb-2';
                        col.innerHTML = `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="${key}" id="col_${key}">
                                <label class="form-check-label" for="col_${key}">
                                    ${label}
                                </label>
                            </div>
                        `;
                        container.appendChild(col);
                    });
                    columnSelection.style.display = 'block';
                } else {
                    columnSelection.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading columns:', error);
                columnSelection.style.display = 'none';
            });
    }

    // Form validation
    const form = document.getElementById('exportForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Initialize
    handleDataTypeChange();
    handleScheduleTypeChange();
    updateSummary();
});
</script>
@endpush
@endsection
