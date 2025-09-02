@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    Webhook Logs
                </h1>
                <p class="text-gray-600 mt-2">Monitor webhook activity, processing status, and debugging tools</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" 
                        onclick="retryFailedWebhooks()" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-yellow-300 rounded-lg shadow-sm text-sm font-medium text-yellow-700 hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Retry Failed
                </button>
                <button type="button" 
                        x-data=""
                        @click="$dispatch('open-modal', 'clear-webhooks-modal')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-lg shadow-sm text-sm font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Clear Old
                </button>
                <button type="button" 
                        onclick="exportWebhooks()" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-green-300 rounded-lg shadow-sm text-sm font-medium text-green-700 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
                <button type="button" 
                        onclick="refreshWebhooks()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8" id="stats-cards">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Webhooks</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['total_webhooks']) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Today</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['today_webhooks']) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-cyan-100 rounded-lg">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($statistics['completed_webhooks']) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Failed</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($statistics['failed_webhooks']) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ number_format($statistics['pending_webhooks']) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Success Rate</p>
                        <p class="text-2xl font-bold text-green-600">{{ $statistics['success_rate'] }}%</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-0">Webhook Activity Trends</h3>
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <input type="radio" class="sr-only" name="chartPeriod" id="period24h" checked>
                        <label for="period24h" class="px-3 py-1 text-sm font-medium text-gray-600 rounded-md cursor-pointer hover:bg-white hover:shadow-sm transition-all duration-150">24h</label>
                        <input type="radio" class="sr-only" name="chartPeriod" id="period7d">
                        <label for="period7d" class="px-3 py-1 text-sm font-medium text-gray-600 rounded-md cursor-pointer hover:bg-white hover:shadow-sm transition-all duration-150">7d</label>
                        <input type="radio" class="sr-only" name="chartPeriod" id="period30d">
                        <label for="period30d" class="px-3 py-1 text-sm font-medium text-gray-600 rounded-md cursor-pointer hover:bg-white hover:shadow-sm transition-all duration-150">30d</label>
                    </div>
                </div>
                <div class="h-72">
                    <canvas id="webhookActivityChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Provider Distribution</h3>
                <div class="h-72">
                    <canvas id="providerDistributionChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Health Metrics --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Health Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6" id="health-metrics">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900 mb-2" id="avg-processing-time">
                        {{ number_format($statistics['avg_processing_time'] ?? 0, 2) }}ms
                    </div>
                    <div class="text-sm text-gray-600">Avg Processing Time</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900 mb-2" id="recent-failures">-</div>
                    <div class="text-sm text-gray-600">Recent Failures (1h)</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-gray-900 mb-2" id="ready-for-retry">-</div>
                    <div class="text-sm text-gray-600">Ready for Retry</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="mb-2">
                        <span id="health-status" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Good
                        </span>
                    </div>
                    <div class="text-sm text-gray-600">System Health</div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Webhook Type</label>
                        <select name="webhook_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Types</option>
                            @foreach($webhookTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('webhook_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Provider</label>
                        <select name="provider" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Providers</option>
                            @foreach($providers as $key => $label)
                                <option value="{{ $key }}" {{ request('provider') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                        <select name="event_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Events</option>
                            @foreach($eventTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('event_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               value="{{ request('date_from') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               placeholder="Search webhooks..." value="{{ request('search') }}">
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('admin.webhook-logs.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-300 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear
                        </a>
                        <label class="inline-flex items-center px-4 py-2 bg-white border border-cyan-300 rounded-lg shadow-sm text-sm font-medium text-cyan-700 hover:bg-cyan-50 cursor-pointer">
                            <input type="checkbox" id="autoRefresh" class="sr-only">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Auto Refresh
                        </label>
                    </div>
                </div>
            </form>
        </div>

        {{-- Webhook Logs Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3 sm:mb-0">Webhook Logs</h3>
                    <button type="button" 
                            onclick="bulkRetrySelected()" 
                            disabled 
                            id="bulkRetryBtn"
                            class="inline-flex items-center px-4 py-2 bg-white border border-yellow-300 rounded-lg shadow-sm text-sm font-medium text-yellow-700 hover:bg-yellow-50 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Retry Selected
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="webhookLogsTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-12 px-6 py-3 text-left">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processing Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="webhookLogsBody" class="bg-white divide-y divide-gray-200">
                        @include('admin.webhook-logs.table')
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Clear Webhooks Modal --}}
<div x-data="{ open: false }" 
     @open-modal.window="if ($event.detail === 'clear-webhooks-modal') open = true"
     @close-modal.window="if ($event.detail === 'clear-webhooks-modal') open = false"
     x-show="open"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <form id="clearWebhooksForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Clear Old Webhooks</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Delete webhooks older than</label>
                                <select name="days" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="7">7 days</option>
                                    <option value="30" selected>30 days</option>
                                    <option value="90">90 days</option>
                                    <option value="180">180 days</option>
                                    <option value="365">1 year</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status filter</label>
                                <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="all">All statuses</option>
                                    <option value="completed">Completed only</option>
                                    <option value="failed">Failed only</option>
                                </select>
                            </div>

                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-800">This action cannot be undone. Please confirm before proceeding.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Clear Webhooks
                    </button>
                    <button type="button" 
                            @click="open = false"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let activityChart = null;
let distributionChart = null;
let autoRefreshInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    loadHealthMetrics();
    setupAutoRefresh();
    setupBulkActions();
    setupRadioButtons();
});

function setupRadioButtons() {
    // Handle chart period radio buttons
    document.querySelectorAll('input[name="chartPeriod"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                // Update labels styling
                document.querySelectorAll('label[for^="period"]').forEach(label => {
                    label.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                    label.classList.add('text-gray-600');
                });
                
                // Style active label
                const activeLabel = document.querySelector(`label[for="${this.id}"]`);
                activeLabel.classList.remove('text-gray-600');
                activeLabel.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
                
                loadChartData(this.id.replace('period', '').replace('h', '').replace('d', ''));
            }
        });
    });
}

function initializeCharts() {
    // Activity Chart
    const activityCtx = document.getElementById('webhookActivityChart').getContext('2d');
    activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total',
                data: [],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }, {
                label: 'Completed',
                data: [],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }, {
                label: 'Failed',
                data: [],
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });

    // Provider Distribution Chart
    const distributionCtx = document.getElementById('providerDistributionChart').getContext('2d');
    distributionChart = new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgb(239, 68, 68)',
                    'rgb(59, 130, 246)', 
                    'rgb(251, 191, 36)',
                    'rgb(16, 185, 129)',
                    'rgb(147, 51, 234)',
                    'rgb(249, 115, 22)',
                    'rgb(236, 72, 153)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Load initial chart data
    loadChartData('7');
}

