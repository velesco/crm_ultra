@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-plus-circle text-primary me-2"></i>Create Custom Report
                    </h1>
                    <p class="text-muted mb-0">Build a custom report with advanced filtering and visualization options</p>
                </div>
                <div>
                    <a href="{{ route('admin.custom-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Report Builder -->
        <div class="col-12">
            <form id="reportForm" method="POST" action="{{ route('admin.custom-reports.store') }}">
                @csrf
                
                <!-- Step 1: Basic Information -->
                <div class="card shadow mb-4" id="step1">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-info-circle me-2"></i>Step 1: Basic Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Report Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" required 
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
                                            <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
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
                                              rows="3" placeholder="Describe what this report does...">{{ old('description') }}</textarea>
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
                                            <option value="{{ $key }}" {{ old('data_source') == $key ? 'selected' : '' }}>
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
                                        <option value="private" {{ old('visibility', 'private') == 'private' ? 'selected' : '' }}>Private</option>
                                        <option value="shared" {{ old('visibility') == 'shared' ? 'selected' : '' }}>Shared</option>
                                        <option value="public" {{ old('visibility') == 'public' ? 'selected' : '' }}>Public</option>
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
                                        <option value="table" {{ old('export_format', 'table') == 'table' ? 'selected' : '' }}>Table Only</option>
                                        <option value="chart" {{ old('export_format') == 'chart' ? 'selected' : '' }}>Chart Only</option>
                                        <option value="both" {{ old('export_format') == 'both' ? 'selected' : '' }}>Table + Chart</option>
                                    </select>
                                    @error('export_format')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                Next: Columns <i class="fas fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Column Selection -->
                <div class="card shadow mb-4" id="step2" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-columns me-2"></i>Step 2: Column Selection
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="columnSelection">
                            <p class="text-muted">Select a data source to see available columns.</p>
                        </div>
                        <div class="row mt-4">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)">
                                    <i class="fas fa-arrow-left me-1"></i>Previous
                                </button>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-success" onclick="nextStep(3)">
                                    Next: Filters <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Filters & Sorting -->
                <div class="card shadow mb-4" id="step3" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-filter me-2"></i>Step 3: Filters & Sorting
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <div class="mb-4">
                            <h6 class="text-info">
                                <i class="fas fa-filter me-1"></i>Filters (Optional)
                            </h6>
                            <div id="filtersContainer">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Add filters to narrow down your data. Leave empty to include all records.
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info" id="addFilterBtn">
                                <i class="fas fa-plus me-1"></i>Add Filter
                            </button>
                        </div>

                        <!-- Sorting Section -->
                        <div class="mb-4">
                            <h6 class="text-info">
                                <i class="fas fa-sort me-1"></i>Sorting (Optional)
                            </h6>
                            <div id="sortingContainer">
                                <!-- Sorting fields will be added here -->
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info" id="addSortBtn">
                                <i class="fas fa-plus me-1"></i>Add Sort Field
                            </button>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)">
                                    <i class="fas fa-arrow-left me-1"></i>Previous
                                </button>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-info" onclick="nextStep(4)">
                                    Next: Visualization <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Chart Configuration -->
                <div class="card shadow mb-4" id="step4" style="display: none;">
                    <div class="card-header bg-warning text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-chart-bar me-2"></i>Step 4: Chart Configuration (Optional)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableChart">
                                <label class="form-check-label" for="enableChart">
                                    Enable Chart Visualization
                                </label>
                            </div>
                        </div>
                        
                        <div id="chartConfig" style="display: none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Chart Type</label>
                                        <select name="chart_config[type]" class="form-select">
                                            @foreach($chartTypes as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">X-Axis Column</label>
                                        <select name="chart_config[x_axis]" class="form-select" id="xAxisSelect">
                                            <option value="">Select column...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Y-Axis Column</label>
                                        <select name="chart_config[y_axis]" class="form-select" id="yAxisSelect">
                                            <option value="">Select column...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(3)">
                                    <i class="fas fa-arrow-left me-1"></i>Previous
                                </button>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-warning" onclick="nextStep(5)">
                                    Next: Preview <i class="fas fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Preview & Save -->
                <div class="card shadow mb-4" id="step5" style="display: none;">
                    <div class="card-header bg-dark text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-eye me-2"></i>Step 5: Preview & Save
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <button type="button" class="btn btn-info" id="previewBtn">
                                <i class="fas fa-play me-1"></i>Preview Report (First 10 rows)
                            </button>
                        </div>

                        <div id="previewContainer" style="display: none;">
                            <h6>Report Preview:</h6>
                            <div id="previewContent"></div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(4)">
                                    <i class="fas fa-arrow-left me-1"></i>Previous
                                </button>
                            </div>
                            <div class="col-6 text-end">
                                <button type="submit" class="btn btn-success" id="saveBtn">
                                    <i class="fas fa-save me-1"></i>Save Report
                                </button>
                            </div>
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
.step-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
}

