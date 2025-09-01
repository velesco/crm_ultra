@extends('layouts.app')

@section('title', 'System Setting Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <!-- Breadcrumb -->
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Admin
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('admin.settings.index') }}" class="ml-1 text-gray-400 hover:text-blue-500 md:ml-2 transition-colors duration-200">System Settings</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('admin.settings.index', ['group' => $systemSetting->group]) }}" class="ml-1 text-gray-400 hover:text-blue-500 md:ml-2 transition-colors duration-200">{{ ucfirst($systemSetting->group) }}</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-gray-500 md:ml-2">{{ $systemSetting->label }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $systemSetting->label }}</h1>
                    <p class="text-gray-400 font-mono text-sm">{{ $systemSetting->key }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.settings.index', ['group' => $systemSetting->group]) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to {{ ucfirst($systemSetting->group) }}
            </a>
            @if($systemSetting->isEditable())
                <a href="{{ route('admin.settings.edit', ['systemSetting' => $systemSetting->id]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Setting
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Setting Details -->
        <div class="lg:col-span-2">
            <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-700">
                    <div class="flex justify-between items-center">
                        <h6 class="text-white font-semibold">Setting Details</h6>
                        <div class="flex items-center space-x-2">
                            @if($systemSetting->is_public)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">
                                    Public
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">
                                    Private
                                </span>
                            @endif
                            
                            @if($systemSetting->is_encrypted)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-900 text-yellow-200">
                                    Encrypted
                                </span>
                            @endif
                            
                            @if($systemSetting->requires_restart)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-200">
                                    Restart Required
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Key Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Setting Key</label>
                            <div class="flex items-center space-x-2">
                                <code class="flex-1 bg-gray-700 text-blue-300 px-3 py-2 rounded-lg text-sm">{{ $systemSetting->key }}</code>
                                <button type="button" 
                                        onclick="copyToClipboard('{{ $systemSetting->key }}')" 
                                        class="inline-flex items-center px-2 py-2 bg-gray-600 hover:bg-gray-500 text-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                                        title="Copy Key">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Data Type</label>
                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-900 text-blue-200">
                                    {{ $systemSetting->type }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Value Display -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-400 mb-2">Current Value</label>
                        <div class="bg-gray-700 rounded-lg border border-gray-600 p-4">
                            @if($systemSetting->is_encrypted)
                                <div class="flex items-center p-3 bg-yellow-900 border border-yellow-700 rounded-lg">
                                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <span class="text-yellow-200">This value is encrypted and cannot be displayed for security reasons.</span>
                                </div>
                            @elseif($systemSetting->type === 'boolean')
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mr-3 {{ $systemSetting->value ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' }}">
                                        {{ $systemSetting->value ? 'True' : 'False' }}
                                    </span>
                                    <svg class="w-5 h-5 {{ $systemSetting->value ? 'text-green-400' : 'text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($systemSetting->value)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @endif
                                    </svg>
                                </div>
                            @elseif($systemSetting->type === 'json')
                                <div class="relative">
                                    <pre class="bg-gray-800 text-gray-300 p-4 rounded-lg text-sm overflow-auto max-h-80 font-mono"><code id="json-value">{{ json_encode($systemSetting->value, JSON_PRETTY_PRINT) }}</code></pre>
                                    <button type="button" 
                                            onclick="copyToClipboard(document.getElementById('json-value').textContent)" 
                                            class="absolute top-2 right-2 inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-500 text-gray-300 rounded text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                                            title="Copy JSON">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        Copy
                                    </button>
                                </div>
                            @elseif($systemSetting->type === 'text')
                                <div class="relative">
                                    <div class="bg-gray-800 text-gray-300 p-4 rounded-lg whitespace-pre-wrap max-h-80 overflow-y-auto">{{ $systemSetting->value ?: 'No value set' }}</div>
                                    @if($systemSetting->value)
                                        <button type="button" 
                                                onclick="copyToClipboard('{{ addslashes($systemSetting->value) }}')" 
                                                class="absolute top-2 right-2 inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-500 text-gray-300 rounded text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                                                title="Copy Text">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            Copy
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center space-x-2">
                                    <code class="flex-1 bg-gray-800 text-white px-3 py-2 rounded text-sm">{{ $systemSetting->value ?? 'null' }}</code>
                                    @if($systemSetting->value)
                                        <button type="button" 
                                                onclick="copyToClipboard('{{ addslashes($systemSetting->value) }}')" 
                                                class="inline-flex items-center px-2 py-2 bg-gray-600 hover:bg-gray-500 text-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                                                title="Copy Value">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($systemSetting->description)
                        <!-- Description -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                            <div class="bg-gray-700 text-gray-300 p-4 rounded-lg">
                                {{ $systemSetting->description }}
                            </div>
                        </div>
                    @endif

                    <!-- Advanced Configuration -->
                    @if($systemSetting->validation_rules || $systemSetting->options)
                        <div class="bg-gray-700 rounded-lg border border-gray-600 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-600">
                                <h6 class="text-white font-semibold">Advanced Configuration</h6>
                            </div>
                            <div class="p-4 space-y-4">
                                @if($systemSetting->validation_rules)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400 mb-2">Validation Rules</label>
                                        <pre class="bg-gray-800 text-gray-300 p-3 rounded text-sm font-mono overflow-auto"><code>{{ json_encode($systemSetting->validation_rules, JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                @endif
                                
                                @if($systemSetting->options)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-400 mb-2">Available Options</label>
                                        <pre class="bg-gray-800 text-gray-300 p-3 rounded text-sm font-mono overflow-auto"><code>{{ json_encode($systemSetting->options, JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Information -->
            <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h6 class="text-white font-semibold">Usage Information</h6>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">PHP Access</label>
                            <div class="flex items-center space-x-2">
                                <code class="flex-1 bg-gray-700 text-green-300 px-3 py-2 rounded text-sm font-mono">SystemSetting::get('{{ $systemSetting->key }}')</code>
                                <button type="button" 
                                        onclick="copyToClipboard('SystemSetting::get(\'{{ $systemSetting->key }}\')')" 
                                        class="inline-flex items-center px-2 py-2 bg-gray-600 hover:bg-gray-500 text-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                                        title="Copy Code">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Helper Function</label>
                            <div class="flex items-center space-x-2">
                                <code class="flex-1 bg-gray-700 text-green-300 px-3 py-2 rounded text-sm font-mono">setting('{{ $systemSetting->key }}')</code>
                                <button type="button" 
                                        onclick="copyToClipboard('setting(\'{{ $systemSetting->key }}\')')" 
                                        class="inline-flex items-center px-2 py-2 bg-gray-600 hover:bg-gray-500 text-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                                        title="Copy Code">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    @if($systemSetting->is_public)
                        <div class="flex items-center p-4 bg-blue-900 border border-blue-700 rounded-lg mb-4">
                            <svg class="w-5 h-5 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-blue-200">
                                <strong>Public Setting:</strong> This setting can be accessed by non-admin users and may be cached for better performance.
                            </div>
                        </div>
                    @endif
                    
                    @if($systemSetting->requires_restart)
                        <div class="flex items-center p-4 bg-yellow-900 border border-yellow-700 rounded-lg">
                            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div class="text-yellow-200">
                                <strong>Restart Required:</strong> Changes to this setting require clearing the application cache or restarting the application to take effect.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="lg:col-span-1">
            <!-- Meta Information -->
            <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h6 class="text-white font-semibold">Meta Information</h6>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Group</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-700 text-gray-300">
                            {{ ucfirst(str_replace('_', ' ', $systemSetting->group)) }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Sort Order</label>
                        <div class="text-white">{{ $systemSetting->sort_order }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Editable</label>
                        <div>
                            @if($systemSetting->isEditable())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">
                                    Yes
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-200">
                                    No
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Created</label>
                        <div class="text-white">{{ $systemSetting->created_at->format('M j, Y g:i A') }}</div>
                        @if($systemSetting->createdBy)
                            <div class="text-gray-400 text-sm">by {{ $systemSetting->createdBy->name }}</div>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Last Modified</label>
                        <div class="text-white">{{ $systemSetting->updated_at->format('M j, Y g:i A') }}</div>
                        @if($systemSetting->updatedBy)
                            <div class="text-gray-400 text-sm">by {{ $systemSetting->updatedBy->name }}</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-sm overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h6 class="text-white font-semibold">Actions</h6>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @if($systemSetting->isEditable())
                            <a href="{{ route('admin.settings.edit', $systemSetting) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Setting
                            </a>
                            
                            <button type="button" onclick="deleteSetting()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-600 rounded-lg text-red-400 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Setting
                            </button>
                        @else
                            <div class="p-4 bg-yellow-900 border border-yellow-700 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <span class="text-yellow-200 text-sm">This setting is read-only and cannot be modified.</span>
                                </div>
                            </div>
                        @endif
                        
                        <button type="button" onclick="exportSetting()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                            </svg>
                            Export Setting
                        </button>
                        
                        <a href="{{ route('admin.settings.create', ['group' => $systemSetting->group]) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-600 rounded-lg text-blue-400 hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Similar Setting
                        </a>
                    </div>
                </div>
            </div>
            
            @if($relatedSettings->count() > 0)
                <!-- Related Settings -->
                <div class="bg-gray-800 rounded-lg border border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h6 class="text-white font-semibold">Related Settings in {{ ucfirst($systemSetting->group) }}</h6>
                    </div>
                    <div class="divide-y divide-gray-700">
                        @foreach($relatedSettings as $related)
                            <a href="{{ route('admin.settings.show', $related) }}" 
                               class="block px-6 py-4 hover:bg-gray-700 transition-colors duration-200">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-white font-medium">{{ $related->label }}</div>
                                        <div class="text-gray-400 text-sm font-mono truncate">{{ $related->key }}</div>
                                    </div>
                                    <div class="flex items-center space-x-1 ml-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-700 text-gray-300">
                                            {{ $related->type }}
                                        </span>
                                        @if($related->is_encrypted)
                                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Encrypted">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="px-6 py-4 bg-gray-700">
                        <a href="{{ route('admin.settings.index', ['group' => $systemSetting->group]) }}" 
                           class="inline-flex items-center text-sm text-blue-400 hover:text-blue-300 transition-colors duration-200">
                            View All {{ ucfirst($systemSetting->group) }} Settings
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if($systemSetting->isEditable())
    <div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="delete-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
            
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
                                <p class="text-sm text-gray-400">Are you sure you want to delete the setting <strong>{{ $systemSetting->label }}</strong>?</p>
                                <div class="mt-4 p-4 bg-red-900 border border-red-700 rounded-lg">
                                    <div class="flex">
                                        <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm text-red-200">
                                                <strong>Warning:</strong> This action cannot be undone and may affect application functionality.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 bg-gray-700 p-3 rounded">
                                    <div class="text-sm text-gray-300">
                                        <strong>Setting:</strong> {{ $systemSetting->key }}<br>
                                        <strong>Current Value:</strong> 
                                        @if($systemSetting->is_encrypted)
                                            ••••••••
                                        @else
                                            {{ $systemSetting->getFormattedValue() }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form method="POST" action="{{ route('admin.settings.destroy', $systemSetting) }}" style="display: inline;">
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
                    <button type="button" onclick="closeModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-600 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Copied to clipboard!', 'success');
    }).catch(function(err) {
        console.error('Failed to copy text: ', err);
        showToast('Failed to copy to clipboard', 'error');
    });
}

function showToast(message, type = 'info') {
    const colors = {
        'success': 'bg-green-600',
        'error': 'bg-red-600',
        'warning': 'bg-yellow-600',
        'info': 'bg-blue-600'
    };
    
    const icons = {
        'success': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'error': 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'warning': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
        'info': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    };
    
    const toastHtml = `
        <div class="fixed top-4 right-4 z-50 ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg animate-fade-in-down flex items-center" role="alert">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[type]}"></path>
            </svg>
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

function deleteSetting() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function exportSetting() {
    const settingData = {
        key: '{{ $systemSetting->key }}',
        value: {!! $systemSetting->is_encrypted ? '"[ENCRYPTED]"' : json_encode($systemSetting->value) !!},
        type: '{{ $systemSetting->type }}',
        group: '{{ $systemSetting->group }}',
        label: '{{ $systemSetting->label }}',
        description: {!! json_encode($systemSetting->description) !!},
        validation_rules: {!! json_encode($systemSetting->validation_rules) !!},
        options: {!! json_encode($systemSetting->options) !!},
        is_public: {{ $systemSetting->is_public ? 'true' : 'false' }},
        is_encrypted: {{ $systemSetting->is_encrypted ? 'true' : 'false' }},
        requires_restart: {{ $systemSetting->requires_restart ? 'true' : 'false' }},
        sort_order: {{ $systemSetting->sort_order }}
    };
    
    const dataStr = JSON.stringify(settingData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `setting-${settingData.key.replace(/\./g, '-')}-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showToast('Setting exported successfully!', 'success');
}

// Enhance JSON syntax highlighting
document.addEventListener('DOMContentLoaded', function() {
    const jsonElement = document.getElementById('json-value');
    if (jsonElement) {
        let jsonText = jsonElement.textContent;
        jsonText = jsonText
            .replace(/"([^"]+)":/g, '<span class="text-blue-400">"$1"</span>:')
            .replace(/: "([^"]*)"/g, ': <span class="text-green-400">"$1"</span>')
            .replace(/: (true|false|null)/g, ': <span class="text-yellow-400">$1</span>')
            .replace(/: (\d+)/g, ': <span class="text-red-400">$1</span>');
        
        jsonElement.innerHTML = jsonText;
    }
});
</script>
@endpush

@push('styles')
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
