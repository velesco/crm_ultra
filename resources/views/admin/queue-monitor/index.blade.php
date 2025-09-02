@extends('layouts.app')

@section('title', 'Queue Monitor')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg text-white overflow-hidden">
                <div class="p-8 text-center">
                    <i class="fas fa-tachometer-alt text-5xl mb-4 opacity-75"></i>
                    <h1 class="text-3xl font-bold mb-2">Queue Monitor</h1>
                    <p class="text-lg opacity-75">Real-time queue monitoring and management</p>
                </div>
            </div>
        </div>

        <!-- Auto-refresh indicator -->
        <div id="autoRefreshIndicator" class="fixed top-20 right-20 z-50 opacity-0 transition-opacity duration-300">
            <div class="bg-green-500 text-white px-4 py-2 rounded-full shadow-lg">
                <i class="fas fa-sync-alt fa-spin mr-2"></i>Refreshing...
            </div>
        </div>

        <!-- Health Status Alert -->
        <div id="healthAlert" class="mb-8 hidden">
            <div id="healthStatusContainer" class="rounded-lg border-l-4 p-4 shadow-sm">
                <div class="flex items-center">
                    <div id="healthIndicator" class="w-5 h-5 rounded-full mr-3"></div>
                    <div id="healthStatus" class="text-sm font-medium"></div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg text-white overflow-hidden transform transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-tasks text-4xl mb-3 opacity-75"></i>
                    <h3 class="text-3xl font-bold mb-1" id="totalJobs">{{ $stats['total_jobs'] ?? 0 }}</h3>
                    <p class="text-base opacity-90 mb-1">Total Jobs</p>
                    <small class="text-sm opacity-75">Last 24 hours</small>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl shadow-lg text-white overflow-hidden transform transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-exclamation-triangle text-4xl mb-3 opacity-75"></i>
                    <h3 class="text-3xl font-bold mb-1" id="failedJobs">{{ $stats['failed_jobs'] ?? 0 }}</h3>
                    <p class="text-base opacity-90 mb-1">Failed Jobs</p>
                    <small class="text-sm opacity-75">Need attention</small>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg text-white overflow-hidden transform transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-clock text-4xl mb-3 opacity-75"></i>
                    <h3 class="text-3xl font-bold mb-1" id="jobsPerHour">{{ $stats['jobs_per_hour'] ?? 0 }}</h3>
                    <p class="text-base opacity-90 mb-1">Jobs/Hour</p>
                    <small class="text-sm opacity-75">Current rate</small>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg text-white overflow-hidden transform transition-transform duration-300 hover:-translate-y-1">
                <div class="p-6 text-center">
                    <i class="fas fa-chart-line text-4xl mb-3 opacity-75"></i>
                    <h3 class="text-3xl font-bold mb-1" id="successRate">{{ $stats['success_rate'] ?? 0 }}%</h3>
                    <p class="text-base opacity-90 mb-1">Success Rate</p>
                    <small class="text-sm opacity-75">Overall performance</small>
                </div>
            </div>
        </div>

        <!-- Queue Sizes and Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Queue Sizes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-list-ol text-blue-500 mr-2"></i>
                            Queue Sizes
                        </h3>
                        <span class="text-sm text-gray-500">Current backlog</span>
                    </div>
                </div>
                <div class="p-6" id="queueSizes">
                    @if(isset($stats['queue_sizes']))
                        @foreach($stats['queue_sizes'] as $queue => $size)
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-medium text-gray-900">{{ ucfirst($queue) }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $size > 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ $size }}
                            </span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Jobs Chart -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-area text-green-500 mr-2"></i>
                        Jobs Activity (24 Hours)
                    </h3>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <canvas id="jobsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-cogs text-purple-500 mr-2"></i>
                    Queue Controls
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="queueSelect" class="block text-sm font-medium text-gray-700 mb-2">Select Queue</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" id="queueSelect">
                            <option value="default">Default</option>
                            <option value="emails">Emails</option>
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="import">Import</option>
                            <option value="sync">Sync</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Queue Operations</label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="pauseQueue()" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-200">
                                <i class="fas fa-pause mr-2"></i>Pause
                            </button>
                            <button type="button" onclick="resumeQueue()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                                <i class="fas fa-play mr-2"></i>Resume
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="retryAllFailed()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-redo mr-2"></i>Retry All Failed
                    </button>
                    <button type="button" onclick="clearAllFailed()" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>Clear All Failed
                    </button>
                    <button type="button" onclick="exportData()" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-download mr-2"></i>Export Data
                    </button>
                    <button type="button" onclick="toggleAutoRefresh()" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>Auto Refresh: <span id="autoRefreshStatus" class="ml-1">ON</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Supervisors and Workers -->
        @if($supervisors->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-users-cog text-indigo-500 mr-2"></i>
                    Supervisors & Workers
                </h3>
            </div>
            <div class="p-6" id="supervisorsContainer">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($supervisors->take(4) as $supervisor)
                        <div class="bg-gradient-to-br from-pink-500 to-red-600 rounded-xl shadow-lg text-white overflow-hidden">
                            <div class="p-6 text-center">
                                <i class="fas fa-server text-3xl mb-3 opacity-75"></i>
                                <h4 class="text-lg font-semibold">{{ $supervisor->name ?? 'Supervisor' }}</h4>
                                <div class="text-sm opacity-75">{{ $supervisor->status ?? 'Active' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Jobs and Failed Jobs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Failed Jobs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-fit">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            Failed Jobs
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $failedJobs->count() }}
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="failedJobsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($failedJobs as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ class_basename($job->name ?? 'Unknown') }}</div>
                                    <div class="text-sm text-gray-500">ID: {{ $job->id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $job->queue ?? 'default' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $job->failed_at ? \Carbon\Carbon::parse($job->failed_at)->diffForHumans() : 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewJob('{{ $job->id }}')" 
                                                class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" 
                                                title="View Details">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <button onclick="retryJob('{{ $job->id }}')" 
                                                class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors" 
                                                title="Retry">
                                            <i class="fas fa-redo text-sm"></i>
                                        </button>
                                        <button onclick="deleteJob('{{ $job->id }}')" 
                                                class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" 
                                                title="Delete">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                                    <div class="text-gray-500">No failed jobs</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Jobs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-fit">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-history text-green-500 mr-2"></i>
                            Recent Jobs
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $recentJobs->count() }}
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="recentJobsTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Runtime</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentJobs as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ class_basename($job->name ?? 'Unknown') }}</div>
                                    <div class="text-sm text-gray-500">ID: {{ $job->id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $job->queue ?? 'default' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClasses = match($job->status) {
                                            'completed' => 'bg-green-100 text-green-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            default => 'bg-yellow-100 text-yellow-800'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                        {{ ucfirst($job->status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $job->runtime ? $job->runtime . 'ms' : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <i class="fas fa-clock text-4xl text-gray-400 mb-3"></i>
                                    <div class="text-gray-500">No recent jobs</div>
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

<!-- Job Details Modal -->
<div id="jobDetailsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">Job Details</h3>
                    <button type="button" onclick="closeJobModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="jobDetailsContent" class="mt-4">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner animate-spin text-2xl text-gray-400"></i>
                        <div class="mt-2 text-gray-500">Loading...</div>
                    </div>
                </div>
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
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Failed Jobs',
                data: chartData.failed,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
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
        autoRefreshInterval = setInterval(refreshData, 30000);
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
    
    let queueSizesHtml = '';
    for (const [queue, size] of Object.entries(stats.queue_sizes || {})) {
        const badgeClass = size > 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
        queueSizesHtml += `
            <div class="flex items-center justify-between mb-3">
                <span class="font-medium text-gray-900">${queue.charAt(0).toUpperCase() + queue.slice(1)}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
                    ${size}
                </span>
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
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${jobName}</div>
                        <div class="text-sm text-gray-500">ID: ${job.id}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ${job.queue || 'default'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${failedAt}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewJob('${job.id}')" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors" title="View Details">
                                <i class="fas fa-eye text-sm"></i>
                            </button>
                            <button onclick="retryJob('${job.id}')" class="p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition-colors" title="Retry">
                                <i class="fas fa-redo text-sm"></i>
                            </button>
                            <button onclick="deleteJob('${job.id}')" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    } else {
        failedJobsHtml = `
            <tr>
                <td colspan="4" class="px-6 py-12 text-center">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                    <div class="text-gray-500">No failed jobs</div>
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
            const statusClass = job.status === 'completed' ? 'bg-green-100 text-green-800' : (job.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
            recentJobsHtml += `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${jobName}</div>
                        <div class="text-sm text-gray-500">ID: ${job.id}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${job.queue || 'default'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                            ${job.status ? job.status.charAt(0).toUpperCase() + job.status.slice(1) : 'Pending'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${job.runtime ? job.runtime + 'ms' : '-'}</td>
                </tr>
            `;
        });
    } else {
        recentJobsHtml = `
            <tr>
                <td colspan="4" class="px-6 py-12 text-center">
                    <i class="fas fa-clock text-4xl text-gray-400 mb-3"></i>
                    <div class="text-gray-500">No recent jobs</div>
                </td>
            </tr>
        `;
    }
    document.querySelector('#recentJobsTable tbody').innerHTML = recentJobsHtml;
}

function showRefreshIndicator() {
    document.getElementById('autoRefreshIndicator').classList.add('opacity-100');
    document.getElementById('autoRefreshIndicator').classList.remove('opacity-0');
}

function hideRefreshIndicator() {
    setTimeout(() => {
        document.getElementById('autoRefreshIndicator').classList.add('opacity-0');
        document.getElementById('autoRefreshIndicator').classList.remove('opacity-100');
    }, 500);
}

function checkHealth() {
    fetch('{{ route("admin.queue-monitor.health") }}')
        .then(response => response.json())
        .then(data => {
            const alert = document.getElementById('healthAlert');
            const container = document.getElementById('healthStatusContainer');
            const status = document.getElementById('healthStatus');
            const indicator = document.getElementById('healthIndicator');
            
            if (data.status !== 'healthy') {
                let alertClass = '';
                let indicatorClass = '';
                
                switch(data.status) {
                    case 'warning':
                        alertClass = 'border-yellow-400 bg-yellow-50';
                        indicatorClass = 'bg-yellow-500';
                        break;
                    case 'critical':
                        alertClass = 'border-red-400 bg-red-50';
                        indicatorClass = 'bg-red-500';
                        break;
                    default:
                        alertClass = 'border-gray-400 bg-gray-50';
                        indicatorClass = 'bg-gray-500';
                }
                
                container.className = `rounded-lg border-l-4 p-4 shadow-sm ${alertClass}`;
                indicator.className = `w-5 h-5 rounded-full ${indicatorClass}`;
                status.innerHTML = `
                    <strong>Queue Health: ${data.status.toUpperCase()}</strong>
                    ${data.issues ? '<br><span class="text-sm">' + data.issues.join(', ') + '</span>' : ''}
                `;
                alert.classList.remove('hidden');
            } else {
                alert.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Failed to check health:', error);
        });
}

function viewJob(id) {
    document.getElementById('jobDetailsModal').classList.remove('hidden');
    const content = document.getElementById('jobDetailsContent');
    
    content.innerHTML = `
        <div class="text-center py-8">
            <i class="fas fa-spinner animate-spin text-2xl text-gray-400"></i>
            <div class="mt-2 text-gray-500">Loading...</div>
        </div>
    `;
    
    fetch(`{{ route("admin.queue-monitor.show", ":id") }}`.replace(':id', id), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            content.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">${data.error}</div>`;
            return;
        }
        
        let detailsHtml = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Job Information</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID:</span>
                            <span class="font-medium">${data.id}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium">${data.name ? data.name.split('\\').pop() : 'Unknown'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Queue:</span>
                            <span class="font-medium">${data.queue || 'default'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${data.status === 'completed' ? 'bg-green-100 text-green-800' : (data.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')}">
                                ${data.status || 'pending'}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Attempts:</span>
                            <span class="font-medium">${data.attempts || 0}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Timing</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Started:</span>
                            <span class="font-medium">${data.started_at ? moment(data.started_at).format('YYYY-MM-DD HH:mm:ss') : 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Finished:</span>
                            <span class="font-medium">${data.finished_at ? moment(data.finished_at).format('YYYY-MM-DD HH:mm:ss') : 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Failed:</span>
                            <span class="font-medium">${data.failed_at ? moment(data.failed_at).format('YYYY-MM-DD HH:mm:ss') : 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Runtime:</span>
                            <span class="font-medium">${data.runtime ? data.runtime + 'ms' : 'N/A'}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        if (data.payload) {
            detailsHtml += `
                <div class="mt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Payload</h4>
                    <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto"><code>${JSON.stringify(data.payload, null, 2)}</code></pre>
                </div>
            `;
        }
        
        if (data.exception) {
            detailsHtml += `
                <div class="mt-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Exception</h4>
                    <pre class="bg-red-100 text-red-800 p-4 rounded-lg text-sm overflow-x-auto"><code>${data.exception}</code></pre>
                </div>
            `;
        }
        
        content.innerHTML = detailsHtml;
    })
    .catch(error => {
        content.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">Failed to load job details: ${error.message}</div>`;
    });
}

function closeJobModal() {
    document.getElementById('jobDetailsModal').classList.add('hidden');
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
    const toastClasses = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'info': 'bg-blue-500',
        'warning': 'bg-yellow-500'
    }[type] || 'bg-blue-500';
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${toastClasses} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    toast.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}
</script>
@endpush
