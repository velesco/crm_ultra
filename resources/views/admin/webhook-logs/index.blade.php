@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gradient fw-bold">Webhook Logs</h1>
            <p class="text-muted mb-0">Monitor webhook activity, processing status, and debugging tools</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-warning" onclick="retryFailedWebhooks()">
                <i class="fas fa-redo"></i> Retry Failed
            </button>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearWebhooksModal">
                <i class="fas fa-trash"></i> Clear Old
            </button>
            <button type="button" class="btn btn-outline-success" onclick="exportWebhooks()">
                <i class="fas fa-download"></i> Export
            </button>
            <button type="button" class="btn btn-primary" onclick="refreshWebhooks()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4" id="stats-cards">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-primary mb-0">{{ number_format($statistics['total_webhooks']) }}</h2>
                            <p class="text-muted mb-0">Total Webhooks</p>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-webhook text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-info mb-0">{{ number_format($statistics['today_webhooks']) }}</h2>
                            <p class="text-muted mb-0">Today</p>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-calendar-day text-info fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-success mb-0">{{ number_format($statistics['completed_webhooks']) }}</h2>
                            <p class="text-muted mb-0">Completed</p>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-danger mb-0">{{ number_format($statistics['failed_webhooks']) }}</h2>
                            <p class="text-muted mb-0">Failed</p>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-times-circle text-danger fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-warning mb-0">{{ number_format($statistics['pending_webhooks']) }}</h2>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-success mb-0">{{ $statistics['success_rate'] }}%</h2>
                            <p class="text-muted mb-0">Success Rate</p>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-percentage text-success fa-lg"></i>
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
                        <h5 class="card-title mb-0">Webhook Activity Trends</h5>
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
                    <canvas id="webhookActivityChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Provider Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="providerDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Health Metrics --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Health Metrics</h5>
                </div>
                <div class="card-body" id="health-metrics">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 mb-1" id="avg-processing-time">{{ number_format($statistics['avg_processing_time'] ?? 0, 2) }}ms</div>
                                <small class="text-muted">Avg Processing Time</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 mb-1" id="recent-failures">-</div>
                                <small class="text-muted">Recent Failures (1h)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 mb-1" id="ready-for-retry">-</div>
                                <small class="text-muted">Ready for Retry</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 mb-1">
                                    <span class="badge badge-success" id="health-status">Good</span>
                                </div>
                                <small class="text-muted">System Health</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Webhook Type</label>
                        <select name="webhook_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            @foreach($webhookTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('webhook_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Provider</label>
                        <select name="provider" class="form-select form-select-sm">
                            <option value="">All Providers</option>
                            @foreach($providers as $key => $label)
                                <option value="{{ $key }}" {{ request('provider') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select form-select-sm">
                            <option value="">All Events</option>
                            @foreach($eventTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('event_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="Search webhooks..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.webhook-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i> Clear
                            </a>
                            <div class="btn-group" role="group">
                                <input type="checkbox" class="btn-check" id="autoRefresh" autocomplete="off">
                                <label class="btn btn-outline-info btn-sm" for="autoRefresh">
                                    <i class="fas fa-sync-alt"></i> Auto Refresh
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Webhook Logs Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Webhook Logs</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="bulkRetrySelected()" disabled id="bulkRetryBtn">
                        <i class="fas fa-redo"></i> Retry Selected
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="webhookLogsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th>Type</th>
                            <th>Provider</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Processing Time</th>
                            <th>Received At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="webhookLogsBody">
                        @include('admin.webhook-logs.table')
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0">
            {{ $logs->links() }}
        </div>
    </div>
</div>

{{-- Clear Webhooks Modal --}}
<div class="modal fade" id="clearWebhooksModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear Old Webhooks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="clearWebhooksForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Delete webhooks older than</label>
                        <select name="days" class="form-select" required>
                            <option value="7">7 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="90">90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status filter</label>
                        <select name="status" class="form-select">
                            <option value="all">All statuses</option>
                            <option value="completed">Completed only</option>
                            <option value="failed">Failed only</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This action cannot be undone. Please confirm before proceeding.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Clear Webhooks</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let activityChart = null;
let distributionChart = null;
let autoRefreshInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadHealthMetrics();
    setupAutoRefresh();
    setupBulkActions();
});

function initializeCharts() {
    // Activity Chart
    const activityCtx = document.getElementById('webhookActivityChart').getContext('2d');
    activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Completed',
                data: [],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }, {
                label: 'Failed',
                data: [],
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });

    // Provider Distribution Chart
    const distributionCtx = document.getElementById('providerDistributionChart').getContext('2d');
    distributionChart = new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#FF6384'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Load initial chart data
    loadChartData('7');
    
    // Setup period change listeners
    document.querySelectorAll('input[name="chartPeriod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                loadChartData(this.id.replace('period', '').replace('h', '').replace('d', ''));
            }
        });
    });
}

