@extends('layouts.app')

@section('title', 'Admin Dashboard - System Overview')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-tachometer-alt text-primary me-2"></i>
                        Admin Dashboard
                    </h1>
                    <p class="text-muted mb-0">System overview and administration tools</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" id="refreshStats">
                        <i class="fas fa-sync-alt me-1"></i>
                        Refresh
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            Quick Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" id="toggleMaintenance">
                                <i class="fas fa-tools me-2"></i>Toggle Maintenance
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="clearCaches">
                                <i class="fas fa-trash me-2"></i>Clear Caches
                            </a></li>
                            <li><a class="dropdown-item" href="#" id="optimizeSystem">
                                <i class="fas fa-rocket me-2"></i>Optimize System
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="exportData">
                                <i class="fas fa-download me-2"></i>Export System Data
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    @if($alertsCount > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>System Alerts:</strong> 
                    <span class="ms-2">{{ $alertsCount }} system alerts require attention.</span>
                    <a href="#systemAlerts" class="btn btn-sm btn-outline-warning ms-auto me-2">View Details</a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- System Overview Cards -->
    <div class="row mb-4">
        <!-- Users Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['users']['total'] ?? 0) }}
                            </div>
                            <div class="text-xs text-success mt-1">
                                <i class="fas fa-plus me-1"></i>
                                {{ $stats['users']['new_today'] ?? 0 }} today
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacts Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Contacts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['contacts']['total'] ?? 0) }}
                            </div>
                            <div class="text-xs text-success mt-1">
                                <i class="fas fa-plus me-1"></i>
                                {{ $stats['contacts']['new_today'] ?? 0 }} today
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-address-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaigns Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Email Campaigns
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['campaigns']['total'] ?? 0) }}
                            </div>
                            <div class="text-xs text-info mt-1">
                                <i class="fas fa-paper-plane me-1"></i>
                                {{ $stats['campaigns']['sent_today'] ?? 0 }} sent today
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communications Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Communications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['communications']['total'] ?? 0) }}
                            </div>
                            <div class="text-xs text-warning mt-1">
                                <i class="fas fa-comments me-1"></i>
                                {{ $stats['communications']['today'] ?? 0 }} today
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="row mb-4">
        <!-- User Growth Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">User Growth (Last 30 Days)</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="#" id="exportChart">Export Chart</a>
                            <a class="dropdown-item" href="#" id="refreshChart">Refresh Data</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="userGrowthChart" style="height: 320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Health</h6>
                </div>
                <div class="card-body">
                    <div id="systemHealthContainer">
                        <!-- Database Health -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="bg-{{ $systemHealth['database']['status'] === 'healthy' ? 'success' : ($systemHealth['database']['status'] === 'warning' ? 'warning' : 'danger') }} rounded-circle" style="width: 12px; height: 12px;"></div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-sm font-weight-bold text-gray-800">Database</div>
                                <div class="text-xs text-muted">{{ $systemHealth['database']['message'] ?? 'N/A' }}</div>
                            </div>
                            <div class="text-xs text-muted">
                                {{ $systemHealth['database']['response_time'] ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Cache Health -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="bg-{{ $systemHealth['cache']['status'] === 'healthy' ? 'success' : ($systemHealth['cache']['status'] === 'warning' ? 'warning' : 'danger') }} rounded-circle" style="width: 12px; height: 12px;"></div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-sm font-weight-bold text-gray-800">Cache</div>
                                <div class="text-xs text-muted">{{ $systemHealth['cache']['message'] ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <!-- Queue Health -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="bg-{{ $systemHealth['queue']['status'] === 'healthy' ? 'success' : ($systemHealth['queue']['status'] === 'warning' ? 'warning' : 'danger') }} rounded-circle" style="width: 12px; height: 12px;"></div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-sm font-weight-bold text-gray-800">Queue</div>
                                <div class="text-xs text-muted">{{ $systemHealth['queue']['message'] ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <!-- Storage Health -->
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="bg-{{ $systemHealth['storage']['status'] === 'healthy' ? 'success' : ($systemHealth['storage']['status'] === 'warning' ? 'warning' : 'danger') }} rounded-circle" style="width: 12px; height: 12px;"></div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-sm font-weight-bold text-gray-800">Storage</div>
                                <div class="text-xs text-muted">{{ $systemHealth['storage']['message'] ?? 'N/A' }}</div>
                            </div>
                            <div class="text-xs text-muted">
                                {{ $systemHealth['storage']['usage_percent'] ?? '0' }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity and Top Users -->
    <div class="row mb-4">
        <!-- Recent Activity -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent System Activity</h6>
                </div>
                <div class="card-body">
                    <div id="recentActivityContainer">
                        @forelse($recentActivity as $activity)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="me-3">
                                <div class="bg-{{ $activity['color'] }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                    <i class="fas fa-{{ $activity['icon'] }} fa-sm"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-sm font-weight-bold text-gray-800">{{ $activity['title'] }}</div>
                                <div class="text-xs text-muted">{{ $activity['description'] }}</div>
                                <div class="text-xs text-primary mt-1">by {{ $activity['user'] }}</div>
                            </div>
                            <div class="text-xs text-muted">
                                {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <p>No recent activity</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Users -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Active Users</h6>
                </div>
                <div class="card-body">
                    @forelse($topUsers as $user)
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                {{ strtoupper(substr($user['name'], 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-sm font-weight-bold text-gray-800">{{ $user['name'] }}</div>
                            <div class="text-xs text-muted">{{ $user['email'] }}</div>
                        </div>
                        <div class="text-xs text-muted">
                            <div class="badge bg-primary">{{ $user['total_activity'] }} actions</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p>No active users data</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- System Usage Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Communication Trends (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="communicationTrendsChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Storage Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Storage Usage</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-gray-800">{{ $stats['storage']['database_size'] ?? '0 MB' }}</div>
                                <div class="text-sm text-muted">Database</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-gray-800">{{ $stats['storage']['uploads_size'] ?? '0 MB' }}</div>
                                <div class="text-sm text-muted">Uploads</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-gray-800">{{ $stats['storage']['log_files_size'] ?? '0 MB' }}</div>
                                <div class="text-sm text-muted">Log Files</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-gray-800">
                                    {{ round(($stats['performance']['memory_usage'] ?? 0) / 1024 / 1024, 1) }} MB
                                </div>
                                <div class="text-sm text-muted">Memory Usage</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <div class="mt-2">Processing...</div>
            </div>
        </div>
    </div>
</div>

<!-- Export Data Modal -->
<div class="modal fade" id="exportDataModal" tabindex="-1" role="dialog" aria-labelledby="exportDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportDataModalLabel">Export System Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportDataForm">
                    <div class="mb-3">
                        <label for="exportType" class="form-label">Data Type</label>
                        <select class="form-select" id="exportType" name="type" required>
                            <option value="users">Users</option>
                            <option value="contacts">Contacts</option>
                            <option value="campaigns">Email Campaigns</option>
                            <option value="messages">Messages</option>
                            <option value="all">All Data</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">Format</label>
                        <select class="form-select" id="exportFormat" name="format">
                            <option value="csv">CSV</option>
                            <option value="xlsx">Excel</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateFrom" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="dateFrom" name="date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dateTo" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="dateTo" name="date_to">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="startExport">Start Export</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize charts
    initUserGrowthChart();
    initCommunicationTrendsChart();

    // Refresh stats button
    $('#refreshStats').on('click', function() {
        location.reload();
    });

    // Toggle maintenance mode
    $('#toggleMaintenance').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to toggle maintenance mode?')) {
            $('#loadingModal').modal('show');
            
            $.ajax({
                url: '{{ route("admin.toggle-maintenance") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#loadingModal').modal('hide');
                    alert(response.message);
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
                }
            });
        }
    });

    // Clear caches
    $('#clearCaches').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to clear all caches?')) {
            $('#loadingModal').modal('show');
            
            $.ajax({
                url: '{{ route("admin.clear-caches") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#loadingModal').modal('hide');
                    alert(response.message);
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
                }
            });
        }
    });

    // Optimize system
    $('#optimizeSystem').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to optimize the system?')) {
            $('#loadingModal').modal('show');
            
            $.ajax({
                url: '{{ route("admin.optimize") }}',
                type: 'POST',
                data: {
                    action: 'all'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#loadingModal').modal('hide');
                    alert(response.message);
                },
                error: function(xhr) {
                    $('#loadingModal').modal('hide');
                    alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
                }
            });
        }
    });

    // Export data
    $('#exportData').on('click', function(e) {
        e.preventDefault();
        $('#exportDataModal').modal('show');
    });

    // Start export
    $('#startExport').on('click', function() {
        const formData = new FormData(document.getElementById('exportDataForm'));
        
        $.ajax({
            url: '{{ route("admin.export-data") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#exportDataModal').modal('hide');
                if (response.success) {
                    alert('Export completed successfully!');
                    // Handle file download if needed
                } else {
                    alert('Export failed: ' + response.message);
                }
            },
            error: function(xhr) {
                $('#exportDataModal').modal('hide');
                alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
            }
        });
    });

    function initUserGrowthChart() {
        const ctx = document.getElementById('userGrowthChart').getContext('2d');
        const chartData = @json($chartData['users_growth'] ?? []);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(item => item.formatted_date),
                datasets: [{
                    label: 'New Users',
                    data: chartData.map(item => item.count),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    function initCommunicationTrendsChart() {
        const ctx = document.getElementById('communicationTrendsChart').getContext('2d');
        const chartData = @json($chartData['system_usage'] ?? []);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(item => item.formatted_date),
                datasets: [
                    {
                        label: 'Emails',
                        data: chartData.map(item => item.emails),
                        backgroundColor: '#4e73df'
                    },
                    {
                        label: 'SMS',
                        data: chartData.map(item => item.sms),
                        backgroundColor: '#1cc88a'
                    },
                    {
                        label: 'WhatsApp',
                        data: chartData.map(item => item.whatsapp),
                        backgroundColor: '#36b9cc'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: true
                    },
                    x: {
                        stacked: true
                    }
                }
            }
        });
    }
});
</script>
@endsection
