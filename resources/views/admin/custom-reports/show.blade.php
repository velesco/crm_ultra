@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg">
                            <i class="fas fa-chart-bar text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $customReport->name }}</h1>
                            <p class="text-gray-600 mt-1">{{ $customReport->description ?? 'Custom report details and results' }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    @if($customReport->canUserAccess(Auth::id()) && Auth::user()->can('update', $customReport))
                        <a href="{{ route('admin.custom-reports.edit', $customReport) }}" 
                           class="inline-flex items-center px-6 py-3 bg-white border border-blue-300 text-blue-700 font-medium rounded-xl hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                            <i class="fas fa-edit mr-2"></i>Edit Report
                        </a>
                    @endif
                    <button type="button" 
                            id="executeBtn"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-700 text-white font-medium rounded-xl hover:from-cyan-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <i class="fas fa-play mr-2"></i>Execute Report
                    </button>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="inline-flex items-center px-4 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                            <i class="fas fa-cog"></i>
                            <i class="fas fa-chevron-down ml-2 text-xs" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                             style="display: none;">
                            <a href="{{ route('admin.custom-reports.index') }}" 
                               class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-arrow-left mr-3 text-gray-400"></i>Back to Reports
                            </a>
                            <hr class="my-2 border-gray-100">
                            @if($customReport->canUserAccess(Auth::id()))
                                <button type="button" 
                                        onclick="duplicateReport()"
                                        class="w-full flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 text-left">
                                    <i class="fas fa-copy mr-3 text-gray-400"></i>Duplicate Report
                                </button>
                                <a href="{{ route('admin.custom-reports.export', $customReport) }}?format=csv" 
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-download mr-3 text-gray-400"></i>Export as CSV
                                </a>
                            @endif
                            @if(Auth::user()->can('delete', $customReport))
                                <hr class="my-2 border-gray-100">
                                <button type="button" 
                                        onclick="deleteReport()"
                                        class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200 text-left">
                                    <i class="fas fa-trash mr-3"></i>Delete Report
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Information Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Category</p>
                            @php $categories = \App\Models\CustomReport::getCategories(); @endphp
                            <p class="text-xl font-bold text-gray-900 mt-2">{{ $categories[$customReport->category] ?? $customReport->category }}</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-tag text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Data Source</p>
                            @php $dataSources = $customReport->getAvailableDataSources(); @endphp
                            <p class="text-xl font-bold text-gray-900 mt-2">{{ $dataSources[$customReport->data_source]['label'] ?? $customReport->data_source }}</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-database text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Runs</p>
                            <p class="text-xl font-bold text-gray-900 mt-2">{{ number_format($customReport->run_count) }}</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-play text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-cyan-500 to-cyan-600"></div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Last Run</p>
                            <p class="text-xl font-bold text-gray-900 mt-2">{{ $customReport->last_run_at ? $customReport->last_run_at->diffForHumans() : 'Never' }}</p>
                        </div>
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-amber-500 to-amber-600"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Report Configuration Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Report Configuration -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-t-2xl">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cog text-lg"></i>
                            <h3 class="text-lg font-semibold">Report Configuration</h3>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Visibility:</span>
                            @php
                                $visibilityClass = match($customReport->visibility) {
                                    'public' => 'bg-green-100 text-green-800',
                                    'shared' => 'bg-yellow-100 text-yellow-800',
                                    'private' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                $visibilityIcon = match($customReport->visibility) {
                                    'public' => 'fas fa-globe',
                                    'shared' => 'fas fa-users', 
                                    'private' => 'fas fa-lock',
                                    default => 'fas fa-question'
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $visibilityClass }}">
                                <i class="{{ $visibilityIcon }} mr-1"></i>{{ ucfirst($customReport->visibility) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Status:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $customReport->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $customReport->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Columns:</span>
                            <span class="text-sm text-gray-900">{{ count($customReport->columns) }} selected</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Filters:</span>
                            <span class="text-sm text-gray-900">{{ count($customReport->filters ?? []) }} applied</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Created:</span>
                            <span class="text-sm text-gray-900">{{ $customReport->created_at->format('M d, Y H:i') }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Creator:</span>
                            <span class="text-sm text-gray-900">{{ $customReport->creator->name }}</span>
                        </div>

                        @if($customReport->is_scheduled)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Scheduled:</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-clock mr-1"></i>Yes
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Selected Columns -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-6 rounded-t-2xl">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-columns text-lg"></i>
                            <h3 class="text-lg font-semibold">Selected Columns</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-2">
                            @foreach($customReport->columns as $column)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border">
                                    {{ str_replace('_', ' ', ucwords($column, '_')) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Applied Filters -->
                @if(!empty($customReport->filters))
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                        <div class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white p-6 rounded-t-2xl">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-filter text-lg"></i>
                                <h3 class="text-lg font-semibold">Applied Filters</h3>
                            </div>
                        </div>
                        <div class="p-6 space-y-3">
                            @foreach($customReport->filters as $filter)
                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-900">{{ str_replace('_', ' ', ucwords($filter['column'], '_')) }}</span> 
                                        <span class="text-gray-600 mx-2">{{ $filter['operator'] }}</span> 
                                        <span class="text-blue-600 font-medium">{{ $filter['value'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Report Results -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-center justify-between p-6 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg">
                                <i class="fas fa-table text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Report Results</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <button id="refreshResults"
                                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            @if($customReport->export_format === 'both' || $customReport->export_format === 'chart')
                                <button id="toggleChart"
                                        class="px-4 py-2 bg-white border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                                    <i class="fas fa-chart-bar mr-2"></i>Toggle Chart
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="p-6">
                        <div id="reportResults">
                            @if(isset($reportData))
                                @include('admin.custom-reports.results', ['data' => $reportData, 'report' => $customReport])
                            @else
                                <div class="text-center py-16">
                                    <div class="flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mx-auto mb-6">
                                        <i class="fas fa-info-circle text-blue-500 text-3xl"></i>
                                    </div>
                                    <h5 class="text-xl font-semibold text-gray-900 mb-2">Report not executed yet</h5>
                                    <p class="text-gray-600 mb-6">Click "Execute Report" to run this report and view results.</p>
                                    <button type="button" 
                                            id="initialExecute"
                                            class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                                        <i class="fas fa-play mr-2"></i>Execute Report
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                @if(($customReport->export_format === 'both' || $customReport->export_format === 'chart') && !empty($customReport->chart_config))
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 hidden" id="chartSection">
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white p-6 rounded-t-2xl">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-chart-bar text-lg"></i>
                                <h3 class="text-lg font-semibold">Chart Visualization</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="relative" style="height: 400px;">
                                <canvas id="reportChart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let reportChart = null;

$(document).ready(function() {
    // Execute report buttons
    $('#executeBtn, #initialExecute').click(function() {
        executeReport();
    });

    // Refresh results
    $('#refreshResults').click(function() {
        executeReport();
    });

    // Toggle chart
    $('#toggleChart').click(function() {
        $('#chartSection').toggleClass('hidden');
        if (!$('#chartSection').hasClass('hidden') && !reportChart) {
            loadChart();
        }
    });
});

function executeReport() {
    const btn = $('#executeBtn, #initialExecute');
    btn.prop('disabled', true);
    btn.find('i').addClass('animate-spin');

    $.post('{{ route("admin.custom-reports.execute", $customReport) }}', {
        _token: '{{ csrf_token() }}',
        limit: 100
    })
    .done(function(response) {
        if (response.success) {
            renderResults(response.data, response.metadata);
            showNotification('success', 'Report executed successfully!');
            
            // Update run count display
            updateRunCount();
            
            // Load chart if available
            if ($('#chartSection').length && !$('#chartSection').hasClass('hidden')) {
                loadChart();
            }
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        showNotification('error', response.error || 'Report execution failed');
        $('#reportResults').html(`
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                ${response.error || 'Failed to execute report'}
            </div>
        `);
    })
    .always(function() {
        btn.prop('disabled', false);
        btn.find('i').removeClass('animate-spin');
    });
}

function renderResults(data, metadata) {
    if (!data || data.length === 0) {
        $('#reportResults').html(`
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4">
                <i class="fas fa-info-circle mr-2"></i>
                No data found matching the report criteria.
            </div>
        `);
        return;
    }

    const columns = Object.keys(data[0]);
    let html = `
        <div class="mb-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-info-circle mr-2"></i>
                Found <span class="font-semibold text-gray-900">${metadata.total_rows}</span> rows with <span class="font-semibold text-gray-900">${metadata.filters_applied}</span> filters applied
                ${metadata.last_run ? ` • Last run: ${new Date(metadata.last_run).toLocaleString()}` : ''}
                ${metadata.run_count ? ` • Total runs: ${metadata.run_count}` : ''}
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 rounded-xl overflow-hidden shadow">
                <thead class="bg-gradient-to-r from-blue-500 to-purple-600">
                    <tr>
                        ${columns.map(col => `<th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">${col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</th>`).join('')}
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    ${data.slice(0, 50).map((row, index) => `
                        <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors duration-200">
                            ${columns.map(col => `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${row[col] !== null ? row[col] : '<span class="text-gray-400">-</span>'}</td>`).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    if (data.length > 50) {
        html += `
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4 mt-4">
                <i class="fas fa-info-circle mr-2"></i>
                Showing first 50 rows of ${data.length} total results. 
                <a href="{{ route('admin.custom-reports.export', $customReport) }}?format=csv" class="text-blue-700 hover:text-blue-800 underline font-medium">
                    Export all data as CSV
                </a> to see complete results.
            </div>
        `;
    }
    
    $('#reportResults').html(html);
}

function loadChart() {
    $.get('{{ route("admin.custom-reports.chart-data", $customReport) }}')
    .done(function(response) {
        if (response.success && response.data && response.data.length > 0) {
            renderChart(response.data, response.config);
        } else {
            $('#chartSection .p-6').html(`
                <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    No chart data available for this report.
                </div>
            `);
        }
    })
    .fail(function() {
        $('#chartSection .p-6').html(`
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Failed to load chart data.
            </div>
        `);
    });
}

function renderChart(data, config) {
    const ctx = document.getElementById('reportChart').getContext('2d');
    
    if (reportChart) {
        reportChart.destroy();
    }
    
    const chartConfig = {
        type: config.type || 'bar',
        data: {
            labels: data.map(item => item.x || item.label),
            datasets: [{
                label: 'Data',
                data: data.map(item => item.y || item.value),
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(133, 135, 150, 0.8)'
                ],
                borderColor: [
                    'rgba(78, 115, 223, 1)',
                    'rgba(28, 200, 138, 1)',
                    'rgba(54, 185, 204, 1)',
                    'rgba(246, 194, 62, 1)',
                    'rgba(231, 74, 59, 1)',
                    'rgba(133, 135, 150, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: '{{ $customReport->name }}'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };
    
    reportChart = new Chart(ctx, chartConfig);
}

function updateRunCount() {
    // Update the run count display in the stats card
    location.reload(); // Simple refresh - in production you might want to update via AJAX
}

function duplicateReport() {
    if (!confirm('Are you sure you want to duplicate this report?')) {
        return;
    }
    
    $.post('{{ route("admin.custom-reports.duplicate", $customReport) }}', {
        _token: '{{ csrf_token() }}'
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
        showNotification('error', response.error || 'Duplication failed');
    });
}

function deleteReport() {
    if (!confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: '{{ route("admin.custom-reports.destroy", $customReport) }}',
        method: 'DELETE',
        data: { _token: '{{ csrf_token() }}' }
    })
    .done(function(response) {
        if (response.success) {
            showNotification('success', response.message);
            setTimeout(() => {
                window.location.href = '{{ route("admin.custom-reports.index") }}';
            }, 1000);
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        showNotification('error', response.error || 'Delete failed');
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
