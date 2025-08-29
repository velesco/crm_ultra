@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit User
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
                                <a href="{{ route('admin.user-management.show', $user) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">{{ $user->name }}</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Edit</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <a href="{{ route('admin.user-management.show', $user) }}" class="inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 bg-white hover:bg-blue-50 font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View User
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

    <form action="{{ route('admin.user-management.update', $user) }}" method="POST" id="editUserForm">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Column - Basic Information -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Basic Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                                <input type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('phone') border-red-500 @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                                       placeholder="+1 (555) 123-4567">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Department</label>
                                <input type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('department') border-red-500 @enderror" 
                                       id="department" name="department" value="{{ old('department', $user->department) }}" 
                                       placeholder="e.g., Marketing, Sales, IT">
                                @error('department')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position/Job Title</label>
                            <input type="text" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('position') border-red-500 @enderror" 
                                   id="position" name="position" value="{{ old('position', $user->position) }}" 
                                   placeholder="e.g., Marketing Manager, Sales Representative">
                            @error('position')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Change Password
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Leave blank to keep current password</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                                <div class="relative">
                                    <input type="password" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 pr-10 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-red-500 @enderror" 
                                           id="password" name="password">
                                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" onclick="togglePassword('password')">
                                        <svg class="w-5 h-5" id="password-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                                @error('password')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                                <div class="relative">
                                    <input type="password" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 pr-10 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('password_confirmation') border-red-500 @enderror" 
                                           id="password_confirmation" name="password_confirmation">
                                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" onclick="togglePassword('password_confirmation')">
                                        <svg class="w-5 h-5" id="password_confirmation-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="inline-flex items-center px-3 py-1 border border-blue-600 text-blue-600 bg-white hover:bg-blue-50 text-sm rounded-md transition-colors" onclick="generatePassword()">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Generate Secure Password
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Roles & Permissions Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Roles & Permissions
                        </h3>
                    </div>
                    <div class="p-6">
                        <!-- Roles Section -->
                        <div class="mb-6">
                            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-3">Roles</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($roles as $role)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2" 
                                                   name="roles[]" value="{{ $role->name }}" 
                                                   id="role_{{ $role->id }}"
                                                   {{ collect(old('roles', $user->roles->pluck('name')->toArray()))->contains($role->name) ? 'checked' : '' }}>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="role_{{ $role->id }}" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                                {{ ucfirst($role->name) }}
                                            </label>
                                            @if($role->description)
                                                <p class="text-gray-500 dark:text-gray-400">{{ $role->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Direct Permissions Section -->
                        <div>
                            <h4 class="text-base font-medium text-gray-900 dark:text-white mb-3">Additional Direct Permissions</h4>
                            <div class="space-y-2" x-data="{ openAccordion: null }">
                                @foreach($permissions as $category => $categoryPermissions)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-md">
                                        <button type="button" 
                                                class="w-full px-4 py-3 text-left bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-600 transition-colors"
                                                @click="openAccordion = openAccordion === {{ $loop->index }} ? null : {{ $loop->index }}">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ ucfirst($category) }} Permissions ({{ $categoryPermissions->count() }})
                                                </span>
                                                <svg class="w-5 h-5 text-gray-500 transition-transform" 
                                                     :class="{ 'rotate-180': openAccordion === {{ $loop->index }} }" 
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                        </button>
                                        <div x-show="openAccordion === {{ $loop->index }}" x-transition class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($categoryPermissions as $permission)
                                                    <div class="flex items-start">
                                                        <div class="flex items-center h-5">
                                                            <input type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2" 
                                                                   name="permissions[]" value="{{ $permission->name }}" 
                                                                   id="permission_{{ $permission->id }}"
                                                                   {{ collect(old('permissions', $user->permissions->pluck('name')->toArray()))->contains($permission->name) ? 'checked' : '' }}>
                                                        </div>
                                                        <div class="ml-3 text-sm">
                                                            <label for="permission_{{ $permission->id }}" class="text-gray-700 dark:text-gray-300 cursor-pointer">
                                                                {{ str_replace('_', ' ', ucfirst($permission->name)) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            Additional Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                            <textarea class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 @error('notes') border-red-500 @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Any additional notes or information about this user...">{{ old('notes', $user->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Settings & Actions -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Account Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Account Settings
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2" 
                                       id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Active Account</label>
                                <p class="text-gray-500 dark:text-gray-400">User can log in and access the system</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2" 
                                       id="email_verified" name="email_verified" value="1" {{ old('email_verified', $user->email_verified_at) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="email_verified" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Email Verified</label>
                                <p class="text-gray-500 dark:text-gray-400">Mark email as verified</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update User
                        </button>

                        @if($user->id !== auth()->id() && !$user->hasRole('super_admin'))
                            <button type="button" class="w-full inline-flex justify-center items-center px-4 py-2 border border-{{ $user->is_active ? 'red' : 'green' }}-600 text-{{ $user->is_active ? 'red' : 'green' }}-600 bg-white hover:bg-{{ $user->is_active ? 'red' : 'green' }}-50 font-medium rounded-md transition-colors" onclick="toggleUserStatus({{ $user->id }})">
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
                            </button>

                            <button type="button" class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-600 text-red-600 bg-white hover:bg-red-50 font-medium rounded-md transition-colors" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete User
                            </button>
                        @endif
                        
                        <a href="{{ route('admin.user-management.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel
                        </a>
                    </div>
                </div>

                <!-- Current Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Current Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="text-sm space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Created:</span>
                                <span class="text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Last Updated:</span>
                                <span class="text-gray-900 dark:text-white">{{ $user->updated_at->format('M d, Y') }}</span>
                            </div>
                            @if($user->last_login_at)
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Last Login:</span>
                                    <span class="text-gray-900 dark:text-white">{{ $user->last_login_at->diffForHumans() }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
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
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + '-eye');
    
    if (field.type === 'password') {
        field.type = 'text';
        eye.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L12 12m-2.122-2.122L7.757 7.757M12 12l2.122 2.122m-2.122-2.122L14.122 9.878M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        `;
    } else {
        field.type = 'password';
        eye.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        `;
    }
}

function generatePassword() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('password_confirmation').value = password;
    
    // Show generated password for copying
    if (!document.getElementById('generated-password-display')) {
        const display = document.createElement('div');
        display.id = 'generated-password-display';
        display.className = 'mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md';
        display.innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Generated Password:</span>
                    <code class="ml-2 px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-sm font-mono">${password}</code>
                </div>
                <button type="button" class="inline-flex items-center px-2 py-1 text-xs border border-blue-300 text-blue-700 bg-white hover:bg-blue-50 rounded transition-colors" onclick="copyPassword('${password}')">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Copy
                </button>
            </div>
        `;
        document.querySelector('[onclick="generatePassword()"]').parentNode.appendChild(display);
    }
}

function copyPassword(password) {
    navigator.clipboard.writeText(password).then(function() {
        alert('Password copied to clipboard!');
    });
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

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirmation').value;
        
        if (password && password !== passwordConfirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password && password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return false;
        }
    });
});
</script>
@endpush
