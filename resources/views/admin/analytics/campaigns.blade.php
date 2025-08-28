@extends('layouts.app')

@section('title', 'Campaign Analytics')

@section('styles')
<style>
    .campaign-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .campaign-card::before {
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

    .campaign-card.engagement {
        background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
    }

    .campaign-card.conversion {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .campaign-card.roi {
        background: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
    }

    .performance-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .performance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .campaign-list-item {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .campaign-list-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .campaign-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
    }

    .status-active {
        background: #dcfce7;
        color: #166534;
    }

    .status-paused {
        background: #fef3c7;
        color: #92400e;
    }

    .status-completed {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-draft {
        background: #f3f4f6;
        color: #374151;
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #667eea;
    }

    .metric-label {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .metric-change {
        font-size: 0.75rem;
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

    .channel-filter {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .filter-chip {
        display: inline-block;
        padding: 0.5rem 1rem;
        margin: 0.25rem;
        background: #f3f4f6;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .filter-chip.active {
        background: #667eea;
        color: white;
    }

    .filter-chip:hover {
        border-color: #667eea;
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .performance-comparison {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .comparison-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background: white;
        border-radius: 6px;
    }

    .progress-wrapper {
        position: relative;
        background: #e5e7eb;
        height: 8px;
        border-radius: 4px;
        margin-top: 0.5rem;
        overflow: hidden;
    }

    .progress-bar-animated {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 4px;
        transition: width 0.5s ease;
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
                    <li class="breadcrumb-item active">Campaign Analytics</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-bullhorn text-primary me-2"></i>
                Campaign Analytics
            </h1>
            <p class="text-muted mb-0">Campaign performance analysis and optimization insights</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="exportCampaigns()">
                <i class="fas fa-download me-1"></i> Export Report
            </button>
            <button class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="channel-filter">
        <div class="row align-items-center">
            <div class="col-md-4">
                <label class="form-label">Date Range</label>
                <div class="input-group">
                    <input type="date" class="form-control" id="start_date" value="{{ $startDate }}">
                    <input type="date" class="form-control" id="end_date" value="{{ $endDate }}">
                    <button class="btn btn-outline-primary" onclick="updateDateRange()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-8">
                <label class="form-label">Channel Filter</label>
                <div>
                    <span class="filter-chip {{ $channel == 'all' ? 'active' : '' }}" onclick="setChannelFilter('all')">All Channels</span>
                    <span class="filter-chip {{ $channel == 'email' ? 'active' : '' }}" onclick="setChannelFilter('email')">Email</span>
                    <span class="filter-chip {{ $channel == 'sms' ? 'active' : '' }}" onclick="setChannelFilter('sms')">SMS</span>
                    <span class="filter-chip {{ $channel == 'whatsapp' ? 'active' : '' }}" onclick="setChannelFilter('whatsapp')">WhatsApp</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="campaign-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">47</div>
                        <div class="small opacity-75">Active Campaigns</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+8</span>
                            <span class="ms-2 opacity-75">this month</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-rocket fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="campaign-card engagement">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">34.7%</div>
                        <div class="small opacity-75">Avg. Engagement Rate</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+5.2%</span>
                            <span class="ms-2 opacity-75">improvement</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="campaign-card conversion">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">12.3%</div>
                        <div class="small opacity-75">Conversion Rate</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+2.8%</span>
                            <span class="ms-2 opacity-75">vs target</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="campaign-card roi">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h2 mb-0">4.2x</div>
                        <div class="small opacity-75">Return on Investment</div>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up trend-up me-1"></i>
                            <span class="trend-up">+0.7x</span>
                            <span class="ms-2 opacity-75">improved ROI</span>
                        </div>
                    </div>
                    <div class="text-white-50">
                        <i class="fas fa-coins fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Campaign Performance Trends</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="updatePerformanceChart('engagement')">Engagement</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updatePerformanceChart('conversion')">Conversion</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updatePerformanceChart('roi')">ROI</button>
                    </div>
                </div>
                <canvas id="performanceChart" height="400"></canvas>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="performance-card">
                <h6 class="mb-3">Channel Performance</h6>
                <canvas id="channelPerformanceChart" height="250"></canvas>
                
                <div class="performance-comparison">
                    <div class="comparison-item">
                        <div>
                            <div class="fw-bold">Email</div>
                            <div class="small text-muted">42.3% engagement</div>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-arrow-up"></i> 8.2%
                        </div>
                    </div>
                    <div class="comparison-item">
                        <div>
                            <div class="fw-bold">SMS</div>
                            <div class="small text-muted">38.7% engagement</div>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-arrow-up"></i> 5.4%
                        </div>
                    </div>
                    <div class="comparison-item">
                        <div>
                            <div class="fw-bold">WhatsApp</div>
                            <div class="small text-muted">31.2% engagement</div>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-arrow-up"></i> 12.1%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Metrics Grid -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="performance-card text-center">
                <div class="metric-label">Total Sends</div>
                <div class="metric-value">284,567</div>
                <div class="metric-change trend-up">
                    <i class="fas fa-arrow-up me-1"></i>
                    +18.3% vs last period
                </div>
                <div class="progress-wrapper">
                    <div class="progress-bar-animated" style="width: 85%"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="performance-card text-center">
                <div class="metric-label">Total Opens</div>
                <div class="metric-value">98,756</div>
                <div class="metric-change trend-up">
                    <i class="fas fa-arrow-up me-1"></i>
                    +12.7% vs last period
                </div>
                <div class="progress-wrapper">
                    <div class="progress-bar-animated" style="width: 68%"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="performance-card text-center">
                <div class="metric-label">Total Clicks</div>
                <div class="metric-value">34,892</div>
                <div class="metric-change trend-up">
                    <i class="fas fa-arrow-up me-1"></i>
                    +15.1% vs last period
                </div>
                <div class="progress-wrapper">
                    <div class="progress-bar-animated" style="width: 52%"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="performance-card text-center">
                <div class="metric-label">Conversions</div>
                <div class="metric-value">4,287</div>
                <div class="metric-change trend-up">
                    <i class="fas fa-arrow-up me-1"></i>
                    +22.4% vs last period
                </div>
                <div class="progress-wrapper">
                    <div class="progress-bar-animated" style="width: 35%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Cost Analysis -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">Cost Analysis</h5>
                <canvas id="costAnalysisChart" height="300"></canvas>
                
                <div class="row mt-3">
                    <div class="col-4 text-center">
                        <div class="h5 text-primary">$12,485</div>
                        <div class="small text-muted">Total Spend</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="h5 text-success">$0.44</div>
                        <div class="small text-muted">Cost per Click</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="h5 text-info">$2.91</div>
                        <div class="small text-muted">Cost per Conversion</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="chart-container">
                <h5 class="mb-3">Conversion Funnel</h5>
                <canvas id="funnelChart" height="300"></canvas>
                
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sent</span>
                        <span class="fw-bold">284,567</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Opened</span>
                        <span class="fw-bold">98,756 (34.7%)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Clicked</span>
                        <span class="fw-bold">34,892 (12.3%)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Converted</span>
                        <span class="fw-bold">4,287 (1.5%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Campaigns -->
    <div class="row">
        <div class="col-12">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Top Performing Campaigns</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" onclick="sortCampaigns('engagement')">By Engagement</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="sortCampaigns('conversion')">By Conversion</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="sortCampaigns('roi')">By ROI</button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <div class="campaign-list-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">Summer Sale Email Campaign</h6>
                                    <div class="small text-muted">
                                        <i class="fas fa-envelope me-1"></i> Email • Created on Aug 15, 2025
                                    </div>
                                </div>
                                <span class="campaign-status status-active">Active</span>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-primary">45.2%</div>
                                    <div class="small text-muted">Open Rate</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-success">18.7%</div>
                                    <div class="small text-muted">Click Rate</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-info">3.2%</div>
                                    <div class="small text-muted">Conversion</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-warning">5.8x</div>
                                    <div class="small text-muted">ROI</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-3">
                        <div class="campaign-list-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">Product Launch WhatsApp</h6>
                                    <div class="small text-muted">
                                        <i class="fab fa-whatsapp me-1"></i> WhatsApp • Created on Aug 20, 2025
                                    </div>
                                </div>
                                <span class="campaign-status status-completed">Completed</span>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-primary">89.3%</div>
                                    <div class="small text-muted">Delivery</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-success">23.4%</div>
                                    <div class="small text-muted">Response</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-info">2.8%</div>
                                    <div class="small text-muted">Conversion</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-warning">4.2x</div>
                                    <div class="small text-muted">ROI</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-3">
                        <div class="campaign-list-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">Flash Sale SMS Alert</h6>
                                    <div class="small text-muted">
                                        <i class="fas fa-sms me-1"></i> SMS • Created on Aug 22, 2025
                                    </div>
                                </div>
                                <span class="campaign-status status-active">Active</span>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-primary">96.7%</div>
                                    <div class="small text-muted">Delivery</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-success">15.2%</div>
                                    <div class="small text-muted">Click Rate</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-info">2.1%</div>
                                    <div class="small text-muted">Conversion</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-warning">3.9x</div>
                                    <div class="small text-muted">ROI</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 mb-3">
                        <div class="campaign-list-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">Newsletter Weekly Digest</h6>
                                    <div class="small text-muted">
                                        <i class="fas fa-envelope me-1"></i> Email • Created on Aug 25, 2025
                                    </div>
                                </div>
                                <span class="campaign-status status-paused">Paused</span>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-primary">38.9%</div>
                                    <div class="small text-muted">Open Rate</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-success">12.3%</div>
                                    <div class="small text-muted">Click Rate</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-info">1.8%</div>
                                    <div class="small text-muted">Conversion</div>
                                </div>
                                <div class="col-3 text-center">
                                    <div class="fw-bold text-warning">2.1x</div>
                                    <div class="small text-muted">ROI</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <button class="btn btn-outline-primary" onclick="loadMoreCampaigns()">
                        <i class="fas fa-plus me-1"></i> Load More Campaigns
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let performanceChart, channelPerformanceChart, costAnalysisChart, funnelChart;
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
    });

    function initializeCharts() {
        // Performance Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        performanceChart = new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8'],
                datasets: [{
                    label: 'Engagement Rate',
                    data: [28.4, 31.2, 29.8, 34.5, 36.2, 33.8, 37.1, 34.7],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Industry Average',
                    data: [25.0, 25.5, 26.0, 26.2, 26.5, 26.8, 27.0, 27.2],
                    borderColor: '#94a3b8',
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
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Channel Performance Chart
        const channelCtx = document.getElementById('channelPerformanceChart').getContext('2d');
        channelPerformanceChart = new Chart(channelCtx, {
            type: 'doughnut',
            data: {
                labels: ['Email', 'SMS', 'WhatsApp'],
                datasets: [{
                    data: [42.3, 38.7, 31.2],
                    backgroundColor: ['#667eea', '#fc466b', '#11998e'],
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

        // Cost Analysis Chart
        const costCtx = document.getElementById('costAnalysisChart').getContext('2d');
        costAnalysisChart = new Chart(costCtx, {
            type: 'bar',
            data: {
                labels: ['Email', 'SMS', 'WhatsApp', 'Social Media'],
                datasets: [{
                    label: 'Cost',
                    data: [4500, 3200, 2800, 1985],
                    backgroundColor: ['#667eea', '#fc466b', '#11998e', '#fdbb2d'],
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

        // Funnel Chart
        const funnelCtx = document.getElementById('funnelChart').getContext('2d');
        funnelChart = new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: ['Sent', 'Opened', 'Clicked', 'Converted'],
                datasets: [{
                    data: [284567, 98756, 34892, 4287],
                    backgroundColor: ['#e5e7eb', '#667eea', '#11998e', '#22c55e'],
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
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
            window.location.href = `{{ route('admin.analytics.campaigns') }}?start_date=${startDate}&end_date=${endDate}&channel={{ $channel }}`;
        }
    }

    function setChannelFilter(channel) {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        window.location.href = `{{ route('admin.analytics.campaigns') }}?start_date=${startDate}&end_date=${endDate}&channel=${channel}`;
    }

    function updatePerformanceChart(metric) {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        // Sample data for different metrics
        const metricData = {
            engagement: [28.4, 31.2, 29.8, 34.5, 36.2, 33.8, 37.1, 34.7],
            conversion: [2.1, 2.4, 2.2, 2.8, 3.1, 2.9, 3.4, 3.2],
            roi: [3.2, 3.6, 3.4, 4.1, 4.4, 4.2, 4.8, 4.6]
        };
        
        performanceChart.data.datasets[0].data = metricData[metric];
        performanceChart.data.datasets[0].label = metric.charAt(0).toUpperCase() + metric.slice(1) + ' Rate';
        performanceChart.update();
    }

    function sortCampaigns(sortBy) {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        console.log('Sorting campaigns by:', sortBy);
        // Here you would typically re-order the campaign list
    }

    function loadMoreCampaigns() {
        // Here you would load more campaigns via AJAX
        console.log('Loading more campaigns...');
    }

    function exportCampaigns() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        const url = `{{ route('admin.analytics.export') }}?type=campaigns&start_date=${startDate}&end_date=${endDate}&channel={{ $channel }}&format=csv`;
        window.open(url, '_blank');
    }

    function refreshData() {
        window.location.reload();
    }
</script>
@endsection