@extends('layouts.app')

@section('title', 'Backup Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c2.21 0 4-1.79 4-4V7c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3 3 8-8"></path>
                </svg>
                Backup Management
            </h1>
            <p class="text-gray-600 mt-1">Manage system backups and restore points</p>
        </div>
        <div class="flex space-x-3">
            <button type="button" 
                    onclick="openModal('createBackupModal')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Backup
            </button>
            <div class="relative">
                <button type="button" 
                        onclick="toggleDropdown('actionsDropdown')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Actions
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="actionsDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
                        <button onclick="scheduleBackup('daily')" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Schedule Daily
                        </button>
                        <button onclick="scheduleBackup('weekly')" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Schedule Weekly
                        </button>
                        <button onclick="scheduleBackup('monthly')" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Schedule Monthly
                        </button>
                        <div class="border-t border-gray-100"></div>
                        <button onclick="cleanupOldBackups()" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Cleanup Old
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg shadow-lg">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-indigo-100 text-sm font-medium">Total Backups</p>
                        <h3 class="text-3xl font-bold" id="total-backups">{{ $stats['total_backups'] }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-indigo-400 bg-opacity-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg shadow-lg">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Successful</p>
                        <h3 class="text-3xl font-bold" id="successful-backups">{{ $stats['successful_backups'] }}</h3>
                        <p class="text-green-200 text-xs">{{ $additionalStats['success_rate'] }}% success rate</p>
                    </div>
                    <div class="w-12 h-12 bg-green-400 bg-opacity-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg shadow-lg">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-red-100 text-sm font-medium">Failed</p>
                        <h3 class="text-3xl font-bold" id="failed-backups">{{ $stats['failed_backups'] }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-red-400 bg-opacity-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-lg">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Size</p>
                        <h3 class="text-3xl font-bold" id="total-size">{{ formatBytes($stats['total_size'] ?? 0) }}</h3>
                        @if(($additionalStats['avg_size'] ?? 0) > 0)
                            <p class="text-blue-200 text-xs">Avg: {{ formatBytes($additionalStats['avg_size']) }}</p>
                        @endif
                    </div>
                    <div class="w-12 h-12 bg-blue-400 bg-opacity-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c2.21 0 4-1.79 4-4V7c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
        <div class="p-6">
            <form method="GET" id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by name, description..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">All Types</option>
                        <option value="full" {{ request('type') == 'full' ? 'selected' : '' }}>Full</option>
                        <option value="database" {{ request('type') == 'database' ? 'selected' : '' }}>Database</option>
                        <option value="files" {{ request('type') == 'files' ? 'selected' : '' }}>Files</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Backups Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Backup History
            </h3>
            <div class="flex space-x-2">
                <button type="button" 
                        onclick="refreshTable()"
                        class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
                <button type="button" 
                        onclick="bulkDelete()" 
                        disabled 
                        id="bulk-delete-btn"
                        class="inline-flex items-center px-3 py-1 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete Selected
                </button>
            </div>
        </div>
        <div id="backups-table">
            @include('admin.backups.table', ['backups' => $backups])
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Recent Activity
            </h3>
        </div>
        <div class="p-6">
            @if($recentActivity->count() > 0)
                <div class="space-y-4">
                    @foreach($recentActivity as $backup)
                        <div class="flex items-center">
                            <div class="mr-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->status_badge_class }}">
                                    <i class="{{ $backup->status_icon }}"></i>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900">{{ $backup->name }}</h4>
                                <p class="text-sm text-gray-500">
                                    {{ $backup->type }} backup • {{ $backup->created_at->diffForHumans() }}
                                    @if($backup->creator)
                                        • by {{ $backup->creator->name }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                @if($backup->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $backup->formatted_file_size }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No recent backup activity</h3>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div id="createBackupModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('createBackupModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('admin.backups.store') }}" method="POST" id="create-backup-form">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create New Backup</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Backup Name</label>
                                    <input type="text" 
                                           name="name" 
                                           required 
                                           placeholder="backup_{{ now()->format('Y_m_d_H_i_s') }}" 
                                           value="backup_{{ now()->format('Y_m_d_H_i_s') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="description" 
                                              rows="2" 
                                              placeholder="Optional description for this backup"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Backup Type</label>
                                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="full">Full Backup (Database + Files)</option>
                                        <option value="database">Database Only</option>
                                        <option value="files">Files Only</option>
                                    </select>
                                </div>
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                <strong>Note:</strong> Full backups may take longer to complete depending on your data size.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10v4a1 1 0 001 1h4M9 10V6a1 1 0 011-1h4a1 1 0 011 1v4"></path>
                        </svg>
                        Create Backup
                    </button>
                    <button type="button" 
                            onclick="closeModal('createBackupModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
<div id="cleanupModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('cleanupModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form onsubmit="performCleanup(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Cleanup Old Backups</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Keep backups for the last</label>
                                    <div class="flex">
                                        <input type="number" 
                                               name="days_to_keep" 
                                               value="30" 
                                               min="1" 
                                               max="365" 
                                               required
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <span class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 text-sm">days</span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Backups older than this will be permanently deleted</p>
                                </div>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                <strong>Warning:</strong> This action cannot be undone. Please make sure you want to delete old backups.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Cleanup
                    </button>
                    <button type="button" 
                            onclick="closeModal('cleanupModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Helper function for formatting bytes
function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('[id$="Dropdown"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Refresh table data
function refreshTable() {
    const params = new URLSearchParams(window.location.search);
    
    fetch(`{{ route('admin.backups.index') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('backups-table').innerHTML = data.backups;
        
        // Update stats
        document.getElementById('total-backups').textContent = data.stats.total_backups;
        document.getElementById('successful-backups').textContent = data.stats.successful_backups;
        document.getElementById('failed-backups').textContent = data.stats.failed_backups;
        document.getElementById('total-size').textContent = formatBytes(data.stats.total_size);
        
        showToast('Table refreshed successfully', 'success');
    })
    .catch(error => {
        console.error('Error refreshing table:', error);
        showToast('Failed to refresh table', 'error');
    });
}

// Schedule backup
function scheduleBackup(frequency) {
    if (!confirm(`Create a ${frequency} scheduled backup?`)) return;
    
    fetch('{{ route('admin.backups.scheduled') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ frequency: frequency })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => refreshTable(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error scheduling backup:', error);
        showToast('Failed to schedule backup', 'error');
    });
    
    // Hide dropdown
    document.getElementById('actionsDropdown').classList.add('hidden');
}

// Cleanup old backups
function cleanupOldBackups() {
    openModal('cleanupModal');
    // Hide dropdown
    document.getElementById('actionsDropdown').classList.add('hidden');
}

function performCleanup(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('{{ route('admin.backups.cleanup') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('cleanupModal');
            setTimeout(() => refreshTable(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error cleaning up backups:', error);
        showToast('Failed to cleanup backups', 'error');
    });
}

// Bulk delete
function bulkDelete() {
    const checked = document.querySelectorAll('input[name="selected_backups[]"]:checked');
    if (checked.length === 0) {
        showToast('Please select backups to delete', 'warning');
        return;
    }
    
    if (!confirm(`Delete ${checked.length} selected backup(s)?`)) return;
    
    const ids = Array.from(checked).map(cb => cb.value);
    
    fetch('{{ route('admin.backups.bulk-action') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: 'delete',
            backup_ids: ids
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const successful = data.results.filter(r => r.success).length;
            showToast(`Successfully deleted ${successful} backup(s)`, 'success');
            setTimeout(() => refreshTable(), 1000);
        } else {
            showToast('Bulk delete failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting backups:', error);
        showToast('Failed to delete backups', 'error');
    });
}

// Toggle select all
function toggleSelectAll() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('input[name="selected_backups[]"]');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

// Update bulk action buttons
function updateBulkActions() {
    const checked = document.querySelectorAll('input[name="selected_backups[]"]:checked');
    const bulkBtn = document.getElementById('bulk-delete-btn');
    
    bulkBtn.disabled = checked.length === 0;
}

// Auto-refresh every 30 seconds
setInterval(refreshTable, 30000);

// Toast notification helper
function showToast(message, type = 'info') {
    const colors = {
        'success': 'bg-green-100 border-green-400 text-green-700',
        'error': 'bg-red-100 border-red-400 text-red-700',
        'warning': 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info': 'bg-blue-100 border-blue-400 text-blue-700'
    };
    
    const toastHtml = `
        <div class="fixed top-4 right-4 z-50 border ${colors[type]} px-4 py-3 rounded animate-fade-in-down" role="alert">
            <span>${message}</span>
            <button type="button" class="float-right ml-4 text-lg font-bold leading-none" onclick="this.parentElement.remove()">&times;</button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    setTimeout(function() {
        const toast = document.querySelector('.fixed.top-4.right-4');
        if (toast) toast.remove();
    }, 5000);
}

// Form submission handling
document.getElementById('create-backup-form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Creating...';
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Add change listeners to checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.name === 'selected_backups[]') {
            updateBulkActions();
        }
    });
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
