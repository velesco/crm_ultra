@extends('layouts.app')

@section('title', 'Performance Monitoring')

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Actions -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line me-2 text-primary"></i>
                Performance Monitoring
            </h1>
            <p class="text-muted mb-0">Real-time system performance metrics and analytics</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="refreshMetrics()">
                    <i class="fas fa-sync-alt me-1"></i>
                    Refresh
                </button>
                <button type="button" class="btn btn-outline-success" onclick="exportMetrics()">
                    <i class="fas fa-download me-1"></i>
                    Export
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="cleanOldMetrics()">
                    <i class="fas fa-broom me-1"></i>
                    Clean Old
                </button>
            </div>
        </div>
    </div>

    <!-- Performance Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-3 p-3">
                                <i class="fas fa-server text-white fa-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold text-primary mb-1">System Health</div>
                            <div class="h4 mb-0" id="system-health-score">{{ $currentMetrics['cpu']['load_1min']['value'] ?? 'N/A' }}</div>
                            <small class="text-muted">Load Average</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-3 p-3">
                                <i class="fas fa-memory text-white fa-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold text-success mb-1">Memory Usage</div>
                            <div class="h4 mb-0" id="memory-usage">{{ $currentMetrics['memory']['usage_percentage']['value'] ?? 'N/A' }}%</div>
                            <small class="text-muted">of {{ $currentMetrics['memory']['current_usage']['metadata']['limit_mb'] ?? 'N/A' }} MB</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-3 p-3">
                                <i class="fas fa-hdd text-white fa-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold text-warning mb-1">Disk Usage</div>
                            <div class="h4 mb-0" id="disk-usage">{{ $currentMetrics['disk']['usage_percentage']['value'] ?? 'N/A' }}%</div>
                            <small class="text-muted">{{ $currentMetrics['disk']['free_space']['value'] ?? 'N/A' }} GB free</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-3 p-3">
                                <i class="fas fa-database text-white fa-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold text-info mb-1">DB Response</div>
                            <div class="h4 mb-0" id="db-response">{{ $currentMetrics['database']['query_response_time']['value'] ?? 'N/A' }}</div>
                            <small class="text-muted">milliseconds</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-area me-2 text-primary"></i>
                                Performance Trends
                            </h5>
                        </div>
                        <div class="col-auto">
                            <select class="form-select form-select-sm" id="chart-metric-type" onchange="updateChart()">
                                <option value="cpu">CPU Usage</option>
                                <option value="memory">Memory Usage</option>
                                <option value="disk">Disk Usage</option>
                                <option value="database">Database Performance</option>
                                <option value="cache">Cache Performance</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                        System Alerts
                    </h5>
                </div>
                <div class="card-body">
                    <div id="system-alerts">
                        @if($criticalMetrics->count() > 0)
                            @foreach($criticalMetrics->take(5) as $metric)
                                <div class="alert alert-danger alert-sm mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-times-circle me-2"></i>
                                        <div class="flex-grow-1">
                                            <strong>{{ ucfirst($metric->metric_type) }}</strong>
                                            <div class="small">{{ $metric->metric_name }}: {{ $metric->formatted_value }}</div>
                                        </div>
                                        <span class="badge bg-danger">Critical</span>
                                    </div>
                                </div>
                            @endforeach
                        @elseif($warningMetrics->count() > 0)
                            @foreach($warningMetrics->take(5) as $metric)
                                <div class="alert alert-warning alert-sm mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <div class="flex-grow-1">
                                            <strong>{{ ucfirst($metric->metric_type) }}</strong>
                                            <div class="small">{{ $metric->metric_name }}: {{ $metric->formatted_value }}</div>
                                        </div>
                                        <span class="badge bg-warning">Warning</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-success alert-sm mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <div class="flex-grow-1">
                                        <strong>All Systems Normal</strong>
                                        <div class="small">No critical alerts detected</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-table me-2 text-primary"></i>
                                Current System Metrics
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm" role="group">
                                @foreach($metricTypes as $type)
                                    <button type="button" class="btn btn-outline-primary metric-filter" data-type="{{ $type }}">
                                        {{ ucfirst($type) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="metrics-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Metric</th>
                                    <th>Value</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="metrics-tbody">
                                @foreach($currentMetrics as $category => $categoryMetrics)
                                    @foreach($categoryMetrics as $name => $data)
                                        <tr class="metric-row" data-category="{{ $category }}">
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($category) }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ ucfirst(str_replace('_', ' ', $name)) }}</strong>
                                                @if(isset($data['metadata']['description']))
                                                    <br><small class="text-muted">{{ $data['metadata']['description'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ $data['value'] }} {{ $data['unit'] }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $status = $data['status'] ?? 'normal';
                                                    $badgeClass = match($status) {
                                                        'critical' => 'bg-danger',
                                                        'warning' => 'bg-warning text-dark',
                                                        default => 'bg-success'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ now()->format('H:i:s') }}</small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="showMetricDetails('{{ $category }}', '{{ $name }}')">
                                                    <i class="fas fa-eye"></i>
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
    </div>
</div>

<!-- Metric Details Modal -->
<div class="modal fade" id="metricDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Metric Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="metric-details-content">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.alert-sm {
    padding: 0.5rem 0.75rem;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.metric-row {
    transition: background-color 0.2s ease;
}

.metric-row:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.metric-filter.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}
</style>
@endpush

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
    
    // Performance Chart (Queue & Database)
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pending Jobs',
                data: labels.map(label => historicalData[label].pending_jobs),
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Failed Jobs',
                data: labels.map(label => historicalData[label].failed_jobs),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Cache Hit Rate (%)',
                data: labels.map(label => historicalData[label].cache_hit_rate),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jobs Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Cache Hit Rate (%)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

