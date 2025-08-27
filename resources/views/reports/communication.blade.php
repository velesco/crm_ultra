@extends('layouts.app')

@section('title', 'Communication Statistics')

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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Communication Statistics</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Cross-channel communication analytics for Email, SMS, and WhatsApp
                    </p>
                </div>
            </div>
            <div class="flex space-x-3">
                <select id="channelFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm">
                    <option value="all">All Channels</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="whatsapp">WhatsApp</option>
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

    <!-- Communication Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-comments text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Messages</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_messages']) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        @if($stats['messages_growth'] >= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">+{{ number_format($stats['messages_growth'], 1) }}%</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($stats['messages_growth'], 1) }}%</span>
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
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Delivery Rate</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['avg_delivery_rate'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['avg_delivery_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-reply text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Response Rate</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['avg_response_rate'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $stats['avg_response_rate'] * 2 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Response Time</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['avg_response_time'] }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        @if($stats['response_time_trend'] <= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">{{ number_format(abs($stats['response_time_trend']), 0) }}% faster</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($stats['response_time_trend'], 0) }}% slower</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-2">vs previous period</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Channel Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Email Statistics -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-envelope text-blue-600 dark:text-blue-400 mr-2"></i>
                        Email
                    </h3>
                    <a href="{{ route('reports.campaigns') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 text-sm font-medium">
                        View Details →
                    </a>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Messages Sent</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['email']['sent']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Open Rate</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['email']['open_rate'], 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Click Rate</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['email']['click_rate'], 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Bounce Rate</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['email']['bounce_rate'], 1) }}%</span>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stats['email']['open_rate'] }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Open rate performance</p>
                </div>
            </div>
        </div>

        <!-- SMS Statistics -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-sms text-green-600 dark:text-green-400 mr-2"></i>
                        SMS
                    </h3>
                    <a href="{{ route('sms.index') }}" class="text-green-600 dark:text-green-400 hover:text-green-500 text-sm font-medium">
                        View Details →
                    </a>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Messages Sent</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['sms']['sent']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Delivery Rate</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['sms']['delivery_rate'], 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Response Rate</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['sms']['response_rate'], 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Cost</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">${{ number_format($stats['sms']['total_cost'], 2) }}</span>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['sms']['delivery_rate'] }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Delivery rate performance</p>
                </div>
            </div>
        </div>

        <!-- WhatsApp Statistics -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <i class="fab fa-whatsapp text-green-600 dark:text-green-400 mr-2"></i>
                        WhatsApp
                    </h3>
                    <a href="{{ route('whatsapp.stats') }}" class="text-green-600 dark:text-green-400 hover:text-green-500 text-sm font-medium">
                        View Details →
                    </a>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Messages Sent</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['whatsapp']['sent']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Read Rate</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['whatsapp']['read_rate'], 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Response Rate</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['whatsapp']['response_rate'], 1) }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Active Sessions</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $stats['whatsapp']['active_sessions'] }}</span>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['whatsapp']['read_rate'] }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Read rate performance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Communication Trends Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Message Volume Trend -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Message Volume Trends</h3>
                    <div class="flex space-x-2">
                        <button class="text-xs px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg">Email</button>
                        <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">SMS</button>
                        <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">WhatsApp</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="volumeTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Channel Performance Comparison -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Channel Performance Comparison</h3>
                <div class="h-64">
                    <canvas id="channelComparisonChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Email</div>
                        <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ number_format($stats['email']['open_rate'], 1) }}%</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">SMS</div>
                        <div class="text-lg font-semibold text-green-600 dark:text-green-400">{{ number_format($stats['sms']['delivery_rate'], 1) }}%</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">WhatsApp</div>
                        <div class="text-lg font-semibold text-purple-600 dark:text-purple-400">{{ number_format($stats['whatsapp']['read_rate'], 1) }}%</div>
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
document.addEventListener('DOMContentLoaded', function() {
    const isDarkMode = document.documentElement.classList.contains('dark');
    const textColor = isDarkMode ? '#D1D5DB' : '#374151';
    const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

    // Volume Trend Chart
    const volumeCtx = document.getElementById('volumeTrendChart').getContext('2d');
    new Chart(volumeCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($stats['trend_labels']) !!},
            datasets: [{
                label: 'Email',
                data: {!! json_encode($stats['email_trend']) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }, {
                label: 'SMS',
                data: {!! json_encode($stats['sms_trend']) !!},
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }, {
                label: 'WhatsApp',
                data: {!! json_encode($stats['whatsapp_trend']) !!},
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
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

    // Channel Comparison Chart
    const comparisonCtx = document.getElementById('channelComparisonChart').getContext('2d');
    new Chart(comparisonCtx, {
        type: 'radar',
        data: {
            labels: ['Delivery Rate', 'Response Rate', 'Speed', 'Cost Efficiency', 'Engagement'],
            datasets: [{
                label: 'Email',
                data: [{{ $stats['email']['delivery_rate'] }}, {{ $stats['email']['response_rate'] }}, 65, 90, {{ $stats['email']['engagement_score'] }}],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2
            }, {
                label: 'SMS',
                data: [{{ $stats['sms']['delivery_rate'] }}, {{ $stats['sms']['response_rate'] }}, 95, 70, {{ $stats['sms']['engagement_score'] }}],
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2
            }, {
                label: 'WhatsApp',
                data: [{{ $stats['whatsapp']['delivery_rate'] }}, {{ $stats['whatsapp']['response_rate'] }}, 90, 85, {{ $stats['whatsapp']['engagement_score'] }}],
                borderColor: '#8B5CF6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2
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
                r: {
                    ticks: { color: textColor },
                    grid: { color: gridColor },
                    pointLabels: { color: textColor }
                }
            }
        }
    });

    // Filter handlers
    document.getElementById('channelFilter').addEventListener('change', function() {
        updateFilters();
    });
    
    document.getElementById('dateRange').addEventListener('change', function() {
        updateFilters();
    });
    
    function updateFilters() {
        const channelFilter = document.getElementById('channelFilter').value;
        const dateRange = document.getElementById('dateRange').value;
        window.location.href = `{{ route('reports.communication') }}?channel=${channelFilter}&period=${dateRange}`;
    }
});
</script>
@endpush
