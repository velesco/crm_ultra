@extends('layouts.app')

@section('title', 'Create Backup')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white flex items-center">
                <svg class="w-8 h-8 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New Backup
            </h1>
            <nav class="flex" aria-label="breadcrumb">
                <ol class="flex items-center space-x-2 text-sm text-gray-400">
                    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-blue-400">Admin</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li><a href="{{ route('admin.backups.index') }}" class="hover:text-blue-400">Backups</a></li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-500">Create</li>
                </ol>
            </nav>
        </div>
        <div>
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
        <div class="lg:col-span-2">
            <!-- Backup Creation Wizard -->
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Backup Creation Wizard
                    </h5>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.backups.store') }}" method="POST" id="backup-creation-form">
                        @csrf
                        
                        <!-- Step 1: Basic Information -->
                        <div class="wizard-step" id="step-1">
                            <div class="mb-6">
                                <h4 class="text-xl font-semibold text-white mb-4 flex items-center">
                                    <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">1</span>
                                    Basic Information
                                </h4>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Backup Name</label>
                                    <input type="text" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" 
                                           name="name" value="{{ old('name', 'backup_' . now()->format('Y_m_d_H_i_s')) }}" required>
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-400">
                                        Choose a descriptive name for your backup
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Description (Optional)</label>
                                    <textarea class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror" 
                                              name="description" rows="3" 
                                              placeholder="Describe the purpose or content of this backup">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Backup Type Selection -->
                        <div class="wizard-step hidden" id="step-2">
                            <div class="mb-6">
                                <h4 class="text-xl font-semibold text-white mb-4 flex items-center">
                                    <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">2</span>
                                    Select Backup Type
                                </h4>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="backup-type-card bg-gray-700 border border-gray-600 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-gray-600 transition-all duration-200" data-type="full">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <svg class="w-12 h-12 text-blue-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4H8c-2.21 0-4-1.79-4-4zm0 0c0 2.21 1.79 4 4 4h4c2.21 0 4-1.79 4-4V3c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4v4z"></path>
                                            </svg>
                                        </div>
                                        <h5 class="text-white font-semibold mb-2">Full Backup</h5>
                                        <p class="text-gray-400 text-sm mb-3">
                                            Complete system backup including database and files
                                        </p>
                                        <ul class="text-left text-xs space-y-1">
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Database structure & data
                                            </li>
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Application files
                                            </li>
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Configuration files
                                            </li>
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Storage files
                                            </li>
                                        </ul>
                                        <div class="mt-4">
                                            <input type="radio" class="hidden" name="type" value="full" id="type-full" required>
                                            <label class="block w-full px-3 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors cursor-pointer" for="type-full">
                                                Select Full Backup
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="backup-type-card bg-gray-700 border border-gray-600 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-gray-600 transition-all duration-200" data-type="database">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <svg class="w-12 h-12 text-yellow-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4H8c-2.21 0-4-1.79-4-4zm0 0c0 2.21 1.79 4 4 4h4c2.21 0 4-1.79 4-4V3c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4v4z"></path>
                                            </svg>
                                        </div>
                                        <h5 class="text-white font-semibold mb-2">Database Only</h5>
                                        <p class="text-gray-400 text-sm mb-3">
                                            Database backup with all tables and data
                                        </p>
                                        <ul class="text-left text-xs space-y-1">
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                All database tables
                                            </li>
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Data and relationships
                                            </li>
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Stored procedures
                                            </li>
                                            <li class="flex items-center text-gray-500">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                Files not included
                                            </li>
                                        </ul>
                                        <div class="mt-4">
                                            <input type="radio" class="hidden" name="type" value="database" id="type-database" required>
                                            <label class="block w-full px-3 py-2 bg-yellow-600 text-white text-sm font-semibold rounded-lg hover:bg-yellow-700 transition-colors cursor-pointer" for="type-database">
                                                Select Database Only
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="backup-type-card bg-gray-700 border border-gray-600 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-gray-600 transition-all duration-200" data-type="files">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <svg class="w-12 h-12 text-cyan-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-5L9.293 3.293A1 1 0 008.586 3H5a2 2 0 00-2 2v2z"></path>
                                            </svg>
                                        </div>
                                        <h5 class="text-white font-semibold mb-2">Files Only</h5>
                                        <p class="text-gray-400 text-sm mb-3">
                                            Application and storage files backup
                                        </p>
                                        <ul class="text-left text-xs space-y-1">
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Application files
                                            </li>
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Configuration files
                                            </li>
                                            <li class="flex items-center text-green-400">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Storage files
                                            </li>
                                            <li class="flex items-center text-gray-500">
                                                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                                Database not included
                                            </li>
                                        </ul>
                                        <div class="mt-4">
                                            <input type="radio" class="hidden" name="type" value="files" id="type-files" required>
                                            <label class="block w-full px-3 py-2 bg-cyan-600 text-white text-sm font-semibold rounded-lg hover:bg-cyan-700 transition-colors cursor-pointer" for="type-files">
                                                Select Files Only
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Backup Size Estimation -->
                            <div class="mt-6 hidden" id="size-estimation">
                                <div class="bg-blue-900 border border-blue-700 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <p class="text-blue-200">
                                                <span class="font-semibold">Estimated backup size:</span> 
                                                <span id="estimated-size" class="font-bold">Calculating...</span>
                                            </p>
                                            <p class="text-xs text-blue-300 mt-1">Actual size may vary. Processing time depends on data volume.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Review & Confirm -->
                        <div class="wizard-step hidden" id="step-3">
                            <div class="mb-6">
                                <h4 class="text-xl font-semibold text-white mb-4 flex items-center">
                                    <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">3</span>
                                    Review & Confirm
                                </h4>
                            </div>
                            
                            <div class="bg-gray-700 rounded-lg p-6">
                                <h6 class="text-white font-semibold mb-4">Backup Summary</h6>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <dl class="space-y-3">
                                            <div class="flex">
                                                <dt class="text-gray-300 font-medium w-24">Name:</dt>
                                                <dd class="text-white ml-4" id="review-name">—</dd>
                                            </div>
                                            <div class="flex">
                                                <dt class="text-gray-300 font-medium w-24">Type:</dt>
                                                <dd class="text-white ml-4" id="review-type">—</dd>
                                            </div>
                                            <div class="flex">
                                                <dt class="text-gray-300 font-medium w-24">Description:</dt>
                                                <dd class="text-white ml-4" id="review-description">—</dd>
                                            </div>
                                        </dl>
                                    </div>
                                    <div>
                                        <dl class="space-y-3">
                                            <div class="flex">
                                                <dt class="text-gray-300 font-medium w-32">Estimated Size:</dt>
                                                <dd class="text-white ml-4" id="review-size">—</dd>
                                            </div>
                                            <div class="flex">
                                                <dt class="text-gray-300 font-medium w-32">Created By:</dt>
                                                <dd class="text-white ml-4">{{ auth()->user()->name }}</dd>
                                            </div>
                                            <div class="flex">
                                                <dt class="text-gray-300 font-medium w-32">Created At:</dt>
                                                <dd class="text-white ml-4">{{ now()->format('Y-m-d H:i:s') }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-yellow-900 border border-yellow-700 rounded-lg p-4 mt-4">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-yellow-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="text-yellow-200 font-semibold">Important:</p>
                                        <ul class="mt-2 text-sm text-yellow-300 space-y-1">
                                            <li>• Backup creation may take several minutes depending on data size</li>
                                            <li>• Do not close this browser window during backup creation</li>
                                            <li>• System performance may be temporarily affected during backup</li>
                                            <li>• You will receive a notification when backup is complete</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-600">
                            <button type="button" class="hidden px-6 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white hover:bg-gray-600 transition-colors duration-200" id="btn-previous">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Previous
                            </button>
                            <div></div>
                            <div class="flex space-x-3">
                                <button type="button" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200" id="btn-next">
                                    Next
                                    <svg class="w-4 h-4 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                <button type="submit" class="hidden px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200" id="btn-create">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10V9a3 3 0 013-3m-3 4V6a3 3 0 113 3v4"></path>
                                    </svg>
                                    Create Backup
                                </button>
                            </div>
                        </div>

                        <!-- Progress Indicator -->
                        <div class="mt-4">
                            <div class="bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300 progress-bar" style="width: 33%"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="px-4 py-3 border-b border-gray-700">
                    <h6 class="text-white font-semibold flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                        </svg>
                        Backup Guide
                    </h6>
                </div>
                <div class="p-4">
                    <div class="backup-help" id="help-step-1">
                        <h6 class="text-white font-semibold mb-2">Step 1: Basic Information</h6>
                        <p class="text-gray-400 text-sm mb-3">
                            Provide a clear name and optional description for your backup. 
                            Use naming conventions that help you identify backups later.
                        </p>
                        <p class="text-gray-400 text-sm">
                            <span class="text-blue-400 font-semibold">Tip:</span> Include date, purpose, or version information in the name.
                        </p>
                    </div>

                    <div class="backup-help hidden" id="help-step-2">
                        <h6 class="text-white font-semibold mb-2">Step 2: Backup Type</h6>
                        <p class="text-gray-400 text-sm mb-2">Choose the appropriate backup type:</p>
                        <ul class="text-gray-400 text-sm space-y-1">
                            <li><span class="text-white font-semibold">Full:</span> Complete system backup (recommended for major changes)</li>
                            <li><span class="text-white font-semibold">Database:</span> Just data and structure (faster, smaller)</li>
                            <li><span class="text-white font-semibold">Files:</span> Application files only (for code changes)</li>
                        </ul>
                    </div>

                    <div class="backup-help hidden" id="help-step-3">
                        <h6 class="text-white font-semibold mb-2">Step 3: Final Review</h6>
                        <p class="text-gray-400 text-sm mb-3">
                            Review all backup settings before creation. Once started, 
                            the backup process cannot be stopped.
                        </p>
                        <p class="text-gray-400 text-sm">
                            <span class="text-blue-400 font-semibold">Note:</span> You can monitor backup progress from the main backup list.
                        </p>
                    </div>
                </div>
            </div>

            <!-- System Resources -->
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="px-4 py-3 border-b border-gray-700">
                    <h6 class="text-white font-semibold flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                        System Resources
                    </h6>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-300">Available Disk Space:</span>
                            <span class="text-white font-semibold" id="available-space">Loading...</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" id="disk-usage" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-300">Database Size:</span>
                        <span class="text-white font-semibold" id="database-size">Loading...</span>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-300">Files Size:</span>
                        <span class="text-white font-semibold" id="files-size">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 3;

// Initialize wizard
document.addEventListener('DOMContentLoaded', function() {
    updateWizardNavigation();
    loadSystemResources();
    
    // Handle backup type selection
    document.querySelectorAll('.backup-type-card').forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            const radio = this.querySelector('input[type="radio"]');
            
            // Clear all selections
            document.querySelectorAll('.backup-type-card').forEach(c => {
                c.classList.remove('border-blue-500', 'bg-gray-600');
                c.classList.add('border-gray-600', 'bg-gray-700');
            });
            document.querySelectorAll('input[name="type"]').forEach(r => r.checked = false);
            
            // Select this card
            this.classList.remove('border-gray-600', 'bg-gray-700');
            this.classList.add('border-blue-500', 'bg-gray-600');
            radio.checked = true;
            
            // Update size estimation
            updateSizeEstimation(type);
        });
    });
    
    // Handle form submission
    document.getElementById('backup-creation-form').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('btn-create');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Creating Backup...';
        
        // Show progress notification
        showToast('Backup creation started. Please wait...', 'info');
    });
});