function refreshMetrics() {
    const button = document.getElementById('refreshMetrics');
    const icon = button.querySelector('i');
    
    // Show loading state
    button.disabled = true;
    icon.classList.add('fa-spin');
    
    // Refresh page or make AJAX call
    fetch('{{ route("admin.performance.metrics") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update metrics on page
                updateMetricsDisplay(data.data);
                showToast('Metrics refreshed successfully', 'success');
            }
        })
        .catch(error => {
            console.error('Error refreshing metrics:', error);
            showToast('Failed to refresh metrics', 'error');
        })
        .finally(() => {
            button.disabled = false;
            icon.classList.remove('fa-spin');
        });
}

function updateMetricsDisplay(metrics) {
    // Update progress bars and values
    updateProgressBar('cpu_usage', metrics.system.cpu_usage);
    updateProgressBar('memory_usage', metrics.system.memory_usage);
    updateProgressBar('disk_usage', metrics.system.disk_usage);
    
    // Update other metric values
    updateMetricValue('database_connections', metrics.database.connection_count);
    updateMetricValue('avg_query_time', metrics.database.query_time_avg.toFixed(2) + 'ms');
    updateMetricValue('slow_queries', metrics.database.slow_queries);
    updateMetricValue('database_size', metrics.database.database_size.toFixed(1) + 'MB');
    
    updateMetricValue('cache_hit_rate', metrics.cache.hit_rate.toFixed(1) + '%');
    updateMetricValue('cache_memory', metrics.cache.memory_usage.toFixed(1) + 'MB');
    updateMetricValue('cache_keys', metrics.cache.keys_count.toLocaleString());
    updateMetricValue('cache_evictions', metrics.cache.evictions);
    
    updateMetricValue('pending_jobs', metrics.queue.pending_jobs);
    updateMetricValue('failed_jobs', metrics.queue.failed_jobs);
    updateMetricValue('processed_jobs', metrics.queue.processed_jobs);
    updateMetricValue('avg_processing_time', metrics.queue.avg_processing_time.toFixed(2) + 'ms');
}

function updateProgressBar(metric, value) {
    const progressBar = document.querySelector(`[data-metric="${metric}"] .progress-bar`);
    const valueSpan = document.querySelector(`[data-metric="${metric}"] .fw-bold`);
    
    if (progressBar) {
        progressBar.style.width = value + '%';
    }
    if (valueSpan) {
        valueSpan.textContent = value.toFixed(1) + '%';
    }
}

function updateMetricValue(selector, value) {
    const element = document.querySelector(`[data-metric="${selector}"]`);
    if (element) {
        element.textContent = value;
    }
}

function cleanupMetrics(days) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('cleanupModal'));
    const button = document.getElementById('confirmCleanup');
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Cleaning...';
    
    fetch('{{ route("admin.performance.cleanup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ days: parseInt(days) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`Cleaned up ${data.deleted_count} old metrics`, 'success');
            modal.hide();
        } else {
            showToast('Failed to clean up metrics', 'error');
        }
    })
    .catch(error => {
        console.error('Error cleaning metrics:', error);
        showToast('Failed to clean up metrics', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-trash-alt me-1"></i>Clean Up';
    });
}

