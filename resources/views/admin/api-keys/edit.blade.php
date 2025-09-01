@extends('layouts.app')

@section('title', 'Edit API Key - ' . $apiKey->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit API Key: {{ $apiKey->name }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Update API key settings and permissions</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.api-keys.show', $apiKey) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                View Details
            </a>
            <a href="{{ route('admin.api-keys.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.api-keys.update', $apiKey) }}" id="editApiKeyForm">
        @csrf
        @method('PUT')
        
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
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    API Key Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $apiKey->name) }}" 
                                       placeholder="e.g., Mobile App API Key"
                                       required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="environment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Environment <span class="text-red-500">*</span>
                                </label>
                                <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('environment') border-red-500 @enderror" 
                                        id="environment" 
                                        name="environment" 
                                        required>
                                    <option value="">Select Environment</option>
                                    <option value="production" {{ old('environment', $apiKey->environment) === 'production' ? 'selected' : '' }}>
                                        Production
                                    </option>
                                    <option value="staging" {{ old('environment', $apiKey->environment) === 'staging' ? 'selected' : '' }}>
                                        Staging
                                    </option>
                                    <option value="development" {{ old('environment', $apiKey->environment) === 'development' ? 'selected' : '' }}>
                                        Development
                                    </option>
                                </select>
                                @error('environment')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Describe the purpose of this API key...">{{ old('description', $apiKey->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="active" {{ old('status', $apiKey->status) === 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="inactive" {{ old('status', $apiKey->status) === 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                    <option value="suspended" {{ old('status', $apiKey->status) === 'suspended' ? 'selected' : '' }}>
                                        Suspended
                                    </option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiration Date (Optional)</label>
                                <input type="datetime-local" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('expires_at') border-red-500 @enderror" 
                                       id="expires_at" 
                                       name="expires_at" 
                                       value="{{ old('expires_at', $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d\TH:i') : '') }}"
                                       min="{{ now()->format('Y-m-d\TH:i') }}">
                                @error('expires_at')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-gray-500 text-sm mt-1">Leave empty for keys that never expire</p>
                            </div>
                        </div>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    API Permissions
                                </h4>
                                <div class="space-y-3">
                                    @foreach($availablePermissions as $key => $label)
                                        <label class="flex items-start p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                            <input class="w-4 h-4 text-indigo-600 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 mt-1" 
                                                   type="checkbox" 
                                                   value="{{ $key }}" 
                                                   id="permission_{{ $key }}" 
                                                   name="permissions[]"
                                                   {{ in_array($key, old('permissions', $apiKey->permissions ?? [])) ? 'checked' : '' }}>
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $key }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                    @error('permissions')
                                        <p class="text-red-500 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    Access Scopes
                                </h4>
                                <div class="space-y-3">
                                    @foreach($availableScopes as $key => $label)
                                        <label class="flex items-start p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                            <input class="w-4 h-4 text-indigo-600 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 mt-1" 
                                                   type="checkbox" 
                                                   value="{{ $key }}" 
                                                   id="scope_{{ $key }}" 
                                                   name="scopes[]"
                                                   {{ in_array($key, old('scopes', $apiKey->scopes ?? [])) ? 'checked' : '' }}>
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $label }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">Access to {{ strtolower($label) }} functionality</div>
                                            </div>
                                        </label>
                                    @endforeach
                                    @error('scopes')
                                        <p class="text-red-500 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-6">
                            <div class="flex">
                                <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-blue-800 dark:text-blue-200 font-medium">Permission Guidelines</h4>
                                    <p class="text-blue-700 dark:text-blue-300 text-sm mt-1">Permissions define specific actions the API key can perform, while scopes determine which modules can be accessed. Select only the minimum required permissions for security.</p>
                                </div>
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
                            Security & Rate Limiting
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    IP Restrictions
                                </h4>
                                <div>
                                    <label for="allowed_ips" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Allowed IP Addresses (Optional)</label>
                                    <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('allowed_ips') border-red-500 @enderror" 
                                              id="allowed_ips" 
                                              name="allowed_ips" 
                                              rows="3" 
                                              placeholder="192.168.1.1, 10.0.0.1, 203.0.113.5">{{ old('allowed_ips', $apiKey->allowed_ips) }}</textarea>
                                    @error('allowed_ips')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-gray-500 text-sm mt-1">Enter comma-separated IP addresses. Leave empty to allow any IP.</p>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Rate Limiting
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label for="rate_limit_per_minute" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Requests per Minute</label>
                                        <input type="number" 
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('rate_limit_per_minute') border-red-500 @enderror" 
                                               id="rate_limit_per_minute" 
                                               name="rate_limit_per_minute" 
                                               value="{{ old('rate_limit_per_minute', $apiKey->rate_limit_per_minute) }}" 
                                               min="1" 
                                               max="1000"
                                               required>
                                        @error('rate_limit_per_minute')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="rate_limit_per_hour" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Requests per Hour</label>
                                        <input type="number" 
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('rate_limit_per_hour') border-red-500 @enderror" 
                                               id="rate_limit_per_hour" 
                                               name="rate_limit_per_hour" 
                                               value="{{ old('rate_limit_per_hour', $apiKey->rate_limit_per_hour) }}" 
                                               min="1" 
                                               max="50000"
                                               required>
                                        @error('rate_limit_per_hour')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="rate_limit_per_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Requests per Day</label>
                                        <input type="number" 
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('rate_limit_per_day') border-red-500 @enderror" 
                                               id="rate_limit_per_day" 
                                               name="rate_limit_per_day" 
                                               value="{{ old('rate_limit_per_day', $apiKey->rate_limit_per_day) }}" 
                                               min="1" 
                                               max="1000000"
                                               required>
                                        @error('rate_limit_per_day')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mt-6">
                            <div class="flex">
                                <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-yellow-800 dark:text-yellow-200 font-medium">Security Notice</h4>
                                    <ul class="text-yellow-700 dark:text-yellow-300 text-sm mt-1 list-disc list-inside space-y-1">
                                        <li>Rate limits help protect against abuse and ensure fair usage</li>
                                        <li>IP restrictions add an extra layer of security</li>
                                        <li>Changes will take effect immediately after saving</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Information & Actions -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Current API Key Info Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                            </svg>
                            Current API Key
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Masked Key:</label>
                            <div class="flex">
                                <input type="text" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-md text-gray-900 dark:text-white" value="{{ $apiKey->masked_key }}" readonly>
                                <button type="button" class="px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-r-md" 
                                        onclick="copyToClipboard('{{ $apiKey->masked_key }}')" 
                                        title="Copy to clipboard">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status:</label>
                            @php
                                $statusClass = match($apiKey->status) {
                                    'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                };
                            @endphp
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ ucfirst($apiKey->status) }}
                                </span>
                                @if($apiKey->is_expired)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 ml-1">
                                        Expired
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usage:</label>
                            <p class="text-gray-900 dark:text-white font-medium">{{ number_format($apiKey->usage_count) }} total calls</p>
                            @if($apiKey->last_used_at)
                                <p class="text-sm text-gray-500 dark:text-gray-400">Last used {{ $apiKey->last_used_at->diffForHumans() }}</p>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">Never used</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Actions
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Save Changes
                            </button>

                            <button type="button" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors" onclick="resetForm()">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset Form
                            </button>

                            <hr class="border-gray-200 dark:border-gray-700">

                            <a href="{{ route('admin.api-keys.show', $apiKey) }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Details
                            </a>

                            <form method="POST" action="{{ route('admin.api-keys.regenerate', $apiKey) }}" style="display: inline;" class="w-full">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 text-white font-medium rounded-md hover:bg-yellow-700 transition-colors" 
                                        onclick="return confirm('Are you sure? This will generate a new API key and invalidate the current one.')">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Regenerate Key
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Help & Tips
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Environment Guidelines:</h4>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li><strong>Production:</strong> Live applications, highest security</li>
                                <li><strong>Staging:</strong> Testing environment, moderate security</li>
                                <li><strong>Development:</strong> Development only, lower limits</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Rate Limiting:</h4>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>Set appropriate limits to prevent abuse</li>
                                <li>Higher limits for production environments</li>
                                <li>Monitor usage patterns regularly</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Security Tips:</h4>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>Use IP restrictions when possible</li>
                                <li>Grant minimum required permissions</li>
                                <li>Set expiration dates for temporary access</li>
                                <li>Rotate keys regularly for security</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Copy to clipboard functionality
