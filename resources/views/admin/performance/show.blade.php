@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line me-2 text-primary"></i>
                {{ ucfirst($metric) }} Performance Details
            </h1>
            <p class="text-muted mb-0">Detailed metrics and analysis for {{ $period }}</p>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.performance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Dashboard
            </a>
            
            <select id="periodFilter" class="form-select" style="width: auto;">
                <option value="1h" {{ $period === '1h' ? 'selected' : '' }}>Last Hour</option>
                <option value="24h" {{ $period === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                <option value="7d" {{ $period === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30d" {{ $period === '30d' ? 'selected' : '' }}>Last 30 Days</option>
            </select>
        </div>
    </div>

    <!-- Metric Overview Cards -->
    <div class="row mb-4">
        @switch($metric)
            @case('system')
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-primary h2 mb-1">{{ number_format($data['current']['cpu_usage'] ?? 0, 1) }}%</div>
                            <h6 class="card-title">CPU Usage</h6>
                            <small class="text-muted">Current utilization</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-info h2 mb-1">{{ number_format($data['current']['memory_usage'] ?? 0, 1) }}%</div>
                            <h6 class="card-title">Memory Usage</h6>
                            <small class="text-muted">RAM utilization</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-warning h2 mb-1">{{ number_format($data['current']['disk_usage'] ?? 0, 1) }}%</div>
                            <h6 class="card-title">Disk Usage</h6>
                            <small class="text-muted">Storage utilization</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-success h2 mb-1">{{ implode(', ', array_map(fn($load) => number_format($load, 2), $data['current']['load_average'] ?? [0, 0, 0])) }}</div>
                            <h6 class="card-title">Load Average</h6>
                            <small class="text-muted">1m, 5m, 15m</small>
                        </div>
                    </div>
                </div>
                @break

            @case('database')
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-primary h2 mb-1">{{ $data['current']['connections'] ?? 0 }}</div>
                            <h6 class="card-title">Active Connections</h6>
                            <small class="text-muted">Current connections</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-info h2 mb-1">{{ number_format($data['current']['avg_query_time'] ?? 0, 2) }}ms</div>
                            <h6 class="card-title">Avg Query Time</h6>
                            <small class="text-muted">Response time</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-warning h2 mb-1">{{ $data['current']['slow_queries'] ?? 0 }}</div>
                            <h6 class="card-title">Slow Queries</h6>
                            <small class="text-muted">Need optimization</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-success h2 mb-1">{{ number_format($data['current']['database_size'] ?? 0, 1) }}MB</div>
                            <h6 class="card-title">Database Size</h6>
                            <small class="text-muted">Total size</small>
                        </div>
                    </div>
                </div>
                @break

            @case('cache')
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-primary h2 mb-1">{{ number_format($data['current']['hit_rate'] ?? 0, 1) }}%</div>
                            <h6 class="card-title">Hit Rate</h6>
                            <small class="text-muted">Cache efficiency</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-info h2 mb-1">{{ number_format($data['current']['memory_usage'] ?? 0, 1) }}MB</div>
                            <h6 class="card-title">Memory Usage</h6>
                            <small class="text-muted">Cache size</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-success h2 mb-1">{{ number_format($data['current']['keys_count'] ?? 0) }}</div>
                            <h6 class="card-title">Keys Stored</h6>
                            <small class="text-muted">Active keys</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-warning h2 mb-1">{{ $data['current']['evictions'] ?? 0 }}</div>
                            <h6 class="card-title">Evictions</h6>
                            <small class="text-muted">Keys removed</small>
                        </div>
                    </div>
                </div>
                @break

            @case('queue')
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-warning h2 mb-1">{{ $data['current']['pending_jobs'] ?? 0 }}</div>
                            <h6 class="card-title">Pending Jobs</h6>
                            <small class="text-muted">Waiting to process</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-danger h2 mb-1">{{ $data['current']['failed_jobs'] ?? 0 }}</div>
                            <h6 class="card-title">Failed Jobs</h6>
                            <small class="text-muted">Need attention</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-success h2 mb-1">{{ $data['current']['processed_jobs'] ?? 0 }}</div>
                            <h6 class="card-title">Processed Jobs</h6>
                            <small class="text-muted">Completed successfully</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-info h2 mb-1">{{ number_format($data['current']['avg_processing_time'] ?? 0, 2) }}ms</div>
                            <h6 class="card-title">Avg Processing Time</h6>
                            <small class="text-muted">Per job</small>
                        </div>
                    </div>
                </div>
                @break
        @endswitch
    </div>

    <!-- Historical Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area me-2"></i>
                        Historical Trends - {{ ucfirst($metric) }}
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="detailChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Table -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Statistics Summary
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td class="text-muted">Average</td>
                                <td class="fw-bold">{{ number_format($data['statistics']['average'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Maximum</td>
                                <td class="fw-bold text-danger">{{ number_format($data['statistics']['max'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Minimum</td>
                                <td class="fw-bold text-success">{{ number_format($data['statistics']['min'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Data Points</td>
                                <td class="fw-bold">{{ number_format($data['statistics']['count'] ?? 0) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Trend</td>
                                <td>
                                    @if(($data['statistics']['trend'] ?? 0) > 0)
                                        <span class="badge bg-success">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            Improving
                                        </span>
                                    @elseif(($data['statistics']['trend'] ?? 0) < 0)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-arrow-down me-1"></i>
                                            Declining
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-minus me-1"></i>
                                            Stable
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Recommendations
                    </h6>
                </div>
                <div class="card-body">
                    @switch($metric)
                        @case('system')
                            <div class="alert alert-info mb-2">
                                <small><strong>CPU:</strong> Keep under 80% for optimal performance</small>
                            </div>
                            <div class="alert alert-warning mb-2">
                                <small><strong>Memory:</strong> Monitor for memory leaks if consistently high</small>
                            </div>
                            <div class="alert alert-success mb-0">
                                <small><strong>Disk:</strong> Clean up logs and temp files when over 85%</small>
                            </div>
                            @break

                        @case('database')
                            <div class="alert alert-info mb-2">
                                <small><strong>Connections:</strong> Monitor for connection pool exhaustion</small>
                            </div>
                            <div class="alert alert-warning mb-2">
                                <small><strong>Query Time:</strong> Optimize queries taking over 100ms</small>
                            </div>
                            <div class="alert alert-success mb-0">
                                <small><strong>Slow Queries:</strong> Add indexes and optimize WHERE clauses</small>
                            </div>
                            @break

                        @case('cache')
                            <div class="alert alert-info mb-2">
                                <small><strong>Hit Rate:</strong> Target 90%+ for optimal performance</small>
                            </div>
                            <div class="alert alert-warning mb-2">
                                <small><strong>Memory:</strong> Increase cache size if hit rate is low</small>
                            </div>
                            <div class="alert alert-success mb-0">
                                <small><strong>Evictions:</strong> High evictions indicate insufficient cache size</small>
                            </div>
                            @break

                        @case('queue')
                            <div class="alert alert-info mb-2">
                                <small><strong>Pending Jobs:</strong> Scale workers if consistently high</small>
                            </div>
                            <div class="alert alert-warning mb-2">
                                <small><strong>Failed Jobs:</strong> Review and retry failed jobs regularly</small>
                            </div>
                            <div class="alert alert-success mb-0">
                                <small><strong>Processing:</strong> Optimize job logic to reduce execution time</small>
                            </div>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data Table -->
    @if(isset($data['recent']) && count($data['recent']) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>
                        Recent Data Points
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    @switch($metric)
                                        @case('system')
                                            <th>CPU %</th>
                                            <th>Memory %</th>
                                            <th>Disk %</th>
                                            <th>Load Avg</th>
                                            @break
                                        @case('database')
                                            <th>Connections</th>
                                            <th>Query Time (ms)</th>
                                            <th>Slow Queries</th>
                                            <th>Size (MB)</th>
                                            @break
                                        @case('cache')
                                            <th>Hit Rate %</th>
                                            <th>Memory (MB)</th>
                                            <th>Keys</th>
                                            <th>Evictions</th>
                                            @break
                                        @case('queue')
                                            <th>Pending</th>
                                            <th>Failed</th>
                                            <th>Processed</th>
                                            <th>Avg Time (ms)</th>
                                            @break
                                    @endswitch
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($data['recent'], 0, 20) as $point)
                                <tr>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($point['timestamp'])->format('M j, H:i') }}</small>
                                    </td>
                                    @switch($metric)
                                        @case('system')
                                            <td>{{ number_format($point['cpu_usage'], 1) }}%</td>
                                            <td>{{ number_format($point['memory_usage'], 1) }}%</td>
                                            <td>{{ number_format($point['disk_usage'], 1) }}%</td>
                                            <td><small>{{ implode(', ', array_map(fn($load) => number_format($load, 2), json_decode($point['load_average'], true) ?? [])) }}</small></td>
                                            @break
                                        @case('database')
                                            <td>{{ $point['database_connections'] }}</td>
                                            <td>{{ number_format($point['avg_query_time'], 2) }}</td>
                                            <td>{{ $point['slow_queries'] }}</td>
                                            <td>{{ number_format($point['database_size'], 1) }}</td>
                                            @break
                                        @case('cache')
                                            <td>{{ number_format($point['cache_hit_rate'], 1) }}%</td>
                                            <td>{{ number_format($point['cache_memory_usage'], 1) }}</td>
                                            <td>{{ number_format($point['cache_keys_count']) }}</td>
                                            <td>{{ $point['cache_evictions'] }}</td>
                                            @break
                                        @case('queue')
                                            <td>{{ $point['pending_jobs'] }}</td>
                                            <td>{{ $point['failed_jobs'] }}</td>
                                            <td>{{ $point['processed_jobs'] }}</td>
                                            <td>{{ number_format($point['avg_processing_time'], 2) }}</td>
                                            @break
                                    @endswitch
                                    <td>
                                        <span class="badge bg-{{ $point['health_status'] === 'good' ? 'success' : ($point['health_status'] === 'warning' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($point['health_status']) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
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