function loadChartData(period) {
    fetch(`{{ route('admin.webhook-logs.chart-data') }}?period=${period}`)
        .then(response => response.json())
        .then(data => {
            // Update activity chart
            if (data.daily_counts) {
                const labels = data.daily_counts.map(item => item.date);
                const totalData = data.daily_counts.map(item => item.total);
                const completedData = data.daily_counts.map(item => item.completed);
                const failedData = data.daily_counts.map(item => item.failed);

                activityChart.data.labels = labels;
                activityChart.data.datasets[0].data = totalData;
                activityChart.data.datasets[1].data = completedData;
                activityChart.data.datasets[2].data = failedData;
                activityChart.update();
            }

            // Update provider distribution chart
            if (data.provider_stats) {
                const labels = data.provider_stats.map(item => item.provider);
                const counts = data.provider_stats.map(item => item.total);

                distributionChart.data.labels = labels;
                distributionChart.data.datasets[0].data = counts;
                distributionChart.update();
            }
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
        });
}

function loadHealthMetrics() {
    fetch('{{ route('admin.webhook-logs.health-metrics') }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('recent-failures').textContent = data.recent_failures || 0;
            document.getElementById('ready-for-retry').textContent = data.ready_for_retry || 0;
            
            const healthStatus = document.getElementById('health-status');
            healthStatus.textContent = data.health_status || 'Unknown';
            
            // Remove old classes
            healthStatus.classList.remove('bg-green-100', 'text-green-800', 'bg-yellow-100', 'text-yellow-800', 'bg-red-100', 'text-red-800');
            
            // Add new classes based on status
            if (data.health_status === 'good') {
                healthStatus.classList.add('bg-green-100', 'text-green-800');
            } else if (data.health_status === 'warning') {
                healthStatus.classList.add('bg-yellow-100', 'text-yellow-800');
            } else {
                healthStatus.classList.add('bg-red-100', 'text-red-800');
            }
        })
        .catch(error => {
            console.error('Error loading health metrics:', error);
        });
}

