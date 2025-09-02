@extends('layouts.app')

@section('title', 'Monthly Revenue Analysis - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-2xl font-bold text-gray-900">Monthly Revenue Analysis</h1>
                            <p class="text-sm text-gray-600">Detailed monthly revenue breakdown and year-over-year comparison</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.revenue.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Overview
                    </a>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            <i class="fas fa-calendar mr-2"></i>{{ $year }}
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <a href="{{ route('admin.revenue.monthly', ['year' => $y]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 {{ $y == $year ? 'bg-blue-50 text-blue-700 font-medium' : '' }} {{ $loop->first ? 'rounded-t-lg' : '' }} {{ $loop->last ? 'rounded-b-lg' : '' }}">{{ $y }}</a>
                            @endfor
                        </div>
                    </div>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150" onclick="exportMonthlyData()">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Year Comparison Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-md transition-all duration-200">
                <p class="text-sm font-medium text-gray-600 mb-2">{{ $year }} Total Revenue</p>
                <p class="text-3xl font-bold text-blue-600 mb-4">${{ number_format($yearComparison['current_year'], 2) }}</p>
                <div class="bg-blue-50 rounded-lg p-4">
                    <i class="fas fa-chart-line text-blue-600 text-3xl mb-3"></i>
                    <p class="text-gray-600 text-sm">Current Year Performance</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-md transition-all duration-200">
                <p class="text-sm font-medium text-gray-600 mb-2">{{ $year - 1 }} Total Revenue</p>
                <p class="text-3xl font-bold text-gray-600 mb-4">${{ number_format($yearComparison['previous_year'], 2) }}</p>
                <div class="bg-gray-50 rounded-lg p-4">
                    <i class="fas fa-history text-gray-600 text-3xl mb-3"></i>
                    <p class="text-gray-600 text-sm">Previous Year Performance</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-md transition-all duration-200">
                <p class="text-sm font-medium text-gray-600 mb-2">Year-over-Year Growth</p>
                <p class="text-3xl font-bold mb-4 {{ $yearComparison['growth_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($yearComparison['growth_percentage'], 1) }}%
                </p>
                <div class="rounded-lg p-4 {{ $yearComparison['growth_percentage'] >= 0 ? 'bg-green-50' : 'bg-red-50' }}">
                    <i class="fas fa-{{ $yearComparison['growth_percentage'] >= 0 ? 'arrow-up' : 'arrow-down' }} {{ $yearComparison['growth_percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }} text-3xl mb-3"></i>
                    <p class="text-gray-600 text-sm">Growth Rate</p>
                </div>
            </div>
        </div>

        <!-- Monthly Trends Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Monthly Revenue Trends</h3>
                        <p class="text-sm text-gray-600">{{ $year }} vs {{ $year - 1 }} comparison</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="relative h-96">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Statistics Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-table text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Monthly Breakdown - {{ $year }}</h3>
                            <p class="text-sm text-gray-600">Detailed monthly statistics and metrics</p>
                        </div>
                    </div>
                    <div class="flex rounded-lg shadow-sm bg-white border border-gray-300" x-data="{ view: 'table' }">
                        <button @click="view = 'table'; toggleView('table')" :class="view === 'table' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50'" class="px-3 py-2 text-sm font-medium rounded-l-lg border-r border-gray-300 focus:z-10 focus:ring-2 focus:ring-blue-500 transition duration-150" id="tableViewBtn">
                            <i class="fas fa-table mr-1"></i> Table
                        </button>
                        <button @click="view = 'cards'; toggleView('cards')" :class="view === 'cards' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-50'" class="px-3 py-2 text-sm font-medium rounded-r-lg focus:z-10 focus:ring-2 focus:ring-blue-500 transition duration-150" id="cardsViewBtn">
                            <i class="fas fa-th mr-1"></i> Cards
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Table View -->
            <div id="tableView" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Campaigns</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg per Customer</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Growth</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($monthlyStats as $index => $month)
                                @php
                                    $prevMonth = $index > 0 ? $monthlyStats[$index - 1] : null;
                                    $growthRate = $prevMonth && $prevMonth['revenue'] > 0 
                                        ? (($month['revenue'] - $prevMonth['revenue']) / $prevMonth['revenue']) * 100 
                                        : 0;
                                    $avgPerCustomer = $month['customers'] > 0 ? $month['revenue'] / $month['customers'] : 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $month['month_name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $year }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="font-semibold text-green-600">${{ number_format($month['revenue'], 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900">{{ number_format($month['customers']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900">{{ number_format($month['campaigns']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900">${{ number_format($avgPerCustomer, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($growthRate != 0)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $growthRate >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                <i class="fas fa-{{ $growthRate >= 0 ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                                {{ number_format(abs($growthRate), 1) }}%
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $performance = 'yellow';
                                            $icon = 'minus';
                                            if ($month['revenue'] > 1000) {
                                                $performance = 'green';
                                                $icon = 'check';
                                            } elseif ($month['revenue'] > 500) {
                                                $performance = 'blue';
                                                $icon = 'arrow-up';
                                            }
                                        @endphp
                                        <span class="inline-flex p-1 rounded-full bg-{{ $performance }}-100">
                                            <i class="fas fa-{{ $icon }} text-{{ $performance }}-600 text-xs"></i>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Total</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format(collect($monthlyStats)->sum('revenue'), 2) }}</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format(collect($monthlyStats)->sum('customers')) }}</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format(collect($monthlyStats)->sum('campaigns')) }}</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format(collect($monthlyStats)->sum('revenue') / max(collect($monthlyStats)->sum('customers'), 1), 2) }}</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">-</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-900">-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Cards View -->
            <div id="cardsView" style="display: none;">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($monthlyStats as $index => $month)
                            @php
                                $prevMonth = $index > 0 ? $monthlyStats[$index - 1] : null;
                                $growthRate = $prevMonth && $prevMonth['revenue'] > 0 
                                    ? (($month['revenue'] - $prevMonth['revenue']) / $prevMonth['revenue']) * 100 
                                    : 0;
                            @endphp
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition duration-150">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-semibold text-gray-900">{{ $month['month_name'] }}</h4>
                                    @if($growthRate != 0)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $growthRate >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <i class="fas fa-{{ $growthRate >= 0 ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                            {{ number_format(abs($growthRate), 1) }}%
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-2xl font-bold text-green-600">${{ number_format($month['revenue'], 2) }}</p>
                                    <p class="text-xs text-gray-500">Revenue</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div class="border-r border-gray-300 pr-2">
                                        <div class="font-semibold text-blue-600">{{ $month['customers'] }}</div>
                                        <div class="text-xs text-gray-500">Customers</div>
                                    </div>
                                    <div class="pl-2">
                                        <div class="font-semibold text-indigo-600">{{ $month['campaigns'] }}</div>
                                        <div class="text-xs text-gray-500">Campaigns</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Insights -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-lightbulb text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Key Insights for {{ $year }}</h3>
                        <p class="text-sm text-gray-600">Automated analysis and recommendations</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-blue-900 font-semibold mb-4 flex items-center">
                            <i class="fas fa-chart-line mr-2"></i>Performance Analysis
                        </h4>
                        @php
                            $bestMonth = collect($monthlyStats)->sortByDesc('revenue')->first();
                            $worstMonth = collect($monthlyStats)->sortBy('revenue')->first();
                            $avgRevenue = collect($monthlyStats)->avg('revenue');
                        @endphp
                        <div class="space-y-4">
                            <div class="flex items-start p-4 bg-yellow-50 rounded-lg">
                                <i class="fas fa-crown text-yellow-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Best month</div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $bestMonth['month_name'] }} with ${{ number_format($bestMonth['revenue'], 2) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-blue-50 rounded-lg">
                                <i class="fas fa-chart-line text-blue-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Average monthly revenue</div>
                                    <p class="text-sm text-gray-600 mt-1">${{ number_format($avgRevenue, 2) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-green-50 rounded-lg">
                                <i class="fas fa-users text-green-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Total customers served</div>
                                    <p class="text-sm text-gray-600 mt-1">{{ number_format(collect($monthlyStats)->sum('customers')) }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-indigo-50 rounded-lg">
                                <i class="fas fa-bullhorn text-indigo-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Total campaigns</div>
                                    <p class="text-sm text-gray-600 mt-1">{{ number_format(collect($monthlyStats)->sum('campaigns')) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-green-900 font-semibold mb-4 flex items-center">
                            <i class="fas fa-target mr-2"></i>Recommendations for {{ $year + 1 }}
                        </h4>
                        
                        <div class="space-y-4">
                            @if($yearComparison['growth_percentage'] >= 0)
                                <div class="flex items-start p-4 bg-green-50 rounded-lg">
                                    <i class="fas fa-rocket text-green-600 mr-3 mt-1"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Capitalize on growth</div>
                                        <p class="text-sm text-gray-600 mt-1">With {{ number_format($yearComparison['growth_percentage'], 1) }}% growth, consider increasing marketing spend and expanding successful campaigns</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start p-4 bg-blue-50 rounded-lg">
                                    <i class="fas fa-target text-blue-600 mr-3 mt-1"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Set ambitious targets</div>
                                        <p class="text-sm text-gray-600 mt-1">Target ${{ number_format($yearComparison['current_year'] * 1.15, 2) }} revenue for next year (+15% stretch goal)</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start p-4 bg-yellow-50 rounded-lg">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Address decline</div>
                                        <p class="text-sm text-gray-600 mt-1">Revenue decreased by {{ number_format(abs($yearComparison['growth_percentage']), 1) }}%. Focus on customer retention and campaign optimization</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start p-4 bg-red-50 rounded-lg">
                                    <i class="fas fa-shield-alt text-red-600 mr-3 mt-1"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Recovery strategy</div>
                                        <p class="text-sm text-gray-600 mt-1">Implement recovery plan targeting {{ $bestMonth['month_name'] }} performance levels across all months</p>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex items-start p-4 bg-indigo-50 rounded-lg">
                                <i class="fas fa-calendar text-indigo-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Seasonal planning</div>
                                    <p class="text-sm text-gray-600 mt-1">Plan major campaigns around {{ $bestMonth['month_name'] }} pattern for maximum impact</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-purple-50 rounded-lg">
                                <i class="fas fa-users text-purple-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Customer focus</div>
                                    <p class="text-sm text-gray-600 mt-1">With {{ number_format(collect($monthlyStats)->sum('customers')) }} customers served, focus on increasing average value per customer</p>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let monthlyTrendsChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeMonthlyChart();
});

function initializeMonthlyChart() {
    const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
    
    // Data for current year and previous year
    const currentYearData = @json(collect($monthlyStats)->pluck('revenue'));
    const previousYearData = Array.from({length: 12}, () => Math.floor(Math.random() * 3000) + 1000); // Sample data
    
    monthlyTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: '{{ $year }}',
                    data: currentYearData,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: false,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3
                },
                {
                    label: '{{ $year - 1 }}',
                    data: previousYearData,
                    borderColor: '#9CA3AF',
                    backgroundColor: 'rgba(156, 163, 175, 0.1)',
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                    borderDash: [5, 5]
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
}

function toggleView(view) {
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    
    if (view === 'table') {
        tableView.style.display = 'block';
        cardsView.style.display = 'none';
    } else {
        tableView.style.display = 'none';
        cardsView.style.display = 'block';
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
