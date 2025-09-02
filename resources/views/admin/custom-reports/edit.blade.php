@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl shadow-lg">
                            <i class="fas fa-edit text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Edit Custom Report</h1>
                            <p class="text-gray-600 mt-1">Modify report configuration and visualization settings</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.custom-reports.show', $customReport) }}" 
                       class="inline-flex items-center px-6 py-3 bg-white border border-cyan-300 text-cyan-700 font-medium rounded-xl hover:bg-cyan-50 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                        <i class="fas fa-eye mr-2"></i>View Report
                    </a>
                    <a href="{{ route('admin.custom-reports.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Report Editor Form -->
        <form id="reportForm" method="POST" action="{{ route('admin.custom-reports.update', $customReport) }}">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-info-circle text-xl"></i>
                        <h3 class="text-xl font-semibold">Basic Information</h3>
                    </div>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Report Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('name') border-red-500 @enderror" 
                                   value="{{ old('name', $customReport->name) }}" 
                                   required 
                                   placeholder="Enter report name...">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select name="category" 
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('category') border-red-500 @enderror" 
                                    required>
                                <option value="">Select category...</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category', $customReport->category) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" 
                                  class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('description') border-red-500 @enderror" 
                                  rows="3" 
                                  placeholder="Describe what this report does...">{{ old('description', $customReport->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Data Source <span class="text-red-500">*</span>
                            </label>
                            <select name="data_source" 
                                    id="dataSourceSelect" 
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('data_source') border-red-500 @enderror" 
                                    required>
                                <option value="">Select data source...</option>
                                @foreach($dataSources as $key => $info)
                                    <option value="{{ $key }}" {{ old('data_source', $customReport->data_source) == $key ? 'selected' : '' }}>
                                        {{ $info['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('data_source')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Visibility <span class="text-red-500">*</span>
                            </label>
                            <select name="visibility" 
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('visibility') border-red-500 @enderror" 
                                    required>
                                <option value="private" {{ old('visibility', $customReport->visibility) == 'private' ? 'selected' : '' }}>Private</option>
                                <option value="shared" {{ old('visibility', $customReport->visibility) == 'shared' ? 'selected' : '' }}>Shared</option>
                                <option value="public" {{ old('visibility', $customReport->visibility) == 'public' ? 'selected' : '' }}>Public</option>
                            </select>
                            @error('visibility')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Export Format <span class="text-red-500">*</span>
                            </label>
                            <select name="export_format" 
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('export_format') border-red-500 @enderror" 
                                    required>
                                <option value="table" {{ old('export_format', $customReport->export_format) == 'table' ? 'selected' : '' }}>Table Only</option>
                                <option value="chart" {{ old('export_format', $customReport->export_format) == 'chart' ? 'selected' : '' }}>Chart Only</option>
                                <option value="both" {{ old('export_format', $customReport->export_format) == 'both' ? 'selected' : '' }}>Table + Chart</option>
                            </select>
                            @error('export_format')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Status Toggle -->
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="activeToggle" 
                                   class="sr-only peer" 
                                   {{ old('is_active', $customReport->is_active) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-900">Active Report</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Column Selection -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-6 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-columns text-xl"></i>
                        <h3 class="text-xl font-semibold">Column Selection</h3>
                    </div>
                </div>
                <div class="p-8">
                    <div id="columnSelection">
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                            <p class="text-lg">Loading columns for selected data source...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Configuration -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8">
                <div class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white p-6 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-filter text-xl"></i>
                            <h3 class="text-xl font-semibold">Filters Configuration</h3>
                        </div>
                        <button type="button" 
                                id="addFilterBtn"
                                class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Add Filter
                        </button>
                    </div>
                </div>
                <div class="p-8">
                    <div id="filtersContainer">
                        @if(!empty($customReport->filters))
                            @foreach($customReport->filters as $index => $filter)
                                <div class="filter-row bg-gray-50 border border-gray-200 rounded-xl p-6 mb-4" data-filter-id="{{ $index }}">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Column</label>
                                            <select name="filters[{{ $index }}][column]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                                <option value="">Select column...</option>
                                                <option value="{{ $filter['column'] }}" selected>{{ $filter['column'] }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Operator</label>
                                            <select name="filters[{{ $index }}][operator]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                                @foreach($operators as $key => $label)
                                                    <option value="{{ $key }}" {{ $filter['operator'] == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Value</label>
                                            <input type="text" 
                                                   name="filters[{{ $index }}][value]" 
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200" 
                                                   value="{{ $filter['value'] }}" 
                                                   placeholder="Enter value...">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                                            <button type="button" 
                                                    onclick="removeFilterRow({{ $index }})"
                                                    class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                No filters configured. Add filters to narrow down your data.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sorting Configuration -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8">
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 text-white p-6 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-sort text-xl"></i>
                            <h3 class="text-xl font-semibold">Sorting Configuration</h3>
                        </div>
                        <button type="button" 
                                id="addSortBtn"
                                class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Add Sort Field
                        </button>
                    </div>
                </div>
                <div class="p-8">
                    <div id="sortingContainer">
                        @if(!empty($customReport->sorting))
                            @foreach($customReport->sorting as $index => $sort)
                                <div class="sort-row bg-gray-50 border border-gray-200 rounded-xl p-6 mb-4" data-sort-id="{{ $index }}">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Column</label>
                                            <select name="sorting[{{ $index }}][column]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                                <option value="">Select column...</option>
                                                <option value="{{ $sort['column'] }}" selected>{{ $sort['column'] }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Direction</label>
                                            <select name="sorting[{{ $index }}][direction]" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                                <option value="asc" {{ $sort['direction'] == 'asc' ? 'selected' : '' }}>Ascending</option>
                                                <option value="desc" {{ $sort['direction'] == 'desc' ? 'selected' : '' }}>Descending</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                                            <button type="button" 
                                                    onclick="removeSortRow({{ $index }})"
                                                    class="w-full px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                No sorting configured. Add sort fields to control data order.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chart Configuration -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8" x-data="{ chartEnabled: {{ !empty($customReport->chart_config) ? 'true' : 'false' }} }">
                <div class="bg-gradient-to-r from-gray-700 to-gray-900 text-white p-6 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-chart-bar text-xl"></i>
                        <h3 class="text-xl font-semibold">Chart Configuration</h3>
                    </div>
                </div>
                <div class="p-8">
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   id="enableChart" 
                                   x-model="chartEnabled"
                                   class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                   {{ !empty($customReport->chart_config) ? 'checked' : '' }}>
                            <span class="ml-3 text-lg font-medium text-gray-900">Enable Chart Visualization</span>
                        </label>
                    </div>
                    
                    <div x-show="chartEnabled" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         class="bg-gray-50 border border-gray-200 rounded-2xl p-6"
                         style="{{ empty($customReport->chart_config) ? 'display: none;' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Chart Type</label>
                                <select name="chart_config[type]" 
                                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                    @foreach($chartTypes as $key => $label)
                                        <option value="{{ $key }}" 
                                            {{ ($customReport->chart_config['type'] ?? '') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">X-Axis Column</label>
                                <select name="chart_config[x_axis]" 
                                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200" 
                                        id="xAxisSelect">
                                    <option value="">Select column...</option>
                                    @if(!empty($customReport->chart_config['x_axis']))
                                        <option value="{{ $customReport->chart_config['x_axis'] }}" selected>
                                            {{ $customReport->chart_config['x_axis'] }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Y-Axis Column</label>
                                <select name="chart_config[y_axis]" 
                                        class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200" 
                                        id="yAxisSelect">
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

            <!-- Save Actions -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8">
                <div class="p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div class="flex-1">
                            <button type="button" 
                                    id="previewBtn"
                                    class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-700 text-white font-medium rounded-xl hover:from-cyan-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                                <i class="fas fa-eye mr-2"></i>Preview Changes
                            </button>
                            <p class="text-sm text-gray-600 mt-2">Preview the report with current settings</p>
                        </div>
                        <div>
                            <button type="submit" 
                                    id="saveBtn"
                                    class="px-8 py-3 bg-gradient-to-r from-green-600 to-emerald-700 text-white font-medium rounded-xl hover:from-green-700 hover:to-emerald-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                                <i class="fas fa-save mr-2"></i>Update Report
                            </button>
                        </div>
                    </div>
                    
                    <!-- Preview Container -->
                    <div id="previewContainer" class="hidden mt-8 p-6 bg-gray-50 rounded-2xl border border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Preview Results:</h4>
                        <div id="previewContent"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

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
            $('#columnSelection').html(`
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-table text-4xl mb-4"></i>
                    <p class="text-lg">Select a data source to see available columns.</p>
                </div>
            `);
            availableColumns = [];
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
    
    let html = `
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    `;
    
    availableColumns.forEach((column, index) => {
        const checked = selectedColumns.includes(column) ? 'checked' : '';
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
    
    $('#sortingContainer .bg-blue-50').remove();
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
        btn.prop('disabled', false).html('<i class="fas fa-eye mr-2"></i>Preview Changes');
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

function updateReport() {
    const btn = $('#saveBtn');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...');
    
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
        btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Update Report');
    });
}

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
@endpush
