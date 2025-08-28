@extends('layouts.app')

@section('title', 'Monthly Revenue Analysis - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>
                        Monthly Revenue Analysis
                    </h1>
                    <p class="text-muted">
                        Detailed monthly revenue breakdown and year-over-year comparison
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.revenue.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Overview
                    </a>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-calendar me-1"></i>{{ $year }}
                        </button>
                        <ul class="dropdown-menu">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <li><a class="dropdown-item" href="{{ route('admin.revenue.monthly', ['year' => $y]) }}">{{ $y }}</a></li>
                            @endfor
                        </ul>
                    </div>
                    <button type="button" class="btn btn-success" onclick="exportMonthlyData()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Comparison Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">{{ $year }} Total Revenue</h6>
                    <h2 class="text-primary mb-3">${{ number_format($yearComparison['current_year'], 2) }}</h2>
                    <div class="bg-primary bg-opacity-10 rounded p-3">
                        <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                        <p class="text-muted mb-0">Current Year Performance</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">{{ $year - 1 }} Total Revenue</h6>
                    <h2 class="text-secondary mb-3">${{ number_format($yearComparison['previous_year'], 2) }}</h2>
                    <div class="bg-secondary bg-opacity-10 rounded p-3">
                        <i class="fas fa-history fa-2x text-secondary mb-2"></i>
                        <p class="text-muted mb-0">Previous Year Performance</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Year-over-Year Growth</h6>
                    <h2 class="text-{{ $yearComparison['growth_percentage'] >= 0 ? 'success' : 'danger' }} mb-3">
                        {{ number_format($yearComparison['growth_percentage'], 1) }}%
                    </h2>
                    <div class="bg-{{ $yearComparison['growth_percentage'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 rounded p-3">
                        <i class="fas fa-{{ $yearComparison['growth_percentage'] >= 0 ? 'arrow-up' : 'arrow-down' }} fa-2x text-{{ $yearComparison['growth_percentage'] >= 0 ? 'success' : 'danger' }} mb-2"></i>
                        <p class="text-muted mb-0">Growth Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2 text-info"></i>
                        Monthly Revenue Trends
                    </h5>
                    <small class="text-muted">{{ $year }} vs {{ $year - 1 }} comparison</small>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="height: 400px;">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2 text-success"></i>
                            Monthly Breakdown - {{ $year }}
                        </h5>
                        <small class="text-muted">Detailed monthly statistics and metrics</small>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="toggleView('table')" id="tableViewBtn">
                            <i class="fas fa-table"></i> Table
                        </button>
                        <button class="btn btn-outline-secondary" onclick="toggleView('cards')" id="cardsViewBtn">
                            <i class="fas fa-th"></i> Cards
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Table View -->
                    <div id="tableView">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Month</th>
                                        <th class="border-0 text-end">Revenue</th>
                                        <th class="border-0 text-end">Customers</th>
                                        <th class="border-0 text-end">Campaigns</th>
                                        <th class="border-0 text-end">Avg per Customer</th>
                                        <th class="border-0 text-end">Growth</th>
                                        <th class="border-0 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthlyStats as $index => $month)
                                        @php
                                            $prevMonth = $index > 0 ? $monthlyStats[$index - 1] : null;
                                            $growthRate = $prevMonth && $prevMonth['revenue'] > 0 
                                                ? (($month['revenue'] - $prevMonth['revenue']) / $prevMonth['revenue']) * 100 
                                                : 0;
                                            $avgPerCustomer = $month['customers'] > 0 ? $month['revenue'] / $month['customers'] : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $month['month_name'] }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $year }}</small>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success">${{ number_format($month['revenue'], 2) }}</strong>
                                            </td>
                                            <td class="text-end">{{ number_format($month['customers']) }}</td>
                                            <td class="text-end">{{ number_format($month['campaigns']) }}</td>
                                            <td class="text-end">${{ number_format($avgPerCustomer, 2) }}</td>
                                            <td class="text-end">
                                                @if($growthRate != 0)
                                                    <span class="badge bg-{{ $growthRate >= 0 ? 'success' : 'danger' }}">
                                                        <i class="fas fa-{{ $growthRate >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                                        {{ number_format(abs($growthRate), 1) }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $performance = 'warning';
                                                    $icon = 'minus';
                                                    if ($month['revenue'] > 1000) {
                                                        $performance = 'success';
                                                        $icon = 'check';
                                                    } elseif ($month['revenue'] > 500) {
                                                        $performance = 'info';
                                                        $icon = 'arrow-up';
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $performance }}">
                                                    <i class="fas fa-{{ $icon }}"></i>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-end">${{ number_format(collect($monthlyStats)->sum('revenue'), 2) }}</th>
                                        <th class="text-end">{{ number_format(collect($monthlyStats)->sum('customers')) }}</th>
                                        <th class="text-end">{{ number_format(collect($monthlyStats)->sum('campaigns')) }}</th>
                                        <th class="text-end">${{ number_format(collect($monthlyStats)->sum('revenue') / max(collect($monthlyStats)->sum('customers'), 1), 2) }}</th>
                                        <th class="text-end">-</th>
                                        <th class="text-center">-</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Cards View -->
                    <div id="cardsView" style="display: none;">
                        <div class="row p-3">
                            @foreach($monthlyStats as $index => $month)
                                @php
                                    $prevMonth = $index > 0 ? $monthlyStats[$index - 1] : null;
                                    $growthRate = $prevMonth && $prevMonth['revenue'] > 0 
                                        ? (($month['revenue'] - $prevMonth['revenue']) / $prevMonth['revenue']) * 100 
                                        : 0;
                                @endphp
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title mb-0">{{ $month['month_name'] }}</h5>
                                                @if($growthRate != 0)
                                                    <span class="badge bg-{{ $growthRate >= 0 ? 'success' : 'danger' }}">
                                                        <i class="fas fa-{{ $growthRate >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                                        {{ number_format(abs($growthRate), 1) }}%
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <div class="mb-3">
                                                <h3 class="text-success mb-1">${{ number_format($month['revenue'], 2) }}</h3>
                                                <small class="text-muted">Revenue</small>
                                            </div>

                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <div class="border-end">
                                                        <strong class="text-primary d-block">{{ $month['customers'] }}</strong>
                                                        <small class="text-muted">Customers</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <strong class="text-info d-block">{{ $month['campaigns'] }}</strong>
                                                    <small class="text-muted">Campaigns</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Insights -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Key Insights for {{ $year }}
                    </h5>
                    <small class="text-muted">Automated analysis and recommendations</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-chart-line me-2"></i>Performance Analysis
                            </h6>
                            <ul class="list-unstyled">
                                @php
                                    $bestMonth = collect($monthlyStats)->sortByDesc('revenue')->first();
                                    $worstMonth = collect($monthlyStats)->sortBy('revenue')->first();
                                    $avgRevenue = collect($monthlyStats)->avg('revenue');
                                @endphp
                                <li class="mb-2">
                                    <i class="fas fa-crown text-warning me-2"></i>
                                    <strong>Best month:</strong> {{ $bestMonth['month_name'] }} with ${{ number_format($bestMonth['revenue'], 2) }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-chart-line text-info me-2"></i>
                                    <strong>Average monthly revenue:</strong> ${{ number_format($avgRevenue, 2) }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-users text-success me-2"></i>
                                    <strong>Total customers served:</strong> {{ number_format(collect($monthlyStats)->sum('customers')) }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-bullhorn text-primary me-2"></i>
                                    <strong>Total campaigns:</strong> {{ number_format(collect($monthlyStats)->sum('campaigns')) }}
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-success mb-3">
                                <i class="fas fa-target me-2"></i>Recommendations
                            </h6>
                            <ul class="list-unstyled">
                                @if($yearComparison['growth_percentage'] >= 10)
                                    <li class="mb-2">
                                        <i class="fas fa-thumbs-up text-success me-2"></i>
                                        Excellent growth rate! Consider scaling successful campaigns.
                                    </li>
                                @elseif($yearComparison['growth_percentage'] >= 0)
                                    <li class="mb-2">
                                        <i class="fas fa-chart-bar text-warning me-2"></i>
                                        Positive growth. Focus on customer retention strategies.
                                    </li>
                                @else
                                    <li class="mb-2">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        Revenue decline. Review campaign effectiveness.
                                    </li>
                                @endif
                                
                                <li class="mb-2">
                                    <i class="fas fa-calendar-plus text-info me-2"></i>
                                    Plan campaigns around {{ $bestMonth['month_name'] }}-type seasonality.
                                </li>
                                
                                <li class="mb-2">
                                    <i class="fas fa-user-plus text-primary me-2"></i>
                                    Focus on customer acquisition in low-revenue months.
                                </li>
                                
                                <li class="mb-2">
                                    <i class="fas fa-sync text-secondary me-2"></i>
                                    Review and optimize underperforming months.
                                </li>
                            </ul>
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
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let monthlyTrendsChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
});

function initializeChart() {
    const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
    
    const currentYearData = @json(collect($monthlyTrends['current'])->pluck('revenue'));
    const previousYearData = @json(collect($monthlyTrends['previous'])->pluck('revenue'));
    const monthLabels = @json(collect($monthlyTrends['current'])->pluck('month_name'));
    
    monthlyTrendsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [
                {
                    label: '{{ $year }}',
                    data: currentYearData,
                    backgroundColor: 'rgba(0, 123, 255, 0.8)',
                    borderColor: '#007bff',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    label: '{{ $year - 1 }}',
                    data: previousYearData,
                    backgroundColor: 'rgba(108, 117, 125, 0.6)',
                    borderColor: '#6c757d',
                    borderWidth: 1,
                    borderRadius: 4
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
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

function toggleView(viewType) {
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    const tableBtn = document.getElementById('tableViewBtn');
    const cardsBtn = document.getElementById('cardsViewBtn');
    
    if (viewType === 'table') {
        tableView.style.display = 'block';
        cardsView.style.display = 'none';
        tableBtn.classList.remove('btn-outline-primary');
        tableBtn.classList.add('btn-primary');
        cardsBtn.classList.remove('btn-primary');
        cardsBtn.classList.add('btn-outline-secondary');
    } else {
        tableView.style.display = 'none';
        cardsView.style.display = 'block';
        cardsBtn.classList.remove('btn-outline-secondary');
        cardsBtn.classList.add('btn-primary');
        tableBtn.classList.remove('btn-primary');
        tableBtn.classList.add('btn-outline-primary');
    }
}

function exportMonthlyData() {
    const url = new URL('{{ route("admin.revenue.export") }}');
    url.searchParams.append('type', 'monthly');
    url.searchParams.append('year', '{{ $year }}');
    
    window.open(url.toString(), '_blank');
}
</script>
@endpush
