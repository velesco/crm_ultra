@extends('layouts.app')

@section('title', 'Revenue Analytics - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-chart-line me-2 text-success"></i>
                        Revenue Analytics
                    </h1>
                    <p class="text-muted">
                        Comprehensive revenue tracking and financial performance analysis
                    </p>
                </div>
                <div>
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm period-filter" data-period="7_days">7 Days</button>
                        <button type="button" class="btn btn-primary btn-sm period-filter" data-period="30_days">30 Days</button>
                        <button type="button" class="btn btn-outline-primary btn-sm period-filter" data-period="90_days">90 Days</button>
                        <button type="button" class="btn btn-outline-primary btn-sm period-filter" data-period="this_year">This Year</button>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-sm" onclick="exportRevenue('summary')">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="refreshStats()">
                            <i class="fas fa-sync me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Statistics Cards -->
    <div class="row mb-4" id="statsCards">
        <div class="col-xl-3 col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Total Revenue</h6>
                            <h3 class="mb-0" id="totalRevenue">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                            @if(isset($stats['revenue_growth']) && $stats['revenue_growth'] != 0)
                                <small class="text-{{ $stats['revenue_growth'] >= 0 ? 'success' : 'danger' }}">
                                    <i class="fas fa-{{ $stats['revenue_growth'] >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                    {{ number_format(abs($stats['revenue_growth']), 1) }}% vs previous period
                                </small>
                            @endif
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
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Avg. Order Value</h6>
                            <h3 class="mb-0" id="avgOrderValue">${{ number_format($stats['average_order_value'] ?? 0, 2) }}</h3>
                            <small class="text-muted">Per campaign</small>
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
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Active Customers</h6>
                            <h3 class="mb-0" id="customerCount">{{ number_format($stats['customer_count'] ?? 0) }}</h3>
                            <small class="text-muted">This period</small>
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
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Conversion Rate</h6>
                            <h3 class="mb-0" id="conversionRate">{{ number_format($stats['conversion_rate'] ?? 0, 1) }}%</h3>
                            <small class="text-muted">Campaign success</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trends Chart and Channel Breakdown -->
    <div class="row mb-4">
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-area me-2 text-primary"></i>
                        Revenue Trends
                    </h5>
                    <small class="text-muted">Daily revenue over selected period</small>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="height: 300px;">
                        <canvas id="revenueTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2 text-success"></i>
                        Channel Revenue
                    </h5>
                    <small class="text-muted">Revenue by communication channel</small>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="height: 300px;">
                        <canvas id="channelRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers and Channel Breakdown -->
    <div class="row mb-4">
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-crown me-2 text-warning"></i>
                            Top Customers by Revenue
                        </h5>
                        <small class="text-muted">Highest value customers this period</small>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="exportRevenue('customers')">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Company</th>
                                    <th class="border-0">Email Interactions</th>
                                    <th class="border-0">SMS Interactions</th>
                                    <th class="border-0">Est. Revenue</th>
                                    <th class="border-0">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers as $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-initial bg-primary text-white rounded-circle">
                                                    {{ strtoupper(substr($customer->first_name, 0, 1)) }}{{ strtoupper(substr($customer->last_name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ $customer->first_name }} {{ $customer->last_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $customer->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $customer->company ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $customer->email_interactions }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $customer->sms_interactions }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">${{ number_format($customer->estimated_revenue, 2) }}</strong>
                                    </td>
                                    <td>
                                        <a href="{{ route('contacts.show', $customer->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No customer data available for this period</p>
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
                        <i class="fas fa-chart-bar me-2 text-info"></i>
                        Channel Performance
                    </h5>
                    <small class="text-muted">Detailed channel breakdown</small>
                </div>
                <div class="card-body">
                    @foreach($channelRevenue as $channel => $data)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @if($channel === 'email')
                                    <i class="fas fa-envelope fa-lg text-primary"></i>
                                @elseif($channel === 'sms')
                                    <i class="fas fa-sms fa-lg text-success"></i>
                                @elseif($channel === 'whatsapp')
                                    <i class="fab fa-whatsapp fa-lg text-success"></i>
                                @endif
                            </div>
                            <div>
                                <strong>{{ ucfirst($channel) }}</strong>
                                <br>
                                <small class="text-muted">{{ $data['count'] }} messages</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <strong class="text-success">${{ number_format($data['revenue'], 2) }}</strong>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-tools me-2 text-primary"></i>
                        Quick Actions
                    </h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.revenue.monthly') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar-alt mb-2 d-block fa-2x"></i>
                                Monthly Analysis
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.revenue.customers') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-users mb-2 d-block fa-2x"></i>
                                Customer Analytics
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.revenue.forecast') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-crystal-ball mb-2 d-block fa-2x"></i>
                                Revenue Forecast
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button onclick="exportRevenue('all')" class="btn btn-outline-info w-100">
                                <i class="fas fa-file-export mb-2 d-block fa-2x"></i>
                                Export All Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Date Range Modal -->
<div class="modal fade" id="customDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Custom Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customDateForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyCustomDate()">Apply</button>
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
    
    .period-filter.btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let revenueTrendsChart;
let channelRevenueChart;
let currentPeriod = '30_days';

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
            loadRevenueData(currentPeriod);
        });
    });
});

