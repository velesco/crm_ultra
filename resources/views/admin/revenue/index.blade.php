@extends('layouts.app')

@section('title', 'Revenue Analytics - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-2xl font-bold text-gray-900">Revenue Analytics</h1>
                            <p class="text-sm text-gray-600">Comprehensive revenue tracking and financial performance analysis</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex rounded-lg shadow-sm bg-white border border-gray-300">
                        <button type="button" class="period-filter px-3 py-2 text-sm font-medium rounded-l-lg border-r border-gray-300 text-gray-700 hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500" data-period="7_days">
                            7 Days
                        </button>
                        <button type="button" class="period-filter px-3 py-2 text-sm font-medium border-r border-gray-300 bg-blue-600 text-white" data-period="30_days">
                            30 Days
                        </button>
                        <button type="button" class="period-filter px-3 py-2 text-sm font-medium border-r border-gray-300 text-gray-700 hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500" data-period="90_days">
                            90 Days
                        </button>
                        <button type="button" class="period-filter px-3 py-2 text-sm font-medium rounded-r-lg text-gray-700 hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500" data-period="this_year">
                            This Year
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150" onclick="exportRevenue('summary')">
                            <i class="fas fa-download mr-2"></i>Export
                        </button>
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150" onclick="refreshStats()">
                            <i class="fas fa-sync mr-2"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Revenue Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="statsCards">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900" id="totalRevenue">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                        @if(isset($stats['revenue_growth']) && $stats['revenue_growth'] != 0)
                            <div class="mt-1 flex items-center text-sm">
                                <i class="fas fa-{{ $stats['revenue_growth'] >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $stats['revenue_growth'] >= 0 ? 'green' : 'red' }}-500 mr-1"></i>
                                <span class="text-{{ $stats['revenue_growth'] >= 0 ? 'green' : 'red' }}-600 font-medium">
                                    {{ number_format(abs($stats['revenue_growth']), 1) }}% vs previous period
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Avg. Order Value</p>
                        <p class="text-2xl font-bold text-gray-900" id="avgOrderValue">${{ number_format($stats['average_order_value'] ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Per campaign</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Active Customers</p>
                        <p class="text-2xl font-bold text-gray-900" id="customerCount">{{ number_format($stats['customer_count'] ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">This period</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-percentage text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Conversion Rate</p>
                        <p class="text-2xl font-bold text-gray-900" id="conversionRate">{{ number_format($stats['conversion_rate'] ?? 0, 1) }}%</p>
                        <p class="text-xs text-gray-500 mt-1">Campaign success</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Trends Chart and Channel Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-chart-area text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Revenue Trends</h3>
                                <p class="text-sm text-gray-600">Daily revenue over selected period</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="relative h-80">
                            <canvas id="revenueTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-chart-pie text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Channel Revenue</h3>
                                <p class="text-sm text-gray-600">Revenue by communication channel</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="relative h-80">
                            <canvas id="channelRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Customers and Channel Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-crown text-yellow-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Top Customers by Revenue</h3>
                                    <p class="text-sm text-gray-600">Highest value customers this period</p>
                                </div>
                            </div>
                            <button class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150" onclick="exportRevenue('customers')">
                                <i class="fas fa-download mr-2"></i>Export
                            </button>
                        </div>
                    </div>
                    <div class="overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">SMS</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($topCustomers as $customer)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-8 h-8">
                                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                        {{ strtoupper(substr($customer->first_name, 0, 1)) }}{{ strtoupper(substr($customer->last_name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $customer->first_name }} {{ $customer->last_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $customer->company ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">{{ $customer->email_interactions }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">{{ $customer->sms_interactions }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="text-sm font-semibold text-green-600">${{ number_format($customer->estimated_revenue, 2) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <a href="{{ route('contacts.show', $customer->id) }}" class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                                                <p class="text-gray-500">No customer data available for this period</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-chart-bar text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Channel Performance</h3>
                                <p class="text-sm text-gray-600">Detailed channel breakdown</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        @foreach($channelRevenue as $channel => $data)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg mr-3
                                    @if($channel === 'email') bg-blue-100
                                    @elseif($channel === 'sms') bg-green-100
                                    @elseif($channel === 'whatsapp') bg-green-100
                                    @endif">
                                    @if($channel === 'email')
                                        <i class="fas fa-envelope text-blue-600 text-lg"></i>
                                    @elseif($channel === 'sms')
                                        <i class="fas fa-sms text-green-600 text-lg"></i>
                                    @elseif($channel === 'whatsapp')
                                        <i class="fab fa-whatsapp text-green-600 text-lg"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ ucfirst($channel) }}</div>
                                    <div class="text-xs text-gray-500">{{ $data['count'] }} messages</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-semibold text-green-600">${{ number_format($data['revenue'], 2) }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-tools text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        <p class="text-sm text-gray-600">Access detailed analytics and reports</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.revenue.monthly') }}" class="group p-6 bg-gray-50 rounded-xl hover:bg-blue-50 border-2 border-transparent hover:border-blue-200 transition-all duration-200 text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition-colors duration-200">
                            <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                        </div>
                        <div class="text-sm font-medium text-gray-900 group-hover:text-blue-700">Monthly Analysis</div>
                    </a>

                    <a href="{{ route('admin.revenue.customers') }}" class="group p-6 bg-gray-50 rounded-xl hover:bg-green-50 border-2 border-transparent hover:border-green-200 transition-all duration-200 text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:bg-green-200 transition-colors duration-200">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                        <div class="text-sm font-medium text-gray-900 group-hover:text-green-700">Customer Analytics</div>
                    </a>

                    <a href="{{ route('admin.revenue.forecast') }}" class="group p-6 bg-gray-50 rounded-xl hover:bg-yellow-50 border-2 border-transparent hover:border-yellow-200 transition-all duration-200 text-center">
                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:bg-yellow-200 transition-colors duration-200">
                            <i class="fas fa-crystal-ball text-yellow-600 text-xl"></i>
                        </div>
                        <div class="text-sm font-medium text-gray-900 group-hover:text-yellow-700">Revenue Forecast</div>
                    </a>

                    <button onclick="exportRevenue('all')" class="group p-6 bg-gray-50 rounded-xl hover:bg-indigo-50 border-2 border-transparent hover:border-indigo-200 transition-all duration-200 text-center w-full">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:bg-indigo-200 transition-colors duration-200">
                            <i class="fas fa-file-export text-indigo-600 text-xl"></i>
                        </div>
                        <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-700">Export All Data</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Date Range Modal -->
<div x-data="{ showModal: false }" 
     x-show="showModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="showModal = false"
     id="customDateModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
             @click="showModal = false">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Custom Date Range</h3>
                    <form id="customDateForm" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="applyCustomDate()" class="w-full inline-flex justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto transition duration-150">
                    Apply
                </button>
                <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center px-4 py-2 bg-white text-gray-900 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto transition duration-150">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

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
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('text-gray-700', 'hover:bg-gray-50');
            });
            this.classList.remove('text-gray-700', 'hover:bg-gray-50');
            this.classList.add('bg-blue-600', 'text-white');
            
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
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 2
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
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
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
                    '#3B82F6',  // Email - Blue
                    '#10B981',  // SMS - Green
                    '#25D366'   // WhatsApp - WhatsApp Green
                ],
                borderWidth: 0,
                cutout: '60%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

function loadRevenueData(period) {
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

function applyCustomDate() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (!startDate || !endDate) {
        showToast('Please select both start and end dates', 'error');
        return;
    }
    
    // Close modal and load data for custom range
    document.getElementById('customDateModal').style.display = 'none';
    loadRevenueData(`${startDate}_${endDate}`);
    showToast('Custom date range applied', 'success');
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white text-sm font-medium transition-all duration-300 transform translate-x-0 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    }`;
    toast.innerHTML = message;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}
</script>
@endpush
