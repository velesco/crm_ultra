@extends('layouts.app')

@section('title', 'Performance Monitoring')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header with Actions -->
        <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-blue-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">Performance Monitoring</h1>
                        <p class="text-gray-600">Real-time system performance metrics and analytics</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" onclick="refreshMetrics()" 
                        class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh
                </button>
                <button type="button" onclick="exportMetrics()" 
                        class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
                <button type="button" onclick="cleanOldMetrics()" 
                        class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors">
                    <i class="fas fa-broom mr-2"></i>
                    Clean Old
                </button>
            </div>
        </div>

        <!-- Performance Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <!-- System Health Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-server text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-sm font-medium text-blue-600 mb-1">System Health</div>
                            <div class="text-2xl font-bold text-gray-900" id="system-health-score">{{ $currentMetrics['cpu']['load_1min']['value'] ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">Load Average</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Memory Usage Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-memory text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-sm font-medium text-green-600 mb-1">Memory Usage</div>
                            <div class="text-2xl font-bold text-gray-900" id="memory-usage">{{ $currentMetrics['memory']['usage_percentage']['value'] ?? 'N/A' }}%</div>
                            <div class="text-sm text-gray-500">of {{ $currentMetrics['memory']['current_usage']['metadata']['limit_mb'] ?? 'N/A' }} MB</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disk Usage Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-hdd text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-sm font-medium text-yellow-600 mb-1">Disk Usage</div>
                            <div class="text-2xl font-bold text-gray-900" id="disk-usage">{{ $currentMetrics['disk']['usage_percentage']['value'] ?? 'N/A' }}%</div>
                            <div class="text-sm text-gray-500">{{ $currentMetrics['disk']['free_space']['value'] ?? 'N/A' }} GB free</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Response Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-database text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="text-sm font-medium text-purple-600 mb-1">DB Response</div>
                            <div class="text-2xl font-bold text-gray-900" id="db-response">{{ $currentMetrics['database']['query_response_time']['value'] ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">milliseconds</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Charts Row -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
            <!-- Performance Trends Chart -->
            <div class="xl:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                            <div class="mb-3 sm:mb-0">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <i class="fas fa-chart-area text-blue-500 mr-2"></i>
                                    Performance Trends
                                </h3>
                            </div>
                            <div>
                                <select id="chart-metric-type" onchange="updateChart()" 
                                        class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="cpu">CPU Usage</option>
                                    <option value="memory">Memory Usage</option>
                                    <option value="disk">Disk Usage</option>
                                    <option value="database">Database Performance</option>
                                    <option value="cache">Cache Performance</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div style="height: 300px;">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Alerts -->
            <div class="xl:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                            System Alerts
                        </h3>
                    </div>
                    <div class="p-6" id="system-alerts">
                        @if($criticalMetrics->count() > 0)
                            <div class="space-y-3">
                                @foreach($criticalMetrics->take(5) as $metric)
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-times-circle text-red-500"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <div class="text-sm font-medium text-red-800">{{ ucfirst($metric->metric_type) }}</div>
                                                <div class="text-sm text-red-700">{{ $metric->metric_name }}: {{ $metric->formatted_value }}</div>
                                            </div>
                                            <div class="ml-3 flex-shrink-0">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Critical
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($warningMetrics->count() > 0)
                            <div class="space-y-3">
                                @foreach($warningMetrics->take(5) as $metric)
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <div class="text-sm font-medium text-yellow-800">{{ ucfirst($metric->metric_type) }}</div>
                                                <div class="text-sm text-yellow-700">{{ $metric->metric_name }}: {{ $metric->formatted_value }}</div>
                                            </div>
                                            <div class="ml-3 flex-shrink-0">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Warning
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-green-800">All Systems Normal</div>
                                        <div class="text-sm text-green-700">No critical alerts detected</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Metrics Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="mb-3 sm:mb-0">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-table text-blue-500 mr-2"></i>
                            Current System Metrics
                        </h3>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($metricTypes as $type)
                            <button type="button" 
                                    class="metric-filter px-3 py-1.5 text-sm font-medium rounded-lg border transition-colors
                                           border-gray-300 text-gray-700 bg-white hover:bg-gray-50 
                                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    data-type="{{ $type }}">
                                {{ ucfirst($type) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="metrics-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metric</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="metrics-tbody" class="bg-white divide-y divide-gray-200">
                        @foreach($currentMetrics as $category => $categoryMetrics)
                            @foreach($categoryMetrics as $name => $data)
                                <tr class="metric-row hover:bg-gray-50 transition-colors duration-150" data-category="{{ $category }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $name)) }}</div>
                                        @if(isset($data['metadata']['description']))
                                            <div class="text-sm text-gray-500">{{ $data['metadata']['description'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900">{{ $data['value'] }} {{ $data['unit'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $status = $data['status'] ?? 'normal';
                                            $badgeClasses = match($status) {
                                                'critical' => 'bg-red-100 text-red-800',
                                                'warning' => 'bg-yellow-100 text-yellow-800',
                                                default => 'bg-green-100 text-green-800'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClasses }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ now()->format('H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button onclick="showMetricDetails('{{ $category }}', '{{ $name }}')" 
                                                class="inline-flex items-center p-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Metric Details Modal -->
<div id="metricDetailsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">Metric Details</h3>
                    <button type="button" onclick="closeMetricModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="metric-details-content" class="mt-4">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let performanceChart;
let autoRefreshInterval;

$(document).ready(function() {
    // Initialize chart
    initializeChart();
    
    // Start auto-refresh
    startAutoRefresh();
    
    // Initialize metric filters
    initializeMetricFilters();
});

function initializeChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: []
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Performance Metrics Over Time'
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    
    updateChart();
}

function updateChart() {
    const metricType = $('#chart-metric-type').val();
    
    $.get(`{{ route('admin.performance.chart-data') }}`, {
        type: metricType,
        period: '24h'
    })
    .done(function(data) {
        performanceChart.data.labels = data.labels;
        performanceChart.data.datasets = data.datasets;
        performanceChart.update();
    })
    .fail(function() {
        showNotification('Error loading chart data', 'error');
    });
}

function refreshMetrics() {
    showNotification('Refreshing metrics...', 'info');
    
    $.get('{{ route("admin.performance.system-metrics") }}')
    .done(function(data) {
        updateSummaryCards(data);
        updateMetricsTable(data);
        updateChart();
        showNotification('Metrics refreshed successfully', 'success');
    })
    .fail(function() {
        showNotification('Error refreshing metrics', 'error');
    });
}

function updateSummaryCards(metrics) {
    if (metrics.cpu && metrics.cpu.load_1min) {
        $('#system-health-score').text(metrics.cpu.load_1min.value);
    }
    
    if (metrics.memory && metrics.memory.usage_percentage) {
        $('#memory-usage').text(metrics.memory.usage_percentage.value + '%');
    }
    
    if (metrics.disk && metrics.disk.usage_percentage) {
        $('#disk-usage').text(metrics.disk.usage_percentage.value + '%');
    }
    
    if (metrics.database && metrics.database.query_response_time) {
        $('#db-response').text(metrics.database.query_response_time.value);
    }
}

function updateMetricsTable(metrics) {
    let tbody = $('#metrics-tbody');
    tbody.empty();
    
    $.each(metrics, function(category, categoryMetrics) {
        $.each(categoryMetrics, function(name, data) {
            let statusBadge = getStatusBadge(data.status || 'normal');
            
            let row = `
                <tr class="metric-row hover:bg-gray-50 transition-colors duration-150" data-category="${category}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ${category.charAt(0).toUpperCase() + category.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</div>
                        ${data.metadata && data.metadata.description ? `<div class="text-sm text-gray-500">${data.metadata.description}</div>` : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-semibold text-gray-900">${data.value} ${data.unit}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date().toLocaleTimeString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button onclick="showMetricDetails('${category}', '${name}')" 
                                class="inline-flex items-center p-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            tbody.append(row);
        });
    });
}

function getStatusBadge(status) {
    const badges = {
        'critical': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Critical</span>',
        'warning': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Warning</span>',
        'normal': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Normal</span>'
    };
    
    return badges[status] || badges['normal'];
}

function initializeMetricFilters() {
    $('.metric-filter').click(function() {
        const type = $(this).data('type');
        
        $('.metric-filter').removeClass('bg-blue-600 text-white border-blue-600').addClass('border-gray-300 text-gray-700 bg-white');
        $(this).removeClass('border-gray-300 text-gray-700 bg-white').addClass('bg-blue-600 text-white border-blue-600');
        
        if (type === 'all') {
            $('.metric-row').show();
        } else {
            $('.metric-row').hide();
            $(`.metric-row[data-category="${type}"]`).show();
        }
    });
    
    $('.metric-filter').first().removeClass('border-gray-300 text-gray-700 bg-white').addClass('bg-blue-600 text-white border-blue-600');
}

function startAutoRefresh() {
    autoRefreshInterval = setInterval(function() {
        refreshMetrics();
    }, 30000);
}

function exportMetrics() {
    const url = '{{ route("admin.performance.export") }}' + '?period=24h';
    window.open(url, '_blank');
}

function cleanOldMetrics() {
    if (confirm('Are you sure you want to clean old performance metrics? This action cannot be undone.')) {
        $.ajax({
            url: '{{ route("admin.performance.clean") }}',
            method: 'DELETE',
            data: {
                days: 30,
                _token: '{{ csrf_token() }}'
            }
        })
        .done(function(response) {
            showNotification(response.message, 'success');
        })
        .fail(function() {
            showNotification('Error cleaning old metrics', 'error');
        });
    }
}

function showMetricDetails(category, name) {
    document.getElementById('metricDetailsModal').classList.remove('hidden');
    
    $('#metric-details-content').html('<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><div class="mt-2 text-gray-500">Loading...</div></div>');
    
    $.get(`{{ route('admin.performance.show') }}`, {
        type: category,
        period: '24h'
    })
    .done(function(data) {
        $('#metric-details-content').html(`
            <div class="mb-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Historical Data for ${name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900">${data.stats.average || 'N/A'}</div>
                        <div class="text-sm text-gray-500">Average</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900">${data.stats.min || 'N/A'}</div>
                        <div class="text-sm text-gray-500">Minimum</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900">${data.stats.max || 'N/A'}</div>
                        <div class="text-sm text-gray-500">Maximum</div>
                    </div>
                </div>
            </div>
        `);
    })
    .fail(function() {
        $('#metric-details-content').html('<div class="bg-red-50 border border-red-200 rounded-lg p-4"><div class="text-red-800">Error loading metric details</div></div>');
    });
}

function closeMetricModal() {
    document.getElementById('metricDetailsModal').classList.add('hidden');
}

function showNotification(message, type) {
    const alertClasses = {
        'success': 'bg-green-100 border-green-500 text-green-900',
        'error': 'bg-red-100 border-red-500 text-red-900',
        'info': 'bg-blue-100 border-blue-500 text-blue-900',
        'warning': 'bg-yellow-100 border-yellow-500 text-yellow-900'
    }[type] || 'bg-blue-100 border-blue-500 text-blue-900';
    
    const notification = `
        <div class="fixed top-4 right-4 max-w-sm w-full ${alertClasses} border-l-4 p-4 shadow-lg rounded-lg z-50 notification">
            <div class="flex">
                <div class="flex-1">
                    ${message}
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-current hover:text-opacity-75">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', notification);
    
    setTimeout(() => {
        const notifications = document.querySelectorAll('.notification');
        if (notifications.length > 0) {
            notifications[0].remove();
        }
    }, 5000);
}

$(window).on('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
@endpush
