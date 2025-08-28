@extends('layouts.app')

@section('title', 'Revenue Forecast - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-crystal-ball me-2 text-purple"></i>
                        Revenue Forecast
                    </h1>
                    <p class="text-muted">
                        Predictive analytics and revenue forecasting with trend analysis
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.revenue.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Overview
                    </a>
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm forecast-period" data-months="3">3 Months</button>
                        <button type="button" class="btn btn-primary btn-sm forecast-period" data-months="6">6 Months</button>
                        <button type="button" class="btn btn-outline-primary btn-sm forecast-period" data-months="12">12 Months</button>
                    </div>
                    <button type="button" class="btn btn-success" onclick="exportForecast()">
                        <i class="fas fa-download me-1"></i>Export Forecast
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forecast Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Predicted Revenue</h6>
                            <h3 class="mb-0" id="predictedRevenue">
                                ${{ number_format(collect($forecastData)->sum('predicted_revenue'), 2) }}
                            </h3>
                            <small class="text-success">Next {{ $months }} months</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-trending-up"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Growth Trend</h6>
                            <h3 class="mb-0 text-{{ $trendAnalysis['trend'] === 'increasing' ? 'success' : ($trendAnalysis['trend'] === 'decreasing' ? 'danger' : 'warning') }}">
                                {{ ucfirst($trendAnalysis['trend']) }}
                            </h3>
                            <small class="text-muted">Current trajectory</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Confidence Level</h6>
                            <h3 class="mb-0" id="confidenceLevel">
                                {{ number_format(collect($forecastData)->avg('confidence') * 100, 0) }}%
                            </h3>
                            <small class="text-muted">Prediction accuracy</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Monthly Average</h6>
                            <h3 class="mb-0" id="monthlyAverage">
                                ${{ number_format(collect($forecastData)->avg('predicted_revenue'), 2) }}
                            </h3>
                            <small class="text-muted">Per month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forecast Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-area me-2 text-primary"></i>
                                Revenue Forecast Chart
                            </h5>
                            <small class="text-muted">Historical data vs predicted revenue for the next {{ $months }} months</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary chart-type" data-type="line">
                                <i class="fas fa-chart-line"></i> Line
                            </button>
                            <button class="btn btn-primary chart-type" data-type="bar">
                                <i class="fas fa-chart-bar"></i> Bar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="height: 400px;">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trend Analysis and Seasonal Patterns -->
    <div class="row mb-4">
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2 text-success"></i>
                        Trend Analysis
                    </h5>
                    <small class="text-muted">Statistical analysis of revenue patterns</small>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <div class="p-3">
                                <h4 class="text-{{ $trendAnalysis['trend'] === 'increasing' ? 'success' : ($trendAnalysis['trend'] === 'decreasing' ? 'danger' : 'warning') }} mb-1">
                                    <i class="fas fa-{{ $trendAnalysis['trend'] === 'increasing' ? 'arrow-up' : ($trendAnalysis['trend'] === 'decreasing' ? 'arrow-down' : 'minus') }}"></i>
                                </h4>
                                <h6 class="text-muted mb-0">Trend Direction</h6>
                                <small class="text-{{ $trendAnalysis['trend'] === 'increasing' ? 'success' : ($trendAnalysis['trend'] === 'decreasing' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($trendAnalysis['trend']) }}
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-4">
                            <div class="p-3">
                                <h4 class="text-info mb-1">${{ number_format($trendAnalysis['average'], 0) }}</h4>
                                <h6 class="text-muted mb-0">Average Revenue</h6>
                                <small class="text-muted">6-month avg</small>
                            </div>
                        </div>
                        
                        <div class="col-4">
                            <div class="p-3">
                                <h4 class="text-warning mb-1">{{ number_format($trendAnalysis['volatility'], 1) }}%</h4>
                                <h6 class="text-muted mb-0">Volatility</h6>
                                <small class="text-muted">Risk level</small>
                            </div>
                        </div>
                    </div>

                    <!-- Trend Insights -->
                    <div class="bg-light rounded p-3">
                        <h6 class="text-primary mb-2">
                            <i class="fas fa-lightbulb me-1"></i>Trend Insights
                        </h6>
                        @if($trendAnalysis['trend'] === 'increasing')
                            <p class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <strong>Positive Growth:</strong> Revenue is showing an upward trend with consistent growth patterns.
                            </p>
                        @elseif($trendAnalysis['trend'] === 'decreasing')
                            <p class="mb-2">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                <strong>Declining Trend:</strong> Revenue is showing a downward trend. Consider reviewing strategies.
                            </p>
                        @else
                            <p class="mb-2">
                                <i class="fas fa-minus-circle text-info me-2"></i>
                                <strong>Stable Pattern:</strong> Revenue is maintaining steady levels with minimal fluctuation.
                            </p>
                        @endif
                        
                        <p class="mb-0">
                            <i class="fas fa-chart-bar text-info me-2"></i>
                            <strong>Volatility:</strong> 
                            @if($trendAnalysis['volatility'] < 10)
                                Low risk - Stable revenue patterns
                            @elseif($trendAnalysis['volatility'] < 20)
                                Moderate risk - Some fluctuation expected
                            @else
                                High risk - Significant revenue variations
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2 text-warning"></i>
                        Seasonal Patterns
                    </h5>
                    <small class="text-muted">Monthly performance multipliers</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($seasonalPatterns as $pattern)
                            @php
                                $multiplier = $pattern['multiplier'];
                                $isPositive = $multiplier >= 1;
                                $percentage = ($multiplier - 1) * 100;
                            @endphp
                            <div class="col-md-6 col-lg-4 col-xl-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center p-2 rounded {{ $isPositive ? 'bg-success' : 'bg-danger' }} bg-opacity-10">
                                    <div>
                                        <strong>{{ $pattern['month_name'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $pattern['description'] }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $isPositive ? 'success' : 'danger' }}">
                                            {{ $isPositive ? '+' : '' }}{{ number_format($percentage, 0) }}%
                                        </span>
                                        <br>
                                        <small class="text-muted">x{{ number_format($multiplier, 2) }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Forecast Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2 text-info"></i>
                        Detailed Forecast Breakdown
                    </h5>
                    <small class="text-muted">Month-by-month revenue predictions with confidence intervals</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Month</th>
                                    <th class="border-0 text-end">Predicted Revenue</th>
                                    <th class="border-0 text-end">Confidence Level</th>
                                    <th class="border-0 text-end">Seasonal Factor</th>
                                    <th class="border-0 text-end">Range (±20%)</th>
                                    <th class="border-0 text-center">Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forecastData as $forecast)
                                    @php
                                        $revenue = $forecast['predicted_revenue'];
                                        $confidence = $forecast['confidence'] * 100;
                                        $lowerBound = $revenue * 0.8;
                                        $upperBound = $revenue * 1.2;
                                        $month = \Carbon\Carbon::parse($forecast['date']);
                                        $seasonalPattern = collect($seasonalPatterns)->firstWhere('month', $month->month);
                                        $riskLevel = $confidence >= 80 ? 'low' : ($confidence >= 60 ? 'medium' : 'high');
                                        $riskColor = $riskLevel === 'low' ? 'success' : ($riskLevel === 'medium' ? 'warning' : 'danger');
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $month->format('F Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $month->diffForHumans() }}</small>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">${{ number_format($revenue, 2) }}</strong>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <div class="progress me-2" style="width: 60px; height: 6px;">
                                                    <div class="progress-bar bg-{{ $riskColor }}" style="width: {{ $confidence }}%"></div>
                                                </div>
                                                <span class="text-{{ $riskColor }}">{{ number_format($confidence, 0) }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if($seasonalPattern)
                                                <span class="badge bg-{{ $seasonalPattern['multiplier'] >= 1 ? 'success' : 'warning' }}">
                                                    x{{ number_format($seasonalPattern['multiplier'], 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <small class="text-muted">
                                                ${{ number_format($lowerBound, 0) }} - ${{ number_format($upperBound, 0) }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $riskColor }}">
                                                {{ ucfirst($riskLevel) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th>Total ({{ $months }} months)</th>
                                    <th class="text-end">${{ number_format(collect($forecastData)->sum('predicted_revenue'), 2) }}</th>
                                    <th class="text-end">{{ number_format(collect($forecastData)->avg('confidence') * 100, 0) }}%</th>
                                    <th class="text-end">-</th>
                                    <th class="text-end">
                                        ${{ number_format(collect($forecastData)->sum('predicted_revenue') * 0.8, 0) }} - 
                                        ${{ number_format(collect($forecastData)->sum('predicted_revenue') * 1.2, 0) }}
                                    </th>
                                    <th class="text-center">-</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Strategic Recommendations -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bullseye me-2 text-success"></i>
                        Strategic Recommendations
                    </h5>
                    <small class="text-muted">AI-powered insights and actionable recommendations</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-rocket me-2"></i>Growth Opportunities
                            </h6>
                            
                            @php
                                $bestMonth = collect($seasonalPatterns)->sortByDesc('multiplier')->first();
                                $worstMonth = collect($seasonalPatterns)->sortBy('multiplier')->first();
                            @endphp
                            
                            <div class="list-group list-group-flush">
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-calendar-star text-warning me-3 mt-1"></i>
                                        <div>
                                            <strong>Seasonal Peak</strong>
                                            <p class="mb-0 text-muted small">
                                                Focus marketing efforts on {{ $bestMonth['month_name'] }} 
                                                ({{ number_format(($bestMonth['multiplier'] - 1) * 100) }}% above average)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-chart-line text-success me-3 mt-1"></i>
                                        <div>
                                            <strong>Trend Acceleration</strong>
                                            <p class="mb-0 text-muted small">
                                                @if($trendAnalysis['trend'] === 'increasing')
                                                    Capitalize on positive trend with increased investment
                                                @else
                                                    Focus on customer retention to reverse declining trend
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-target text-info me-3 mt-1"></i>
                                        <div>
                                            <strong>Revenue Target</strong>
                                            <p class="mb-0 text-muted small">
                                                Set monthly target of ${{ number_format(collect($forecastData)->avg('predicted_revenue') * 1.1, 0) }} 
                                                (+10% stretch goal)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-danger mb-3">
                                <i class="fas fa-shield-alt me-2"></i>Risk Mitigation
                            </h6>
                            
                            <div class="list-group list-group-flush">
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-calendar-minus text-danger me-3 mt-1"></i>
                                        <div>
                                            <strong>Low Season Planning</strong>
                                            <p class="mb-0 text-muted small">
                                                Prepare for {{ $worstMonth['month_name'] }} downturn 
                                                ({{ number_format((1 - $worstMonth['multiplier']) * 100) }}% below average)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-exclamation-triangle text-warning me-3 mt-1"></i>
                                        <div>
                                            <strong>Volatility Management</strong>
                                            <p class="mb-0 text-muted small">
                                                @if($trendAnalysis['volatility'] > 15)
                                                    High volatility detected - diversify revenue streams
                                                @else
                                                    Maintain current stability with consistent strategies
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-chart-pie text-secondary me-3 mt-1"></i>
                                        <div>
                                            <strong>Confidence Buffer</strong>
                                            <p class="mb-0 text-muted small">
                                                Plan for ±20% variance in predictions (confidence: {{ number_format(collect($forecastData)->avg('confidence') * 100, 0) }}%)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-purple {
        color: #6f42c1 !important;
    }
    
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .progress {
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let forecastChart;
let currentMonths = {{ $months }};
let currentChartType = 'bar';

document.addEventListener('DOMContentLoaded', function() {
    initializeForecastChart();
    
    // Period filter handlers
    document.querySelectorAll('.forecast-period').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.forecast-period').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
            
            currentMonths = parseInt(this.getAttribute('data-months'));
            loadForecastData(currentMonths);
        });
    });
    
    // Chart type handlers
    document.querySelectorAll('.chart-type').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.chart-type').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
            
            currentChartType = this.getAttribute('data-type');
            updateChartType(currentChartType);
        });
    });
});