.step-indicator .step {
    flex: 1;
    text-align: center;
    padding: 1rem;
    position: relative;
}

.step-indicator .step.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 0.5rem;
}

.filter-row, .sort-row {
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background: #f8f9fc;
}

.column-checkbox {
    margin-bottom: 0.5rem;
}

.column-selection-grid {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e3e6f0;
    border-radius: 0.35rem;
    padding: 1rem;
}

.chart-config-section {
    background: #f8f9fc;
    border-radius: 0.35rem;
    padding: 1.5rem;
    border: 1px solid #e3e6f0;
}
</style>
@endpush

@push('scripts')
<script>
let availableColumns = [];
let filterCount = 0;
let sortCount = 0;

$(document).ready(function() {
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

    // Load columns when data source is pre-selected
    const initialDataSource = $('#dataSourceSelect').val();
    if (initialDataSource) {
        loadColumns(initialDataSource);
    }
});

// Step validation functions
function validateStep(step) {
    let isValid = true;
    
    switch(step) {
        case 1:
            // Validate basic information
            if (!$('input[name="name"]').val().trim()) {
                showNotification('error', 'Report name is required');
                isValid = false;
            }
            if (!$('select[name="category"]').val()) {
                showNotification('error', 'Category is required');
                isValid = false;
            }
            if (!$('select[name="data_source"]').val()) {
                showNotification('error', 'Data source is required');
                isValid = false;
            }
            break;
            
        case 2:
            // Validate column selection
            if ($('input[name="columns[]"]:checked').length === 0) {
                showNotification('error', 'At least one column must be selected');
                isValid = false;
            }
            break;
    }
    
    return isValid;
}

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
    let html = `
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    `;
    
    availableColumns.forEach((column, index) => {
        const checked = index < 5 ? 'checked' : ''; // Auto-select first 5 columns
        const displayName = column.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        html += `
            <label class="flex items-center p-3 bg-white border border-gray-200 rounded-xl hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition-all duration-200">
                <input type="checkbox" 
                       name="columns[]" 
                       value="${column}" 
                       ${checked} 
                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                <span class="ml-3 text-sm font-medium text-gray-900">${displayName}</span>
            </label>
        `;
    });
    
    html += `
            </div>
            <div class="flex gap-3">
                <button type="button" 
                        onclick="selectAllColumns()" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                    Select All
                </button>
                <button type="button" 
                        onclick="selectNoneColumns()" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    Select None
                </button>
            </div>
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
        <div class="filter-row bg-gray-50 border border-gray-200 rounded-xl p-6 mb-4" data-filter-id="${filterCount}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Column</label>
                    <select name="filters[${filterCount}][column]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                        <option value="">Select column...</option>
                        ${availableColumns.map(col => `<option value="${col}">${col.replace(/_/g, ' ')}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Operator</label>
                    <select name="filters[${filterCount}][operator]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                        @foreach($operators as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Value</label>
                    <input type="text" 
                           name="filters[${filterCount}][value]" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200" 
                           placeholder="Enter value...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                    <button type="button" 
                            onclick="removeFilterRow(${filterCount})" 
                            class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#filtersContainer .bg-blue-50').remove();
    $('#filtersContainer').append(html);
}

