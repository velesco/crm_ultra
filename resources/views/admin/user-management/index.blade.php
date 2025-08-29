@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header with actions -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    User Management
                </h1>
                <nav class="flex mt-1" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                Admin
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">User Management</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <button type="button" class="inline-flex items-center px-4 py-2 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 font-medium rounded-md transition-colors" x-data x-on:click="$dispatch('open-modal', 'bulk-action-modal')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Bulk Actions
                </button>
                <a href="{{ route('admin.user-management.export') }}" class="inline-flex items-center px-4 py-2 border border-green-600 text-green-600 bg-white hover:bg-green-50 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('admin.user-management.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create User
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <!-- Total Users -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-indigo-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide mb-1">
                        Total Users
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['total_users'] }}
                    </div>
                </div>
                <div class="ml-3">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-green-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">
                        Active
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['active_users'] }}
                    </div>
                </div>
                <div class="ml-3">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Inactive -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-yellow-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide mb-1">
                        Inactive
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['inactive_users'] }}
                    </div>
                </div>
                <div class="ml-3">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-blue-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">
                        Pending
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['pending_verification'] }}
                    </div>
                </div>
                <div class="ml-3">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- This Month -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-gray-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-1">
                        This Month
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['new_this_month'] }}
                    </div>
                </div>
                <div class="ml-3">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Roles -->
        <div class="bg-white dark:bg-gray-800 border-l-4 border-purple-500 rounded-lg shadow-sm p-4">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wide mb-1">
                        Roles
                    </div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['roles_distribution']->count() }}
                    </div>
                </div>
                <div class="ml-3">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('admin.user-management.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <input type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Name, email, phone...">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                <select class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="role" name="role">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                <input type="date" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                <input type="date" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                    </svg>
                    Filter
                </button>
            </div>
        </form>

        @if(request()->hasAny(['search', 'role', 'status', 'date_from', 'date_to']))
            <div class="mt-4">
                <a href="{{ route('admin.user-management.index') }}" class="inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </a>
            </div>
        @endif
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h6 class="text-lg font-semibold text-gray-900 dark:text-white">Users ({{ $users->total() }})</h6>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 text-sm rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                    Sort
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                    <div class="py-1">
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => 'asc']) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Name (A-Z)</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => 'desc']) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Name (Z-A)</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'desc']) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Newest First</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'asc']) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Oldest First</a>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'last_login_at', 'sort_order' => 'desc']) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Recent Login</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            @if($users->count() > 0)
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <div class="flex items-center">
                                    <input type="checkbox" id="selectAll" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2">
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Roles</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" class="user-checkbox w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2" 
                                               value="{{ $user->id }}" name="user_ids[]">
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                                                 class="h-10 w-10 rounded-full object-cover">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                            @if($user->phone)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @if($user->roles->count() > 0)
                                            @foreach($user->roles as $role)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                No Role
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->department)
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->department }}</div>
                                    @endif
                                    @if($user->position)
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->position }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <div><strong>Campaigns:</strong> {{ $user->email_campaigns_count }}</div>
                                        <div><strong>Contacts:</strong> {{ $user->contacts_created_count }}</div>
                                        <div><strong>Segments:</strong> {{ $user->contact_segments_count }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Inactive
                                            </span>
                                        @endif
                                        
                                        @if($user->email_verified_at)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Verified
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <div>{{ $user->created_at->format('M d, Y') }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">{{ $user->created_at->diffForHumans() }}</div>
                                        @if($user->last_login_at)
                                            <div class="text-green-600 dark:text-green-400">Last: {{ $user->last_login_at->diffForHumans() }}</div>
                                        @else
                                            <div class="text-gray-500 dark:text-gray-400">Never logged in</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.user-management.show', $user) }}" 
                                           class="inline-flex items-center p-1 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded transition-colors" title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.user-management.edit', $user) }}" 
                                           class="inline-flex items-center p-1 border border-yellow-600 text-yellow-600 hover:bg-yellow-50 rounded transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        @if($user->id !== auth()->id() && !$user->hasRole('super_admin'))
                                            <button type="button" class="inline-flex items-center p-1 border border-{{ $user->is_active ? 'red' : 'green' }}-600 text-{{ $user->is_active ? 'red' : 'green' }}-600 hover:bg-{{ $user->is_active ? 'red' : 'green' }}-50 rounded transition-colors" 
                                                    onclick="toggleUserStatus({{ $user->id }})" title="Toggle Status">
                                                @if($user->is_active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @endif
                                            </button>
                                            <button type="button" class="inline-flex items-center p-1 border border-red-600 text-red-600 hover:bg-red-50 rounded transition-colors" 
                                                    onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} 
                        of {{ $users->total() }} results
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h5 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Users Found</h5>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No users match your current filters.</p>
                    @if(request()->hasAny(['search', 'role', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('admin.user-management.index') }}" class="inline-flex items-center px-4 py-2 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 font-medium rounded-md transition-colors">
                            Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div x-data="{ show: false }" 
     x-on:open-modal.window="$event.detail === 'bulk-action-modal' ? show = true : null"
     x-show="show" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="show = false"></div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <form id="bulkActionForm" method="POST" action="{{ route('admin.user-management.bulk-action') }}">
                @csrf
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                                Bulk Actions
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="bulk_action" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Action</label>
                                    <select class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="bulk_action" name="action" required>
                                        <option value="">Choose action...</option>
                                        <option value="activate">Activate Users</option>
                                        <option value="deactivate">Deactivate Users</option>
                                        <option value="delete">Delete Users</option>
                                        <option value="assign_role">Assign Role</option>
                                        <option value="remove_role">Remove Role</option>
                                    </select>
                                </div>

                                <div id="role_selection" style="display: none;">
                                    <label for="bulk_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Role</label>
                                    <select class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500" id="bulk_role" name="role">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="text-sm text-blue-700 dark:text-blue-300">
                                            Selected users: <span id="selectedCount" class="font-medium">0</span>
                                        </div>
                                    </div>
                                </div>

                                <div id="warningAlert" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-3" style="display: none;">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <div class="text-sm text-yellow-700 dark:text-yellow-300">
                                            <span id="warningText"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="bulkActionSubmit" disabled 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Execute Action
                    </button>
                    <button type="button" @click="show = false" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
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
$(document).ready(function() {
    // Select All functionality
    $('#selectAll').change(function() {
        $('.user-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });

    $('.user-checkbox').change(function() {
        updateSelectedCount();
        $('#selectAll').prop('checked', $('.user-checkbox:checked').length === $('.user-checkbox').length);
    });

    // Bulk action modal
    $('#bulk_action').change(function() {
        const action = $(this).val();
        const roleSelection = $('#role_selection');
        const warningAlert = $('#warningAlert');
        const warningText = $('#warningText');

        if (action === 'assign_role' || action === 'remove_role') {
            roleSelection.show();
        } else {
            roleSelection.hide();
        }

        // Show warnings for destructive actions
        if (action === 'delete') {
            warningText.text('This action cannot be undone. Deleted users will be permanently removed.');
            warningAlert.show();
        } else if (action === 'deactivate') {
            warningText.text('Deactivated users will not be able to log in until reactivated.');
            warningAlert.show();
        } else {
            warningAlert.hide();
        }
    });

    // Bulk action form submission
    $('#bulkActionForm').submit(function(e) {
        const selectedUsers = $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedUsers.length === 0) {
            e.preventDefault();
            alert('Please select at least one user.');
            return false;
        }

        // Add selected user IDs to form
        selectedUsers.forEach(function(userId) {
            $('<input>').attr({
                type: 'hidden',
                name: 'user_ids[]',
                value: userId
            }).appendTo('#bulkActionForm');
        });

        return confirm('Are you sure you want to perform this action on ' + selectedUsers.length + ' user(s)?');
    });
});

function updateSelectedCount() {
    const count = $('.user-checkbox:checked').length;
    $('#selectedCount').text(count);
    $('#bulkActionSubmit').prop('disabled', count === 0);
}

function toggleUserStatus(userId) {
    if (confirm('Are you sure you want to toggle this user\'s status?')) {
        $.ajax({
            url: `/admin/user-management/${userId}/toggle-status`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                alert('Error: ' + (response?.error || 'Failed to update user status'));
            }
        });
    }
}

function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/user-management/${userId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = $('meta[name="csrf-token"]').attr('content');
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