// Navigation functions
document.getElementById('btn-next').addEventListener('click', function() {
    if (validateCurrentStep()) {
        nextStep();
    }
});

document.getElementById('btn-previous').addEventListener('click', function() {
    previousStep();
});

function nextStep() {
    if (currentStep < totalSteps) {
        // Hide current step
        document.getElementById(`step-${currentStep}`).classList.add('hidden');
        document.getElementById(`help-step-${currentStep}`).classList.add('hidden');
        
        // Show next step
        currentStep++;
        document.getElementById(`step-${currentStep}`).classList.remove('hidden');
        document.getElementById(`help-step-${currentStep}`).classList.remove('hidden');
        
        // Update navigation
        updateWizardNavigation();
        
        // Update review data if on final step
        if (currentStep === 3) {
            updateReviewData();
        }
    }
}

function previousStep() {
    if (currentStep > 1) {
        // Hide current step
        document.getElementById(`step-${currentStep}`).classList.add('hidden');
        document.getElementById(`help-step-${currentStep}`).classList.add('hidden');
        
        // Show previous step
        currentStep--;
        document.getElementById(`step-${currentStep}`).classList.remove('hidden');
        document.getElementById(`help-step-${currentStep}`).classList.remove('hidden');
        
        // Update navigation
        updateWizardNavigation();
    }
}

