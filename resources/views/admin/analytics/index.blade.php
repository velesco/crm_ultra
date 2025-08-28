@extends('layouts.app')

@section('title', 'Business Analytics Dashboard')

@section('styles')
<style>
    .analytics-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 1.5rem;
        color: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .analytics-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30px, -30px);
    }

    .analytics-card.revenue {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .analytics-card.engagement {
        background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
    }

    .analytics-card.growth {
        background: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
    }

    .analytics-card.conversion {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .metric-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: bold;
        color: #667eea;
        margin: 0.5rem 0;
    }

    .metric-label {
        color: #6b7280;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .date-filter-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .trend-indicator {
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        margin-top: 0.5rem;
    }

    .trend-up {
        color: #10b981;
    }

    .trend-down {
        color: #ef4444;
    }

    .analytics-section {
        margin-bottom: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
    }

    .quick-filter {
        display: flex;
        gap: 0.5rem;
    }

    .filter-btn {
        padding: 0.375rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background: white;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .filter-btn:hover {
        background: #f3f4f6;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-spinner {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        text-align: center;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .spinner {
        border: 4px solid #f3f4f6;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line text-primary me-2"></i>
                Business Analytics Dashboard
            </h1>
            <p class="text-muted mb-0">Comprehensive business intelligence and performance insights</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" onclick="exportAnalytics('overview')">
                <i class="fas fa-download me-1"></i> Export Data
            </button>
            <button class="btn btn-success" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="date-filter-card">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" value="{{ $startDate }}" onchange="updateDateRange()">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" value="{{ $endDate }}" onchange="updateDateRange()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quick Filters</label>
                        <div class="quick-filter">
                            <button class="filter-btn {{ $period == '7days' ? 'active' : '' }}" onclick="setQuickFilter('7days')">7 Days</button>
                            <button class="filter-btn {{ $period == '30days' ? 'active' : '' }}" onclick="setQuickFilter('30days')">30 Days</button>
                            <button class="filter-btn {{ $period == '90days' ? 'active' : '' }}" onclick="setQuickFilter('90days')">90 Days</button>
                            <button class="filter-btn {{ $period == '1year' ? 'active' : '' }}" onclick="setQuickFilter('1year')">1 Year</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="small text-muted">
                    Last Updated: <span id="last-updated">{{ now()->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Metrics -->
    <div class="analytics-section">
        <div class="section-header">
            <h2 class="section-title">Overview Metrics</h2>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="analytics-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h2 mb-0">{{ number_format($data['overview']['total_contacts']) }}</div>
                            <div class="small opacity-75">Total Contacts</div>
                            <div class="trend-indicator">
                                <i class="fas fa-arrow-up trend-up me-1"></i>
                                <span class="trend-up">+{{ number_format($data['growth']['contact_growth']) }}%</span>
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="analytics-card revenue">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h2 mb-0">${{ number_format($data['overview']['revenue'], 2) }}</div>
                            <div class="small opacity-75">Revenue</div>
                            <div class="trend-indicator">
                                <i class="fas fa-arrow-up trend-up me-1"></i>
                                <span class="trend-up">+12.5%</span>
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="analytics-card engagement">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h2 mb-0">{{ $data['engagement']['overall_engagement'] }}%</div>
                            <div class="small opacity-75">Engagement Rate</div>
                            <div class="trend-indicator">
                                <i class="fas fa-arrow-up trend-up me-1"></i>
                                <span class="trend-up">+3.2%</span>
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-heart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="analytics-card conversion">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h2 mb-0">{{ number_format($data['overview']['conversion_rate'], 1) }}%</div>
                            <div class="small opacity-75">Conversion Rate</div>
                            <div class="trend-indicator">
                                <i class="fas fa-arrow-up trend-up me-1"></i>
                                <span class="trend-up">+1.8%</span>
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="metric-card">
                <div class="metric-label">Total Campaigns</div>
                <div class="metric-value">{{ number_format($data['overview']['total_campaigns']) }}</div>
                <div class="small text-muted">This period</div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="metric-card">
                <div class="metric-label">Total Messages</div>
                <div class="metric-value">{{ number_format($data['overview']['total_messages']) }}</div>
                <div class="small text-muted">All channels</div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="metric-card">
                <div class="metric-label">Active Users</div>
                <div class="metric-value">{{ number_format($data['overview']['active_users']) }}</div>
                <div class="small text-muted">This period</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Growth Chart -->
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Contact Growth Trend</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateGrowthChart('daily')">Daily</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateGrowthChart('weekly')">Weekly</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateGrowthChart('monthly')">Monthly</button>
                    </div>
                </div>
                <canvas id="growthChart" height="300"></canvas>
            </div>
        </div>

        <!-- Channel Performance -->
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <h5 class="mb-3">Channel Performance</h5>
                <canvas id="channelChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="chart-container">
                <h5 class="mb-3">Engagement Metrics Overview</h5>
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="h4 text-primary">{{ $data['engagement']['email_open_rate'] }}%</div>
                        <div class="small text-muted">Email Open Rate</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="h4 text-success">{{ $data['engagement']['email_click_rate'] }}%</div>
                        <div class="small text-muted">Email Click Rate</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="h4 text-info">{{ $data['engagement']['sms_delivery_rate'] }}%</div>
                        <div class="small text-muted">SMS Delivery Rate</div>
                    </div>
                </div>
                <hr>
                <canvas id="engagementChart" height="200"></canvas>
            </div>
        </div>

        <!-- Top Segments -->
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h5 class="mb-3">Top Performing Segments</h5>
                <div class="segments-list">
                    @foreach($data['segments']->take(5) as $segment)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-bold">{{ $segment['name'] }}</div>
                            <div class="small text-muted">{{ number_format($segment['contacts']) }} contacts</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-success">{{ $segment['engagement'] }}% engagement</div>
                            <div class="small text-primary">{{ $segment['conversion'] }}% conversion</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- User Activity -->
    <div class="analytics-section">
        <div class="section-header">
            <h2 class="section-title">User Activity</h2>
            <a href="{{ route('admin.analytics.contacts') }}" class="btn btn-outline-primary">
                View Detailed Report <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="chart-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activities</th>
                            <th>Campaigns Created</th>
                            <th>Contacts Added</th>
                            <th>Last Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['users']->take(10) as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center text-white">
                                        {{ substr($user['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user['name'] }}</div>
                                        <div class="small text-muted">{{ $user['email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ number_format($user['activities']) }}</td>
                            <td>{{ number_format($user['campaigns_created']) }}</td>
                            <td>{{ number_format($user['contacts_created']) }}</td>
                            <td>
                                <span class="small text-muted">
                                    {{ $user['last_active'] ? \Carbon\Carbon::parse($user['last_active'])->diffForHumans() : 'Never' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <a href="{{ route('admin.analytics.revenue') }}" class="btn btn-outline-success w-100 py-3">
                <i class="fas fa-dollar-sign fa-2x mb-2 d-block"></i>
                <strong>Revenue Analytics</strong>
                <div class="small text-muted">Financial performance insights</div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('admin.analytics.campaigns') }}" class="btn btn-outline-primary w-100 py-3">
                <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                <strong>Campaign Analytics</strong>
                <div class="small text-muted">Campaign performance analysis</div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="{{ route('admin.analytics.contacts') }}" class="btn btn-outline-info w-100 py-3">
                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                <strong>Contact Analytics</strong>
                <div class="small text-muted">Contact lifecycle analysis</div>
            </a>
        </div>
        <div class="col-md-3 mb-3">
            <button class="btn btn-outline-warning w-100 py-3" onclick="openRealtimeModal()">
                <i class="fas fa-tachometer-alt fa-2x mb-2 d-block"></i>
                <strong>Real-time Data</strong>
                <div class="small text-muted">Live performance monitoring</div>
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="loading-spinner">
        <div class="spinner"></div>
        <div>Loading analytics data...</div>
    </div>
</div>

<!-- Real-time Data Modal -->
<div class="modal fade" id="realtimeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-broadcast-tower text-success me-2"></i>
                    Real-time Analytics
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="realtime-content">
                    <div class="text-center py-4">
                        <div class="spinner"></div>
                        <p>Loading real-time data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let growthChart, channelChart, engagementChart;
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        
        // Auto-refresh every 5 minutes
        setInterval(refreshDashboard, 300000);
    });

    function initializeCharts() {
        // Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        const growthData = @json($data['growth']['daily_growth']);
        
        growthChart = new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: Object.keys(growthData),
                datasets: [{
                    label: 'Contact Growth',
                    data: Object.values(growthData),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
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
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Channel Chart
        const channelCtx = document.getElementById('channelChart').getContext('2d');
        const channelData = @json($data['channels']);
        
        channelChart = new Chart(channelCtx, {
            type: 'doughnut',
            data: {
                labels: ['Email', 'SMS', 'WhatsApp'],
                datasets: [{
                    data: [
                        channelData.email.sent,
                        channelData.sms.sent,
                        channelData.whatsapp.sent
                    ],
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#fc466b'
                    ],
                    borderWidth: 0
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

        // Engagement Chart
        const engagementCtx = document.getElementById('engagementChart').getContext('2d');
        
        engagementChart = new Chart(engagementCtx, {
            type: 'bar',
            data: {
                labels: ['Email Open', 'Email Click', 'SMS Delivery', 'WhatsApp Response'],
                datasets: [{
                    data: [
                        {{ $data['engagement']['email_open_rate'] }},
                        {{ $data['engagement']['email_click_rate'] }},
                        {{ $data['engagement']['sms_delivery_rate'] }},
                        {{ $data['engagement']['whatsapp_response_rate'] }}
                    ],
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#11998e',
                        '#fc466b'
                    ],
                    borderRadius: 4,
                    borderSkipped: false,
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
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    function updateDateRange() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate) {
            showLoading();
            window.location.href = `{{ route('admin.analytics.index') }}?start_date=${startDate}&end_date=${endDate}`;
        }
    }

    function setQuickFilter(period) {
        showLoading();
        window.location.href = `{{ route('admin.analytics.index') }}?period=${period}`;
    }

    function updateGrowthChart(type) {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        showLoading();
        
        // Fetch new data based on type
        fetch(`{{ route('admin.analytics.index') }}?chart_type=${type}`)
            .then(response => response.json())
            .then(data => {
                growthChart.data.labels = Object.keys(data.growth[`${type}_growth`]);
                growthChart.data.datasets[0].data = Object.values(data.growth[`${type}_growth`]);
                growthChart.update();
                hideLoading();
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                showAlert('Error updating chart', 'error');
            });
    }

    function refreshDashboard() {
        showLoading();
        window.location.reload();
    }

    function exportAnalytics(type) {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        const url = `{{ route('admin.analytics.export') }}?type=${type}&start_date=${startDate}&end_date=${endDate}&format=csv`;
        window.open(url, '_blank');
    }

    function openRealtimeModal() {
        const modal = new bootstrap.Modal(document.getElementById('realtimeModal'));
        modal.show();
        
        // Load real-time data
        fetch('{{ route('admin.analytics.realtime') }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('realtime-content').innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="h3 text-primary">${data.active_campaigns}</div>
                                    <div class="small text-muted">Active Campaigns</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="h3 text-success">${data.online_users}</div>
                                    <div class="small text-muted">Online Users</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">System Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="badge bg-success">System Healthy</div>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('realtime-content').innerHTML = `
                    <div class="alert alert-danger">
                        Error loading real-time data. Please try again.
                    </div>
                `;
            });
    }

    function showLoading() {
        document.getElementById('loading-overlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loading-overlay').style.display = 'none';
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '10000';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
</script>
@endsection