function removeFilterRow(filterId) {
    $(`.filter-row[data-filter-id="${filterId}"]`).remove();
}

function addSortRow() {
    sortCount++;
    const html = `
        <div class="sort-row bg-gray-50 border border-gray-200 rounded-xl p-6 mb-4" data-sort-id="${sortCount}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Column</label>
                    <select name="sorting[${sortCount}][column]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                        <option value="">Select column...</option>
                        ${availableColumns.map(col => `<option value="${col}">${col.replace(/_/g, ' ')}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Direction</label>
                    <select name="sorting[${sortCount}][direction]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                        <option value="asc">Ascending</option>
                        <option value="desc">Descending</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                    <button type="button" 
                            onclick="removeSortRow(${sortCount})" 
                            class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#sortingContainer').append(html);
}

function removeSortRow(sortId) {
    $(`.sort-row[data-sort-id="${sortId}"]`).remove();
}

function updateFilterOptions() {
    // Update existing filter column options
    $('.filter-row select[name*="[column]"]').each(function() {
        const currentValue = $(this).val();
        $(this).html(`
            <option value="">Select column...</option>
            ${availableColumns.map(col => `<option value="${col}" ${col === currentValue ? 'selected' : ''}>${col.replace(/_/g, ' ')}</option>`).join('')}
        `);
    });
}

function updateSortOptions() {
    // Update existing sort column options
    $('.sort-row select[name*="[column]"]').each(function() {
        const currentValue = $(this).val();
        $(this).html(`
            <option value="">Select column...</option>
            ${availableColumns.map(col => `<option value="${col}" ${col === currentValue ? 'selected' : ''}>${col.replace(/_/g, ' ')}</option>`).join('')}
        `);
    });
}

function updateChartOptions() {
    const options = availableColumns.map(col => `<option value="${col}">${col.replace(/_/g, ' ')}</option>`).join('');
    $('#xAxisSelect, #yAxisSelect').html('<option value="">Select column...</option>' + options);
}

function previewReport() {
    const btn = $('#previewBtn');
    const formData = new FormData($('#reportForm')[0]);
    
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Loading Preview...');
    
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
            $('#previewContainer').removeClass('hidden');
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        showNotification('error', response.error || 'Preview failed');
    })
    .always(function() {
        btn.prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Preview Report (First 10 rows)');
    });
}

function renderPreview(data) {
    if (data.length === 0) {
        $('#previewContent').html('<div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4"><i class="fas fa-info-circle mr-2"></i>No data found with the current configuration.</div>');
        return;
    }

    const columns = Object.keys(data[0]);
    let html = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 rounded-xl overflow-hidden shadow">
                <thead class="bg-gradient-to-r from-blue-500 to-purple-600">
                    <tr>
                        ${columns.map(col => `<th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">${col.replace(/_/g, ' ')}</th>`).join('')}
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${data.map((row, index) => `
                        <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors duration-200">
                            ${columns.map(col => `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row[col] || ''}</td>`).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
        <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4 mt-4">
            <i class="fas fa-info-circle mr-2"></i>
            Showing first ${data.length} rows. Full report may contain more data.
        </div>
    `;
    
    $('#previewContent').html(html);
}

// Form submission
$('#reportForm').submit(function(e) {
    e.preventDefault();
    
    const btn = $('#saveBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: new FormData(this),
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
            showNotification('error', response.error || 'Save failed');
        }
    })
    .always(function() {
        btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Save Report');
    });
});

// Alpine.js validation function
window.validateStep = function(step) {
    let isValid = true;
    
    switch(step) {
        case 1:
            if (!$('input[name="name"]').val().trim()) {
                showNotification('error', 'Report name is required');
                isValid = false;
            }
            if (!$('select[name="category"]').val()) {
                showNotification('error', 'Category is required');
                isValid = false;
            }
            if (!$('select[name="data_source"]').val()) {
                showNotification('error', 'Data source is required');
                isValid = false;
            }
            break;
            
        case 2:
            if ($('input[name="columns[]"]:checked').length === 0) {
                showNotification('error', 'At least one column must be selected');
                isValid = false;
            }
            break;
    }
    
    return isValid;
};

