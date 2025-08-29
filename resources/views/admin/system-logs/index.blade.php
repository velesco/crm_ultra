@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">System Logs</h1>
            <p class="text-gray-600 mt-1">Monitor system activity, errors, and performance metrics</p>
        </div>
        <div class="flex space-x-3">
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                    onclick="openModal('clearLogsModal')">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Clear Old Logs
            </button>
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                    onclick="exportLogs()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                    onclick="refreshLogs()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8" id="stats-cards">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-indigo-600">{{ number_format($statistics['total_logs']) }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Total Logs</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-blue-600">{{ number_format($statistics['today_logs']) }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Today's Logs</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-red-600">{{ number_format($statistics['error_logs']) }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Errors</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-green-600">{{ $statistics['success_rate'] }}%</h3>
                        <p class="text-sm text-gray-500 mt-1">Success Rate</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Activity Trends</h3>
                    <div class="flex space-x-1" role="group">
                        <input type="radio" id="period24h" name="chartPeriod" class="sr-only peer" checked>
                        <label for="period24h" class="inline-flex items-center px-3 py-1 text-xs font-medium border border-gray-300 rounded-l-md bg-white text-gray-700 hover:bg-gray-50 cursor-pointer peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">
                            24h
                        </label>
                        <input type="radio" id="period7d" name="chartPeriod" class="sr-only peer">
                        <label for="period7d" class="inline-flex items-center px-3 py-1 text-xs font-medium border-t border-b border-gray-300 bg-white text-gray-700 hover:bg-gray-50 cursor-pointer peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">
                            7d
                        </label>
                        <input type="radio" id="period30d" name="chartPeriod" class="sr-only peer">
                        <label for="period30d" class="inline-flex items-center px-3 py-1 text-xs font-medium border border-gray-300 rounded-r-md bg-white text-gray-700 hover:bg-gray-50 cursor-pointer peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">
                            30d
                        </label>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="activityChart" height="300"></canvas>
            </div>
        </div>
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Category Distribution</h3>
            </div>
            <div class="p-6">
                <canvas id="categoryChart" height="300"></canvas>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search logs..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                    <select id="level" name="level" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Levels</option>
                        @foreach($levels as $value => $label)
                            <option value="{{ $value }}" {{ request('level') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ request('category') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select id="user_id" name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="flex space-x-2">
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="p-6" id="logs-container">
            @include('admin.system-logs.table')
        </div>
    </div>
</div>

{{-- Clear Logs Modal --}}
<div id="clearLogsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('clearLogsModal')"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Clear Old Logs
                        </h3>
                        <form id="clear-logs-form" class="mt-4">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            This action will permanently delete logs older than the specified number of days.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="days" class="block text-sm font-medium text-gray-700 mb-1">Delete logs older than (days)</label>
                                <input type="number" 
                                       id="days" 
                                       name="days" 
                                       value="90" 
                                       min="1" 
                                       max="365" 
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500">Recommended: 90 days for normal operation, 30 days for high-volume systems</p>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Log Levels to Delete (optional)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center">
                                        <input id="level-debug" name="levels[]" value="debug" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="level-debug" class="ml-2 text-sm text-gray-700">Debug</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="level-info" name="levels[]" value="info" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="level-info" class="ml-2 text-sm text-gray-700">Info</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="level-warning" name="levels[]" value="warning" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="level-warning" class="ml-2 text-sm text-gray-700">Warning</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="level-error" name="levels[]" value="error" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="level-error" class="ml-2 text-sm text-gray-700">Error</label>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Leave unchecked to delete all log levels</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        onclick="submitClearLogsForm()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Clear Logs
                </button>
                <button type="button" 
                        onclick="closeModal('clearLogsModal')"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.activity-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.log-row {
    transition: all 0.2s ease;
}

.log-row:hover {
    background-color: #f9fafb;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

input[type="radio"]:checked + label {
    background-color: #4f46e5;
    color: white;
    border-color: #4f46e5;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let activityChart, categoryChart;
let refreshInterval;

$(document).ready(function() {
    initCharts();
    bindEvents();
    startAutoRefresh();
});

function bindEvents() {
    // Filter form submission
    $('#filter-form input, #filter-form select').on('change', function() {
        loadLogs();
    });

    // Chart period change
    $('input[name="chartPeriod"]').on('change', function() {
        updateCharts($(this).attr('id').replace('period', ''));
    });

    // Auto-refresh toggle
    $(document).on('keypress', function(e) {
        if (e.which == 32) { // Spacebar
            toggleAutoRefresh();
        }
    });
}

function loadLogs() {
    const formData = $('#filter-form').serialize();
    
    $.ajax({
        url: '{{ route("admin.system-logs.index") }}',
        method: 'GET',
        data: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            $('#logs-container').html(response.html);
            updateStatistics(response.statistics);
        },
        error: function() {
            showAlert('Error loading logs', 'danger');
        }
    });
}

function updateStatistics(stats) {
    $('#stats-cards').find('h3').each(function(index) {
        const values = [
            stats.total_logs,
            stats.today_logs,
            stats.error_logs,
            stats.success_rate + '%'
        ];
        $(this).text(values[index]);
    });
}

function initCharts() {
    // Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Total Activity',
                data: [],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Errors',
                data: [],
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c',
                    '#4facfe',
                    '#00f2fe',
                    '#43e97b',
                    '#38f9d7'
                ]
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

    updateCharts('24h');
}

function updateCharts(period) {
    $.ajax({
        url: '{{ route("admin.system-logs.chart-data") }}',
        method: 'GET',
        data: { period: period },
        success: function(data) {
            // Update activity chart
            activityChart.data.labels = data.activity.map(item => item.period);
            activityChart.data.datasets[0].data = data.activity.map(item => item.total);
            activityChart.data.datasets[1].data = data.activity.map(item => item.errors);
            activityChart.update();

            // Update category chart
            categoryChart.data.labels = data.categories.map(item => item.category);
            categoryChart.data.datasets[0].data = data.categories.map(item => item.count);
            categoryChart.update();
        },
        error: function() {
            showAlert('Error updating charts', 'danger');
        }
    });
}

function refreshLogs() {
    loadLogs();
    updateCharts($('input[name="chartPeriod"]:checked').attr('id').replace('period', ''));
    showAlert('Logs refreshed successfully', 'success');
}

function exportLogs() {
    const formData = $('#filter-form').serialize();
    window.location.href = '{{ route("admin.system-logs.export") }}?' + formData;
}

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function submitClearLogsForm() {
    const formData = new FormData(document.getElementById('clear-logs-form'));
    
    $.ajax({
        url: '{{ route("admin.system-logs.clear-old") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                closeModal('clearLogsModal');
                refreshLogs();
            } else {
                showAlert('Error clearing logs', 'danger');
            }
        },
        error: function() {
            showAlert('Error clearing logs', 'danger');
        }
    });
}

function startAutoRefresh() {
    refreshInterval = setInterval(function() {
        loadLogs();
        updateCharts($('input[name="chartPeriod"]:checked').attr('id').replace('period', ''));
    }, 30000); // Refresh every 30 seconds
}

function toggleAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
        showAlert('Auto-refresh disabled', 'info');
    } else {
        startAutoRefresh();
        showAlert('Auto-refresh enabled', 'success');
    }
}

function showAlert(message, type) {
    const colors = {
        'success': 'bg-green-100 border-green-400 text-green-700',
        'danger': 'bg-red-100 border-red-400 text-red-700',
        'info': 'bg-blue-100 border-blue-400 text-blue-700'
    };
    
    const alertHtml = `
        <div class="fixed top-4 right-4 z-50 border ${colors[type]} px-4 py-3 rounded animate-fade-in-down" role="alert" style="animation: fadeInDown 0.5s ease-out;">
            <span>${message}</span>
            <button type="button" class="float-right ml-4 text-lg font-bold leading-none" onclick="$(this).parent().remove();">&times;</button>
        </div>
    `;
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.fixed.top-4.right-4').remove();
    }, 5000);
}

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style>
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -100%, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}
.animate-fade-in-down {
    animation: fadeInDown 0.5s ease-out;
}
</style>
@endpush
