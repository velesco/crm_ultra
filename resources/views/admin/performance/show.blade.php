@extends('layouts.app')

@section('title', ucfirst($metric) . ' Performance Details')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-blue-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">{{ ucfirst($metric) }} Performance Details</h1>
                        <p class="text-gray-600">Detailed metrics and analysis for {{ $period }}</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.performance.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
                
                <select id="periodFilter" 
                        class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="1h" {{ $period === '1h' ? 'selected' : '' }}>Last Hour</option>
                    <option value="24h" {{ $period === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                    <option value="7d" {{ $period === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30d" {{ $period === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                </select>
            </div>
        </div>

        <!-- Metric Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @switch($metric)
                @case('system')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ number_format($data['current']['cpu_usage'] ?? 0, 1) }}%</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">CPU Usage</h3>
                            <p class="text-xs text-gray-500">Current utilization</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-purple-600 mb-2">{{ number_format($data['current']['memory_usage'] ?? 0, 1) }}%</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Memory Usage</h3>
                            <p class="text-xs text-gray-500">RAM utilization</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-yellow-600 mb-2">{{ number_format($data['current']['disk_usage'] ?? 0, 1) }}%</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Disk Usage</h3>
                            <p class="text-xs text-gray-500">Storage utilization</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-xl font-bold text-green-600 mb-2">{{ implode(', ', array_map(fn($load) => number_format($load, 2), $data['current']['load_average'] ?? [0, 0, 0])) }}</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Load Average</h3>
                            <p class="text-xs text-gray-500">1m, 5m, 15m</p>
                        </div>
                    </div>
                    @break

                @case('database')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ $data['current']['connections'] ?? 0 }}</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Active Connections</h3>
                            <p class="text-xs text-gray-500">Current connections</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-purple-600 mb-2">{{ number_format($data['current']['avg_query_time'] ?? 0, 2) }}ms</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Avg Query Time</h3>
                            <p class="text-xs text-gray-500">Response time</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-yellow-600 mb-2">{{ $data['current']['slow_queries'] ?? 0 }}</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Slow Queries</h3>
                            <p class="text-xs text-gray-500">Need optimization</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-green-600 mb-2">{{ number_format($data['current']['database_size'] ?? 0, 1) }}MB</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Database Size</h3>
                            <p class="text-xs text-gray-500">Total size</p>
                        </div>
                    </div>
                    @break

                @case('cache')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ number_format($data['current']['hit_rate'] ?? 0, 1) }}%</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Hit Rate</h3>
                            <p class="text-xs text-gray-500">Cache efficiency</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-purple-600 mb-2">{{ number_format($data['current']['memory_usage'] ?? 0, 1) }}MB</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Memory Usage</h3>
                            <p class="text-xs text-gray-500">Cache size</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-green-600 mb-2">{{ number_format($data['current']['keys_count'] ?? 0) }}</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Keys Stored</h3>
                            <p class="text-xs text-gray-500">Active keys</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-yellow-600 mb-2">{{ $data['current']['evictions'] ?? 0 }}</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Evictions</h3>
                            <p class="text-xs text-gray-500">Keys removed</p>
                        </div>
                    </div>
                    @break

                @case('queue')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-yellow-600 mb-2">{{ $data['current']['pending_jobs'] ?? 0 }}</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Pending Jobs</h3>
                            <p class="text-xs text-gray-500">Waiting to process</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-red-600 mb-2">{{ $data['current']['failed_jobs'] ?? 0 }}</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Failed Jobs</h3>
                            <p class="text-xs text-gray-500">Need attention</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-green-600 mb-2">{{ $data['current']['processed_jobs'] ?? 0 }}</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Processed Jobs</h3>
                            <p class="text-xs text-gray-500">Completed successfully</p>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-purple-600 mb-2">{{ number_format($data['current']['avg_processing_time'] ?? 0, 2) }}ms</div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">Avg Processing Time</h3>
                            <p class="text-xs text-gray-500">Per job</p>
                        </div>
                    </div>
                    @break
            @endswitch
        </div>

        <!-- Historical Chart -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-area text-blue-500 mr-2"></i>
                        Historical Trends - {{ ucfirst($metric) }}
                    </h3>
                </div>
                <div class="p-6">
                    <div style="height: 400px;">
                        <canvas id="detailChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics and Recommendations Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Statistics Summary -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Statistics Summary
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Average</span>
                            <span class="font-semibold text-gray-900">{{ number_format($data['statistics']['average'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Maximum</span>
                            <span class="font-semibold text-red-600">{{ number_format($data['statistics']['max'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Minimum</span>
                            <span class="font-semibold text-green-600">{{ number_format($data['statistics']['min'] ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Data Points</span>
                            <span class="font-semibold text-gray-900">{{ number_format($data['statistics']['count'] ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Trend</span>
                            <div>
                                @if(($data['statistics']['trend'] ?? 0) > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-arrow-up mr-1"></i>
                                        Improving
                                    </span>
                                @elseif(($data['statistics']['trend'] ?? 0) < 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-arrow-down mr-1"></i>
                                        Declining
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-minus mr-1"></i>
                                        Stable
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recommendations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Recommendations
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @switch($metric)
                            @case('system')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="text-sm text-blue-800">
                                        <strong>CPU:</strong> Keep under 80% for optimal performance
                                    </div>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <div class="text-sm text-yellow-800">
                                        <strong>Memory:</strong> Monitor for memory leaks if consistently high
                                    </div>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="text-sm text-green-800">
                                        <strong>Disk:</strong> Clean up logs and temp files when over 85%
                                    </div>
                                </div>
                                @break

                            @case('database')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="text-sm text-blue-800">
                                        <strong>Connections:</strong> Monitor for connection pool exhaustion
                                    </div>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <div class="text-sm text-yellow-800">
                                        <strong>Query Time:</strong> Optimize queries taking over 100ms
                                    </div>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="text-sm text-green-800">
                                        <strong>Slow Queries:</strong> Add indexes and optimize WHERE clauses
                                    </div>
                                </div>
                                @break

                            @case('cache')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="text-sm text-blue-800">
                                        <strong>Hit Rate:</strong> Target 90%+ for optimal performance
                                    </div>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <div class="text-sm text-yellow-800">
                                        <strong>Memory:</strong> Increase cache size if hit rate is low
                                    </div>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="text-sm text-green-800">
                                        <strong>Evictions:</strong> High evictions indicate insufficient cache size
                                    </div>
                                </div>
                                @break

                            @case('queue')
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="text-sm text-blue-800">
                                        <strong>Pending Jobs:</strong> Scale workers if consistently high
                                    </div>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <div class="text-sm text-yellow-800">
                                        <strong>Failed Jobs:</strong> Review and retry failed jobs regularly
                                    </div>
                                </div>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <div class="text-sm text-green-800">
                                        <strong>Processing:</strong> Optimize job logic to reduce execution time
                                    </div>
                                </div>
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Data Table -->
        @if(isset($data['recent']) && count($data['recent']) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-table text-blue-500 mr-2"></i>
                    Recent Data Points
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            @switch($metric)
                                @case('system')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPU %</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Memory %</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disk %</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Load Avg</th>
                                    @break
                                @case('database')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Connections</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Query Time (ms)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slow Queries</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size (MB)</th>
                                    @break
                                @case('cache')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hit Rate %</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Memory (MB)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keys</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evictions</th>
                                    @break
                                @case('queue')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processed</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Time (ms)</th>
                                    @break
                            @endswitch
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach(array_slice($data['recent'], 0, 20) as $point)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($point['timestamp'])->format('M j, H:i') }}
                            </td>
                            @switch($metric)
                                @case('system')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['cpu_usage'], 1) }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['memory_usage'], 1) }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['disk_usage'], 1) }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ implode(', ', array_map(fn($load) => number_format($load, 2), json_decode($point['load_average'], true) ?? [])) }}</td>
                                    @break
                                @case('database')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $point['database_connections'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['avg_query_time'], 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $point['slow_queries'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['database_size'], 1) }}</td>
                                    @break
                                @case('cache')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['cache_hit_rate'], 1) }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['cache_memory_usage'], 1) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['cache_keys_count']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $point['cache_evictions'] }}</td>
                                    @break
                                @case('queue')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $point['pending_jobs'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $point['failed_jobs'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $point['processed_jobs'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($point['avg_processing_time'], 2) }}</td>
                                    @break
                            @endswitch
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = match($point['health_status']) {
                                        'good' => 'bg-green-100 text-green-800',
                                        'warning' => 'bg-yellow-100 text-yellow-800',
                                        default => 'bg-red-100 text-red-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                    {{ ucfirst($point['health_status']) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart
    initializeDetailChart();
    
    // Period filter
    document.getElementById('periodFilter').addEventListener('change', function() {
        window.location.href = `{{ route('admin.performance.show', $metric) }}?period=${this.value}`;
    });
});

function initializeDetailChart() {
    const ctx = document.getElementById('detailChart').getContext('2d');
    const data = @json($data);
    const metric = '{{ $metric }}';
    
    let datasets = [];
    let labels = [];
    
    if (data.historical && data.historical.length > 0) {
        labels = data.historical.map(point => point.time);
        
        switch (metric) {
            case 'system':
                datasets = [{
                    label: 'CPU Usage (%)',
                    data: data.historical.map(point => point.cpu_usage),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Memory Usage (%)',
                    data: data.historical.map(point => point.memory_usage),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Disk Usage (%)',
                    data: data.historical.map(point => point.disk_usage),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                }];
                break;
                
            case 'database':
                datasets = [{
                    label: 'Active Connections',
                    data: data.historical.map(point => point.connections),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Avg Query Time (ms)',
                    data: data.historical.map(point => point.avg_query_time),
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }];
                break;
                
            case 'cache':
                datasets = [{
                    label: 'Hit Rate (%)',
                    data: data.historical.map(point => point.hit_rate),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Memory Usage (MB)',
                    data: data.historical.map(point => point.memory_usage),
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }];
                break;
                
            case 'queue':
                datasets = [{
                    label: 'Pending Jobs',
                    data: data.historical.map(point => point.pending_jobs),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Failed Jobs',
                    data: data.historical.map(point => point.failed_jobs),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }];
                break;
        }
    }
    
    const config = {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Primary Metric'
                    }
                }
            }
        }
    };
    
    // Add second y-axis for dual-metric charts
    if ((metric === 'database' || metric === 'cache') && datasets.length > 1) {
        config.options.scales.y1 = {
            type: 'linear',
            display: true,
            position: 'right',
            beginAtZero: true,
            title: {
                display: true,
                text: 'Secondary Metric'
            },
            grid: {
                drawOnChartArea: false,
            }
        };
    }
    
    new Chart(ctx, config);
}
</script>
@endpush
