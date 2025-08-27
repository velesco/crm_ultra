@extends('layouts.app')

@section('title', 'System Health & Performance')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reports.index') }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Health & Performance</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Monitor system performance, errors, and resource usage
                    </p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh Status
                </button>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                    <i class="fas fa-download mr-2"></i>
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- System Status Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $health['memory_usage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hdd text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Disk Usage</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($health['disk_usage'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ $health['disk_free'] }} GB free</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Status -->    
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">AWS Services Status</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Database -->    
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 {{ $health['services']['database']['status'] === 'connected' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            <i class="fas fa-database {{ $health['services']['database']['status'] === 'connected' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">MySQL Database</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $health['services']['database']['response_time'] }}ms</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $health['services']['database']['status'] === 'connected' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                        {{ ucfirst($health['services']['database']['status']) }}
                    </span>
                </div>

                <!-- Redis -->    
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 {{ $health['services']['redis']['status'] === 'connected' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            <i class="fas fa-cube {{ $health['services']['redis']['status'] === 'connected' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Redis Cache</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $health['services']['redis']['memory_usage'] }} used</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $health['services']['redis']['status'] === 'connected' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                        {{ ucfirst($health['services']['redis']['status']) }}
                    </span>
                </div>

                <!-- Queue System (Horizon) -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 {{ $health['services']['horizon']['status'] === 'running' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            <i class="fas fa-tasks {{ $health['services']['horizon']['status'] === 'running' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Laravel Horizon</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $health['services']['horizon']['jobs_processed'] }} jobs processed</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $health['services']['horizon']['status'] === 'running' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                        {{ ucfirst($health['services']['horizon']['status']) }}
                    </span>
                </div>

                <!-- Storage (S3) -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 {{ $health['services']['s3']['status'] === 'connected' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            <i class="fas fa-cloud {{ $health['services']['s3']['status'] === 'connected' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">AWS S3 Storage</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $health['services']['s3']['storage_used'] }} used</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $health['services']['s3']['status'] === 'connected' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                        {{ ucfirst($health['services']['s3']['status']) }}
                    </span>
                </div>

                <!-- ElastiCache -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 {{ $health['services']['elasticache']['status'] === 'available' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            <i class="fas fa-bolt {{ $health['services']['elasticache']['status'] === 'available' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">ElastiCache</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $health['services']['elasticache']['hit_rate'] }}% hit rate</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $health['services']['elasticache']['status'] === 'available' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                        {{ ucfirst($health['services']['elasticache']['status']) }}
                    </span>
                </div>

                <!-- SES Email Service -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 {{ $health['services']['ses']['status'] === 'available' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope-open {{ $health['services']['ses']['status'] === 'available' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">AWS SES</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $health['services']['ses']['reputation'] }}% reputation</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $health['services']['ses']['status'] === 'available' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                        {{ ucfirst($health['services']['ses']['status']) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Performance -->    
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Performance</h3>
                <div class="h-64">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Error Rates -->    
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Error Rates & Response Times</h3>
                <div class="h-64">
                    <canvas id="errorChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Queue Status (Horizon) -->    
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Queue Status (Laravel Horizon)</h3>
                <a href="/horizon" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 text-sm font-medium">
                    Open Horizon Dashboard →
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($health['queues']['pending_jobs']) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pending Jobs</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($health['queues']['processed_jobs']) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Processed Today</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($health['queues']['failed_jobs']) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Failed Jobs</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $health['queues']['active_workers'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Active Workers</div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Queue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Processed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Failed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg Wait Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($health['queue_details'] as $queue)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $queue['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($queue['pending']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($queue['processed']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($queue['failed']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $queue['wait_time'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent System Events -->    
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Recent System Events</h3>
            
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($health['recent_events'] as $event)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800 
                                        {{ $event['type'] === 'error' ? 'bg-red-500' : ($event['type'] === 'warning' ? 'bg-yellow-500' : 'bg-green-500') }}">
                                        <i class="fas {{ $event['type'] === 'error' ? 'fa-exclamation-triangle' : ($event['type'] === 'warning' ? 'fa-exclamation' : 'fa-check') }} text-white text-xs"></i>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $event['service'] }}</span> 
                                            {{ $event['message'] }}
                                        </p>
                                        @if($event['details'])
                                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $event['details'] }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $event['timestamp']->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#D1D5DB' : '#374151';
    const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

    // Performance Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($health['performance_labels']) !!},
            datasets: [{
                label: 'CPU Usage (%)',
                data: {!! json_encode($health['cpu_trend']) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }, {
                label: 'Memory Usage (%)',
                data: {!! json_encode($health['memory_trend']) !!},
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }, {
                label: 'Disk Usage (%)',
                data: {!! json_encode($health['disk_trend']) !!},
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    ticks: { color: textColor },
                    grid: { color: gridColor },
                    min: 0,
                    max: 100
                }
            }
        }
    });

    // Error Chart
    const errorCtx = document.getElementById('errorChart').getContext('2d');
    new Chart(errorCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($health['error_labels']) !!},
            datasets: [{
                label: 'Error Rate (%)',
                data: {!! json_encode($health['error_rates']) !!},
                backgroundColor: '#EF4444',
                borderWidth: 0,
                yAxisID: 'y'
            }, {
                label: 'Response Time (ms)',
                data: {!! json_encode($health['response_times']) !!},
                type: 'line',
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: false,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    ticks: { color: textColor },
                    grid: {
                        drawOnChartArea: false,
                        color: gridColor
                    }
                }
            }
        }
    });

    // Auto-refresh every 30 seconds
    setInterval(function() {
        if (document.querySelector('[data-auto-refresh="true"]')) {
            location.reload();
        }
    }, 30000);
});
</script>
@endpush="flex-shrink-0">
                        <div class="w-8 h-8 {{ $health['overall_status'] === 'healthy' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            <i class="fas {{ $health['overall_status'] === 'healthy' ? 'fa-check-circle text-green-600 dark:text-green-400' : 'fa-exclamation-triangle text-red-600 dark:text-red-400' }}"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">System Status</dt>
                            <dd class="text-lg font-semibold {{ $health['overall_status'] === 'healthy' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ ucfirst($health['overall_status']) }}
                            </dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Uptime: {{ $health['uptime'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-server text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">CPU Usage</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($health['cpu_usage'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $health['cpu_usage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-memory text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Memory Usage</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($health['memory_usage'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $health['memory_usage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hdd text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Disk Usage</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($health['disk_usage'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-orange-600 h-2 rounded-full" style="width: {{ $health['disk_usage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Status -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Service Status</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($health['services'] as $service => $status)
                <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 {{ $status['status'] === 'active' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-lg flex items-center justify-center">
                            <i class="fas {{ $status['icon'] }} {{ $status['status'] === 'active' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $status['name'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $status['description'] }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $status['status'] === 'active' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' }}">
                            {{ $status['status'] === 'active' ? 'Active' : 'Inactive' }}
                        </span>
                        @if($status['response_time'])
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $status['response_time'] }}ms</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Response Time Trends -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Response Time Trends</h3>
                    <div class="flex space-x-2">
                        <button class="text-xs px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg">API</button>
                        <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">DB</button>
                        <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">Cache</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="responseTimeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Resource Usage -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resource Usage</h3>
                <div class="h-64">
                    <canvas id="resourceUsageChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">CPU</div>
                        <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ number_format($health['cpu_usage'], 1) }}%</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Memory</div>
                        <div class="text-lg font-semibold text-purple-600 dark:text-purple-400">{{ number_format($health['memory_usage'], 1) }}%</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Disk</div>
                        <div class="text-lg font-semibold text-orange-600 dark:text-orange-400">{{ number_format($health['disk_usage'], 1) }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Logs & Issues -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Errors & Issues</h3>
                <div class="flex space-x-2">
                    <select class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <option>All Levels</option>
                        <option>Critical</option>
                        <option>Error</option>
                        <option>Warning</option>
                    </select>
                    <button class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            
            @if($health['recent_errors']->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Component</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Occurrence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($health['recent_errors'] as $error)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $error['level'] === 'critical' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 
                                       ($error['level'] === 'error' ? 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200' : 
                                        'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200') }}">
                                    <i class="fas {{ $error['level'] === 'critical' ? 'fa-exclamation-circle' : ($error['level'] === 'error' ? 'fa-exclamation-triangle' : 'fa-exclamation') }} mr-1"></i>
                                    {{ ucfirst($error['level']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($error['message'], 60) }}</div>
                                @if($error['context'])
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($error['context'], 80) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    {{ $error['component'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $error['count'] }}</div>
                                @if($error['trend'])
                                <div class="text-xs {{ $error['trend'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    {{ $error['trend'] > 0 ? '+' : '' }}{{ number_format($error['trend'], 1) }}%
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $error['last_occurrence']->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button class="text-blue-600 dark:text-blue-400 hover:text-blue-500">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-gray-600 dark:text-gray-400 hover:text-gray-500">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                    <button class="text-red-600 dark:text-red-400 hover:text-red-500">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-400 dark:text-green-500 mb-4"></i>
                <div class="text-lg font-medium text-gray-900 dark:text-white">No Recent Errors</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">System is running smoothly</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Maintenance & Optimization -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Maintenance & Optimization</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Cache Status</h4>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                            {{ $health['cache_stats']['hit_rate'] }}% hit rate
                        </span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $health['cache_stats']['size'] }} cached items</div>
                    <button class="mt-3 text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                        Clear Cache
                    </button>
                </div>
                
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Log Cleanup</h4>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $health['log_stats']['size'] }}</span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Last cleanup: {{ $health['log_stats']['last_cleanup'] }}</div>
                    <button class="mt-3 text-xs bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded">
                        Clean Logs
                    </button>
                </div>
                
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Database Optimization</h4>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $health['db_optimization']['fragmentation'] }}% fragmented</span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Last optimized: {{ $health['db_optimization']['last_optimized'] }}</div>
                    <button class="mt-3 text-xs bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded">
                        Optimize DB
                    </button>
                </div>
                
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Security Scan</h4>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                            No issues
                        </span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Last scan: {{ $health['security_scan']['last_scan'] }}</div>
                    <button class="mt-3 text-xs bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                        Run Scan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#D1D5DB' : '#374151';
    const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

    // Response Time Chart
    const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
    new Chart(responseTimeCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($health['response_time_labels']) !!},
            datasets: [{
                label: 'API Response Time',
                data: {!! json_encode($health['api_response_times']) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }, {
                label: 'Database Response Time',
                data: {!! json_encode($health['db_response_times']) !!},
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    ticks: { 
                        color: textColor,
                        callback: function(value) {
                            return value + 'ms';
                        }
                    },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Resource Usage Chart
    const resourceUsageCtx = document.getElementById('resourceUsageChart').getContext('2d');
    new Chart(resourceUsageCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($health['usage_labels']) !!},
            datasets: [{
                label: 'CPU Usage (%)',
                data: {!! json_encode($health['cpu_history']) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: false
            }, {
                label: 'Memory Usage (%)',
                data: {!! json_encode($health['memory_history']) !!},
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2,
                fill: false
            }, {
                label: 'Disk Usage (%)',
                data: {!! json_encode($health['disk_history']) !!},
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    ticks: { 
                        color: textColor,
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: { color: gridColor },
                    min: 0,
                    max: 100
                }
            }
        }
    });

    // Auto-refresh functionality
    setInterval(function() {
        // You can implement auto-refresh here
        // location.reload();
    }, 30000); // Refresh every 30 seconds
});
</script>
@endpush
