@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gradient fw-bold">System Logs</h1>
            <p class="text-muted mb-0">Monitor system activity, errors, and performance metrics</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                <i class="fas fa-trash"></i> Clear Old Logs
            </button>
            <button type="button" class="btn btn-outline-success" onclick="exportLogs()">
                <i class="fas fa-download"></i> Export
            </button>
            <button type="button" class="btn btn-primary" onclick="refreshLogs()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4" id="stats-cards">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-primary mb-0">{{ number_format($statistics['total_logs']) }}</h2>
                            <p class="text-muted mb-0">Total Logs</p>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-list-alt text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-info mb-0">{{ number_format($statistics['today_logs']) }}</h2>
                            <p class="text-muted mb-0">Today's Logs</p>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-calendar-day text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-danger mb-0">{{ number_format($statistics['error_logs']) }}</h2>
                            <p class="text-muted mb-0">Errors</p>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-success mb-0">{{ $statistics['success_rate'] }}%</h2>
                            <p class="text-muted mb-0">Success Rate</p>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Activity Trends</h5>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="chartPeriod" id="period24h" autocomplete="off" checked>
                            <label class="btn btn-outline-primary btn-sm" for="period24h">24h</label>
                            <input type="radio" class="btn-check" name="chartPeriod" id="period7d" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="period7d">7d</label>
                            <input type="radio" class="btn-check" name="chartPeriod" id="period30d" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="period30d">30d</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Category Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="filter-form" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search logs...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Level</label>
                    <select class="form-select" name="level">
                        <option value="">All Levels</option>
                        @foreach($levels as $value => $label)
                            <option value="{{ $value }}" {{ request('level') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ request('category') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">User</label>
                    <select class="form-select" name="user_id">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body" id="logs-container">
            @include('admin.system-logs.table')
        </div>
    </div>
</div>

{{-- Clear Logs Modal --}}
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear Old Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="clear-logs-form">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This action will permanently delete logs older than the specified number of days.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delete logs older than (days)</label>
                        <input type="number" class="form-control" name="days" value="90" min="1" max="365" required>
                        <div class="form-text">Recommended: 90 days for normal operation, 30 days for high-volume systems</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Log Levels to Delete (optional)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="levels[]" value="debug" id="level-debug">
                                    <label class="form-check-label" for="level-debug">Debug</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="levels[]" value="info" id="level-info">
                                    <label class="form-check-label" for="level-info">Info</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="levels[]" value="warning" id="level-warning">
                                    <label class="form-check-label" for="level-warning">Warning</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="levels[]" value="error" id="level-error">
                                    <label class="form-check-label" for="level-error">Error</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">Leave unchecked to delete all log levels</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Clear Logs</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.text-gradient {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.activity-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.log-row {
    transition: all 0.2s ease;
}

.log-row:hover {
    background-color: var(--bs-light);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75rem;
}

.table th {
    background: linear-gradient(45deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    color: #495057;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let activityChart, categoryChart;
let refreshInterval;

$(document).ready(function() {
    initCharts();
    bindEvents();
    startAutoRefresh();
});

function bindEvents() {
    // Filter form submission
    $('#filter-form input, #filter-form select').on('change', function() {
        loadLogs();
    });

    // Chart period change
    $('input[name="chartPeriod"]').on('change', function() {
        updateCharts($(this).attr('id').replace('period', ''));
    });

    // Clear logs form
    $('#clear-logs-form').on('submit', function(e) {
        e.preventDefault();
        clearOldLogs();
    });

    // Auto-refresh toggle
    $(document).on('keypress', function(e) {
        if (e.which == 32) { // Spacebar
            toggleAutoRefresh();
        }
    });
}

function loadLogs() {
    const formData = $('#filter-form').serialize();
    
    $.ajax({
        url: '{{ route("admin.system-logs.index") }}',
        method: 'GET',
        data: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            $('#logs-container').html(response.html);
            updateStatistics(response.statistics);
        },
        error: function() {
            showAlert('Error loading logs', 'danger');
        }
    });
}

function updateStatistics(stats) {
    $('#stats-cards').find('h2').each(function(index) {
        const values = [
            stats.total_logs,
            stats.today_logs,
            stats.error_logs,
            stats.success_rate + '%'
        ];
        $(this).text(values[index]);
    });
}

function initCharts() {
    // Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total Activity',
                data: [],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Errors',
                data: [],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c',
                    '#4facfe',
                    '#00f2fe',
                    '#43e97b',
                    '#38f9d7'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    updateCharts('24h');
}

function updateCharts(period) {
    $.ajax({
        url: '{{ route("admin.system-logs.chart-data") }}',
        method: 'GET',
        data: { period: period },
        success: function(data) {
            // Update activity chart
            activityChart.data.labels = data.activity.map(item => item.period);
            activityChart.data.datasets[0].data = data.activity.map(item => item.total);
            activityChart.data.datasets[1].data = data.activity.map(item => item.errors);
            activityChart.update();

            // Update category chart
            categoryChart.data.labels = data.categories.map(item => item.category);
            categoryChart.data.datasets[0].data = data.categories.map(item => item.count);
            categoryChart.update();
        },
        error: function() {
            showAlert('Error updating charts', 'danger');
        }
    });
}

function refreshLogs() {
    loadLogs();
    updateCharts($('input[name="chartPeriod"]:checked').attr('id').replace('period', ''));
    showAlert('Logs refreshed successfully', 'success');
}

function exportLogs() {
    const formData = $('#filter-form').serialize();
    window.location.href = '{{ route("admin.system-logs.export") }}?' + formData;
}

function clearOldLogs() {
    const formData = new FormData($('#clear-logs-form')[0]);
    
    $.ajax({
        url: '{{ route("admin.system-logs.clear-old") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                $('#clearLogsModal').modal('hide');
                refreshLogs();
            } else {
                showAlert('Error clearing logs', 'danger');
            }
        },
        error: function() {
            showAlert('Error clearing logs', 'danger');
        }
    });
}

function startAutoRefresh() {
    refreshInterval = setInterval(function() {
        loadLogs();
        updateCharts($('input[name="chartPeriod"]:checked').attr('id').replace('period', ''));
    }, 30000); // Refresh every 30 seconds
}

function toggleAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
        showAlert('Auto-refresh disabled', 'info');
    } else {
        startAutoRefresh();
        showAlert('Auto-refresh enabled', 'success');
    }
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>
@endpush