function copyToClipboard(text) {
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

// Reset form to original values
function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.getElementById('editApiKeyForm').reset();
        
        // Reset checkboxes to their original state
        @if($apiKey->permissions)
            const originalPermissions = @json($apiKey->permissions);
            document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                checkbox.checked = originalPermissions.includes(checkbox.value);
            });
        @endif

        @if($apiKey->scopes)
            const originalScopes = @json($apiKey->scopes);
            document.querySelectorAll('input[name="scopes[]"]').forEach(checkbox => {
                checkbox.checked = originalScopes.includes(checkbox.value);
            });
        @endif
    }
}

// Form submission with loading state
document.getElementById('editApiKeyForm').addEventListener('submit', function(e) {
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Saving Changes...';
    submitButton.disabled = true;
    
    // Reset button if there are validation errors (will be handled by Laravel)
    setTimeout(() => {
        if (submitButton.disabled) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    }, 5000);
});

// Auto-calculate rate limits based on environment
document.getElementById('environment').addEventListener('change', function() {
    const environment = this.value;
    const minuteField = document.getElementById('rate_limit_per_minute');
    const hourField = document.getElementById('rate_limit_per_hour');
    const dayField = document.getElementById('rate_limit_per_day');
    
    // Only update if current values are defaults or empty
    const isDefaultValues = (
        minuteField.value == 60 || minuteField.value == 100 || minuteField.value == 30 || minuteField.value == '' ||
        hourField.value == 1000 || hourField.value == 5000 || hourField.value == 500 || hourField.value == '' ||
        dayField.value == 10000 || dayField.value == 50000 || dayField.value == 5000 || dayField.value == ''
    );
    
    if (isDefaultValues && confirm('Update rate limits based on the selected environment?')) {
        switch(environment) {
            case 'production':
                minuteField.value = 100;
                hourField.value = 5000;
                dayField.value = 50000;
                break;
            case 'staging':
                minuteField.value = 60;
                hourField.value = 1000;
                dayField.value = 10000;
                break;
            case 'development':
                minuteField.value = 30;
                hourField.value = 500;
                dayField.value = 5000;
                break;
        }
    }
});
</script>
@endpush