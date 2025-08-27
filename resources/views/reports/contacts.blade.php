@extends('layouts.app')

@section('title', 'Contact Analytics')

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
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Contact Analytics</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Detailed insights into your contact database performance
                    </p>
                </div>
            </div>
            <div class="flex space-x-3">
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

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Contacts</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($analytics['total_contacts']) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        @if($analytics['total_growth'] >= 0)
                            <span class="text-green-600 dark:text-green-400 font-medium">+{{ number_format($analytics['total_growth'], 1) }}%</span>
                        @else
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($analytics['total_growth'], 1) }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-2">from previous period</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-plus text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">New Contacts</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($analytics['new_contacts']) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        <span class="text-blue-600 dark:text-blue-400 font-medium">{{ number_format($analytics['new_contacts_daily'], 1) }}</span>
                        <span class="text-gray-500 dark:text-gray-400 ml-2">per day average</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Contacts</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($analytics['active_contacts']) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-center text-sm">
                        <span class="text-blue-600 dark:text-blue-400 font-medium">{{ number_format($analytics['activity_rate'], 1) }}%</span>
                        <span class="text-gray-500 dark:text-gray-400 ml-2">activity rate</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-heart text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Engagement Score</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($analytics['engagement_score'], 1) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-orange-600 h-2 rounded-full" style="width: {{ $analytics['engagement_score'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Contact Growth Chart -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Contact Growth Over Time</h3>
                    <div class="flex space-x-2">
                        <button class="text-xs px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg">Daily</button>
                        <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">Weekly</button>
                        <button class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg">Monthly</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="contactGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Contact Sources -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Contact Sources</h3>
                <div class="h-64">
                    <canvas id="contactSourcesChart"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach($analytics['contact_sources'] as $source => $count)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $loop->iteration == 1 ? '#3B82F6' : ($loop->iteration == 2 ? '#10B981' : ($loop->iteration == 3 ? '#F59E0B' : '#EF4444')) }}"></div>
                            <span class="text-gray-600 dark:text-gray-400 capitalize">{{ $source }}</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($count) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Segments Performance -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Segment Performance</h3>
                <a href="{{ route('segments.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 text-sm font-medium">
                    Manage Segments →
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Segment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contacts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Growth</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Engagement</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($analytics['segment_performance'] as $segment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white text-sm font-semibold">
                                        {{ strtoupper(substr($segment['name'], 0, 2)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $segment['name'] }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($segment['type']) }} segment</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div class="font-medium">{{ number_format($segment['contact_count']) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($segment['growth'] >= 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        <i class="fas fa-arrow-up mr-1"></i>
                                        +{{ number_format($segment['growth'], 1) }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                        <i class="fas fa-arrow-down mr-1"></i>
                                        {{ number_format($segment['growth'], 1) }}%
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $segment['engagement'] }}%"></div>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ number_format($segment['engagement'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $segment['last_updated']->diffForHumans() }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Contact Activity Timeline -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Recent Contact Activity</h3>
            
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($analytics['recent_activities'] as $activity)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800 
                                        {{ $activity['type'] === 'created' ? 'bg-green-500' : ($activity['type'] === 'updated' ? 'bg-blue-500' : 'bg-yellow-500') }}">
                                        <i class="fas {{ $activity['type'] === 'created' ? 'fa-user-plus' : ($activity['type'] === 'updated' ? 'fa-edit' : 'fa-envelope') }} text-white text-xs"></i>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Contact <span class="font-medium text-gray-900 dark:text-white">{{ $activity['contact_name'] }}</span> 
                                            {{ $activity['action'] }}
                                        </p>
                                        @if($activity['details'])
                                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $activity['details'] }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $activity['created_at']->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            
            <div class="mt-6">
                <a href="#" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 text-sm font-medium">
                    View all activity →
                </a>
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

    // Contact Growth Chart
    const growthCtx = document.getElementById('contactGrowthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($analytics['growth_labels']) !!},
            datasets: [{
                label: 'New Contacts',
                data: {!! json_encode($analytics['growth_data']) !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
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

    // Contact Sources Chart
    const sourcesCtx = document.getElementById('contactSourcesChart').getContext('2d');
    new Chart(sourcesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($analytics['contact_sources'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($analytics['contact_sources'])) !!},
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

    // Date Range Handler
    document.getElementById('dateRange').addEventListener('change', function() {
        window.location.href = `{{ route('reports.contacts') }}?period=${this.value}`;
    });
});
</script>
@endpush