function initializeCharts() {
    // Revenue Trends Chart
    const trendsCtx = document.getElementById('revenueTrendsChart').getContext('2d');
    revenueTrendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: @json(collect($trends)->pluck('formatted_date')),
            datasets: [{
                label: 'Revenue ($)',
                data: @json(collect($trends)->pluck('revenue')),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
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
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Channel Revenue Chart
    const channelCtx = document.getElementById('channelRevenueChart').getContext('2d');
    const channelData = @json($channelRevenue);
    
    channelRevenueChart = new Chart(channelCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(channelData).map(channel => channel.charAt(0).toUpperCase() + channel.slice(1)),
            datasets: [{
                data: Object.values(channelData).map(data => data.revenue),
                backgroundColor: [
                    '#007bff',  // Email - Blue
                    '#28a745',  // SMS - Green
                    '#25D366'   // WhatsApp - WhatsApp Green
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
                    position: 'bottom'
                }
            }
        }
    });
}

function loadRevenueData(period) {
    // Show loading state
    const loadingOverlay = '<div class="d-flex justify-content-center align-items-center" style="height: 200px;"><div class="spinner-border text-primary" role="status"></div></div>';
    
    fetch(`{{ route('admin.revenue.stats') }}?period=${period}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        updateStatsCards(data.stats);
        updateCharts(data.trends, data.channel_breakdown);
    })
    .catch(error => {
        console.error('Error loading revenue data:', error);
        showToast('Error loading revenue data', 'error');
    });
}

function updateStatsCards(stats) {
    document.getElementById('totalRevenue').textContent = '$' + parseFloat(stats.total_revenue || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('avgOrderValue').textContent = '$' + parseFloat(stats.average_order_value || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('customerCount').textContent = parseInt(stats.customer_count || 0).toLocaleString();
    document.getElementById('conversionRate').textContent = parseFloat(stats.conversion_rate || 0).toFixed(1) + '%';
}

function updateCharts(trends, channelData) {
    // Update trends chart
    revenueTrendsChart.data.labels = trends.map(item => item.formatted_date);
    revenueTrendsChart.data.datasets[0].data = trends.map(item => item.revenue);
    revenueTrendsChart.update();
    
    // Update channel chart
    channelRevenueChart.data.labels = Object.keys(channelData).map(channel => channel.charAt(0).toUpperCase() + channel.slice(1));
    channelRevenueChart.data.datasets[0].data = Object.values(channelData).map(data => data.revenue);
    channelRevenueChart.update();
}

function exportRevenue(type) {
    const url = new URL('{{ route("admin.revenue.export") }}');
    url.searchParams.append('type', type);
    url.searchParams.append('period', currentPeriod);
    
    window.open(url.toString(), '_blank');
}

function refreshStats() {
    loadRevenueData(currentPeriod);
    showToast('Revenue statistics refreshed', 'success');
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}
</script>
@endpush
