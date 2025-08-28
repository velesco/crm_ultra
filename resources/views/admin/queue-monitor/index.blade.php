@extends('layouts.app')

@section('title', 'Queue Monitor')
@section('page-title', 'Queue Monitor')

@section('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item active">Queue Monitor</li>
    </ol>
</nav>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
<style>
    .queue-stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        color: white;
        transition: transform 0.3s ease;
    }
    .queue-stat-card:hover {
        transform: translateY(-5px);
    }
    .queue-status-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
    }
    .job-table {
        font-size: 0.9rem;
    }
    .queue-chart-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .supervisor-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border-radius: 15px;
        color: white;
    }
    .auto-refresh-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 10px 15px;
        background: rgba(40, 167, 69, 0.9);
        color: white;
        border-radius: 25px;
        font-size: 0.8rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .auto-refresh-indicator.show {
        opacity: 1;
    }
    .health-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }
    .health-healthy { background-color: #28a745; }
    .health-warning { background-color: #ffc107; }
    .health-critical { background-color: #dc3545; }
    .health-error { background-color: #6c757d; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Auto-refresh indicator -->
    <div id="autoRefreshIndicator" class="auto-refresh-indicator">
        <i class="fas fa-sync-alt fa-spin me-2"></i>Refreshing...
    </div>

    <!-- Health Status Alert -->
    <div id="healthAlert" class="alert alert-dismissible fade show d-none" role="alert">
        <span id="healthStatus"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card queue-stat-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-2x mb-3 opacity-75"></i>
                    <h3 class="mb-2" id="totalJobs">{{ $stats['total_jobs'] ?? 0 }}</h3>
                    <p class="mb-0">Total Jobs</p>
                    <small class="opacity-75">Last 24 hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card queue-stat-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3 opacity-75"></i>
                    <h3 class="mb-2" id="failedJobs">{{ $stats['failed_jobs'] ?? 0 }}</h3>
                    <p class="mb-0">Failed Jobs</p>
                    <small class="opacity-75">Need attention</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card queue-stat-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-3 opacity-75"></i>
                    <h3 class="mb-2" id="jobsPerHour">{{ $stats['jobs_per_hour'] ?? 0 }}</h3>
                    <p class="mb-0">Jobs/Hour</p>
                    <small class="opacity-75">Current rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card queue-stat-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x mb-3 opacity-75"></i>
                    <h3 class="mb-2" id="successRate">{{ $stats['success_rate'] ?? 0 }}%</h3>
                    <p class="mb-0">Success Rate</p>
                    <small class="opacity-75">Overall performance</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Queue Sizes and Charts -->
    <div class="row mb-4">
        <!-- Queue Sizes -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>Queue Sizes</h5>
                    <small class="text-muted">Current backlog</small>
                </div>
                <div class="card-body" id="queueSizes">
                    @if(isset($stats['queue_sizes']))
                        @foreach($stats['queue_sizes'] as $queue => $size)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">{{ ucfirst($queue) }}</span>
                            <span class="badge bg-{{ $size > 10 ? 'warning' : 'success' }} rounded-pill">{{ $size }}</span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Jobs Chart -->
        <div class="col-md-8">
            <div class="card queue-chart-container">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Jobs Activity (24 Hours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="jobsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Control Panel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Queue Controls</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="queueSelect" class="form-label">Select Queue</label>
                                <select class="form-select" id="queueSelect">
                                    <option value="default">Default</option>
                                    <option value="emails">Emails</option>
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="import">Import</option>
                                    <option value="sync">Sync</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Queue Operations</label>
                                <div class="btn-group d-block" role="group">
                                    <button type="button" class="btn btn-warning me-2" onclick="pauseQueue()">
                                        <i class="fas fa-pause me-1"></i>Pause
                                    </button>
                                    <button type="button" class="btn btn-success me-2" onclick="resumeQueue()">
                                        <i class="fas fa-play me-1"></i>Resume
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-info me-2" onclick="retryAllFailed()">
                                    <i class="fas fa-redo me-1"></i>Retry All Failed
                                </button>
                                <button type="button" class="btn btn-danger me-2" onclick="clearAllFailed()">
                                    <i class="fas fa-trash me-1"></i>Clear All Failed
                                </button>
                                <button type="button" class="btn btn-secondary me-2" onclick="exportData()">
                                    <i class="fas fa-download me-1"></i>Export Data
                                </button>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="toggleAutoRefresh()">
                                    <i class="fas fa-sync-alt me-1"></i>Auto Refresh: <span id="autoRefreshStatus">ON</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Supervisors and Workers -->
    @if($supervisors->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users-cog me-2"></i>Supervisors & Workers</h5>
                </div>
                <div class="card-body" id="supervisorsContainer">
                    <div class="row">
                        @foreach($supervisors->take(4) as $supervisor)
                        <div class="col-md-3">
                            <div class="card supervisor-card mb-3">
                                <div class="card-body text-center">
                                    <i class="fas fa-server fa-2x mb-2 opacity-75"></i>
                                    <h6>{{ $supervisor->name ?? 'Supervisor' }}</h6>
                                    <small>{{ $supervisor->status ?? 'Active' }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Jobs and Failed Jobs -->
    <div class="row">
        <!-- Failed Jobs -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Failed Jobs</h5>
                    <span class="badge bg-danger">{{ $failedJobs->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover job-table mb-0" id="failedJobsTable">
                            <thead>
                                <tr>
                                    <th>Job</th>
                                    <th>Queue</th>
                                    <th>Failed At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($failedJobs as $job)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ class_basename($job->name ?? 'Unknown') }}</div>
                                        <small class="text-muted">ID: {{ $job->id }}</small>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $job->queue ?? 'default' }}</span></td>
                                    <td>
                                        <small>{{ $job->failed_at ? \Carbon\Carbon::parse($job->failed_at)->diffForHumans() : 'Unknown' }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="viewJob('{{ $job->id }}')" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" onclick="retryJob('{{ $job->id }}')" title="Retry">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteJob('{{ $job->id }}')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                                        No failed jobs
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-success"></i>Recent Jobs</h5>
                    <span class="badge bg-success">{{ $recentJobs->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover job-table mb-0" id="recentJobsTable">
                            <thead>
                                <tr>
                                    <th>Job</th>
                                    <th>Queue</th>
                                    <th>Status</th>
                                    <th>Runtime</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentJobs as $job)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ class_basename($job->name ?? 'Unknown') }}</div>
                                        <small class="text-muted">ID: {{ $job->id }}</small>
                                    </td>
                                    <td><span class="badge bg-primary">{{ $job->queue ?? 'default' }}</span></td>
                                    <td>
                                        <span class="queue-status-badge badge bg-{{ $job->status === 'completed' ? 'success' : ($job->status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($job->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $job->runtime ? $job->runtime . 'ms' : '-' }}</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-clock fa-2x mb-2"></i><br>
                                        No recent jobs
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Job Details Modal -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Job Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="jobDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
<script>
let autoRefreshEnabled = true;
let autoRefreshInterval;
let jobsChart;

$(document).ready(function() {
    initializeChart();
    checkHealth();
    startAutoRefresh();
});

function initializeChart() {
    const ctx = document.getElementById('jobsChart').getContext('2d');
    const chartData = @json($chartData ?? ['labels' => [], 'processed' => [], 'failed' => []]);
    
    jobsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Processed Jobs',
                data: chartData.processed,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Failed Jobs',
                data: chartData.failed,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            }
        }
    });
}

function startAutoRefresh() {
    if (autoRefreshEnabled) {
        autoRefreshInterval = setInterval(refreshData, 30000); // Refresh every 30 seconds
    }
}

function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const status = document.getElementById('autoRefreshStatus');
    
    if (autoRefreshEnabled) {
        status.textContent = 'ON';
        startAutoRefresh();
    } else {
        status.textContent = 'OFF';
        clearInterval(autoRefreshInterval);
    }
}