function showNotification(type, message) {
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
    
    const notification = $(`
        <div class="fixed top-4 right-4 z-50 flex items-center p-4 ${bgColor} text-white rounded-xl shadow-2xl transform transition-all duration-500 translate-x-full">
            <i class="fas fa-${icon} mr-3"></i>
            <span class="font-medium">${message}</span>
            <button class="ml-4 text-white hover:text-gray-200" onclick="$(this).parent().remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Animate in
    setTimeout(() => notification.removeClass('translate-x-full'), 100);
    
    // Auto remove
    setTimeout(() => {
        notification.addClass('translate-x-full');
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}
</script>
@endpushselected
    const initialDataSource = $('#dataSourceSelect').val();
    if (initialDataSource) {
        loadColumns(initialDataSource);
    }
});

function nextStep(step) {
    // Validate current step
    const currentStep = $('.card[id^="step"]:visible').attr('id').replace('step', '');
    
    if (!validateStep(currentStep)) {
        return;
    }

    // Hide all steps
    $('[id^="step"]').hide();
    
    // Show target step
    $('#step' + step).show();
    
    // Update step indicator if you have one
    updateStepIndicator(step);
}

function prevStep(step) {
    $('[id^="step"]').hide();
    $('#step' + step).show();
    updateStepIndicator(step);
}

function updateStepIndicator(activeStep) {
    // You can implement a step indicator here
}

function validateStep(step) {
    let isValid = true;
    
    switch(step) {
        case '1':
            // Validate basic information
            if (!$('input[name="name"]').val().trim()) {
                showNotification('error', 'Report name is required');
                isValid = false;
            }
            if (!$('select[name="category"]').val()) {
                showNotification('error', 'Category is required');
                isValid = false;
            }
            if (!$('select[name="data_source"]').val()) {
                showNotification('error', 'Data source is required');
                isValid = false;
            }
            break;
            
        case '2':
            // Validate column selection
            if ($('input[name="columns[]"]:checked').length === 0) {
                showNotification('error', 'At least one column must be selected');
                isValid = false;
            }
            break;
    }
    
    return isValid;
}

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
    let html = '<div class="column-selection-grid"><div class="row">';
    
    availableColumns.forEach((column, index) => {
        const checked = index < 5 ? 'checked' : ''; // Auto-select first 5 columns
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
        <div class="filter-row" data-filter-id="${filterCount}">
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
        <div class="sort-row" data-sort-id="${sortCount}">
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
    
    $('#sortingContainer').append(html);
}

function removeSortRow(sortId) {
    $(`.sort-row[data-sort-id="${sortId}"]`).remove();
}

function updateFilterOptions() {
    // Update existing filter column options
    $('.filter-row select[name*="[column]"]').each(function() {
        const currentValue = $(this).val();
        $(this).html(`
            <option value="">Select column...</option>
            ${availableColumns.map(col => `<option value="${col}" ${col === currentValue ? 'selected' : ''}>${col.replace(/_/g, ' ')}</option>`).join('')}
        `);
    });
}

function updateSortOptions() {
    // Update existing sort column options
    $('.sort-row select[name*="[column]"]').each(function() {
        const currentValue = $(this).val();
        $(this).html(`
            <option value="">Select column...</option>
            ${availableColumns.map(col => `<option value="${col}" ${col === currentValue ? 'selected' : ''}>${col.replace(/_/g, ' ')}</option>`).join('')}
        `);
    });
}

function updateChartOptions() {
    const options = availableColumns.map(col => `<option value="${col}">${col.replace(/_/g, ' ')}</option>`).join('');
    $('#xAxisSelect, #yAxisSelect').html('<option value="">Select column...</option>' + options);
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
        btn.prop('disabled', false).html('<i class="fas fa-play me-1"></i>Preview Report (First 10 rows)');
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

// Form submission
$('#reportForm').submit(function(e) {
    e.preventDefault();
    
    const btn = $('#saveBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: new FormData(this),
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
            showNotification('error', response.error || 'Save failed');
        }
    })
    .always(function() {
        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Report');
    });
});

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