function updateWizardNavigation() {
    const btnPrevious = document.getElementById('btn-previous');
    const btnNext = document.getElementById('btn-next');
    const btnCreate = document.getElementById('btn-create');
    const progressBar = document.querySelector('.progress-bar');
    
    // Update buttons visibility
    btnPrevious.classList.toggle('hidden', currentStep === 1);
    btnNext.classList.toggle('hidden', currentStep === totalSteps);
    btnCreate.classList.toggle('hidden', currentStep !== totalSteps);
    
    // Update progress bar
    const progress = (currentStep / totalSteps) * 100;
    progressBar.style.width = `${progress}%`;
}

function validateCurrentStep() {
    switch (currentStep) {
        case 1:
            const name = document.querySelector('input[name="name"]').value;
            if (!name.trim()) {
                showToast('Please enter a backup name', 'warning');
                return false;
            }
            return true;
            
        case 2:
            const type = document.querySelector('input[name="type"]:checked');
            if (!type) {
                showToast('Please select a backup type', 'warning');
                return false;
            }
            return true;
            
        default:
            return true;
    }
}

function updateReviewData() {
    const name = document.querySelector('input[name="name"]').value;
    const description = document.querySelector('textarea[name="description"]').value;
    const type = document.querySelector('input[name="type"]:checked')?.value;
    
    document.getElementById('review-name').textContent = name || '—';
    document.getElementById('review-description').textContent = description || 'No description';
    document.getElementById('review-type').textContent = type ? type.charAt(0).toUpperCase() + type.slice(1) + ' Backup' : '—';
    document.getElementById('review-size').textContent = document.getElementById('estimated-size').textContent;
}

