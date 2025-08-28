@extends('layouts.app')

@section('title', 'Admin Dashboard - System Overview')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Admin Dashboard
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">System overview and administration tools</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <button type="button" class="inline-flex items-center px-4 py-2 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 font-medium rounded-md transition-colors" id="refreshStats">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Quick Actions
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                        <div class="py-1">
                            <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" id="toggleMaintenance">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Toggle Maintenance
                            </button>
                            <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" id="clearCaches">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Clear Caches
                            </button>
                            <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" id="optimizeSystem">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Optimize System
                            </button>
                            <div class="border-t border-gray-100 dark:border-gray-700"></div>
                            <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" id="exportData">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export System Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Alerts -->
    @if($alertsCount > 0)
    <div class="mb-6">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-4" x-data="{ show: true }" x-show="show">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        <strong>System Alerts:</strong> {{ $alertsCount }} system alerts require attention.
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="#systemAlerts" class="inline-flex items-center px-3 py-1 border border-yellow-300 dark:border-yellow-600 text-yellow-800 dark:text-yellow-200 text-sm rounded hover:bg-yellow-100 dark:hover:bg-yellow-800/30 transition-colors">
                        View Details
                    </a>
                    <button @click="show = false" class="text-yellow-400 hover:text-yellow-600 dark:text-yellow-300 dark:hover:text-yellow-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- System Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- Users Stats -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-indigo-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide mb-1">
                        Total Users
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['users']['total'] ?? 0) }}
                    </div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ $stats['users']['new_today'] ?? 0 }} today
                    </div>
                </div>
                <div class="ml-4">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Contacts Stats -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-green-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">
                        Total Contacts
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['contacts']['total'] ?? 0) }}
                    </div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ $stats['contacts']['new_today'] ?? 0 }} today
                    </div>
                </div>
                <div class="ml-4">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Campaigns Stats -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-blue-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">
                        Email Campaigns
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['campaigns']['total'] ?? 0) }}
                    </div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        {{ $stats['campaigns']['sent_today'] ?? 0 }} sent today
                    </div>
                </div>
                <div class="ml-4">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Communications Stats -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-yellow-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide mb-1">
                        Total Communications
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($stats['communications']['total'] ?? 0) }}
                    </div>
                    <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        {{ $stats['communications']['today'] ?? 0 }} today
                    </div>
                </div>
                <div class="ml-4">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-8">
        <!-- User Growth Chart -->
        <div class="xl:col-span-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-row items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">User Growth (Last 30 Days)</h3>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                            <div class="py-1">
                                <button id="exportChart" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 w-full text-left">Export Chart</button>
                                <button id="refreshChart" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 w-full text-left">Refresh Data</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="chart-area">
                        <canvas id="userGrowthChart" style="height: 320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="xl:col-span-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Health</h3>
                </div>
                <div class="p-6">
                    <div id="systemHealthContainer" class="space-y-4">
                        <!-- Database Health -->
                        <div class="flex items-center">
                            <div class="mr-3">
                                <div class="@if(($systemHealth['database']['status'] ?? '') === 'healthy') bg-green-500 @elseif(($systemHealth['database']['status'] ?? '') === 'warning') bg-yellow-500 @else bg-red-500 @endif rounded-full w-3 h-3"></div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Database</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $systemHealth['database']['message'] ?? 'N/A' }}</div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $systemHealth['database']['response_time'] ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Cache Health -->
                        <div class="flex items-center">
                            <div class="mr-3">
                                <div class="@if(($systemHealth['cache']['status'] ?? '') === 'healthy') bg-green-500 @elseif(($systemHealth['cache']['status'] ?? '') === 'warning') bg-yellow-500 @else bg-red-500 @endif rounded-full w-3 h-3"></div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Cache</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $systemHealth['cache']['message'] ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <!-- Queue Health -->
                        <div class="flex items-center">
                            <div class="mr-3">
                                <div class="@if(($systemHealth['queue']['status'] ?? '') === 'healthy') bg-green-500 @elseif(($systemHealth['queue']['status'] ?? '') === 'warning') bg-yellow-500 @else bg-red-500 @endif rounded-full w-3 h-3"></div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Queue</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $systemHealth['queue']['message'] ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <!-- Storage Health -->
                        <div class="flex items-center">
                            <div class="mr-3">
                                <div class="@if(($systemHealth['storage']['status'] ?? '') === 'healthy') bg-green-500 @elseif(($systemHealth['storage']['status'] ?? '') === 'warning') bg-yellow-500 @else bg-red-500 @endif rounded-full w-3 h-3"></div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Storage</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $systemHealth['storage']['message'] ?? 'N/A' }}</div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $systemHealth['storage']['usage_percent'] ?? '0' }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity and Top Users -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-8">
        <!-- Recent Activity -->
        <div class="xl:col-span-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent System Activity</h3>
                </div>
                <div class="p-6">
                    <div id="recentActivityContainer" class="space-y-4">
                        @forelse($recentActivity as $activity)
                        <div class="flex items-center pb-4 border-b border-gray-100 dark:border-gray-700 last:border-b-0 last:pb-0">
                            <div class="mr-4">
                                <div class="bg-{{ $activity['color'] ?? 'indigo' }}-500 text-white rounded-full flex items-center justify-center w-9 h-9">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $activity['title'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $activity['description'] }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">by {{ $activity['user'] }}</div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p>No recent activity</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Users -->
        <div class="xl:col-span-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Active Users</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($topUsers as $user)
                        <div class="flex items-center">
                            <div class="mr-4">
                                <div class="bg-indigo-500 text-white rounded-full flex items-center justify-content-center w-9 h-9 text-sm font-medium">
                                    {{ strtoupper(substr($user['name'], 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $user['name'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user['email'] }}</div>
                            </div>
                            <div class="text-xs">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">{{ $user['total_activity'] }} actions</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <p>No active users data</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Usage Chart -->
    <div class="mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Communication Trends (Last 7 Days)</h3>
            </div>
            <div class="p-6">
                <div class="chart-area">
                    <canvas id="communicationTrendsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Storage Information -->
    <div class="mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Storage Usage</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $stats['storage']['database_size'] ?? '0 MB' }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Database</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $stats['storage']['uploads_size'] ?? '0 MB' }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Uploads</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $stats['storage']['log_files_size'] ?? '0 MB' }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Log Files</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                            {{ round(($stats['performance']['memory_usage'] ?? 0) / 1024 / 1024, 1) }} MB
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Memory Usage</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div x-data="{ showLoading: false }" x-show="showLoading" x-cloak class="fixed inset-0 z-50 overflow-y-auto" id="loadingModal">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="text-center">
                    <div class="inline-flex items-center px-4 py-2">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">Processing...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Data Modal -->
<div x-data="{ showExportModal: false }" x-show="showExportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" id="exportDataModal">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showExportModal = false"></div>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Export System Data</h3>
                        <button @click="showExportModal = false" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="exportDataForm" class="space-y-4">
                        <div>
                            <label for="exportType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Type</label>
                            <select id="exportType" name="type" required class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="users">Users</option>
                                <option value="contacts">Contacts</option>
                                <option value="campaigns">Email Campaigns</option>
                                <option value="messages">Messages</option>
                                <option value="all">All Data</option>
                            </select>
                        </div>
                        <div>
                            <label for="exportFormat" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Format</label>
                            <select id="exportFormat" name="format" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="csv">CSV</option>
                                <option value="xlsx">Excel</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="dateFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                                <input type="date" id="dateFrom" name="date_from" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="dateTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                                <input type="date" id="dateTo" name="date_to" class="block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 flex justify-end space-x-3">
                    <button @click="showExportModal = false" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium rounded-md transition-colors">Cancel</button>
                    <button type="button" id="startExport" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">Start Export</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize charts
    initUserGrowthChart();
    initCommunicationTrendsChart();

    // Refresh stats button
    $('#refreshStats').on('click', function() {
        location.reload();
    });

    // Toggle maintenance mode
    $('#toggleMaintenance').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to toggle maintenance mode?')) {
            document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = true;
            
            $.ajax({
                url: '{{ route("admin.toggle-maintenance") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = false;
                    alert(response.message);
                },
                error: function(xhr) {
                    document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = false;
                    alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
                }
            });
        }
    });

    // Clear caches
    $('#clearCaches').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to clear all caches?')) {
            document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = true;
            
            $.ajax({
                url: '{{ route("admin.clear-caches") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = false;
                    alert(response.message);
                },
                error: function(xhr) {
                    document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = false;
                    alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
                }
            });
        }
    });

    // Optimize system
    $('#optimizeSystem').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to optimize the system?')) {
            document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = true;
            
            $.ajax({
                url: '{{ route("admin.optimize") }}',
                type: 'POST',
                data: {
                    action: 'all'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = false;
                    alert(response.message);
                },
                error: function(xhr) {
                    document.querySelector('[x-data*="showLoading"]').__x.$data.showLoading = false;
                    alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
                }
            });
        }
    });

    // Export data
    $('#exportData').on('click', function(e) {
        e.preventDefault();
        document.querySelector('[x-data*="showExportModal"]').__x.$data.showExportModal = true;
    });

    // Start export
    $('#startExport').on('click', function() {
        const formData = new FormData(document.getElementById('exportDataForm'));
        
        $.ajax({
            url: '{{ route("admin.export-data") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                document.querySelector('[x-data*="showExportModal"]').__x.$data.showExportModal = false;
                if (response.success) {
                    alert('Export completed successfully!');
                    // Handle file download if needed
                } else {
                    alert('Export failed: ' + response.message);
                }
            },
            error: function(xhr) {
                document.querySelector('[x-data*="showExportModal"]').__x.$data.showExportModal = false;
                alert('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
            }
        });
    });

    function initUserGrowthChart() {
        const ctx = document.getElementById('userGrowthChart').getContext('2d');
        const chartData = @json($chartData['users_growth'] ?? []);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(item => item.formatted_date),
                datasets: [{
                    label: 'New Users',
                    data: chartData.map(item => item.count),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
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
                }
            }
        });
    }

    function initCommunicationTrendsChart() {
        const ctx = document.getElementById('communicationTrendsChart').getContext('2d');
        const chartData = @json($chartData['system_usage'] ?? []);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(item => item.formatted_date),
                datasets: [
                    {
                        label: 'Emails',
                        data: chartData.map(item => item.emails),
                        backgroundColor: '#4e73df'
                    },
                    {
                        label: 'SMS',
                        data: chartData.map(item => item.sms),
                        backgroundColor: '#1cc88a'
                    },
                    {
                        label: 'WhatsApp',
                        data: chartData.map(item => item.whatsapp),
                        backgroundColor: '#36b9cc'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stacked: true
                    },
                    x: {
                        stacked: true
                    }
                }
            }
        });
    }
});
</script>
@endsection