function initializeForecastChart() {
    const ctx = document.getElementById('forecastChart').getContext('2d');
    
    // Generate historical data (last 6 months)
    const historicalLabels = [];
    const historicalData = [];
    const currentDate = new Date();
    
    for (let i = 5; i >= 0; i--) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        historicalLabels.push(date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' }));
        historicalData.push(Math.floor(Math.random() * 5000) + 3000); // Sample historical data
    }
    
    // Forecast data
    const forecastLabels = @json(collect($forecastData)->pluck('date')->map(function($date) {
        return \Carbon\Carbon::parse($date)->format('M Y');
    }));
    const forecastRevenue = @json(collect($forecastData)->pluck('predicted_revenue'));
    const confidenceLevels = @json(collect($forecastData)->pluck('confidence'));
    
    // Combine labels and data
    const allLabels = [...historicalLabels, ...forecastLabels];
    const allHistoricalData = [...historicalData, ...Array(forecastLabels.length).fill(null)];
    const allForecastData = [...Array(historicalLabels.length).fill(null), ...forecastRevenue];
    
    forecastChart = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Historical Revenue',
                    data: allHistoricalData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Predicted Revenue',
                    data: allForecastData,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
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
                },
                x: {
                    grid: {
                        display: true,
                        color: function(context) {
                            // Highlight the boundary between historical and forecast data
                            return context.index === historicalLabels.length - 1 ? 'rgba(255, 193, 7, 0.5)' : 'rgba(0,0,0,0.1)';
                        },
                        lineWidth: function(context) {
                            return context.index === historicalLabels.length - 1 ? 3 : 1;
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

function updateChartType(type) {
    forecastChart.config.type = type;
    forecastChart.update();
}

function loadForecastData(months) {
    // This would normally make an AJAX call to update the forecast
    console.log('Loading forecast for', months, 'months');
    
    // Update UI elements
    document.getElementById('predictedRevenue').textContent = '$' + (Math.random() * 50000 + 20000).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('confidenceLevel').textContent = Math.floor(Math.random() * 30 + 70) + '%';
    document.getElementById('monthlyAverage').textContent = '$' + (Math.random() * 8000 + 4000).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function exportForecast() {
    const url = new URL('{{ route("admin.revenue.export") }}');
    url.searchParams.append('type', 'forecast');
    url.searchParams.append('months', currentMonths);
    
    window.open(url.toString(), '_blank');
}
</script>
@endpush
