@extends('layouts.app')

@section('title', 'Campaign Performance')

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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Campaign Performance</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Analyze your email campaign performance and engagement metrics
                    </p>
                </div>
            </div>
            <div class="flex space-x-3">
                <select id="campaignFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm">
                    <option value="all">All Campaigns</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="draft">Draft</option>
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

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-paper-plane text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Campaigns Sent</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($performance['campaigns_sent']) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        @if($performance['campaigns_growth'] >= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">+{{ number_format($performance['campaigns_growth'], 1) }}%</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($performance['campaigns_growth'], 1) }}%</span>
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
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope-open text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Open Rate</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($performance['avg_open_rate'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $performance['avg_open_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-mouse-pointer text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Click Rate</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($performance['avg_click_rate'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $performance['avg_click_rate'] * 5 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Bounce Rate</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($performance['avg_bounce_rate'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-orange-600 h-2 rounded-full" style="width: {{ $performance['avg_bounce_rate'] * 10 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-times text-red-600 dark:text-red-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Unsubscribe Rate</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($performance['avg_unsubscribe_rate'], 2) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        @if($performance['unsubscribe_trend'] <= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">{{ number_format($performance['unsubscribe_trend'], 2) }}%</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">+{{ number_format($performance['unsubscribe_trend'], 2) }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-2">vs previous period</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Campaign Engagement Trend -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Engagement Trends</h3>
                    <div class="flex space-x-2">
                        <button class="text-xs px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg">Opens</button>
                        <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">Clicks</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="engagementTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Performing Campaigns -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Performing Campaigns</h3>
                <div class="h-64">
                    <canvas id="topCampaignsChart"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach($performance['top_campaigns'] as $campaign)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'][$loop->index % 5] }}"></div>
                            <span class="text-gray-600 dark:text-gray-400">{{ Str::limit($campaign['name'], 25) }}</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($campaign['open_rate'], 1) }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Performance Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Campaign Details</h3>
                <div class="flex space-x-2">
                    <input type="text" placeholder="Search campaigns..." class="w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    <button class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campaign</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recipients</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Open Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Click Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conversions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sent Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($performance['campaigns'] as $campaign)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white text-sm font-semibold">
                                        {{ strtoupper(substr($campaign['name'], 0, 2)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $campaign['name'] }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $campaign['subject'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $campaign['status'] === 'sent' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 
                                       ($campaign['status'] === 'draft' ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' : 
                                        'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200') }}">
                                    {{ ucfirst($campaign['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ number_format($campaign['recipients']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $campaign['open_rate'] }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($campaign['open_rate'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $campaign['click_rate'] * 5 }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ number_format($campaign['click_rate'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ number_format($campaign['conversions']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $campaign['sent_at']->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('email.campaigns.show', $campaign['id']) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('email.campaigns.edit', $campaign['id']) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-500">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="text-red-600 dark:text-red-400 hover:text-red-500">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ $performance['campaigns']->firstItem() }} to {{ $performance['campaigns']->lastItem() }} of {{ $performance['campaigns']->total() }} campaigns
                </div>
                {{ $performance['campaigns']->links() }}
            </div>
        </div>
    </div>

    <!-- A/B Test Results -->
    @if($performance['ab_tests']->count() > 0)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">A/B Test Results</h3>
            
            <div class="space-y-6">
                @foreach($performance['ab_tests'] as $test)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $test['name'] }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $test['description'] }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $test['status'] === 'completed' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' }}">
                            {{ ucfirst($test['status']) }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Variant A -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-sm font-medium text-gray-900 dark:text-white">Variant A</h5>
                                @if($test['winning_variant'] === 'A')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                    <i class="fas fa-crown mr-1"></i>Winner
                                </span>
                                @endif
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Open Rate:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($test['variant_a_open_rate'], 1) }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Click Rate:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($test['variant_a_click_rate'], 1) }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Conversions:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $test['variant_a_conversions'] }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Variant B -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="text-sm font-medium text-gray-900 dark:text-white">Variant B</h5>
                                @if($test['winning_variant'] === 'B')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                    <i class="fas fa-crown mr-1"></i>Winner
                                </span>
                                @endif
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Open Rate:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($test['variant_b_open_rate'], 1) }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Click Rate:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($test['variant_b_click_rate'], 1) }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Conversions:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $test['variant_b_conversions'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($test['confidence_level'])
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-chart-line text-blue-600 dark:text-blue-400 mr-2"></i>
                            <span class="text-sm text-blue-800 dark:text-blue-200">
                                Statistical Significance: <strong>{{ number_format($test['confidence_level'], 1) }}%</strong>
                                @if($test['confidence_level'] >= 95)
                                    <span class="text-green-600 dark:text-green-400 ml-2">(Statistically significant)</span>
                                @else
                                    <span class="text-orange-600 dark:text-orange-400 ml-2">(More data needed)</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#D1D5DB' : '#374151';
    const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

    // Engagement Trend Chart
    const engagementCtx = document.getElementById('engagementTrendChart').getContext('2d');
    new Chart(engagementCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($performance['trend_labels']) !!},
            datasets: [{
                label: 'Open Rate',
                data: {!! json_encode($performance['open_trend']) !!},
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }, {
                label: 'Click Rate',
                data: {!! json_encode($performance['click_trend']) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Top Campaigns Chart
    const topCampaignsCtx = document.getElementById('topCampaignsChart').getContext('2d');
    new Chart(topCampaignsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($performance['top_campaigns'], 'name')) !!},
            datasets: [{
                label: 'Open Rate (%)',
                data: {!! json_encode(array_column($performance['top_campaigns'], 'open_rate')) !!},
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
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
                    ticks: { color: textColor },
                    grid: { color: gridColor }
                }
            }
        }
    });

    // Filter handlers
    document.getElementById('campaignFilter').addEventListener('change', function() {
        updateFilters();
    });
    
    document.getElementById('dateRange').addEventListener('change', function() {
        updateFilters();
    });
    
    function updateFilters() {
        const campaignFilter = document.getElementById('campaignFilter').value;
        const dateRange = document.getElementById('dateRange').value;
        window.location.href = `{{ route('reports.campaigns') }}?status=${campaignFilter}&period=${dateRange}`;
    }
});
</script>
@endpush
