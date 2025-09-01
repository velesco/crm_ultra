@extends('layouts.app')

@section('title', 'API Keys Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                API Keys Management
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage API keys for external integrations and third-party access</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.api-keys.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New API Key
            </a>
            <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors" onclick="openExportModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 border-l-4 border-indigo-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide mb-1">
                        Total API Keys
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $statistics['total'] }}</div>
                </div>
                <div class="ml-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-l-4 border-green-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">
                        Active Keys
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $statistics['active'] }}</div>
                </div>
                <div class="ml-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-l-4 border-yellow-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide mb-1">
                        Expired Keys
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $statistics['expired'] }}</div>
                </div>
                <div class="ml-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-l-4 border-cyan-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wide mb-1">
                        Total API Calls
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($statistics['total_usage']) }}</div>
                </div>
                <div class="ml-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
                </svg>
                Search & Filters
            </h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('admin.api-keys.index') }}" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" 
                               name="search" id="search" placeholder="Search by name, description..." value="{{ request('search') }}">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div>
                        <label for="environment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Environment</label>
                        <select name="environment" id="environment" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Environments</option>
                            <option value="production" {{ request('environment') === 'production' ? 'selected' : '' }}>Production</option>
                            <option value="staging" {{ request('environment') === 'staging' ? 'selected' : '' }}>Staging</option>
                            <option value="development" {{ request('environment') === 'development' ? 'selected' : '' }}>Development</option>
                        </select>
                    </div>
                    <div>
                        <label for="expiry" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiry Status</label>
                        <select name="expiry" id="expiry" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="expired" {{ request('expiry') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="expiring_soon" {{ request('expiry') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                            <option value="never_expires" {{ request('expiry') === 'never_expires' ? 'selected' : '' }}>Never Expires</option>
                        </select>
                    </div>
                    <div>
                        <label for="usage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usage</label>
                        <select name="usage" id="usage" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="unused" {{ request('usage') === 'unused' ? 'selected' : '' }}>Unused (30d)</option>
                            <option value="active" {{ request('usage') === 'active' ? 'selected' : '' }}>Active (30d)</option>
                            <option value="high_usage" {{ request('usage') === 'high_usage' ? 'selected' : '' }}>High Usage</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search
                        </button>
                        <a href="{{ route('admin.api-keys.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- API Keys Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">API Keys List</h3>
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Bulk Actions
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10 border border-gray-200 dark:border-gray-700">
                    <div class="py-1">
                        <a href="#" onclick="bulkAction('activate')" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Activate Selected
                        </a>
                        <a href="#" onclick="bulkAction('deactivate')" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Deactivate Selected
                        </a>
                        <a href="#" onclick="bulkAction('suspend')" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                            </svg>
                            Suspend Selected
                        </a>
                        <hr class="my-1 border-gray-200 dark:border-gray-700">
                        <a href="#" onclick="bulkAction('delete')" class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Selected
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            @if($apiKeys->count() > 0)
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="w-12 px-6 py-3">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-indigo-600 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Key</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Environment</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permissions</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usage</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expires</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($apiKeys as $apiKey)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 row-checkbox" value="{{ $apiKey->id }}">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $apiKey->name }}</div>
                                    @if($apiKey->description)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($apiKey->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-sm bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded">{{ $apiKey->masked_key }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClass = match($apiKey->status) {
                                            'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ ucfirst($apiKey->status) }}
                                    </span>
                                    @if($apiKey->is_expired)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 ml-1">
                                            Expired
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $envClass = match($apiKey->environment) {
                                            'production' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            'staging' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'development' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $envClass }}">
                                        {{ ucfirst($apiKey->environment) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($apiKey->permissions && count($apiKey->permissions) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                            {{ count($apiKey->permissions) }} permissions
                                        </span>
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">No permissions</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ number_format($apiKey->usage_count) }}</div>
                                    @if($apiKey->last_used_at)
                                        <div class="text-gray-500 dark:text-gray-400">Last: {{ $apiKey->last_used_at->format('M j, Y') }}</div>
                                    @else
                                        <div class="text-gray-500 dark:text-gray-400">Never used</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($apiKey->expires_at)
                                        @if($apiKey->is_expired)
                                            <span class="text-red-600 dark:text-red-400 font-medium">Expired</span>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">{{ $apiKey->expires_at->format('M j, Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">Never expires</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.api-keys.show', $apiKey) }}" 
                                           class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.api-keys.edit', $apiKey) }}" 
                                           class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.api-keys.destroy', $apiKey) }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this API key?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ $apiKeys->firstItem() }} to {{ $apiKeys->lastItem() }} of {{ $apiKeys->total() }} results
                    </div>
                    <div>
                        {{ $apiKeys->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No API Keys Found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No API keys match your current filters.</p>
                    <a href="{{ route('admin.api-keys.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Your First API Key
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" x-data="{ show: false }" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Export API Keys</h3>
            <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form method="GET" action="{{ route('admin.api-keys.export') }}" class="mt-4">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="export_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" id="export_status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div>
                    <label for="export_environment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Environment</label>
                    <select name="environment" id="export_environment" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Environments</option>
                        <option value="production">Production</option>
                        <option value="staging">Staging</option>
                        <option value="development">Development</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" @click="show = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                    Cancel
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Export modal functionality
function openExportModal() {
    // Using Alpine.js for modal control
    window.dispatchEvent(new CustomEvent('show-export-modal'));
}

// Bulk actions
function bulkAction(action) {
    const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one API key');
        return;
    }

    const actionText = action === 'delete' ? 'delete' : action;
    if (confirm(`Are you sure you want to ${actionText} ${selectedIds.length} selected API key(s)?`)) {
        fetch('/admin/api-keys/bulk-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                action: action,
                api_keys: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>
@endpush