function setupAutoRefresh() {
    const autoRefreshToggle = document.getElementById('autoRefresh');
    autoRefreshToggle.addEventListener('change', function() {
        if (this.checked) {
            autoRefreshInterval = setInterval(() => {
                refreshWebhooks();
                loadHealthMetrics();
            }, 30000); // Refresh every 30 seconds
        } else {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }
    });
}

function setupBulkActions() {
    const selectAll = document.getElementById('selectAll');
    const bulkRetryBtn = document.getElementById('bulkRetryBtn');
    
    selectAll.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.webhook-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActions();
    });
    
    // Update bulk actions when individual checkboxes change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('webhook-checkbox')) {
            updateBulkActions();
        }
    });
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.webhook-checkbox:checked');
    const bulkRetryBtn = document.getElementById('bulkRetryBtn');
    
    if (checkedBoxes.length > 0) {
        bulkRetryBtn.disabled = false;
        bulkRetryBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        bulkRetryBtn.disabled = true;
        bulkRetryBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

function refreshWebhooks() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    fetch(`{{ route('admin.webhook-logs.index') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('webhookLogsBody').innerHTML = data.html;
        
        // Update statistics
        if (data.statistics) {
            updateStatisticsCards(data.statistics);
        }
        
        setupBulkActions(); // Re-setup after content update
    })
    .catch(error => {
        console.error('Error refreshing webhooks:', error);
    });
}

function updateStatisticsCards(stats) {
    // Update each statistic card with new values
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            element.textContent = number_format(stats[key]);
        }
    });
}

function exportWebhooks() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    window.location.href = `{{ route('admin.webhook-logs.export') }}?${params.toString()}`;
}

function retryFailedWebhooks() {
    if (!confirm('Are you sure you want to retry all failed webhooks?')) return;
    
    fetch('{{ route('admin.webhook-logs.bulk-retry') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            webhook_ids: Array.from(document.querySelectorAll('.webhook-checkbox:checked')).map(cb => cb.value)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            refreshWebhooks();
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error retrying webhooks:', error);
        showToast('error', 'Failed to retry webhooks');
    });
}

function bulkRetrySelected() {
    const checkedBoxes = document.querySelectorAll('.webhook-checkbox:checked');
    if (checkedBoxes.length === 0) {
        showToast('warning', 'Please select webhooks to retry');
        return;
    }
    
    if (!confirm(`Are you sure you want to retry ${checkedBoxes.length} selected webhooks?`)) return;
    
    const webhookIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    fetch('{{ route('admin.webhook-logs.bulk-retry') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            webhook_ids: webhookIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            refreshWebhooks();
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error retrying webhooks:', error);
        showToast('error', 'Failed to retry selected webhooks');
    });
}

// Clear webhooks form submission
document.getElementById('clearWebhooksForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route('admin.webhook-logs.clear-old') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            refreshWebhooks();
            // Close modal
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'clear-webhooks-modal' }));
        } else {
            showToast('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error clearing webhooks:', error);
        showToast('error', 'Failed to clear old webhooks');
    });
});

function showToast(type, message) {
    // Implement your toast notification system here
    console.log(`${type}: ${message}`);
}

function number_format(number) {
    return new Intl.NumberFormat().format(number);
}
</script>
@endpush
