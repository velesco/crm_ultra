@extends('layouts.app')

@section('title', 'Import Contacts')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import Contacts</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Import contacts from CSV or Excel files
            </p>
        </div>
        <a href="{{ route('contacts.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Contacts
        </a>
    </div>

    <!-- Import Steps -->
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Import Process</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('data.template', 'contacts') }}" 
                       class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download Template
                    </a>
                </div>
            </div>
        </div>
        <div class="px-6 py-4">
            <!-- Steps Navigation -->
            <nav aria-label="Progress" class="mb-8">
                <ol class="flex items-center">
                    <li class="relative pr-8 sm:pr-20">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-gray-200 dark:bg-gray-600"></div>
                        </div>
                        <a href="#" class="relative w-8 h-8 flex items-center justify-center bg-indigo-600 rounded-full hover:bg-indigo-900 transition duration-150" id="step1-indicator">
                            <span class="text-white text-sm font-medium" id="step1-number">1</span>
                        </a>
                        <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Upload File</p>
                    </li>

                    <li class="relative pr-8 sm:pr-20">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full bg-gray-200 dark:bg-gray-600"></div>
                        </div>
                        <a href="#" class="relative w-8 h-8 flex items-center justify-center bg-gray-300 dark:bg-gray-600 rounded-full transition duration-150" id="step2-indicator">
                            <span class="text-gray-700 dark:text-gray-300 text-sm font-medium" id="step2-number">2</span>
                        </a>
                        <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Map Fields</p>
                    </li>

                    <li class="relative">
                        <a href="#" class="relative w-8 h-8 flex items-center justify-center bg-gray-300 dark:bg-gray-600 rounded-full transition duration-150" id="step3-indicator">
                            <span class="text-gray-700 dark:text-gray-300 text-sm font-medium" id="step3-number">3</span>
                        </a>
                        <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Review & Import</p>
                    </li>
                </ol>
            </nav>

            <!-- Step 1: Upload File -->
            <div id="step1" class="step-content">
                <form id="upload-form" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-6">
                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select File to Import
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                        <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a file</span>
                                            <input id="file-upload" name="file" type="file" class="sr-only" accept=".csv,.xlsx,.xls" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        CSV, XLSX, XLS up to 10MB
                                    </p>
                                </div>
                            </div>
                            <div id="file-info" class="mt-2 hidden">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Selected: <span id="file-name" class="font-medium"></span>
                                    (<span id="file-size"></span>)
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                                Upload & Analyze
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Import Progress -->
            <div id="import-progress" class="step-content hidden">
                <div class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Processing Import...</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please wait while we import your contacts.</p>
                </div>
            </div>

            <!-- Import Results -->
            <div id="import-results" class="step-content hidden">
                <div id="results-content">
                    <!-- Results will be loaded dynamically -->
                </div>
                
                <div class="mt-6 flex justify-center">
                    <a href="{{ route('contacts.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        View All Contacts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// File upload handling
document.getElementById('file-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-size').textContent = formatFileSize(file.size);
        document.getElementById('file-info').classList.remove('hidden');
    }
});

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Form submission
document.getElementById('upload-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('file-upload');
    if (!fileInput.files[0]) {
        alert('Please select a file to upload.');
        return;
    }
    
    // Show progress
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('import-progress').classList.remove('hidden');
    
    // Simulate import process (replace with actual import)
    setTimeout(() => {
        showImportResults({
            success: true,
            imported: 150,
            updated: 25,
            skipped: 5
        });
    }, 3000);
});

function showImportResults(results) {
    document.getElementById('import-progress').classList.add('hidden');
    
    let resultsHtml = '';
    
    if (results.success) {
        resultsHtml = `
            <div class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Import Successful!</h3>
                <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">${results.imported || 0}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Contacts Imported</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">${results.updated || 0}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Contacts Updated</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-600">${results.skipped || 0}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Duplicates Skipped</div>
                    </div>
                </div>
            </div>
        `;
    } else {
        resultsHtml = `
            <div class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Import Failed</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">${results.message}</p>
            </div>
        `;
    }
    
    document.getElementById('results-content').innerHTML = resultsHtml;
    document.getElementById('import-results').classList.remove('hidden');
}
</script>
@endpush
@endsection