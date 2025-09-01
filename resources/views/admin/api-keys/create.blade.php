@extends('layouts.app')

@section('title', 'Create API Key')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New API Key
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Generate a new API key for external integrations</p>
        </div>
        <a href="{{ route('admin.api-keys.index') }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to API Keys
        </a>
    </div>

    <!-- Progress Indicator -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-8">
        <div class="px-6 py-4">
            <div class="flex items-center justify-center space-x-8">
                <div class="flex items-center step-progress active" id="progress1">
                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 text-white rounded-full mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">Basic Info</span>
                </div>
                <div class="flex items-center step-progress" id="progress2">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Permissions</span>
                </div>
                <div class="flex items-center step-progress" id="progress3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <span class="font-medium text-gray-500 dark:text-gray-400">Security</span>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.api-keys.store') }}" id="createApiKeyForm">
        @csrf
        
        <!-- Step 1: Basic Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-8" id="step1">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">Step 1 of 3:</span> Basic Information
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
                               value="{{ old('name') }}" 
                               placeholder="e.g., Mobile App API Key"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">Choose a descriptive name to identify this API key</p>
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
                            <option value="production" {{ old('environment') === 'production' ? 'selected' : '' }}>
                                Production
                            </option>
                            <option value="staging" {{ old('environment') === 'staging' ? 'selected' : '' }}>
                                Staging
                            </option>
                            <option value="development" {{ old('environment') === 'development' ? 'selected' : '' }}>
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
                              placeholder="Describe the purpose of this API key...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Initial Status <span class="text-red-500">*</span>
                        </label>
                        <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('status') border-red-500 @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
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
                               value="{{ old('expires_at') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('expires_at')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">Leave empty for keys that never expire</p>
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors" onclick="nextStep(2)">
                        Next: Permissions
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Permissions & Scopes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-8 hidden" id="step2">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">Step 2 of 3:</span> Permissions & Scopes
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
                                           {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}>
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
                                           {{ in_array($key, old('scopes', [])) ? 'checked' : '' }}>
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

                <div class="flex justify-between mt-8">
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors" onclick="prevStep(1)">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Previous: Basic Info
                    </button>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors" onclick="nextStep(3)">
                        Next: Security Settings
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Security Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-8 hidden" id="step3">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">Step 3 of 3:</span> Security & Rate Limiting
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
                                      placeholder="192.168.1.1, 10.0.0.1, 203.0.113.5">{{ old('allowed_ips') }}</textarea>
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
                                       value="{{ old('rate_limit_per_minute', 60) }}" 
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
                                       value="{{ old('rate_limit_per_hour', 1000) }}" 
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
                                       value="{{ old('rate_limit_per_day', 10000) }}" 
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
                                <li>The API key will be generated automatically and shown only once after creation</li>
                                <li>Store the API key securely - it cannot be recovered if lost</li>
                                <li>Rate limits help protect against abuse and ensure fair usage</li>
                                <li>IP restrictions add an extra layer of security</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors" onclick="prevStep(2)">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Previous: Permissions
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
                        </svg>
                        Create API Key
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let currentStep = 1;

function nextStep(step) {
    // Validate current step
    if (!validateStep(currentStep)) {
        return;
    }

    // Hide current step
    document.getElementById('step' + currentStep).classList.add('hidden');
    updateProgressStep(currentStep, false);

    // Show next step
    currentStep = step;
    document.getElementById('step' + currentStep).classList.remove('hidden');
    updateProgressStep(currentStep, true);
}

function prevStep(step) {
    // Hide current step
    document.getElementById('step' + currentStep).classList.add('hidden');
    updateProgressStep(currentStep, false);

    // Show previous step
    currentStep = step;
    document.getElementById('step' + currentStep).classList.remove('hidden');
    updateProgressStep(currentStep, true);
}

function updateProgressStep(stepNumber, isActive) {
    const progressElement = document.getElementById('progress' + stepNumber);
    const circle = progressElement.querySelector('div');
    const text = progressElement.querySelector('span');
    
    if (isActive) {
        progressElement.classList.add('active');
        circle.className = 'flex items-center justify-center w-10 h-10 bg-indigo-600 text-white rounded-full mr-3';
        text.className = 'font-medium text-gray-900 dark:text-white';
    } else {
        progressElement.classList.remove('active');
        circle.className = 'flex items-center justify-center w-10 h-10 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full mr-3';
        text.className = 'font-medium text-gray-500 dark:text-gray-400';
    }
}

function validateStep(step) {
    let isValid = true;
    
    if (step === 1) {
        // Validate basic information
        const name = document.getElementById('name').value.trim();
        const environment = document.getElementById('environment').value;
        const status = document.getElementById('status').value;
        
        if (!name) {
            showFieldError('name', 'API Key name is required');
            isValid = false;
        }
        if (!environment) {
            showFieldError('environment', 'Environment is required');
            isValid = false;
        }
        if (!status) {
            showFieldError('status', 'Status is required');
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('border-red-500');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.custom-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('p');
    errorDiv.className = 'text-red-500 text-sm mt-1 custom-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
    
    // Remove error on input
    field.addEventListener('input', function() {
        this.classList.remove('border-red-500');
        const errorMsg = this.parentNode.querySelector('.custom-error');
        if (errorMsg) {
            errorMsg.remove();
        }
    }, { once: true });
}

// Auto-calculate rate limits based on environment
document.getElementById('environment').addEventListener('change', function() {
    const environment = this.value;
    const minuteField = document.getElementById('rate_limit_per_minute');
    const hourField = document.getElementById('rate_limit_per_hour');
    const dayField = document.getElementById('rate_limit_per_day');
    
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
});

// Form submission
document.getElementById('createApiKeyForm').addEventListener('submit', function(e) {
    // Final validation
    if (!validateStep(1)) {
        e.preventDefault();
        nextStep(1);
        return;
    }
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Creating API Key...';
    submitButton.disabled = true;
    
    // Reset button if there are validation errors (will be handled by Laravel)
    setTimeout(() => {
        if (submitButton.disabled) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    }, 5000);
});
</script>
@endpush