@extends('layouts.app')

@section('title', 'API Key Details - ' . $apiKey->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                </svg>
                {{ $apiKey->name }}
            </h1>
            <div class="flex items-center mt-1">
                <p class="text-gray-600 dark:text-gray-400 mr-3">API Key Details & Usage Statistics</p>
                @if($apiKey->is_expired)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        EXPIRED
                    </span>
                @elseif(!$apiKey->is_active)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        INACTIVE
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        ACTIVE
                    </span>
                @endif
            </div>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.api-keys.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
            <a href="{{ route('admin.api-keys.edit', $apiKey) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
            <form method="POST" action="{{ route('admin.api-keys.regenerate', $apiKey) }}" style="display: inline;" 
                  onsubmit="return confirm('Are you sure? This will invalidate the current API key.')">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white font-medium rounded-md hover:bg-yellow-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Regenerate
                </button>
            </form>
            <form method="POST" action="{{ route('admin.api-keys.destroy', $apiKey) }}" style="display: inline;" 
                  onsubmit="return confirm('Are you sure you want to delete this API key? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    @if(session('api_key'))
        <!-- New API Key Alert -->
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-8">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-green-800 dark:text-green-200 font-medium">API Key Generated Successfully!</h3>
                    <p class="text-green-700 dark:text-green-300 text-sm mt-1">Your new API key has been generated. <strong>This is the only time you'll see the full key - save it securely!</strong></p>
                    <div class="mt-3 flex">
                        <input type="text" class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-green-300 dark:border-green-600 rounded-l-md text-gray-900 dark:text-white" id="fullApiKey" value="{{ session('api_key') }}" readonly>
                        <button type="button" class="px-4 py-2 bg-green-600 text-white border border-l-0 border-green-600 rounded-r-md hover:bg-green-700 transition-colors" onclick="copyToClipboard('fullApiKey')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="button" class="text-green-400 hover:text-green-600 dark:hover:text-green-200 ml-4" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content - Left Column -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Basic Information Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Basic Information
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name:</label>
                                <p class="text-gray-900 dark:text-white">{{ $apiKey->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Key:</label>
                                <div class="flex items-center">
                                    <code class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded mr-2">{{ $apiKey->masked_key }}</code>
                                    <button type="button" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300" 
                                            onclick="copyToClipboard('{{ $apiKey->masked_key }}')" 
                                            title="Copy masked key">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status:</label>
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
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Environment:</label>
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
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Created:</label>
                                <p class="text-gray-900 dark:text-white">{{ $apiKey->created_at->format('M j, Y g:i A') }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $apiKey->created_at->diffForHumans() }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Created By:</label>
                                @if($apiKey->createdBy)
                                    <p class="text-gray-900 dark:text-white">{{ $apiKey->createdBy->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $apiKey->createdBy->email }}</p>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400">System</p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Updated:</label>
                                <p class="text-gray-900 dark:text-white">{{ $apiKey->updated_at->format('M j, Y g:i A') }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $apiKey->updated_at->diffForHumans() }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expires:</label>
                                @if($apiKey->expires_at)
                                    @if($apiKey->is_expired)
                                        <div class="text-red-600 dark:text-red-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Expired on {{ $apiKey->expires_at->format('M j, Y') }}
                                        </div>
                                    @else
                                        <p class="text-gray-500 dark:text-gray-400">{{ $apiKey->expires_at->format('M j, Y') }}</p>
                                    @endif
                                @else
                                    <p class="text-gray-500 dark:text-gray-400">Never expires</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($apiKey->description)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description:</label>
                            <p class="text-gray-600 dark:text-gray-400">{{ $apiKey->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Permissions & Scopes Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Permissions & Scopes
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">API Permissions</h4>
                            @if($apiKey->permissions && count($apiKey->permissions) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($apiKey->permissions as $permission)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                            {{ $permission }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">No specific permissions set</p>
                            @endif
                        </div>
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Access Scopes</h4>
                            @if($apiKey->scopes && count($apiKey->scopes) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($apiKey->scopes as $scope)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $scope }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">No specific scopes set</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Security Settings
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Rate Limits</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Per Minute:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ number_format($apiKey->rate_limit_per_minute) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Per Hour:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ number_format($apiKey->rate_limit_per_hour) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Per Day:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        {{ number_format($apiKey->rate_limit_per_day) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-3">IP Restrictions</h4>
                            @if($apiKey->allowed_ips)
                                @php
                                    $ips = explode(',', $apiKey->allowed_ips);
                                @endphp
                                <div class="space-y-2">
                                    @foreach($ips as $ip)
                                        <code class="block bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1 rounded text-sm">{{ trim($ip) }}</code>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">No IP restrictions (any IP allowed)</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Statistics & Actions -->
        <div class="lg:col-span-1 space-y-8">
            <!-- Usage Statistics Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Usage Statistics
                    </h3>
                </div>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <h2 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($apiKey->usage_count) }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">Total API Calls</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ number_format($usageStats['requests_today']) }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Today</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($usageStats['requests_this_week']) }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">This Week</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($usageStats['requests_this_month']) }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">This Month</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="text-lg font-bold text-gray-600 dark:text-gray-400">{{ $usageStats['avg_requests_per_day'] }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Avg/Day</p>
                        </div>
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700 mb-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Used:</label>
                        @if($apiKey->last_used_at)
                            <p class="text-gray-900 dark:text-white">{{ $apiKey->last_used_at->format('M j, Y g:i A') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $apiKey->last_used_at->diffForHumans() }}</p>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">Never used</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('admin.api-keys.edit', $apiKey) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Settings
                        </a>

                        <form method="POST" action="{{ route('admin.api-keys.regenerate', $apiKey) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors" 
                                    onclick="return confirm('Are you sure? This will invalidate the current API key.')">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Regenerate Key
                            </button>
                        </form>

                        <button type="button" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors" onclick="exportApiKey()">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Configuration
                        </button>
                    </div>
                </div>
            </div>

            <!-- API Documentation Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        API Documentation
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Use this API key in your applications:</p>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Authentication Header:</label>
                        <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-md">
                            <code class="text-sm text-gray-800 dark:text-gray-200">Authorization: Bearer YOUR_API_KEY</code>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Example cURL Request:</label>
                        <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-md">
                            <code class="text-sm text-gray-800 dark:text-gray-200 block">
                                curl -H "Authorization: Bearer {{ $apiKey->masked_key }}" \<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;{{ url('/api/contacts') }}
                            </code>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Full API Documentation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(elementIdOrText) {
    let text;
    if (typeof elementIdOrText === 'string' && elementIdOrText.includes('_')) {
        // It's an element ID
        const element = document.getElementById(elementIdOrText);
        text = element ? element.value : elementIdOrText;
    } else {
        // It's the text itself
        text = elementIdOrText;
    }
    
    navigator.clipboard.writeText(text).then(function() {
        // Show success notification
        showNotification('Copied to clipboard!', 'success');
    }).catch(function() {
        showNotification('Failed to copy to clipboard', 'error');
    });
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-md text-white ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} shadow-lg`;
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                }
            </svg>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

function exportApiKey() {
    const apiKeyData = {
        name: '{{ $apiKey->name }}',
        key: '{{ $apiKey->masked_key }}',
        environment: '{{ $apiKey->environment }}',
        permissions: @json($apiKey->permissions ?? []),
        scopes: @json($apiKey->scopes ?? []),
        rate_limits: {
            per_minute: {{ $apiKey->rate_limit_per_minute }},
            per_hour: {{ $apiKey->rate_limit_per_hour }},
            per_day: {{ $apiKey->rate_limit_per_day }}
        },
        allowed_ips: '{{ $apiKey->allowed_ips ?? '' }}',
        created_at: '{{ $apiKey->created_at->format('Y-m-d H:i:s') }}',
        expires_at: '{{ $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : null }}'
    };

    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(apiKeyData, null, 2));
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", "api_key_{{ $apiKey->name }}.json");
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
    
    showNotification('Configuration exported successfully!', 'success');
}
</script>
@endpush