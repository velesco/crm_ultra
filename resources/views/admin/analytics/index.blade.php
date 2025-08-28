@extends('layouts.app')

@section('title', 'Business Analytics Dashboard')

@section('styles')
<!-- All styles now handled by Tailwind CSS -->
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Business Analytics Dashboard
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive business intelligence and performance insights</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors" onclick="exportAnalytics('overview')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Data
            </button>
            <button class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors" onclick="refreshDashboard()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                        <input type="date" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="start_date" value="{{ $startDate }}" onchange="updateDateRange()">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                        <input type="date" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="end_date" value="{{ $endDate }}" onchange="updateDateRange()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quick Filters</label>
                        <div class="flex flex-wrap gap-2">
                            <button class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md transition-colors {{ $period == '7days' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' }}" onclick="setQuickFilter('7days')">7 Days</button>
                            <button class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md transition-colors {{ $period == '30days' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' }}" onclick="setQuickFilter('30days')">30 Days</button>
                            <button class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md transition-colors {{ $period == '90days' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' }}" onclick="setQuickFilter('90days')">90 Days</button>
                            <button class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md transition-colors {{ $period == '1year' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600' }}" onclick="setQuickFilter('1year')">1 Year</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Last Updated: <span id="last-updated">{{ now()->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Metrics -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Overview Metrics</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-white bg-opacity-10 rounded-full transform translate-x-6 -translate-y-6"></div>
                <div class="flex justify-between items-center relative z-10">
                    <div>
                        <div class="text-3xl font-bold mb-1">{{ number_format($data['overview']['total_contacts']) }}</div>
                        <div class="text-sm opacity-75">Total Contacts</div>
                        <div class="flex items-center mt-2 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span>+{{ number_format($data['growth']['contact_growth']) }}%</span>
                        </div>
                    </div>
                    <div class="text-white text-opacity-50">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-white bg-opacity-10 rounded-full transform translate-x-6 -translate-y-6"></div>
                <div class="flex justify-between items-center relative z-10">
                    <div>
                        <div class="text-3xl font-bold mb-1">${{ number_format($data['overview']['revenue'], 2) }}</div>
                        <div class="text-sm opacity-75">Revenue</div>
                        <div class="flex items-center mt-2 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span>+12.5%</span>
                        </div>
                    </div>
                    <div class="text-white text-opacity-50">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-pink-500 to-indigo-600 rounded-xl p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-white bg-opacity-10 rounded-full transform translate-x-6 -translate-y-6"></div>
                <div class="flex justify-between items-center relative z-10">
                    <div>
                        <div class="text-3xl font-bold mb-1">{{ $data['engagement']['overall_engagement'] }}%</div>
                        <div class="text-sm opacity-75">Engagement Rate</div>
                        <div class="flex items-center mt-2 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span>+3.2%</span>
                        </div>
                    </div>
                    <div class="text-white text-opacity-50">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-400 to-cyan-500 rounded-xl p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-white bg-opacity-10 rounded-full transform translate-x-6 -translate-y-6"></div>
                <div class="flex justify-between items-center relative z-10">
                    <div>
                        <div class="text-3xl font-bold mb-1">{{ number_format($data['overview']['conversion_rate'], 1) }}%</div>
                        <div class="text-sm opacity-75">Conversion Rate</div>
                        <div class="flex items-center mt-2 text-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span>+1.8%</span>
                        </div>
                    </div>
                    <div class="text-white text-opacity-50">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center shadow-sm border-l-4 border-indigo-500 hover:shadow-md transition-shadow">
            <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide mb-2">Total Campaigns</div>
            <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">{{ number_format($data['overview']['total_campaigns']) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">This period</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center shadow-sm border-l-4 border-green-500 hover:shadow-md transition-shadow">
            <div class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide mb-2">Total Messages</div>
            <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">{{ number_format($data['overview']['total_messages']) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">All channels</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center shadow-sm border-l-4 border-blue-500 hover:shadow-md transition-shadow">
            <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-2">Active Users</div>
            <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">{{ number_format($data['overview']['active_users']) }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">This period</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Growth Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Contact Growth Trend</h3>
                <div class="flex rounded-md shadow-sm" role="group">
                    <button type="button" class="px-3 py-1 text-sm font-medium text-white bg-indigo-600 border border-indigo-600 rounded-l-md hover:bg-indigo-700 focus:z-10 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" onclick="updateGrowthChart('daily')">Daily</button>
                    <button type="button" class="px-3 py-1 text-sm font-medium text-indigo-600 bg-white border-t border-b border-indigo-600 hover:bg-indigo-50 focus:z-10 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-indigo-400 dark:hover:bg-gray-700" onclick="updateGrowthChart('weekly')">Weekly</button>
                    <button type="button" class="px-3 py-1 text-sm font-medium text-indigo-600 bg-white border border-indigo-600 rounded-r-md hover:bg-indigo-50 focus:z-10 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-indigo-400 dark:hover:bg-gray-700" onclick="updateGrowthChart('monthly')">Monthly</button>
                </div>
            </div>
            <canvas id="growthChart" height="300"></canvas>
        </div>

        <!-- Channel Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Channel Performance</h3>
            <canvas id="channelChart" height="300"></canvas>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Engagement Metrics Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $data['engagement']['email_open_rate'] }}%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Email Open Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $data['engagement']['email_click_rate'] }}%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Email Click Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $data['engagement']['sms_delivery_rate'] }}%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">SMS Delivery Rate</div>
                </div>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <canvas id="engagementChart" height="200"></canvas>
            </div>
        </div>

        <!-- Top Segments -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Top Performing Segments</h3>
            <div class="space-y-4">
                @foreach($data['segments']->take(5) as $segment)
                <div class="flex justify-between items-center">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $segment['name'] }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($segment['contacts']) }} contacts</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-green-600 dark:text-green-400">{{ $segment['engagement'] }}% engagement</div>
                        <div class="text-sm text-indigo-600 dark:text-indigo-400">{{ $segment['conversion'] }}% conversion</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- User Activity -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">User Activity</h2>
            <a href="{{ route('admin.analytics.contacts') }}" class="inline-flex items-center px-4 py-2 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 font-medium rounded-md transition-colors dark:bg-gray-800 dark:text-indigo-400 dark:hover:bg-gray-700">
                View Detailed Report
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activities</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campaigns Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contacts Added</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Active</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($data['users']->take(10) as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-medium">
                                            {{ substr($user['name'], 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user['email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($user['activities']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($user['campaigns_created']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($user['contacts_created']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user['last_active'] ? \Carbon\Carbon::parse($user['last_active'])->diffForHumans() : 'Never' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <a href="{{ route('admin.analytics.revenue') }}" class="block p-6 bg-white dark:bg-gray-800 border border-green-200 dark:border-green-700 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center group">
            <svg class="w-12 h-12 text-green-500 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Revenue Analytics</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Financial performance insights</p>
        </a>
        <a href="{{ route('admin.analytics.campaigns') }}" class="block p-6 bg-white dark:bg-gray-800 border border-indigo-200 dark:border-indigo-700 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center group">
            <svg class="w-12 h-12 text-indigo-500 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Campaign Analytics</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Campaign performance analysis</p>
        </a>
        <a href="{{ route('admin.analytics.contacts') }}" class="block p-6 bg-white dark:bg-gray-800 border border-cyan-200 dark:border-cyan-700 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center group">
            <svg class="w-12 h-12 text-cyan-500 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Contact Analytics</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Contact lifecycle analysis</p>
        </a>
        <button class="block p-6 bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-700 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center group w-full" onclick="openRealtimeModal()">
            <svg class="w-12 h-12 text-yellow-500 mx-auto mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Real-time Data</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Live performance monitoring</p>
        </button>
    </div>
</div>

<!-- Loading Overlay -->
<div x-data="{ showLoading: false }" x-show="showLoading" x-cloak class="fixed inset-0 z-50 bg-gray-500 bg-opacity-75 flex items-center justify-center" id="loading-overlay">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center max-w-sm mx-4">
        <div class="inline-flex items-center justify-center w-16 h-16 mb-4">
            <svg class="animate-spin w-12 h-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <p class="text-gray-700 dark:text-gray-300 font-medium">Loading analytics data...</p>
    </div>
</div>

<!-- Real-time Data Modal -->
<div x-data="{ showRealtimeModal: false }" x-show="showRealtimeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" id="realtimeModal">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRealtimeModal = false"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Real-time Analytics
                        </h3>
                        <button @click="showRealtimeModal = false" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="realtime-content">
                        <div class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 mb-4">
                                <svg class="animate-spin w-12 h-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300">Loading real-time data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let growthChart, channelChart, engagementChart;
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        
        // Auto-refresh every 5 minutes
        setInterval(refreshDashboard, 300000);
    });

    function initializeCharts() {
        // Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        const growthData = @json($data['growth']['daily_growth']);
        
        growthChart = new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: Object.keys(growthData),
                datasets: [{
                    label: 'Contact Growth',
                    data: Object.values(growthData),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
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
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Channel Chart
        const channelCtx = document.getElementById('channelChart').getContext('2d');
        const channelData = @json($data['channels']);
        
        channelChart = new Chart(channelCtx, {
            type: 'doughnut',
            data: {
                labels: ['Email', 'SMS', 'WhatsApp'],
                datasets: [{
                    data: [
                        channelData.email.sent,
                        channelData.sms.sent,
                        channelData.whatsapp.sent
                    ],
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#fc466b'
                    ],
                    borderWidth: 0
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

        // Engagement Chart
        const engagementCtx = document.getElementById('engagementChart').getContext('2d');
        
        engagementChart = new Chart(engagementCtx, {
            type: 'bar',
            data: {
                labels: ['Email Open', 'Email Click', 'SMS Delivery', 'WhatsApp Response'],
                datasets: [{
                    data: [
                        {{ $data['engagement']['email_open_rate'] }},
                        {{ $data['engagement']['email_click_rate'] }},
                        {{ $data['engagement']['sms_delivery_rate'] }},
                        {{ $data['engagement']['whatsapp_response_rate'] }}
                    ],
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#11998e',
                        '#fc466b'
                    ],
                    borderRadius: 4,
                    borderSkipped: false,
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
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    function updateDateRange() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate) {
            showLoading();
            window.location.href = `{{ route('admin.analytics.index') }}?start_date=${startDate}&end_date=${endDate}`;
        }
    }

    function setQuickFilter(period) {
        showLoading();
        window.location.href = `{{ route('admin.analytics.index') }}?period=${period}`;
    }

    function updateGrowthChart(type) {
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        showLoading();
        
        // Fetch new data based on type
        fetch(`{{ route('admin.analytics.index') }}?chart_type=${type}`)
            .then(response => response.json())
            .then(data => {
                growthChart.data.labels = Object.keys(data.growth[`${type}_growth`]);
                growthChart.data.datasets[0].data = Object.values(data.growth[`${type}_growth`]);
                growthChart.update();
                hideLoading();
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                showAlert('Error updating chart', 'error');
            });
    }

    function refreshDashboard() {
        showLoading();
        window.location.reload();
    }

    function exportAnalytics(type) {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        const url = `{{ route('admin.analytics.export') }}?type=${type}&start_date=${startDate}&end_date=${endDate}&format=csv`;
        window.open(url, '_blank');
    }

    function openRealtimeModal() {
        document.querySelector('[x-data*="showRealtimeModal"]').__x.$data.showRealtimeModal = true;
        
        // Load real-time data
        fetch('{{ route('admin.analytics.realtime') }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('realtime-content').innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-6 text-center shadow-sm">
                            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mb-2">${data.active_campaigns}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Active Campaigns</div>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-6 text-center shadow-sm">
                            <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">${data.online_users}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Online Users</div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">System Status</h4>
                        </div>
                        <div class="p-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">System Healthy</span>
                        </div>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('realtime-content').innerHTML = `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                        <div class="text-red-800 dark:text-red-200">
                            Error loading real-time data. Please try again.
                        </div>
                    </div>
                `;
            });
    }

    function showLoading() {
        document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = true;
    }

    function hideLoading() {
        document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = false;
    }

    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-5 right-5 z-50 max-w-sm p-4 rounded-md shadow-lg ${
            type === 'error' 
                ? 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-200' 
                : 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-200'
        }`;
        alertDiv.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${message}</span>
                <button type="button" class="ml-3 text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
</script>
@endsection