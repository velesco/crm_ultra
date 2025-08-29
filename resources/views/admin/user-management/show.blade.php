@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    User Details
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
                                <a href="{{ route('admin.user-management.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">User Management</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">{{ $user->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <a href="{{ route('admin.user-management.edit', $user) }}" class="inline-flex items-center px-4 py-2 border border-yellow-600 text-yellow-600 bg-white hover:bg-yellow-50 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit User
                </a>
                <a href="{{ route('admin.user-management.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column - User Info -->
        <div class="lg:col-span-4 space-y-6">
            <!-- User Profile Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Profile Information</h3>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center p-1 border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                            <div class="py-1">
                                <a href="{{ route('admin.user-management.edit', $user) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit User
                                </a>
                                @if($user->id !== auth()->id() && !$user->hasRole('super_admin'))
                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                    <a href="#" onclick="toggleUserStatus({{ $user->id }})" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        @if($user->is_active)
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Deactivate User
                                        @else
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Activate User
                                        @endif
                                    </a>
                                    <a href="#" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete User
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6 text-center">
                    <div class="mb-4">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                             class="w-32 h-32 rounded-full object-cover mx-auto">
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $user->name }}</h5>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">{{ $user->email }}</p>
                    
                    <!-- Status Badges -->
                    <div class="mb-4 flex justify-center space-x-2">
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
                                Pending Verification
                            </span>
                        @endif
                    </div>

                    <!-- User Details -->
                    <div class="text-left space-y-3">
                        @if($user->phone)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $user->phone }}</span>
                            </div>
                        @endif
                        
                        @if($user->department)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $user->department }}</span>
                            </div>
                        @endif
                        
                        @if($user->position)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6"></path>
                                </svg>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $user->position }}</span>
                            </div>
                        @endif
                        
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a3 3 0 100-6 3 3 0 000 6zm0 0v1m0 0v-1m0-8V8a4 4 0 10-8 0v1.5m0 0V10a1 1 0 011-1h2a1 1 0 011 1v1.5m-4 0h4"></path>
                            </svg>
                            <span class="text-sm text-gray-900 dark:text-white">Joined {{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        @if($user->last_login_at)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-900 dark:text-white">Last login {{ $user->last_login_at->diffForHumans() }}</span>
                            </div>
                        @else
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Never logged in</span>
                            </div>
                        @endif
                    </div>

                    @if($user->notes)
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h6 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Notes</h6>
                            <p class="text-sm text-left text-gray-900 dark:text-white">{{ $user->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Roles & Permissions Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Roles & Permissions</h3>
                </div>
                <div class="p-6">
                    <!-- Roles -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Roles</h4>
                        <div class="flex flex-wrap gap-2">
                            @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    No Roles Assigned
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Direct Permissions -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Direct Permissions</h4>
                        <div class="flex flex-wrap gap-2">
                            @if($user->permissions->count() > 0)
                                @foreach($user->permissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    No Direct Permissions
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Statistics Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Activity Statistics</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['email_campaigns'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Email Campaigns</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['contacts_created'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Contacts Created</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['segments_created'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Segments Created</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['total_logins'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Logins</div>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400 text-center space-y-1">
                            <div>Account Age: {{ $stats['account_age'] }}</div>
                            <div>Last Login: {{ $stats['last_login'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Activity Feed -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Recent Activity Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                    <button type="button" class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50 text-sm rounded-md transition-colors" onclick="refreshActivity()">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
                <div class="p-6">
                    <div id="activity-container">
                        @if($recentActivity->count() > 0)
                            <div class="timeline space-y-6">
                                @foreach($recentActivity as $activity)
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full">
                                                <svg class="w-5 h-5 text-{{ $activity['color'] ?? 'gray' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1 ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $activity['title'] }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['date']->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h5 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Recent Activity</h5>
                                <p class="text-gray-500 dark:text-gray-400">This user hasn't performed any tracked activities yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- System Information Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">System Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">User ID:</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $user->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Created:</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y H:i:s') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated:</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ $user->updated_at->format('M d, Y H:i:s') }}</span>
                            </div>
                            @if($user->email_verified_at)
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verified:</span>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $user->email_verified_at->format('M d, Y H:i:s') }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="space-y-3">
                            @if($user->createdBy)
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By:</span>
                                    <a href="{{ route('admin.user-management.show', $user->createdBy) }}" 
                                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                        {{ $user->createdBy->name }}
                                    </a>
                                </div>
                            @endif
                            @if($user->updatedBy)
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated By:</span>
                                    <a href="{{ route('admin.user-management.show', $user->updatedBy) }}" 
                                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                        {{ $user->updatedBy->name }}
                                    </a>
                                </div>
                            @endif
                            @if($user->timezone)
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Timezone:</span>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $user->timezone }}</span>
                                </div>
                            @endif
                            @if($user->language)
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Language:</span>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $user->language }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

function refreshActivity() {
    const container = $('#activity-container');
    const button = $('[onclick="refreshActivity()"]');
    const icon = button.find('i');
    
    // Show loading state
    icon.addClass('animate-spin');
    button.prop('disabled', true);
    
    $.ajax({
        url: `/admin/user-management/{{ $user->id }}/activity`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let html = '';
                if (response.activity.length > 0) {
                    html = '<div class="timeline space-y-6">';
                    response.activity.forEach(function(activity) {
                        html += `
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full">
                                        <svg class="w-5 h-5 text-${activity.color || 'gray'}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1 ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">${activity.title}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">${activity.formatted_date}</div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    html = `
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h5 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Recent Activity</h5>
                            <p class="text-gray-500 dark:text-gray-400">This user hasn't performed any tracked activities yet.</p>
                        </div>
                    `;
                }
                container.html(html);
            }
        },
        error: function(xhr) {
            console.error('Failed to refresh activity:', xhr);
        },
        complete: function() {
            // Remove loading state
            icon.removeClass('animate-spin');
            button.prop('disabled', false);
        }
    });
}
</script>
@endpush
