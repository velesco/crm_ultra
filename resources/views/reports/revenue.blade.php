@extends('layouts.app')

@section('title', 'Revenue & ROI Analytics')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('reports.index') }}" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Revenue & ROI Analytics</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Track sales performance, conversions, and return on investment
                    </p>
                </div>
            </div>
            <div class="flex space-x-3">
                <select id="currencyFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm">
                    <option value="USD">USD ($)</option>
                    <option value="EUR">EUR (€)</option>
                    <option value="RON">RON (Lei)</option>
                </select>
                <select id="dateRange" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 3 months</option>
                    <option value="365">Last year</option>
                </select>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
                    <i class="fas fa-download mr-2"></i>
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Revenue Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Revenue</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($revenue['total_revenue'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        @if($revenue['revenue_growth'] >= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">+{{ number_format($revenue['revenue_growth'], 1) }}%</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($revenue['revenue_growth'], 1) }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-2">vs previous period</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Campaign ROI</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($revenue['campaign_roi'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($revenue['campaign_roi'], 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-plus text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Cost per Lead</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($revenue['cost_per_lead'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        @if($revenue['cpl_trend'] <= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">{{ number_format(abs($revenue['cpl_trend']), 1) }}% lower</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($revenue['cpl_trend'], 1) }}% higher</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-2">vs previous period</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Conversions</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($revenue['total_conversions']) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        <span class="text-blue-600 dark:text-blue-400 font-medium">{{ number_format($revenue['conversion_rate'], 1) }}%</span>
                        <span class="text-gray-500 dark:text-gray-400 ml-2">conversion rate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Trend -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue Trend</h3>
                    <div class="flex space-x-2">
                        <button class="text-xs px-3 py-1 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-lg">Revenue</button>
                        <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">Costs</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="revenueTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Sources -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue by Source</h3>
                <div class="h-64">
                    <canvas id="revenueSourcesChart"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach($revenue['revenue_sources'] as $source => $amount)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'][$loop->index % 5] }}"></div>
                            <span class="text-gray-600 dark:text-gray-400 capitalize">{{ $source }}</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">${{ number_format($amount, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ROI Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- ROI by Channel -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">ROI by Channel</h3>
                
                <div class="space-y-4">
                    @foreach($revenue['channel_roi'] as $channel => $data)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center 
                                {{ $channel === 'email' ? 'bg-blue-100 dark:bg-blue-900' : 
                                   ($channel === 'sms' ? 'bg-green-100 dark:bg-green-900' : 'bg-purple-100 dark:bg-purple-900') }}">
                                <i class="fas {{ $channel === 'email' ? 'fa-envelope text-blue-600 dark:text-blue-400' : 
                                              ($channel === 'sms' ? 'fa-sms text-green-600 dark:text-green-400' : 
                                               'fa-comment text-purple-600 dark:text-purple-400') }}"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($channel) }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">${{ number_format($data['revenue'], 2) }} revenue</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold {{ $data['roi'] >= 100 ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                {{ number_format($data['roi'], 1) }}%
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">ROI</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    <canvas id="channelROIChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Conversion Funnel -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Conversion Funnel</h3>
                
                <div class="space-y-4">
                    @foreach($revenue['conversion_funnel'] as $step)
                    <div class="relative">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $step['name'] }}</span>
                            <div class="text-right">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($step['count']) }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ number_format($step['percentage'], 1) }}%)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-500" 
                                 style="width: {{ $step['percentage'] }}%"></div>
                        </div>
                        @if(!$loop->last)
                        <div class="flex justify-center mt-2">
                            <div class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                {{ number_format($step['drop_rate'], 1) }}% drop
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">Overall Conversion Rate</div>
                            <div class="text-xs text-blue-700 dark:text-blue-300">From visitors to customers</div>
                        </div>
                        <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($revenue['overall_conversion_rate'], 2) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Forecasting -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue Forecasting</h3>
                <div class="flex space-x-2">
                    <button class="text-xs px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg">Next Month</button>
                    <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">Next Quarter</button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">${{ number_format($revenue['forecast']['next_month'], 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Projected Next Month</div>
                    <div class="mt-2 flex items-center justify-center text-sm">
                        <i class="fas fa-arrow-up text-green-500 mr-1"></i>
                        <span class="text-green-600 dark:text-green-400">{{ number_format($revenue['forecast']['confidence'], 1) }}% confidence</span>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">${{ number_format($revenue['forecast']['next_quarter'], 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Projected Next Quarter</div>
                    <div class="mt-2 flex items-center justify-center text-sm">
                        <i class="fas fa-chart-line text-blue-500 mr-1"></i>
                        <span class="text-blue-600 dark:text-blue-400">Based on trends</span>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">${{ number_format($revenue['forecast']['annual_target'], 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Annual Target</div>
                    <div class="mt-2 flex items-center justify-center text-sm">
                        <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $revenue['forecast']['target_progress'] }}%"></div>
                        </div>
                        <span class="text-purple-600 dark:text-purple-400">{{ number_format($revenue['forecast']['target_progress'], 1) }}%</span>
                    </div>
                </div>
            </div>
            
            <div class="h-64">
                <canvas id="revenueForecastChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#D1D5DB' : '#374151';
    const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

    // Revenue Trend Chart
    const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
    new Chart(revenueTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenue['trend_labels']) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($revenue['revenue_trend']) !!},
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Costs',
                data: {!! json_encode($revenue['cost_trend']) !!},
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    ticks: { 
                        color: textColor,
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Revenue Sources Chart
    const revenueSourcesCtx = document.getElementById('revenueSourcesChart').getContext('2d');
    new Chart(revenueSourcesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($revenue['revenue_sources'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($revenue['revenue_sources'])) !!},
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'
                ],
                borderWidth: 0
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

    // Channel ROI Chart
    const channelROICtx = document.getElementById('channelROIChart').getContext('2d');
    new Chart(channelROICtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($revenue['channel_roi'])) !!},
            datasets: [{
                label: 'ROI (%)',
                data: {!! json_encode(array_column($revenue['channel_roi'], 'roi')) !!},
                backgroundColor: ['#3B82F6', '#10B981', '#8B5CF6'],
                borderWidth: 0
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
                x: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    ticks: { 
                        color: textColor,
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Revenue Forecast Chart
    const forecastCtx = document.getElementById('revenueForecastChart').getContext('2d');
    new Chart(forecastCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenue['forecast']['labels']) !!},
            datasets: [{
                label: 'Historical Revenue',
                data: {!! json_encode($revenue['forecast']['historical']) !!},
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: false
            }, {
                label: 'Projected Revenue',
                data: {!! json_encode($revenue['forecast']['projected']) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor }
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                },
                y: {
                    ticks: { 
                        color: textColor,
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Filter handlers
    document.getElementById('currencyFilter').addEventListener('change', function() {
        updateFilters();
    });
    
    document.getElementById('dateRange').addEventListener('change', function() {
        updateFilters();
    });
    
    function updateFilters() {
        const currency = document.getElementById('currencyFilter').value;
        const dateRange = document.getElementById('dateRange').value;
        window.location.href = `{{ route('reports.revenue') }}?currency=${currency}&period=${dateRange}`;
    }
});
</script>
@endpush