function showToast(message, type = 'info') {
    // Simple toast implementation
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}
</script>
@endpush: true,
                    title: {
                        display: true,
                        text: 'Value'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Time'
                    }
                }
            }
        }
    });
    
    // Load initial data
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
    // Show loading state
    showNotification('Refreshing metrics...', 'info');
    
    // Get fresh system metrics
    $.get('{{ route("admin.performance.system-metrics") }}')
    .done(function(data) {
        // Update summary cards
        updateSummaryCards(data);
        
        // Update metrics table
        updateMetricsTable(data);
        
        // Update chart
        updateChart();
        
        showNotification('Metrics refreshed successfully', 'success');
    })
    .fail(function() {
        showNotification('Error refreshing metrics', 'error');
    });
}

function updateSummaryCards(metrics) {
    // Update system health
    if (metrics.cpu && metrics.cpu.load_1min) {
        $('#system-health-score').text(metrics.cpu.load_1min.value);
    }
    
    // Update memory usage
    if (metrics.memory && metrics.memory.usage_percentage) {
        $('#memory-usage').text(metrics.memory.usage_percentage.value + '%');
    }
    
    // Update disk usage
    if (metrics.disk && metrics.disk.usage_percentage) {
        $('#disk-usage').text(metrics.disk.usage_percentage.value + '%');
    }
    
    // Update database response
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
                <tr class="metric-row" data-category="${category}">
                    <td><span class="badge bg-secondary">${category.charAt(0).toUpperCase() + category.slice(1)}</span></td>
                    <td>
                        <strong>${name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</strong>
                        ${data.metadata && data.metadata.description ? `<br><small class="text-muted">${data.metadata.description}</small>` : ''}
                    </td>
                    <td><span class="fw-bold">${data.value} ${data.unit}</span></td>
                    <td>${statusBadge}</td>
                    <td><small class="text-muted">${new Date().toLocaleTimeString()}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="showMetricDetails('${category}', '${name}')">
                            <i class="fas fa-eye"></i>
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
        'critical': '<span class="badge bg-danger">Critical</span>',
        'warning': '<span class="badge bg-warning text-dark">Warning</span>',
        'normal': '<span class="badge bg-success">Normal</span>'
    };
    
    return badges[status] || badges['normal'];
}

function initializeMetricFilters() {
    $('.metric-filter').click(function() {
        const type = $(this).data('type');
        
        // Update active button
        $('.metric-filter').removeClass('active');
        $(this).addClass('active');
        
        // Filter table rows
        if (type === 'all') {
            $('.metric-row').show();
        } else {
            $('.metric-row').hide();
            $(`.metric-row[data-category="${type}"]`).show();
        }
    });
    
    // Set first button as active
    $('.metric-filter').first().addClass('active');
}

function startAutoRefresh() {
    autoRefreshInterval = setInterval(function() {
        refreshMetrics();
    }, 30000); // Refresh every 30 seconds
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
    const modal = new bootstrap.Modal(document.getElementById('metricDetailsModal'));
    
    // Load metric details
    $('#metric-details-content').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    // Show historical data for this metric
    $.get(`{{ route('admin.performance.show') }}`, {
        type: category,
        period: '24h'
    })
    .done(function(data) {
        $('#metric-details-content').html(`
            <h6>Historical Data for ${name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>${data.stats.average || 'N/A'}</h5>
                            <small class="text-muted">Average</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>${data.stats.min || 'N/A'}</h5>
                            <small class="text-muted">Minimum</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5>${data.stats.max || 'N/A'}</h5>
                            <small class="text-muted">Maximum</small>
                        </div>
                    </div>
                </div>
            </div>
        `);
    })
    .fail(function() {
        $('#metric-details-content').html('<div class="alert alert-danger">Error loading metric details</div>');
    });
    
    modal.show();
}

function showNotification(message, type) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'info': 'alert-info',
        'warning': 'alert-warning'
    }[type] || 'alert-info';
    
    const toast = `
        <div class="toast align-items-center border-0 ${alertClass}" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Add to toast container (create if needed)
    if (!$('#toast-container').length) {
        $('body').append('<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 11000;"></div>');
    }
    
    $('#toast-container').append(toast);
    $('.toast').last().toast('show');
}

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
@endpush