function refreshData() {
    showRefreshIndicator();
    
    fetch('{{ route("admin.queue-monitor.index") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateStats(data.stats);
        updateChart(data.chartData);
        updateTables(data);
        hideRefreshIndicator();
        checkHealth();
    })
    .catch(error => {
        console.error('Failed to refresh data:', error);
        hideRefreshIndicator();
    });
}

function updateStats(stats) {
    document.getElementById('totalJobs').textContent = stats.total_jobs;
    document.getElementById('failedJobs').textContent = stats.failed_jobs;
    document.getElementById('jobsPerHour').textContent = stats.jobs_per_hour;
    document.getElementById('successRate').textContent = stats.success_rate + '%';
    
    // Update queue sizes
    let queueSizesHtml = '';
    for (const [queue, size] of Object.entries(stats.queue_sizes || {})) {
        const badgeClass = size > 10 ? 'warning' : 'success';
        queueSizesHtml += `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-medium">${queue.charAt(0).toUpperCase() + queue.slice(1)}</span>
                <span class="badge bg-${badgeClass} rounded-pill">${size}</span>
            </div>
        `;
    }
    document.getElementById('queueSizes').innerHTML = queueSizesHtml;
}

function updateChart(chartData) {
    if (jobsChart && chartData) {
        jobsChart.data.labels = chartData.labels;
        jobsChart.data.datasets[0].data = chartData.processed;
        jobsChart.data.datasets[1].data = chartData.failed;
        jobsChart.update('none');
    }
}

