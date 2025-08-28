@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>Edit Custom Report
                    </h1>
                    <p class="text-muted mb-0">Modify report configuration and visualization settings</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.custom-reports.show', $customReport) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye me-1"></i>View Report
                    </a>
                    <a href="{{ route('admin.custom-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Report Editor -->
        <div class="col-12">
            <form id="reportForm" method="POST" action="{{ route('admin.custom-reports.update', $customReport) }}">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Report Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $customReport->name) }}" required 
                                           placeholder="Enter report name...">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Select category...</option>
                                        @foreach($categories as $key => $label)
                                            <option value="{{ $key }}" {{ old('category', $customReport->category) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Describe what this report does...">{{ old('description', $customReport->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Data Source <span class="text-danger">*</span></label>
                                    <select name="data_source" id="dataSourceSelect" class="form-select @error('data_source') is-invalid @enderror" required>
                                        <option value="">Select data source...</option>
                                        @foreach($dataSources as $key => $info)
                                            <option value="{{ $key }}" {{ old('data_source', $customReport->data_source) == $key ? 'selected' : '' }}>
                                                {{ $info['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('data_source')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Visibility <span class="text-danger">*</span></label>
                                    <select name="visibility" class="form-select @error('visibility') is-invalid @enderror" required>
                                        <option value="private" {{ old('visibility', $customReport->visibility) == 'private' ? 'selected' : '' }}>Private</option>
                                        <option value="shared" {{ old('visibility', $customReport->visibility) == 'shared' ? 'selected' : '' }}>Shared</option>
                                        <option value="public" {{ old('visibility', $customReport->visibility) == 'public' ? 'selected' : '' }}>Public</option>
                                    </select>
                                    @error('visibility')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Export Format <span class="text-danger">*</span></label>
                                    <select name="export_format" class="form-select @error('export_format') is-invalid @enderror" required>
                                        <option value="table" {{ old('export_format', $customReport->export_format) == 'table' ? 'selected' : '' }}>Table Only</option>
                                        <option value="chart" {{ old('export_format', $customReport->export_format) == 'chart' ? 'selected' : '' }}>Chart Only</option>
                                        <option value="both" {{ old('export_format', $customReport->export_format) == 'both' ? 'selected' : '' }}>Table + Chart</option>
                                    </select>
                                    @error('export_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Toggle -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" 
                                               id="activeToggle" {{ old('is_active', $customReport->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="activeToggle">
                                            Active Report
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column Selection -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-columns me-2"></i>Column Selection
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="columnSelection">
                            <p class="text-muted">Loading columns for selected data source...</p>
                        </div>
                    </div>
                </div>

                <!-- Filters Configuration -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-filter me-2"></i>Filters Configuration
                        </h6>
                        <button type="button" class="btn btn-sm btn-light" id="addFilterBtn">
                            <i class="fas fa-plus me-1"></i>Add Filter
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="filtersContainer">
                            @if(!empty($customReport->filters))
                                @foreach($customReport->filters as $index => $filter)
                                    <div class="filter-row mb-3 p-3 border rounded" data-filter-id="{{ $index }}">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label">Column</label>
                                                <select name="filters[{{ $index }}][column]" class="form-select">
                                                    <option value="">Select column...</option>
                                                    <option value="{{ $filter['column'] }}" selected>{{ $filter['column'] }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Operator</label>
                                                <select name="filters[{{ $index }}][operator]" class="form-select">
                                                    @foreach($operators as $key => $label)
                                                        <option value="{{ $key }}" {{ $filter['operator'] == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Value</label>
                                                <input type="text" name="filters[{{ $index }}][value]" class="form-control" 
                                                       value="{{ $filter['value'] }}" placeholder="Enter value...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-outline-danger d-block" onclick="removeFilterRow({{ $index }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No filters configured. Add filters to narrow down your data.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sorting Configuration -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-sort me-2"></i>Sorting Configuration
                        </h6>
                        <button type="button" class="btn btn-sm btn-light" id="addSortBtn">
                            <i class="fas fa-plus me-1"></i>Add Sort Field
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="sortingContainer">
                            @if(!empty($customReport->sorting))
                                @foreach($customReport->sorting as $index => $sort)
                                    <div class="sort-row mb-3 p-3 border rounded" data-sort-id="{{ $index }}">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <label class="form-label">Column</label>
                                                <select name="sorting[{{ $index }}][column]" class="form-select">
                                                    <option value="">Select column...</option>
                                                    <option value="{{ $sort['column'] }}" selected>{{ $sort['column'] }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label">Direction</label>
                                                <select name="sorting[{{ $index }}][direction]" class="form-select">
                                                    <option value="asc" {{ $sort['direction'] == 'asc' ? 'selected' : '' }}>Ascending</option>
                                                    <option value="desc" {{ $sort['direction'] == 'desc' ? 'selected' : '' }}>Descending</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-outline-danger d-block" onclick="removeSortRow({{ $index }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No sorting configured. Add sort fields to control data order.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Chart Configuration -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-chart-bar me-2"></i>Chart Configuration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableChart" 
                                       {{ !empty($customReport->chart_config) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enableChart">
                                    Enable Chart Visualization
                                </label>
                            </div>
                        </div>
                        
                        <div id="chartConfig" style="{{ empty($customReport->chart_config) ? 'display: none;' : '' }}">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Chart Type</label>
                                        <select name="chart_config[type]" class="form-select">
                                            @foreach($chartTypes as $key => $label)
                                                <option value="{{ $key }}" 
                                                    {{ ($customReport->chart_config['type'] ?? '') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">X-Axis Column</label>
                                        <select name="chart_config[x_axis]" class="form-select" id="xAxisSelect">
                                            <option value="">Select column...</option>
                                            @if(!empty($customReport->chart_config['x_axis']))
                                                <option value="{{ $customReport->chart_config['x_axis'] }}" selected>
                                                    {{ $customReport->chart_config['x_axis'] }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Y-Axis Column</label>
                                        <select name="chart_config[y_axis]" class="form-select" id="yAxisSelect">
                                            <option value="">Select column...</option>
                                            @if(!empty($customReport->chart_config['y_axis']))
                                                <option value="{{ $customReport->chart_config['y_axis'] }}" selected>
                                                    {{ $customReport->chart_config['y_axis'] }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Actions -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-info me-2" id="previewBtn">
                                    <i class="fas fa-eye me-1"></i>Preview Changes
                                </button>
                                <small class="text-muted">Preview the report with current settings</small>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-success" id="saveBtn">
                                    <i class="fas fa-save me-1"></i>Update Report
                                </button>
                            </div>
                        </div>
                        
                        <!-- Preview Container -->
                        <div id="previewContainer" class="mt-4" style="display: none;">
                            <h6>Preview Results:</h6>
                            <div id="previewContent"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.filter-row, .sort-row {
    background: #f8f9fc;
    border: 1px solid #e3e6f0 !important;
}

.column-selection-grid {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
    padding: 1rem;
}

.column-checkbox {
    margin-bottom: 0.5rem;
}

.card {
    border: 1px solid #e3e6f0;
}

.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.alert {
    border: none;
}
</style>
@endpush

@push('scripts')
<script>
let availableColumns = [];
let filterCount = {{ count($customReport->filters ?? []) }};
let sortCount = {{ count($customReport->sorting ?? []) }};

$(document).ready(function() {
    // Load columns for current data source
    const currentDataSource = $('#dataSourceSelect').val();
    if (currentDataSource) {
        loadColumns(currentDataSource);
    }

    // Data source change handler
    $('#dataSourceSelect').change(function() {
        const dataSource = $(this).val();
        if (dataSource) {
            loadColumns(dataSource);
        } else {
            $('#columnSelection').html('<p class="text-muted">Select a data source to see available columns.</p>');
            availableColumns = [];
        }
    });

    // Enable chart checkbox
    $('#enableChart').change(function() {
        if ($(this).is(':checked')) {
            $('#chartConfig').show();
        } else {
            $('#chartConfig').hide();
        }
    });

    // Add filter button
    $('#addFilterBtn').click(function() {
        addFilterRow();
    });

    // Add sort button
    $('#addSortBtn').click(function() {
        addSortRow();
    });

    // Preview button
    $('#previewBtn').click(function() {
        previewReport();
    });

    // Form submission
    $('#reportForm').submit(function(e) {
        e.preventDefault();
        updateReport();
    });
});

function loadColumns(dataSource) {
    $.get(`{{ route('admin.custom-reports.get-columns', '') }}/${dataSource}`)
    .done(function(response) {
        if (response.success) {
            availableColumns = response.columns;
            renderColumnSelection();
            updateFilterOptions();
            updateSortOptions();
            updateChartOptions();
        }
    })
    .fail(function() {
        showNotification('error', 'Failed to load columns');
    });
}

function renderColumnSelection() {
    const selectedColumns = {!! json_encode($customReport->columns) !!};
    
    let html = '<div class="column-selection-grid"><div class="row">';
    
    availableColumns.forEach((column, index) => {
        const checked = selectedColumns.includes(column) ? 'checked' : '';
        html += `
            <div class="col-md-4 column-checkbox">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="columns[]" value="${column}" ${checked} id="col_${index}">
                    <label class="form-check-label" for="col_${index}">
                        ${column.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </label>
                </div>
            </div>
        `;
    });
    
    html += '</div></div>';
    html += `
        <div class="mt-3">
            <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="selectAllColumns()">Select All</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectNoneColumns()">Select None</button>
        </div>
    `;
    
    $('#columnSelection').html(html);
}

function selectAllColumns() {
    $('input[name="columns[]"]').prop('checked', true);
}

function selectNoneColumns() {
    $('input[name="columns[]"]').prop('checked', false);
}

function addFilterRow() {
    filterCount++;
    const html = `
        <div class="filter-row mb-3 p-3 border rounded" data-filter-id="${filterCount}">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Column</label>
                    <select name="filters[${filterCount}][column]" class="form-select">
                        <option value="">Select column...</option>
                        ${availableColumns.map(col => `<option value="${col}">${col.replace(/_/g, ' ')}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Operator</label>
                    <select name="filters[${filterCount}][operator]" class="form-select">
                        @foreach($operators as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Value</label>
                    <input type="text" name="filters[${filterCount}][value]" class="form-control" placeholder="Enter value...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger d-block" onclick="removeFilterRow(${filterCount})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#filtersContainer .alert').remove();
    $('#filtersContainer').append(html);
}

function removeFilterRow(filterId) {
    $(`.filter-row[data-filter-id="${filterId}"]`).remove();
}

function addSortRow() {
    sortCount++;
    const html = `
        <div class="sort-row mb-3 p-3 border rounded" data-sort-id="${sortCount}">
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label">Column</label>
                    <select name="sorting[${sortCount}][column]" class="form-select">
                        <option value="">Select column...</option>
                        ${availableColumns.map(col => `<option value="${col}">${col.replace(/_/g, ' ')}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Direction</label>
                    <select name="sorting[${sortCount}][direction]" class="form-select">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-danger d-block" onclick="removeSortRow(${sortCount})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#sortingContainer .alert').remove();
    $('#sortingContainer').append(html);
}

function removeSortRow(sortId) {
    $(`.sort-row[data-sort-id="${sortId}"]`).remove();
}

function updateFilterOptions() {
    $('.filter-row select[name*="[column]"]').each(function() {
        const currentValue = $(this).val();
        $(this).html(`
            <option value="">Select column...</option>
            ${availableColumns.map(col => `<option value="${col}" ${col === currentValue ? 'selected' : ''}>${col.replace(/_/g, ' ')}</option>`).join('')}
        `);
    });
}

function updateSortOptions() {
    $('.sort-row select[name*="[column]"]').each(function() {
        const currentValue = $(this).val();
        $(this).html(`
            <option value="">Select column...</option>
            ${availableColumns.map(col => `<option value="${col}" ${col === currentValue ? 'selected' : ''}>${col.replace(/_/g, ' ')}</option>`).join('')}
        `);
    });
}

function updateChartOptions() {
    const xAxisCurrent = $('#xAxisSelect').val();
    const yAxisCurrent = $('#yAxisSelect').val();
    const options = availableColumns.map(col => `<option value="${col}" ${col === xAxisCurrent || col === yAxisCurrent ? 'selected' : ''}>${col.replace(/_/g, ' ')}</option>`).join('');
    
    $('#xAxisSelect').html('<option value="">Select column...</option>' + options);
    $('#yAxisSelect').html('<option value="">Select column...</option>' + options);
    
    // Restore selected values
    if (xAxisCurrent) $('#xAxisSelect').val(xAxisCurrent);
    if (yAxisCurrent) $('#yAxisSelect').val(yAxisCurrent);
}

function previewReport() {
    const btn = $('#previewBtn');
    const formData = new FormData($('#reportForm')[0]);
    
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Loading Preview...');
    
    $.ajax({
        url: '{{ route("admin.custom-reports.preview") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false
    })
    .done(function(response) {
        if (response.success) {
            renderPreview(response.data);
            $('#previewContainer').show();
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        showNotification('error', response.error || 'Preview failed');
    })
    .always(function() {
        btn.prop('disabled', false).html('<i class="fas fa-eye me-1"></i>Preview Changes');
    });
}

function renderPreview(data) {
    if (data.length === 0) {
        $('#previewContent').html('<div class="alert alert-info">No data found with the current configuration.</div>');
        return;
    }

    const columns = Object.keys(data[0]);
    let html = `
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-dark">
                    <tr>
                        ${columns.map(col => `<th>${col.replace(/_/g, ' ')}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${data.map(row => `
                        <tr>
                            ${columns.map(col => `<td>${row[col] || ''}</td>`).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div class="alert alert-info mt-2">
            <i class="fas fa-info-circle me-2"></i>
            Showing first ${data.length} rows. Full report may contain more data.
        </div>
    `;
    
    $('#previewContent').html(html);
}

function updateReport() {
    const btn = $('#saveBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...');
    
    $.ajax({
        url: $('#reportForm').attr('action'),
        method: 'POST',
        data: new FormData($('#reportForm')[0]),
        processData: false,
        contentType: false
    })
    .done(function(response) {
        if (response.success) {
            showNotification('success', response.message);
            if (response.redirect) {
                setTimeout(() => {
                    window.location.href = response.redirect;
                }, 1000);
            }
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        if (response.errors) {
            Object.keys(response.errors).forEach(field => {
                showNotification('error', response.errors[field][0]);
            });
        } else {
            showNotification('error', response.error || 'Update failed');
        }
    })
    .always(function() {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Update Report');
    });
}

function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.alert('close');
    }, 5000);
}
</script>
@endpush
