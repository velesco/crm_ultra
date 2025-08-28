@extends('layouts.app')

@section('title', 'Revenue Analytics')

@section('styles')
<style>
    .revenue-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .revenue-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 120px;
        height: 120px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(40px, -40px);
    }

    .revenue-card.profit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .revenue-card.growth {
        background: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
    }

    .revenue-card.forecast {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .kpi-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
        border-left: 4px solid #11998e;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: bold;
        color: #11998e;
        margin-bottom: 0.5rem;
    }

    .kpi-label {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .kpi-change {
        font-size: 0.75rem;
        display: flex;
        align-items: center;
    }

    .trend-up {
        color: #10b981;
    }

    .trend-down {
        color: #ef4444;
    }

    .forecast-chart {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 1rem;
        background: #f9fafb;
    }

    .revenue-breakdown {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .revenue-source {
        display: flex;
        justify-content: between;
        align-items: center;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #11998e;
    }

    .progress-bar-custom {
        height: 6px;
        border-radius: 3px;
        background: #e5e7eb;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, #11998e, #38ef7d);
    }

    .metric-comparison {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    .comparison-item {
        flex: 1;
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.analytics.index') }}">Analytics</a></li>
                    <li class="breadcrumb-item active">Revenue Analytics</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-dollar-sign text-success me-2"></i>
                Revenue Analytics
            </h1>
            <p class="text-muted mb-0">Financial performance insights and revenue tracking</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="exportRevenue()">
                <i class="fas fa-download me-1"></i> Export Report
            </button>
            <button class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quick Filters</label>
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('30days')">30D</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('90days')">90D</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setDateRange('1year')">1Y</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="updateDateRange()">
                        <i class="fas fa-search me-1"></i> Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="revenue-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">$125,480</div>
                        <div class="small opacity-75">Total Revenue</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+15.3%</span>
                            <span class="ms-2 opacity-75">vs last period</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="revenue-card profit">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">$89,340</div>
                        <div class="small opacity-75">Gross Profit</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+12.8%</span>
                            <span class="ms-2 opacity-75">71.2% margin</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-coins fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="revenue-card growth">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">$4,185</div>
                        <div class="small opacity-75">Avg. Revenue/Customer</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+8.4%</span>
                            <span class="ms-2 opacity-75">improvement</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-user-dollar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="revenue-card forecast">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">$145,200</div>
                        <div class="small opacity-75">30-Day Forecast</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+16.7%</span>
                            <span class="ms-2 opacity-75">projected growth</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-crystal-ball fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trends Chart -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Revenue Trends</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateRevenueChart('daily')">Daily</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateRevenueChart('weekly')">Weekly</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateRevenueChart('monthly')">Monthly</button>
                    </div>
                </div>
                <canvas id="revenueChart" height="400"></canvas>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="chart-container">
                <h5 class="mb-3">Key Performance Indicators</h5>
                <div class="kpi-card">
                    <div class="kpi-value">32.4%</div>
                    <div class="kpi-label">Revenue Growth Rate</div>
                    <div class="kpi-change trend-up">
                        <i class="fas fa-arrow-up me-1"></i>
                        +5.2% from last quarter
                    </div>
                </div>
                
                <div class="kpi-card">
                    <div class="kpi-value">$1,847</div>
                    <div class="kpi-label">Customer Lifetime Value</div>
                    <div class="kpi-change trend-up">
                        <i class="fas fa-arrow-up me-1"></i>
                        +11.3% improvement
                    </div>
                </div>
                
                <div class="kpi-card">
                    <div class="kpi-value">2.8x</div>
                    <div class="kpi-label">Return on Ad Spend</div>
                    <div class="kpi-change trend-down">
                        <i class="fas fa-arrow-down me-1"></i>
                        -0.2x from target
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Channel and Segment -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">Revenue by Channel</h5>
                <canvas id="channelRevenueChart" height="300"></canvas>
                
                <div class="metric-comparison mt-3">
                    <div class="comparison-item">
                        <div class="h5 text-primary">$67,240</div>
                        <div class="small text-muted">Email Marketing</div>
                        <div class="small text-success">+18.3%</div>
                    </div>
                    <div class="comparison-item">
                        <div class="h5 text-info">$32,890</div>
                        <div class="small text-muted">SMS Campaigns</div>
                        <div class="small text-success">+12.7%</div>
                    </div>
                    <div class="comparison-item">
                        <div class="h5 text-warning">$25,350</div>
                        <div class="small text-muted">WhatsApp</div>
                        <div class="small text-success">+22.1%</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">Revenue by Customer Segment</h5>
                <canvas id="segmentRevenueChart" height="300"></canvas>
                
                <div class="mt-3">
                    <div class="revenue-source">
                        <div>
                            <div class="fw-bold">Enterprise Clients</div>
                            <div class="small text-muted">$78,450 • 62.5% of total</div>
                        </div>
                        <div class="text-end">
                            <div class="text-success">+23.4%</div>
                        </div>
                    </div>
                    
                    <div class="revenue-source">
                        <div>
                            <div class="fw-bold">SMB Customers</div>
                            <div class="small text-muted">$31,780 • 25.3% of total</div>
                        </div>
                        <div class="text-end">
                            <div class="text-success">+15.2%</div>
                        </div>
                    </div>
                    
                    <div class="revenue-source">
                        <div>
                            <div class="fw-bold">Individual Users</div>
                            <div class="small text-muted">$15,250 • 12.2% of total</div>
                        </div>
                        <div class="text-end">
                            <div class="text-success">+8.7%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Forecast -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Revenue Forecast (Next 90 Days)</h5>
                    <div class="text-muted small">
                        Based on historical data and current trends
                    </div>
                </div>
                <div class="forecast-chart">
                    <canvas id="forecastChart" height="250"></canvas>
                    
                    <div class="row mt-3">
                        <div class="col-md-3 text-center">
                            <div class="h5 text-success">$48,600</div>
                            <div class="small text-muted">Next 30 Days</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h5 text-info">$97,200</div>
                            <div class="small text-muted">Next 60 Days</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h5 text-primary">$145,800</div>
                            <div class="small text-muted">Next 90 Days</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="h5 text-warning">85%</div>
                            <div class="small text-muted">Confidence Level</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown Table -->
    <div class="revenue-breakdown">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Detailed Revenue Breakdown</h5>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary active" onclick="showBreakdown('product')">By Product</button>
                <button type="button" class="btn btn-outline-secondary" onclick="showBreakdown('region')">By Region</button>
                <button type="button" class="btn btn-outline-secondary" onclick="showBreakdown('time')">By Time</button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product/Service</th>
                        <th>Revenue</th>
                        <th>% of Total</th>
                        <th>Growth</th>
                        <th>Units Sold</th>
                        <th>Avg. Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                                Email Marketing Pro
                            </div>
                        </td>
                        <td class="fw-bold">$67,240</td>
                        <td>53.6%</td>
                        <td class="text-success">+18.3%</td>
                        <td>134</td>
                        <td>$502</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-info rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                                SMS Campaign Plus
                            </div>
                        </td>
                        <td class="fw-bold">$32,890</td>
                        <td>26.2%</td>
                        <td class="text-success">+12.7%</td>
                        <td>89</td>
                        <td>$369</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                                WhatsApp Business
                            </div>
                        </td>
                        <td class="fw-bold">$25,350</td>
                        <td>20.2%</td>
                        <td class="text-success">+22.1%</td>
                        <td>67</td>
                        <td>$378</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let revenueChart, channelRevenueChart, segmentRevenueChart, forecastChart;
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    function initializeCharts() {
        // Revenue Trends Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
                datasets: [{
                    label: 'Revenue',
                    data: [12400, 15600, 18200, 21800, 19400, 23600, 25800, 28200],
                    borderColor: '#11998e',
                    backgroundColor: 'rgba(17, 153, 142, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Target',
                    data: [15000, 16000, 17000, 18000, 19000, 20000, 21000, 22000],
                    borderColor: '#ff6b6b',
                    borderDash: [5, 5],
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Channel Revenue Chart
        const channelCtx = document.getElementById('channelRevenueChart').getContext('2d');
        channelRevenueChart = new Chart(channelCtx, {
            type: 'doughnut',
            data: {
                labels: ['Email', 'SMS', 'WhatsApp'],
                datasets: [{
                    data: [67240, 32890, 25350],
                    backgroundColor: ['#667eea', '#11998e', '#fc466b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: $${value.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Segment Revenue Chart
        const segmentCtx = document.getElementById('segmentRevenueChart').getContext('2d');
        segmentRevenueChart = new Chart(segmentCtx, {
            type: 'bar',
            data: {
                labels: ['Enterprise', 'SMB', 'Individual'],
                datasets: [{
                    data: [78450, 31780, 15250],
                    backgroundColor: ['#667eea', '#11998e', '#fc466b'],
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
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Forecast Chart
        const forecastCtx = document.getElementById('forecastChart').getContext('2d');
        forecastChart = new Chart(forecastCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8', 'Week 9', 'Week 10', 'Week 11', 'Week 12'],
                datasets: [{
                    label: 'Historical',
                    data: [12400, 13200, 14800, 15600, 16200, 17800, 18400, 19200, null, null, null, null],
                    borderColor: '#11998e',
                    backgroundColor: 'rgba(17, 153, 142, 0.1)',
                    borderWidth: 3,
                    fill: true
                }, {
                    label: 'Forecast',
                    data: [null, null, null, null, null, null, null, 19200, 20400, 21600, 22800, 24000],
                    borderColor: '#ff6b6b',
                    backgroundColor: 'rgba(255, 107, 107, 0.1)',
                    borderDash: [5, 5],
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
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
            window.location.href = `{{ route('admin.analytics.revenue') }}?start_date=${startDate}&end_date=${endDate}`;
        }
    }

    function setDateRange(period) {
        window.location.href = `{{ route('admin.analytics.revenue') }}?period=${period}`;
    }

    function updateRevenueChart(type) {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        // Here you would typically fetch new data from the server
        // For now, we'll just update the chart with sample data
        const sampleData = {
            daily: [1200, 1400, 1600, 1800, 1500, 2000, 2200, 1900],
            weekly: [8400, 9200, 10800, 11600, 10200, 13600, 14800, 13200],
            monthly: [35000, 38000, 42000, 45000, 41000, 48000, 52000, 49000]
        };
        
        revenueChart.data.datasets[0].data = sampleData[type];
        revenueChart.update();
    }

    function showBreakdown(type) {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        // Here you would update the table based on the breakdown type
        // For demo purposes, we'll just show an alert
        console.log('Showing breakdown by:', type);
    }

    function exportRevenue() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        const url = `{{ route('admin.analytics.export') }}?type=revenue&start_date=${startDate}&end_date=${endDate}&format=csv`;
        window.open(url, '_blank');
    }

    function refreshData() {
        window.location.reload();
    }
</script>
@endsection