function updateTables(data) {
    // Update failed jobs table
    let failedJobsHtml = '';
    if (data.failedJobs && data.failedJobs.length > 0) {
        data.failedJobs.forEach(job => {
            const jobName = job.name ? job.name.split('\\').pop() : 'Unknown';
            const failedAt = job.failed_at ? moment(job.failed_at).fromNow() : 'Unknown';
            failedJobsHtml += `
                <tr>
                    <td>
                        <div class="fw-medium">${jobName}</div>
                        <small class="text-muted">ID: ${job.id}</small>
                    </td>
                    <td><span class="badge bg-secondary">${job.queue || 'default'}</span></td>
                    <td><small>${failedAt}</small></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewJob('${job.id}')" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-success" onclick="retryJob('${job.id}')" title="Retry">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteJob('${job.id}')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    } else {
        failedJobsHtml = `
            <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                    No failed jobs
                </td>
            </tr>
        `;
    }
    document.querySelector('#failedJobsTable tbody').innerHTML = failedJobsHtml;

    // Update recent jobs table
    let recentJobsHtml = '';
    if (data.recentJobs && data.recentJobs.length > 0) {
        data.recentJobs.forEach(job => {
            const jobName = job.name ? job.name.split('\\').pop() : 'Unknown';
            const statusClass = job.status === 'completed' ? 'success' : (job.status === 'failed' ? 'danger' : 'warning');
            recentJobsHtml += `
                <tr>
                    <td>
                        <div class="fw-medium">${jobName}</div>
                        <small class="text-muted">ID: ${job.id}</small>
                    </td>
                    <td><span class="badge bg-primary">${job.queue || 'default'}</span></td>
                    <td>
                        <span class="queue-status-badge badge bg-${statusClass}">
                            ${job.status ? job.status.charAt(0).toUpperCase() + job.status.slice(1) : 'Pending'}
                        </span>
                    </td>
                    <td><small>${job.runtime ? job.runtime + 'ms' : '-'}</small></td>
                </tr>
            `;
        });
    } else {
        recentJobsHtml = `
            <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                    <i class="fas fa-clock fa-2x mb-2"></i><br>
                    No recent jobs
                </td>
            </tr>
        `;
    }
    document.querySelector('#recentJobsTable tbody').innerHTML = recentJobsHtml;
}

function showRefreshIndicator() {
    document.getElementById('autoRefreshIndicator').classList.add('show');
}

function hideRefreshIndicator() {
    setTimeout(() => {
        document.getElementById('autoRefreshIndicator').classList.remove('show');
    }, 500);
}

function checkHealth() {
    fetch('{{ route("admin.queue-monitor.health") }}')
        .then(response => response.json())
        .then(data => {
            const alert = document.getElementById('healthAlert');
            const status = document.getElementById('healthStatus');
            
            if (data.status !== 'healthy') {
                let alertClass = '';
                let icon = '';
                
                switch(data.status) {
                    case 'warning':
                        alertClass = 'alert-warning';
                        icon = 'fas fa-exclamation-triangle';
                        break;
                    case 'critical':
                        alertClass = 'alert-danger';
                        icon = 'fas fa-exclamation-circle';
                        break;
                    default:
                        alertClass = 'alert-secondary';
                        icon = 'fas fa-question-circle';
                }
                
                alert.className = `alert ${alertClass} alert-dismissible fade show`;
                status.innerHTML = `
                    <span class="health-indicator health-${data.status}"></span>
                    <i class="${icon} me-2"></i>
                    <strong>Queue Health: ${data.status.toUpperCase()}</strong>
                    ${data.issues ? '<br><small>' + data.issues.join(', ') + '</small>' : ''}
                `;
                alert.classList.remove('d-none');
            } else {
                alert.classList.add('d-none');
            }
        })
        .catch(error => {
            console.error('Failed to check health:', error);
        });
}

function viewJob(id) {
    const modal = new bootstrap.Modal(document.getElementById('jobDetailsModal'));
    const content = document.getElementById('jobDetailsContent');
    
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    fetch(`{{ route("admin.queue-monitor.show", ":id") }}`.replace(':id', id), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            content.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            return;
        }
        
        let detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Job Information</h6>
                    <table class="table table-sm">
                        <tr><th>ID:</th><td>${data.id}</td></tr>
                        <tr><th>Name:</th><td>${data.name ? data.name.split('\\').pop() : 'Unknown'}</td></tr>
                        <tr><th>Queue:</th><td>${data.queue || 'default'}</td></tr>
                        <tr><th>Status:</th><td><span class="badge bg-${data.status === 'completed' ? 'success' : (data.status === 'failed' ? 'danger' : 'warning')}">${data.status || 'pending'}</span></td></tr>
                        <tr><th>Attempts:</th><td>${data.attempts || 0}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Timing</h6>
                    <table class="table table-sm">
                        <tr><th>Started:</th><td>${data.started_at ? moment(data.started_at).format('YYYY-MM-DD HH:mm:ss') : 'N/A'}</td></tr>
                        <tr><th>Finished:</th><td>${data.finished_at ? moment(data.finished_at).format('YYYY-MM-DD HH:mm:ss') : 'N/A'}</td></tr>
                        <tr><th>Failed:</th><td>${data.failed_at ? moment(data.failed_at).format('YYYY-MM-DD HH:mm:ss') : 'N/A'}</td></tr>
                        <tr><th>Runtime:</th><td>${data.runtime ? data.runtime + 'ms' : 'N/A'}</td></tr>
                    </table>
                </div>
            </div>
        `;
        
        if (data.payload) {
            detailsHtml += `
                <div class="mt-3">
                    <h6>Payload</h6>
                    <pre class="bg-light p-3 rounded"><code>${JSON.stringify(data.payload, null, 2)}</code></pre>
                </div>
            `;
        }
        
        if (data.exception) {
            detailsHtml += `
                <div class="mt-3">
                    <h6>Exception</h6>
                    <pre class="bg-danger text-white p-3 rounded"><code>${data.exception}</code></pre>
                </div>
            `;
        }
        
        content.innerHTML = detailsHtml;
    })
    .catch(error => {
        content.innerHTML = `<div class="alert alert-danger">Failed to load job details: ${error.message}</div>`;
    });
}

function retryJob(id) {
    if (!confirm('Are you sure you want to retry this job?')) {
        return;
    }
    
    fetch(`{{ route("admin.queue-monitor.retry", ":id") }}`.replace(':id', id), {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Job retried successfully', 'success');
            refreshData();
        } else {
            showToast(data.message || 'Failed to retry job', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to retry job', 'error');
        console.error(error);
    });
}

function deleteJob(id) {
    if (!confirm('Are you sure you want to delete this failed job? This action cannot be undone.')) {
        return;
    }
    
    fetch(`{{ route("admin.queue-monitor.delete", ":id") }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Job deleted successfully', 'success');
            refreshData();
        } else {
            showToast(data.message || 'Failed to delete job', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to delete job', 'error');
        console.error(error);
    });
}

function retryAllFailed() {
    if (!confirm('Are you sure you want to retry all failed jobs?')) {
        return;
    }
    
    fetch('{{ route("admin.queue-monitor.retry-all") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('All failed jobs retried successfully', 'success');
            refreshData();
        } else {
            showToast(data.message || 'Failed to retry jobs', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to retry all jobs', 'error');
        console.error(error);
    });
}

function clearAllFailed() {
    if (!confirm('Are you sure you want to clear all failed jobs? This action cannot be undone.')) {
        return;
    }
    
    fetch('{{ route("admin.queue-monitor.clear-all") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('All failed jobs cleared successfully', 'success');
            refreshData();
        } else {
            showToast(data.message || 'Failed to clear jobs', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to clear all failed jobs', 'error');
        console.error(error);
    });
}

function pauseQueue() {
    const queue = document.getElementById('queueSelect').value;
    
    fetch('{{ route("admin.queue-monitor.pause") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ queue: queue })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Queue paused successfully', 'success');
        } else {
            showToast(data.message || 'Failed to pause queue', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to pause queue', 'error');
        console.error(error);
    });
}

function resumeQueue() {
    const queue = document.getElementById('queueSelect').value;
    
    fetch('{{ route("admin.queue-monitor.resume") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ queue: queue })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Queue resumed successfully', 'success');
        } else {
            showToast(data.message || 'Failed to resume queue', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to resume queue', 'error');
        console.error(error);
    });
}

function exportData() {
    showToast('Preparing export...', 'info');
    window.location.href = '{{ route("admin.queue-monitor.export") }}';
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to toast container or create one
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    container.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}
</script>
@endpush
