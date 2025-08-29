@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-full flex items-center justify-center mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-white">System Settings</h1>
                <p class="text-gray-400">Manage global system configuration and preferences</p>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.settings.create', ['group' => $selectedGroup]) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Setting
            </a>
            <button type="button" onclick="exportSettings()" 
                    class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                Export
            </button>
            <button type="button" onclick="clearCache()" 
                    class="inline-flex items-center px-4 py-2 border border-yellow-600 rounded-lg text-yellow-400 hover:bg-yellow-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Clear Cache
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-blue-100">Total Settings</h6>
                        <h3 class="text-2xl font-bold">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-green-100">Public Settings</h6>
                        <h3 class="text-2xl font-bold">{{ number_format($stats['public']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-green-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-yellow-100">Encrypted Settings</h6>
                        <h3 class="text-2xl font-bold">{{ number_format($stats['encrypted']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-red-100">Requires Restart</h6>
                        <h3 class="text-2xl font-bold">{{ number_format($stats['requires_restart']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-red-500 bg-opacity-30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar - Settings Groups -->
        <div class="lg:col-span-1">
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="px-4 py-3 border-b border-gray-700">
                    <h6 class="text-white font-semibold">Setting Groups</h6>
                </div>
                <div class="p-4">
                    <nav class="space-y-1">
                        @foreach($groups as $key => $name)
                            <a href="{{ route('admin.settings.index', ['group' => $key, 'search' => $search]) }}" 
                               class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200 {{ $selectedGroup === $key ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($key === 'general')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                        @elseif($key === 'email')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        @elseif($key === 'sms')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        @elseif($key === 'whatsapp')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        @elseif($key === 'api')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                        @elseif($key === 'security')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        @endif
                                    </svg>
                                    {{ $name }}
                                </span>
                                @if(isset($stats['by_group'][$key]))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $selectedGroup === $key ? 'bg-blue-700 text-blue-100' : 'bg-gray-700 text-gray-300' }}">
                                        {{ $stats['by_group'][$key] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main Content - Settings List -->
        <div class="lg:col-span-3">
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700">
                    <div>
                        <h6 class="text-white font-semibold">{{ $groups[$selectedGroup] ?? 'All' }} Settings</h6>
                    </div>
                    <div class="flex items-center">
                        <!-- Search Form -->
                        <form method="GET" class="flex items-center">
                            <input type="hidden" name="group" value="{{ $selectedGroup }}">
                            <div class="flex items-center">
                                <input type="text" name="search" value="{{ $search }}" 
                                       class="w-64 px-3 py-2 border border-gray-600 rounded-l-lg bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                       placeholder="Search settings...">
                                <button type="submit" 
                                        class="px-3 py-2 bg-gray-600 border border-l-0 border-gray-600 rounded-r-lg text-gray-300 hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </button>
                                @if($search)
                                    <a href="{{ route('admin.settings.index', ['group' => $selectedGroup]) }}" 
                                       class="ml-2 px-3 py-2 bg-gray-600 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="overflow-hidden">
                    @if($settings->count() > 0)
                        <!-- Settings Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-12">
                                            <input type="checkbox" id="select-all" 
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700">
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Setting
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Value
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                                            Modified
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider w-32">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-gray-800 divide-y divide-gray-700">
                                    @foreach($settings as $setting)
                                        <tr class="hover:bg-gray-700 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" class="setting-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700" 
                                                       value="{{ $setting->id }}">
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col">
                                                    <div class="text-white font-medium">{{ $setting->label }}</div>
                                                    <div class="text-gray-400 text-sm font-mono">{{ $setting->key }}</div>
                                                    @if($setting->description)
                                                        <div class="text-gray-400 text-sm mt-1">{{ Str::limit($setting->description, 80) }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 max-w-xs">
                                                <div class="setting-value">
                                                    @if($setting->is_encrypted)
                                                        <span class="text-gray-500">••••••••</span>
                                                    @elseif($setting->type === 'boolean')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $setting->value ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' }}">
                                                            {{ $setting->value ? 'Yes' : 'No' }}
                                                        </span>
                                                    @elseif($setting->type === 'json')
                                                        <code class="text-sm text-blue-400 bg-gray-900 px-2 py-1 rounded">{{ Str::limit(json_encode($setting->value), 40) }}</code>
                                                    @else
                                                        <span class="text-white">{{ Str::limit($setting->value ?? 'null', 40) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600">
                                                    {{ $setting->type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col space-y-1">
                                                    @if($setting->is_public)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-900 text-green-200">Public</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-700 text-gray-300">Private</span>
                                                    @endif
                                                    
                                                    @if($setting->is_encrypted)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-900 text-yellow-200">Encrypted</span>
                                                    @endif
                                                    
                                                    @if($setting->requires_restart)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-900 text-red-200">Restart Required</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                <div class="text-gray-300">{{ $setting->updated_at->format('M j, Y') }}</div>
                                                @if($setting->updatedBy)
                                                    <div class="text-gray-500 text-xs">by {{ $setting->updatedBy->name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-2">
                                                    <a href="{{ route('admin.settings.show', $setting) }}" 
                                                       class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors duration-200"
                                                       title="View">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </a>
                                                    @if($setting->isEditable())
                                                        <a href="{{ route('admin.settings.edit', $setting) }}" 
                                                           class="inline-flex items-center px-2 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-xs rounded transition-colors duration-200"
                                                           title="Edit">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </a>
                                                        <button type="button" 
                                                                onclick="deleteSetting({{ $setting->id }})" 
                                                                class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition-colors duration-200"
                                                                title="Delete">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 bg-gray-600 text-gray-400 text-xs rounded cursor-not-allowed" title="Read Only">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($settings->hasPages())
                            <div class="px-6 py-4 border-t border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-400">
                                        Showing {{ $settings->firstItem() }} to {{ $settings->lastItem() }} of {{ $settings->total() }} settings
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($settings->onFirstPage())
                                            <span class="px-3 py-1 text-sm font-medium text-gray-500 bg-gray-700 border border-gray-600 rounded-lg cursor-not-allowed">
                                                Previous
                                            </span>
                                        @else
                                            <a href="{{ $settings->previousPageUrl() }}" class="px-3 py-1 text-sm font-medium text-gray-300 bg-gray-700 border border-gray-600 rounded-lg hover:bg-gray-600 hover:text-white transition-colors duration-200">
                                                Previous
                                            </a>
                                        @endif

                                        @foreach($settings->getUrlRange(1, $settings->lastPage()) as $page => $url)
                                            @if($page == $settings->currentPage())
                                                <span class="px-3 py-1 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg">
                                                    {{ $page }}
                                                </span>
                                            @else
                                                <a href="{{ $url }}" class="px-3 py-1 text-sm font-medium text-gray-300 bg-gray-700 border border-gray-600 rounded-lg hover:bg-gray-600 hover:text-white transition-colors duration-200">
                                                    {{ $page }}
                                                </a>
                                            @endif
                                        @endforeach

                                        @if($settings->hasMorePages())
                                            <a href="{{ $settings->nextPageUrl() }}" class="px-3 py-1 text-sm font-medium text-gray-300 bg-gray-700 border border-gray-600 rounded-lg hover:bg-gray-600 hover:text-white transition-colors duration-200">
                                                Next
                                            </a>
                                        @else
                                            <span class="px-3 py-1 text-sm font-medium text-gray-500 bg-gray-700 border border-gray-600 rounded-lg cursor-not-allowed">
                                                Next
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="mx-auto w-24 h-24 bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                </svg>
                            </div>
                            <h5 class="text-lg font-medium text-gray-300 mb-2">No Settings Found</h5>
                            <p class="text-gray-400 mb-6">
                                @if($search)
                                    No settings match your search criteria.
                                @else
                                    No settings configured for this group yet.
                                @endif
                            </p>
                            <a href="{{ route('admin.settings.create', ['group' => $selectedGroup]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add First Setting
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="bulkActionsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="bulk-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('bulkActionsModal')"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-700">
            <form id="bulkActionsForm" method="POST" action="{{ route('admin.settings.bulk-action') }}">
                @csrf
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-white" id="bulk-modal-title">
                                Bulk Actions
                            </h3>
                            <div class="mt-4">
                                <div class="mb-3">
                                    <label for="bulkAction" class="block text-sm font-medium text-gray-300 mb-2">Action</label>
                                    <select class="w-full px-3 py-2 border border-gray-600 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                            id="bulkAction" name="action" required>
                                        <option value="">Select an action...</option>
                                        <option value="delete">Delete Selected</option>
                                        <option value="toggle_public">Toggle Public/Private</option>
                                        <option value="export">Export Selected</option>
                                    </select>
                                </div>
                                <div id="selectedSettings"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="executeBulkAction()" 
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Execute Action
                    </button>
                    <button type="button" onclick="closeModal('bulkActionsModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-600 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="delete-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('deleteModal')"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-700">
            <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-white" id="delete-modal-title">
                            Confirm Delete
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-400">Are you sure you want to delete this setting? This action cannot be undone.</p>
                            <div class="bg-red-900 border border-red-700 rounded-lg p-4 mt-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm text-red-200">
                                        <strong>Warning:</strong> Deleting system settings may affect application functionality.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Setting
                    </button>
                </form>
                <button type="button" onclick="closeModal('deleteModal')"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-600 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const settingCheckboxes = document.querySelectorAll('.setting-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            settingCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Update select all when individual checkboxes change
    settingCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.setting-checkbox:checked');
            selectAllCheckbox.checked = checkedBoxes.length === settingCheckboxes.length;
        });
    });

    // Show bulk actions when settings are selected
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('setting-checkbox')) {
            const checkedBoxes = document.querySelectorAll('.setting-checkbox:checked');
            
            // Show/hide bulk actions button
            let bulkBtn = document.getElementById('bulk-actions-btn');
            if (checkedBoxes.length > 0) {
                if (!bulkBtn) {
                    const toolbar = document.querySelector('.flex.items-center.space-x-3');
                    bulkBtn = document.createElement('button');
                    bulkBtn.id = 'bulk-actions-btn';
                    bulkBtn.className = 'inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200';
                    bulkBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>Bulk Actions';
                    bulkBtn.onclick = showBulkActions;
                    toolbar.appendChild(bulkBtn);
                }
            } else if (bulkBtn) {
                bulkBtn.remove();
            }
        }
    });
});

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function showBulkActions() {
    const checkedBoxes = document.querySelectorAll('.setting-checkbox:checked');
    if (checkedBoxes.length === 0) {
        showToast('Please select at least one setting.', 'warning');
        return;
    }
    
    // Update the form with selected settings
    const selectedSettings = Array.from(checkedBoxes).map(cb => cb.value);
    const container = document.getElementById('selectedSettings');
    container.innerHTML = '';
    
    selectedSettings.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'settings[]';
        input.value = id;
        container.appendChild(input);
    });
    
    openModal('bulkActionsModal');
}

function executeBulkAction() {
    const form = document.getElementById('bulkActionsForm');
    const action = document.getElementById('bulkAction').value;
    
    if (!action) {
        showToast('Please select an action.', 'warning');
        return;
    }
    
    if (action === 'delete') {
        if (!confirm('Are you sure you want to delete the selected settings? This action cannot be undone.')) {
            return;
        }
    }
    
    form.submit();
}

function deleteSetting(settingId) {
    document.getElementById('deleteForm').action = `/admin/settings/${settingId}`;
    openModal('deleteModal');
}

function exportSettings() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = "{{ route('admin.settings.export') }}?" + params.toString();
}

function clearCache() {
    if (confirm('Are you sure you want to clear the system cache? This will refresh all cached settings.')) {
        fetch("{{ route('admin.settings.clear-cache') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                showToast(data.message, 'success');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to clear cache. Please try again.', 'error');
        });
    }
}

// Toast notification helper
function showToast(message, type = 'info') {
    const colors = {
        'success': 'bg-green-600',
        'error': 'bg-red-600',
        'warning': 'bg-yellow-600',
        'info': 'bg-blue-600'
    };
    
    const toastHtml = `
        <div class="fixed top-4 right-4 z-50 ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg animate-fade-in-down" role="alert">
            <span>${message}</span>
            <button type="button" class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.remove();">&times;</button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    setTimeout(function() {
        const toasts = document.querySelectorAll('.fixed.top-4.right-4');
        toasts.forEach(toast => toast.remove());
    }, 5000);
}
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