function updateSizeEstimation(type) {
    document.getElementById('size-estimation').classList.remove('hidden');
    document.getElementById('estimated-size').textContent = 'Calculating...';
    
    // Simulate size calculation
    setTimeout(() => {
        let estimatedSize;
        switch (type) {
            case 'full':
                estimatedSize = 'Approximately 150-500 MB';
                break;
            case 'database':
                estimatedSize = 'Approximately 50-150 MB';
                break;
            case 'files':
                estimatedSize = 'Approximately 100-350 MB';
                break;
        }
        document.getElementById('estimated-size').textContent = estimatedSize;
    }, 1000);
}

function loadSystemResources() {
    // Simulate loading system resources
    setTimeout(() => {
        document.getElementById('available-space').textContent = '2.5 GB';
        document.getElementById('database-size').textContent = '125 MB';
        document.getElementById('files-size').textContent = '280 MB';
        
        // Update disk usage bar (example: 65% used)
        const diskUsage = document.getElementById('disk-usage');
        diskUsage.style.width = '65%';
        
        if (65 > 80) {
            diskUsage.classList.remove('bg-blue-600');
            diskUsage.classList.add('bg-yellow-500');
        }
        if (65 > 90) {
            diskUsage.classList.remove('bg-yellow-500');
            diskUsage.classList.add('bg-red-500');
        }
    }, 500);
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
.wizard-step {
    min-height: 300px;
}

.backup-type-card.selected {
    border-color: #3b82f6 !important;
    background-color: #374151 !important;
}

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