function loadChartData(period) {
    fetch(`{{ route('admin.webhook-logs.chart-data') }}?period=${period}`)
        .then(response => response.json())
        .then(data => {
            // Update activity chart
            if (data.daily_counts) {
                const labels = data.daily_counts.map(item => item.date);
                const totalData = data.daily_counts.map(item => item.total);
                const completedData = data.daily_counts.map(item => item.completed);
                const failedData = data.daily_counts.map(item => item.failed);

                activityChart.data.labels = labels;
                activityChart.data.datasets[0].data = totalData;
                activityChart.data.datasets[1].data = completedData;
                activityChart.data.datasets[2].data = failedData;
                activityChart.update();
            }

            // Update provider distribution chart
            if (data.provider_stats) {
                const labels = data.provider_stats.map(item => item.provider);
                const counts = data.provider_stats.map(item => item.total);

                distributionChart.data.labels = labels;
                distributionChart.data.datasets[0].data = counts;
                distributionChart.update();
            }
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
}

function loadHealthMetrics() {
    fetch('{{ route('admin.webhook-logs.health-metrics') }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('recent-failures').textContent = data.recent_failures || 0;
            document.getElementById('ready-for-retry').textContent = data.ready_for_retry || 0;
            
            const healthStatus = document.getElementById('health-status');
            healthStatus.textContent = data.health_status || 'Unknown';
            healthStatus.className = `badge badge-${data.health_status === 'good' ? 'success' : 
                                                   data.health_status === 'warning' ? 'warning' : 'danger'}`;
        })
        .catch(error => {
            console.error('Error loading health metrics:', error);
        });
}

function setupAutoRefresh() {
    const autoRefreshToggle = document.getElementById('autoRefresh');
    autoRefreshToggle.addEventListener('change', function() {
        if (this.checked) {
            autoRefreshInterval = setInterval(() => {
                refreshWebhooks();
                loadHealthMetrics();
            }, 30000); // Refresh every 30 seconds
        } else {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }
    });
}

function setupBulkActions() {
    const selectAll = document.getElementById('selectAll');
    const bulkRetryBtn = document.getElementById('bulkRetryBtn');
    
    selectAll.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.webhook-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActions();
    });
    
    // Update bulk actions when individual checkboxes change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('webhook-checkbox')) {
            updateBulkActions();
        }
    });
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.webhook-checkbox:checked');
    const bulkRetryBtn = document.getElementById('bulkRetryBtn');
    
    if (checkedBoxes.length > 0) {
        bulkRetryBtn.disabled = false;
    } else {
        bulkRetryBtn.disabled = true;
    }
}

function refreshWebhooks() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    fetch(`{{ route('admin.webhook-logs.index') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('webhookLogsBody').innerHTML = data.html;
        
        // Update statistics
        if (data.statistics) {
            updateStatisticsCards(data.statistics);
        }
        
        setupBulkActions(); // Re-setup after content update
    })
    .catch(error => {
        console.error('Error refreshing webhooks:', error);
    });
}

function updateStatisticsCards(stats) {
    // Update each statistic card with new values
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            element.textContent = number_format(stats[key]);
        }
    });
}

function exportWebhooks() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    window.location.href = `{{ route('admin.webhook-logs.export') }}?${params.toString()}`;
}

function retryFailedWebhooks() {
    if (!confirm('Are you sure you want to retry all failed webhooks?')) return;
    
    fetch('{{ route('admin.webhook-logs.bulk-retry') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            webhook_ids: Array.from(document.querySelectorAll('.webhook-checkbox:checked')).map(cb => cb.value)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            refreshWebhooks();
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error retrying webhooks:', error);
        showToast('error', 'Failed to retry webhooks');
    });
}

function bulkRetrySelected() {
    const checkedBoxes = document.querySelectorAll('.webhook-checkbox:checked');
    if (checkedBoxes.length === 0) {
        showToast('warning', 'Please select webhooks to retry');
        return;
    }
    
    if (!confirm(`Are you sure you want to retry ${checkedBoxes.length} selected webhooks?`)) return;
    
    const webhookIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    fetch('{{ route('admin.webhook-logs.bulk-retry') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            webhook_ids: webhookIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            refreshWebhooks();
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error retrying webhooks:', error);
        showToast('error', 'Failed to retry selected webhooks');
    });
}

// Clear webhooks form submission
document.getElementById('clearWebhooksForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route('admin.webhook-logs.clear-old') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            refreshWebhooks();
            document.querySelector('[data-bs-dismiss="modal"]').click();
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error clearing webhooks:', error);
        showToast('error', 'Failed to clear old webhooks');
    });
});

function showToast(type, message) {
    // Implement your toast notification system here
    console.log(`${type}: ${message}`);
}

function number_format(number) {
    return new Intl.NumberFormat().format(number);
}
</script>
@endpush
