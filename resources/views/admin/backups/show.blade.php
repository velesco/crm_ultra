@extends('layouts.app')

@section('title', 'Backup Details - ' . $backup->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white flex items-center">
                <i class="{{ $backup->type_icon }} text-{{ $backup->status === 'completed' ? 'green' : ($backup->status === 'failed' ? 'red' : 'yellow') }}-400 mr-3"></i>
                {{ $backup->name }}
            </h1>
            <nav class="flex" aria-label="breadcrumb">
                <ol class="flex items-center space-x-2 text-sm text-gray-400">
                    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-blue-400">Admin</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('admin.backups.index') }}" class="hover:text-blue-400">Backups</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-500">{{ $backup->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center space-x-3">
            @if($backup->status === 'completed' && $backup->file_path)
                <a href="{{ route('admin.backups.download', $backup) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                    </svg>
                    Download
                </a>
                <button type="button" onclick="openModal('restoreModal')" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    Restore
                </button>
            @endif
            <a href="{{ route('admin.backups.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white hover:bg-gray-600 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Card -->
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700">
                    <h5 class="text-lg font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Backup Information
                    </h5>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $backup->status === 'completed' ? 'bg-green-900 text-green-200' : ($backup->status === 'failed' ? 'bg-red-900 text-red-200' : 'bg-yellow-900 text-yellow-200') }}">
                        <i class="{{ $backup->status_icon }} mr-1"></i>
                        {{ ucwords(str_replace('_', ' ', $backup->status)) }}
                    </span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dl class="space-y-3">
                                <div class="flex">
                                    <dt class="text-gray-300 font-medium w-24">Name:</dt>
                                    <dd class="text-white ml-4">{{ $backup->name }}</dd>
                                </div>
                                
                                <div class="flex">
                                    <dt class="text-gray-300 font-medium w-24">Type:</dt>
                                    <dd class="text-white ml-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600">
                                            <i class="{{ $backup->type_icon }} mr-1"></i>
                                            {{ ucfirst($backup->type) }} Backup
                                        </span>
                                    </dd>
                                </div>
                                
                                <div class="flex">
                                    <dt class="text-gray-300 font-medium w-24">Size:</dt>
                                    <dd class="text-white ml-4">
                                        @if($backup->file_size)
                                            <span class="font-semibold">{{ $backup->formatted_file_size }}</span>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div class="flex">
                                    <dt class="text-gray-300 font-medium w-24">Duration:</dt>
                                    <dd class="text-white ml-4">
                                        @if($backup->formatted_duration)
                                            {{ $backup->formatted_duration }}
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <dl class="space-y-3">
                                <div class="flex">
                                    <dt class="text-gray-300 font-medium w-32">Created:</dt>
                                    <dd class="text-white ml-4">
                                        {{ $backup->created_at->format('M j, Y H:i') }}
                                        <br>
                                        <small class="text-gray-400">{{ $backup->created_at->diffForHumans() }}</small>
                                    </dd>
                                </div>
                                
                                <div class="flex">
                                    <dt class="text-gray-300 font-medium w-32">Started:</dt>
                                    <dd class="text-white ml-4">
                                        @if($backup->started_at)
                                            {{ $backup->started_at->format('H:i:s') }}
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div class="flex">
                                    <dt class="text-gray-300 font-medium w-32">Completed:</dt>
                                    <dd class="text-white ml-4">
                                        @if($backup->completed_at)
                                            {{ $backup->completed_at->format('H:i:s') }}
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </dd>
                                </div>
                                
                                <div class="flex">
                                    <dt class="text-gray-300 font-medium w-32">Created by:</dt>
                                    <dd class="text-white ml-4">
                                        @if($backup->creator)
                                            <div class="flex items-center">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($backup->creator->name) }}&size=24&background=007bff&color=fff" 
                                                     class="rounded-full mr-2" width="24" height="24" alt="Avatar">
                                                {{ $backup->creator->name }}
                                            </div>
                                        @else
                                            <span class="text-gray-500">System</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    @if($backup->description)
                        <div class="mt-6 pt-6 border-t border-gray-700">
                            <h6 class="text-white font-semibold mb-2">Description:</h6>
                            <p class="text-gray-400">{{ $backup->description }}</p>
                        </div>
                    @endif
                    
                    @if($backup->status === 'failed' && $backup->error_message)
                        <div class="mt-6 pt-6 border-t border-gray-700">
                            <h6 class="text-red-400 font-semibold mb-2">Error Details:</h6>
                            <div class="bg-red-900 border border-red-700 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-red-200">{{ $backup->error_message }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Validation Results -->
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-700">
                    <h5 class="text-lg font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Backup Validation
                    </h5>
                    <button type="button" onclick="validateBackup()" class="inline-flex items-center px-3 py-1 border border-blue-600 rounded-lg text-sm font-medium text-blue-400 hover:bg-blue-600 hover:text-white transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Re-validate
                    </button>
                </div>
                <div class="p-6">
                    <div id="validation-results">
                        @if($validation['valid'])
                            <div class="bg-green-900 border border-green-700 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-green-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-green-200 font-semibold">Valid:</p>
                                        <p class="text-green-300 text-sm">{{ $validation['message'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-red-900 border border-red-700 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-red-200 font-semibold">Invalid:</p>
                                        <p class="text-red-300 text-sm">{{ $validation['error'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($backup->status === 'completed')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <div class="bg-gray-700 rounded-lg p-4 text-center">
                                <svg class="w-8 h-8 text-blue-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h6 class="text-white font-semibold mb-1">File Integrity</h6>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $validation['valid'] ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' }}">
                                    {{ $validation['valid'] ? 'Valid' : 'Invalid' }}
                                </span>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-4 text-center">
                                <svg class="w-8 h-8 text-cyan-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4H8c-2.21 0-4-1.79-4-4zm0 0c0 2.21 1.79 4 4 4h4c2.21 0 4-1.79 4-4V3c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4v4z"></path>
                                </svg>
                                <h6 class="text-white font-semibold mb-1">File Exists</h6>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->fileExists() ? 'bg-green-900 text-green-200' : 'bg-red-900 text-red-200' }}">
                                    {{ $backup->fileExists() ? 'Yes' : 'No' }}
                                </span>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-4 text-center">
                                <svg class="w-8 h-8 text-yellow-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-1m-3 1l-3-1"></path>
                                </svg>
                                <h6 class="text-white font-semibold mb-1">Size Match</h6>
                                @php
                                    $actualSize = $backup->getActualFileSize();
                                    $sizeMatch = $actualSize === $backup->file_size;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sizeMatch ? 'bg-green-900 text-green-200' : 'bg-yellow-900 text-yellow-200' }}">
                                    {{ $sizeMatch ? 'Match' : 'Different' }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Backup Contents -->
            @if($backup->status === 'completed' && $backup->metadata)
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h5 class="text-lg font-semibold text-white flex items-center">
                            <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Backup Contents
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($backup->type === 'full' || $backup->type === 'database')
                                <div>
                                    <h6 class="text-white font-semibold mb-3 flex items-center">
                                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4H8c-2.21 0-4-1.79-4-4zm0 0c0 2.21 1.79 4 4 4h4c2.21 0 4-1.79 4-4V3c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4v4z"></path>
                                        </svg>
                                        Database
                                    </h6>
                                    <ul class="space-y-2">
                                        <li class="flex items-center text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            All tables and data
                                        </li>
                                        <li class="flex items-center text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Indexes and constraints
                                        </li>
                                        <li class="flex items-center text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Stored procedures
                                        </li>
                                        <li class="flex items-center text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            User permissions
                                        </li>
                                    </ul>
                                </div>
                            @endif
                            
                            @if($backup->type === 'full' || $backup->type === 'files')
                                <div>
                                    <h6 class="text-white font-semibold mb-3 flex items-center">
                                        <svg class="w-5 h-5 text-cyan-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5L9.293 3.293A1 1 0 008.586 3H5a2 2 0 00-2 2v2z"></path>
                                        </svg>
                                        Files
                                    </h6>
                                    <ul class="space-y-2">
                                        <li class="flex items-center text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Application files
                                        </li>
                                        <li class="flex items-center text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Configuration files
                                        </li>
                                        <li class="flex items-center text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Storage files
                                        </li>
                                        <li class="flex items-center text-green-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            View templates
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                        
                        @if(isset($backup->metadata['includes']))
                            <div class="mt-6 pt-6 border-t border-gray-700">
                                <h6 class="text-white font-semibold mb-3">Included Files:</h6>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($backup->metadata['includes'] as $key => $file)
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300 mr-2">{{ ucfirst($key) }}:</span>
                                            <code class="text-sm text-blue-400">{{ $file }}</code>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Quick Actions -->
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="px-4 py-3 border-b border-gray-700">
                    <h6 class="text-white font-semibold flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Quick Actions
                    </h6>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @if($backup->status === 'completed' && $backup->file_path)
                            <a href="{{ route('admin.backups.download', $backup) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                </svg>
                                Download Backup
                            </a>
                            <button type="button" onclick="openModal('restoreModal')" class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                                Restore System
                            </button>
                            <button type="button" onclick="validateBackup()" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Validate Integrity
                            </button>
                        @endif
                        
                        @if($backup->canBeDeleted())
                            <button type="button" onclick="deleteBackup()" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Backup
                            </button>
                        @endif
                        
                        <a href="{{ route('admin.backups.create') }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-600 rounded-lg font-medium text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create New Backup
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="px-4 py-3 border-b border-gray-700">
                    <h6 class="text-white font-semibold flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                        System Information
                    </h6>
                </div>
                <div class="p-4">
                    @if($backup->metadata)
                        <dl class="space-y-2 text-sm">
                            @if(isset($backup->metadata['laravel_version']))
                                <div class="flex justify-between">
                                    <dt class="text-gray-300">Laravel:</dt>
                                    <dd class="text-white">{{ $backup->metadata['laravel_version'] }}</dd>
                                </div>
                            @endif
                            
                            @if(isset($backup->metadata['php_version']))
                                <div class="flex justify-between">
                                    <dt class="text-gray-300">PHP:</dt>
                                    <dd class="text-white">{{ $backup->metadata['php_version'] }}</dd>
                                </div>
                            @endif
                            
                            @if(isset($backup->metadata['database']))
                                <div class="flex justify-between">
                                    <dt class="text-gray-300">Database:</dt>
                                    <dd class="text-white">{{ $backup->metadata['database'] }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif

                    <div class="mt-4">
                        <h6 class="text-white text-sm font-semibold mb-2">Backup Health:</h6>
                        <div class="w-full bg-gray-700 rounded-full h-2 mb-2">
                            @php
                                $healthScore = $backup->status === 'completed' ? ($validation['valid'] ? 100 : 50) : 0;
                                $healthColor = $healthScore >= 80 ? 'bg-green-600' : ($healthScore >= 50 ? 'bg-yellow-600' : 'bg-red-600');
                            @endphp
                            <div class="{{ $healthColor }} h-2 rounded-full transition-all duration-300" style="width: {{ $healthScore }}%"></div>
                        </div>
                        <small class="text-gray-400">Health Score: {{ $healthScore }}%</small>
                    </div>
                </div>
            </div>

            <!-- Related Backups -->
            @if($relatedBackups->count() > 0)
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="px-4 py-3 border-b border-gray-700">
                        <h6 class="text-white font-semibold flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            Related Backups
                        </h6>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            @foreach($relatedBackups as $related)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <a href="{{ route('admin.backups.show', $related) }}" class="text-blue-400 hover:text-blue-300 font-medium text-sm">
                                            {{ $related->name }}
                                        </a>
                                        <p class="text-xs text-gray-400">
                                            {{ $related->type }} • {{ $related->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $related->status === 'completed' ? 'bg-green-900 text-green-200' : ($related->status === 'failed' ? 'bg-red-900 text-red-200' : 'bg-yellow-900 text-yellow-200') }}">
                                        {{ $related->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div id="restoreModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="restore-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('restoreModal')"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-700">
            <form action="{{ route('admin.backups.restore', $backup) }}" method="POST">
                @csrf
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-white" id="restore-modal-title">
                                Restore System from Backup
                            </h3>
                            <div class="mt-4">
                                <p class="text-sm text-gray-400">
                                    This will restore your system to the state when this backup was created. All current data will be overwritten.
                                </p>
                                <div class="mt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="confirm_restore" required class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700">
                                        <span class="ml-2 text-sm text-gray-300">I understand that this action cannot be undone</span>
                                    </label>
                                </div>
                                <div class="bg-yellow-900 border border-yellow-700 rounded-lg p-4 mt-4">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-sm text-yellow-200">
                                            <strong>Warning:</strong> This is a destructive operation. Make sure you want to proceed.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                        </svg>
                        Restore System
                    </button>
                    <button type="button" 
                            onclick="closeModal('restoreModal')"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-600 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Validate backup
function validateBackup() {
    const resultsContainer = document.getElementById('validation-results');
    resultsContainer.innerHTML = '<div class="text-center"><svg class="w-8 h-8 animate-spin text-blue-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><p class="text-gray-400 mt-2">Validating backup...</p></div>';
    
    fetch(`{{ route('admin.backups.validate', $backup) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            resultsContainer.innerHTML = `
                <div class="bg-green-900 border border-green-700 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-green-200 font-semibold">Valid:</p>
                            <p class="text-green-300 text-sm">${data.message}</p>
                        </div>
                    </div>
                </div>
            `;
        } else {
            resultsContainer.innerHTML = `
                <div class="bg-red-900 border border-red-700 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-red-200 font-semibold">Invalid:</p>
                            <p class="text-red-300 text-sm">${data.error}</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        showToast('Backup validation completed', 'info');
    })
    .catch(error => {
        console.error('Validation error:', error);
        resultsContainer.innerHTML = `
            <div class="bg-red-900 border border-red-700 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="text-red-200 font-semibold">Error:</p>
                        <p class="text-red-300 text-sm">Failed to validate backup</p>
                    </div>
                </div>
            </div>
        `;
        showToast('Failed to validate backup', 'error');
    });
}

// Delete backup
function deleteBackup() {
    if (!confirm('Are you sure you want to delete this backup? This action cannot be undone.')) return;
    
    fetch(`{{ route('admin.backups.destroy', $backup) }}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Backup deleted successfully', 'success');
            setTimeout(() => {
                window.location.href = '{{ route('admin.backups.index') }}';
            }, 1500);
        } else {
            showToast('Failed to delete backup', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Failed to delete backup', 'error');
    });
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