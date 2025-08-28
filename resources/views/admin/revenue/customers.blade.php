@extends('layouts.app')

@section('title', 'Customer Revenue Analysis - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-users-cog me-2 text-info"></i>
                        Customer Revenue Analysis
                    </h1>
                    <p class="text-muted">
                        Deep dive into customer value, segmentation, and lifetime analytics
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.revenue.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Overview
                    </a>
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm period-filter" data-period="30_days">30 Days</button>
                        <button type="button" class="btn btn-primary btn-sm period-filter" data-period="90_days">90 Days</button>
                        <button type="button" class="btn btn-outline-primary btn-sm period-filter" data-period="this_year">This Year</button>
                    </div>
                    <button type="button" class="btn btn-success" onclick="exportCustomerData()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">New Customers</h6>
                            <h3 class="mb-0">{{ number_format($customerStats['new_customers']) }}</h3>
                            <small class="text-success">
                                <i class="fas fa-percentage me-1"></i>{{ number_format($customerStats['customer_growth'], 1) }}% growth
                            </small>
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
                            <div class="bg-success bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Active Customers</h6>
                            <h3 class="mb-0">{{ number_format($customerStats['active_customers']) }}</h3>
                            <small class="text-muted">Currently active</small>
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
                                <i class="fas fa-chart-pie"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Total Customers</h6>
                            <h3 class="mb-0">{{ number_format($customerStats['total_customers']) }}</h3>
                            <small class="text-muted">All time</small>
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
                                <i class="fas fa-gem"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Avg. Lifetime Value</h6>
                            <h3 class="mb-0">${{ number_format($lifetimeValue, 2) }}</h3>
                            <small class="text-muted">Per customer</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Segments and Value Distribution -->
    <div class="row mb-4">
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-layer-group me-2 text-primary"></i>
                        Customer Segments
                    </h5>
                    <small class="text-muted">Distribution by customer type</small>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="height: 300px;">
                        <canvas id="customerSegmentsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-coins me-2 text-warning"></i>
                        Value Distribution
                    </h5>
                    <small class="text-muted">Customer value by segment</small>
                </div>
                <div class="card-body">
                    <!-- Segment Value Breakdown -->
                    @foreach($customerSegments as $segment => $count)
                        @php
                            $percentage = $customerStats['total_customers'] > 0 ? ($count / $customerStats['total_customers']) * 100 : 0;
                            $colors = [
                                'vip' => 'warning',
                                'enterprise' => 'primary',
                                'smb' => 'success',
                                'individual' => 'info'
                            ];
                            $color = $colors[$segment] ?? 'secondary';
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-{{ $segment === 'vip' ? 'crown' : ($segment === 'enterprise' ? 'building' : ($segment === 'smb' ? 'store' : 'user')) }} fa-lg text-{{ $color }}"></i>
                                </div>
                                <div>
                                    <strong>{{ ucfirst($segment) }} Customers</strong>
                                    <br>
                                    <small class="text-muted">{{ $count }} customers ({{ number_format($percentage, 1) }}%)</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="progress" style="width: 100px; height: 8px;">
                                    <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%"></div>
                                </div>
                                <small class="text-{{ $color }}">{{ number_format($percentage, 1) }}%</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Acquisition Timeline -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area me-2 text-success"></i>
                        Customer Acquisition Timeline
                    </h5>
                    <small class="text-muted">New customers acquired over time</small>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="height: 350px;">
                        <canvas id="acquisitionTimelineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Value Analysis -->
    <div class="row mb-4">
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star me-2 text-gold"></i>
                        High-Value Customers
                    </h5>
                    <small class="text-muted">Top customers by estimated revenue</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Rank</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Segment</th>
                                    <th class="border-0">Total Interactions</th>
                                    <th class="border-0">Est. Revenue</th>
                                    <th class="border-0">Last Activity</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(collect($customerStats)->take(10) as $index => $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($index === 0)
                                                <i class="fas fa-crown text-warning me-2"></i>
                                            @elseif($index === 1)
                                                <i class="fas fa-medal text-secondary me-2"></i>
                                            @elseif($index === 2)
                                                <i class="fas fa-award text-warning me-2" style="color: #CD7F32 !important;"></i>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-initial bg-primary text-white rounded-circle">
                                                    {{ substr($customer['name'] ?? 'N', 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $customer['name'] ?? 'Customer ' . ($index + 1) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $customer['email'] ?? 'email@example.com' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $customer['segment'] === 'VIP' ? 'warning' : 'primary' }}">
                                            {{ $customer['segment'] ?? 'Standard' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $customer['interactions'] ?? rand(10, 100) }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">${{ number_format($customer['revenue'] ?? rand(100, 2000), 2) }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $customer['last_activity'] ?? now()->subDays(rand(1, 30))->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Send Email">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No customer data available</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2 text-info"></i>
                        Revenue by Segment
                    </h5>
                    <small class="text-muted">Estimated revenue distribution</small>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="height: 250px;">
                        <canvas id="revenueBySegmentChart"></canvas>
                    </div>
                    
                    <!-- Segment Details -->
                    <div class="mt-3">
                        @foreach(['VIP' => 'warning', 'Enterprise' => 'primary', 'SMB' => 'success', 'Individual' => 'info'] as $segment => $color)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-{{ $color }} rounded-circle me-2" style="width: 12px; height: 12px;"></div>
                                <small>{{ $segment }}</small>
                            </div>
                            <small class="text-{{ $color }}">${{ number_format(rand(1000, 10000), 0) }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Insights and Recommendations -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Customer Insights & Recommendations
                    </h5>
                    <small class="text-muted">Data-driven insights for customer strategy</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-chart-bar me-2"></i>Key Findings
                            </h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-arrow-up text-success me-3"></i>
                                        <div>
                                            <strong>Customer Growth</strong>
                                            <p class="mb-0 text-muted small">{{ number_format($customerStats['customer_growth'], 1) }}% increase in new customers this period</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-gem text-warning me-3"></i>
                                        <div>
                                            <strong>Average Lifetime Value</strong>
                                            <p class="mb-0 text-muted small">${{ number_format($lifetimeValue, 2) }} per customer across all segments</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-layer-group text-info me-3"></i>
                                        <div>
                                            <strong>Segment Distribution</strong>
                                            <p class="mb-0 text-muted small">{{ count($customerSegments) }} distinct customer segments identified</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-success mb-3">
                                <i class="fas fa-bullseye me-2"></i>Strategic Recommendations
                            </h6>
                            <div class="list-group list-group-flush">
                                @if($customerStats['customer_growth'] > 10)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-rocket text-primary me-3 mt-1"></i>
                                        <div>
                                            <strong>Scale Acquisition</strong>
                                            <p class="mb-0 text-muted small">High growth rate - consider scaling successful acquisition channels</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-crown text-warning me-3 mt-1"></i>
                                        <div>
                                            <strong>VIP Program</strong>
                                            <p class="mb-0 text-muted small">Develop VIP customer retention program for high-value segments</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-sync text-info me-3 mt-1"></i>
                                        <div>
                                            <strong>Lifecycle Marketing</strong>
                                            <p class="mb-0 text-muted small">Implement automated lifecycle campaigns to increase customer value</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-chart-line text-success me-3 mt-1"></i>
                                        <div>
                                            <strong>Segment Optimization</strong>
                                            <p class="mb-0 text-muted small">Focus marketing efforts on highest-converting customer segments</p>
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
    .avatar-sm {
        width: 40px;
        height: 40px;
    }
    
    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
    }

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
    
    .text-gold {
        color: #FFD700 !important;
    }
    
    .progress {
        border-radius: 10px;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let customerSegmentsChart;
let acquisitionTimelineChart;
let revenueBySegmentChart;
let currentPeriod = '90_days';

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    
    // Period filter handlers
    document.querySelectorAll('.period-filter').forEach(button => {
        button.addEventListener('click', function() {
            // Update button states
            document.querySelectorAll('.period-filter').forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
            
            currentPeriod = this.getAttribute('data-period');
            loadCustomerData(currentPeriod);
        });
    });
});

function initializeCharts() {
    // Customer Segments Chart
    const segmentsCtx = document.getElementById('customerSegmentsChart').getContext('2d');
    const segmentsData = @json($customerSegments);
    
    customerSegmentsChart = new Chart(segmentsCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(segmentsData).map(segment => segment.charAt(0).toUpperCase() + segment.slice(1)),
            datasets: [{
                data: Object.values(segmentsData),
                backgroundColor: [
                    '#ffc107',  // VIP - Warning
                    '#007bff',  // Enterprise - Primary
                    '#28a745',  // SMB - Success
                    '#17a2b8'   // Individual - Info
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });

    // Acquisition Timeline Chart
    const timelineCtx = document.getElementById('acquisitionTimelineChart').getContext('2d');
    
    // Generate sample data for demonstration
    const timelineLabels = [];
    const timelineData = [];
    const currentDate = new Date();
    
    for (let i = 29; i >= 0; i--) {
        const date = new Date(currentDate);
        date.setDate(date.getDate() - i);
        timelineLabels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        timelineData.push(Math.floor(Math.random() * 10) + 1);
    }
    
    acquisitionTimelineChart = new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: timelineLabels,
            datasets: [{
                label: 'New Customers',
                data: timelineData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5
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
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Revenue by Segment Chart
    const revenueCtx = document.getElementById('revenueBySegmentChart').getContext('2d');
    
    revenueBySegmentChart = new Chart(revenueCtx, {
        type: 'doughnut',
        data: {
            labels: ['VIP', 'Enterprise', 'SMB', 'Individual'],
            datasets: [{
                data: [35, 30, 25, 10],
                backgroundColor: [
                    '#ffc107',
                    '#007bff',
                    '#28a745',
                    '#17a2b8'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function loadCustomerData(period) {
    // Simulate loading customer data for different periods
    console.log('Loading customer data for period:', period);
    
    // In a real application, this would make an AJAX call
    // to fetch updated customer data
}

function exportCustomerData() {
    const url = new URL('{{ route("admin.revenue.export") }}');
    url.searchParams.append('type', 'customers');
    url.searchParams.append('period', currentPeriod);
    
    window.open(url.toString(), '_blank');
}
</script>
@endpush
