@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
<div class="space-y-6" x-data="importWizard()">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import Data</h1>
            <p class="text-gray-600 dark:text-gray-400">Import contacts from CSV or Excel files</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('data.template') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Template
            </a>
            <a href="{{ route('data.history') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Import History
            </a>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <div class="flex items-center justify-between mb-8">
            <!-- Step 1: Upload -->
            <div class="flex items-center">
                <div class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium"
                     :class="{ 'bg-green-600': currentStep > 1, 'bg-blue-600': currentStep === 1, 'bg-gray-300 dark:bg-gray-600': currentStep < 1 }">
                    <span x-show="currentStep === 1">1</span>
                    <svg x-show="currentStep > 1" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Upload File</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Select your CSV/Excel file</p>
                </div>
            </div>
            
            <!-- Separator -->
            <div class="flex-1 mx-4">
                <div class="h-px bg-gray-200 dark:bg-gray-600"></div>
            </div>
            
            <!-- Step 2: Map Fields -->
            <div class="flex items-center">
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium"
                     :class="{ 'bg-green-600 text-white': currentStep > 2, 'bg-blue-600 text-white': currentStep === 2, 'bg-gray-300 dark:bg-gray-600 text-gray-500': currentStep < 2 }">
                    <span x-show="currentStep <= 2">2</span>
                    <svg x-show="currentStep > 2" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white"
                       :class="{ 'text-gray-400': currentStep < 2 }">Map Fields</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Match your columns</p>
                </div>
            </div>
            
            <!-- Separator -->
            <div class="flex-1 mx-4">
                <div class="h-px bg-gray-200 dark:bg-gray-600"></div>
            </div>
            
            <!-- Step 3: Import -->
            <div class="flex items-center">
                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium"
                     :class="{ 'bg-green-600 text-white': currentStep > 3, 'bg-blue-600 text-white': currentStep === 3, 'bg-gray-300 dark:bg-gray-600 text-gray-500': currentStep < 3 }">
                    <span x-show="currentStep <= 3">3</span>
                    <svg x-show="currentStep > 3" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white"
                       :class="{ 'text-gray-400': currentStep < 3 }">Import</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Process your data</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 1: File Upload -->
    <div x-show="currentStep === 1" x-transition class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Step 1: Upload Your File</h2>
        
        <form @submit.prevent="uploadFile()" enctype="multipart/form-data">
            @csrf
            <!-- File Upload Area -->
            <div class="mb-6">
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
                     @dragover.prevent 
                     @dragenter.prevent 
                     @drop.prevent="handleFileDrop($event)">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                            <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>Upload a file</span>
                                <input id="file-upload" name="file" type="file" class="sr-only" accept=".csv,.xlsx,.xls" @change="handleFileSelect($event)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">CSV, XLSX files up to 10MB</p>
                    </div>
                </div>
                
                <!-- Selected File Info -->
                <div x-show="selectedFile" x-transition class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-200" x-text="selectedFile?.name"></p>
                            <p class="text-sm text-blue-600 dark:text-blue-300" x-text="formatFileSize(selectedFile?.size)"></p>
                        </div>
                        <button type="button" @click="selectedFile = null" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Import Options -->
            <div class="space-y-4 mb-6">
                <h3 class="text-md font-medium text-gray-900 dark:text-white">Import Options</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Duplicate Handling -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Handle Duplicates
                        </label>
                        <select x-model="importOptions.duplicateHandling" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="skip">Skip Duplicates</option>
                            <option value="update">Update Existing</option>
                            <option value="create">Create Anyway</option>
                        </select>
                    </div>

                    <!-- Auto-assign to Segment -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Auto-assign to Segment (Optional)
                        </label>
                        <select x-model="importOptions.segmentId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="">None</option>
                            @foreach($segments as $segment)
                                <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="checkbox" x-model="importOptions.hasHeaders" id="has_headers" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <label for="has_headers" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            First row contains headers
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" x-model="importOptions.sendWelcome" id="send_welcome" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <label for="send_welcome" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Send welcome email to new contacts
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <button type="button" 
                        @click="selectedFile = null" 
                        class="btn-secondary">
                    Clear
                </button>
                <button type="submit" 
                        :disabled="!selectedFile" 
                        :class="{ 'opacity-50 cursor-not-allowed': !selectedFile }"
                        class="btn-primary">
                    <span x-show="!uploading">Continue to Mapping</span>
                    <span x-show="uploading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Step 2: Field Mapping -->
    <div x-show="currentStep === 2" x-transition class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Step 2: Map Your Fields</h2>
        
        <div class="space-y-6">
            <!-- Preview Data -->
            <div x-show="previewData.length > 0">
                <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Data Preview</h3>
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <template x-for="(column, index) in Object.keys(previewData[0])" :key="index">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" x-text="column"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(row, rowIndex) in previewData.slice(0, 5)" :key="rowIndex">
                                <tr>
                                    <template x-for="(value, column) in row" :key="column">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="value"></td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Showing first 5 rows</p>
            </div>

            <!-- Field Mapping -->
            <div>
                <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Field Mapping</h3>
                <div class="space-y-3">
                    <template x-for="(column, index) in fileColumns" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">File Column</label>
                                <p class="text-sm text-gray-900 dark:text-white" x-text="column"></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Maps to</label>
                                <select :name="`mapping[${column}]`" x-model="fieldMapping[column]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">Don't Import</option>
                                    <option value="first_name">First Name</option>
                                    <option value="last_name">Last Name</option>
                                    <option value="email">Email *</option>
                                    <option value="phone">Phone</option>
                                    <option value="company">Company</option>
                                    <option value="job_title">Job Title</option>
                                    <option value="address">Address</option>
                                    <option value="city">City</option>
                                    <option value="state">State</option>
                                    <option value="postal_code">Postal Code</option>
                                    <option value="country">Country</option>
                                    <option value="website">Website</option>
                                    <option value="source">Source</option>
                                    <option value="status">Status</option>
                                    <option value="notes">Notes</option>
                                </select>
                            </div>
                            <div x-show="previewData.length > 0">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sample Value</label>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="previewData[0][column]"></p>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Validation Messages -->
                <div x-show="!isEmailMapped" x-transition class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-red-700 dark:text-red-400">
                            <strong>Email field is required:</strong> Please map at least one column to the email field.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between">
                <button type="button" @click="currentStep = 1" class="btn-secondary">
                    Back to Upload
                </button>
                <button type="button" 
                        @click="proceedToImport()" 
                        :disabled="!isEmailMapped"
                        :class="{ 'opacity-50 cursor-not-allowed': !isEmailMapped }"
                        class="btn-primary">
                    Continue to Import
                </button>
            </div>
        </div>
    </div>

    <!-- Step 3: Import Process -->
    <div x-show="currentStep === 3" x-transition class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Step 3: Import Process</h2>
        
        <div class="space-y-6">
            <!-- Import Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-1">Total Rows</h4>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100" x-text="importStats.total"></p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-green-800 dark:text-green-200 mb-1">Successfully Imported</h4>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100" x-text="importStats.success"></p>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">Errors/Skipped</h4>
                    <p class="text-2xl font-bold text-red-900 dark:text-red-100" x-text="importStats.errors"></p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div>
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <span>Processing...</span>
                    <span x-text="`${Math.round(importProgress)}%`"></span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="`width: ${importProgress}%`"></div>
                </div>
            </div>

            <!-- Import Status -->
            <div x-show="importing" class="flex items-center justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-lg text-gray-600 dark:text-gray-400">Importing your contacts...</p>
            </div>

            <!-- Import Complete -->
            <div x-show="importComplete" x-transition class="text-center py-8">
                <svg class="mx-auto h-16 w-16 text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Import Complete!</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Your contacts have been successfully imported.</p>
                
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('contacts.index') }}" class="btn-primary">
                        View Contacts
                    </a>
                    <a href="{{ route('data.history') }}" class="btn-secondary">
                        View Import History
                    </a>
                    <button type="button" @click="resetWizard()" class="btn-secondary">
                        Import More Files
                    </button>
                </div>
            </div>

            <!-- Error Details -->
            <div x-show="importErrors.length > 0" x-transition class="mt-6">
                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Import Errors</h4>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 max-h-64 overflow-y-auto">
                    <template x-for="error in importErrors" :key="error.row">
                        <div class="text-sm text-red-700 dark:text-red-400 mb-2">
                            <strong>Row <span x-text="error.row"></span>:</strong>
                            <span x-text="error.message"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Action Buttons -->
            <div x-show="!importing && !importComplete" class="flex justify-between">
                <button type="button" @click="currentStep = 2" class="btn-secondary">
                    Back to Mapping
                </button>
                <button type="button" @click="startImport()" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Start Import
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function importWizard() {
    return {
        currentStep: 1,
        selectedFile: null,
        uploading: false,
        importing: false,
        importComplete: false,
        importProgress: 0,
        previewData: [],
        fileColumns: [],
        fieldMapping: {},
        importStats: {
            total: 0,
            success: 0,
            errors: 0
        },
        importErrors: [],
        importOptions: {
            duplicateHandling: 'skip',
            segmentId: '',
            hasHeaders: true,
            sendWelcome: false
        },
        
        handleFileSelect(event) {
            this.selectedFile = event.target.files[0];
        },
        
        handleFileDrop(event) {
            event.preventDefault();
            this.selectedFile = event.dataTransfer.files[0];
        },
        
        formatFileSize(bytes) {
            if (!bytes) return '0 bytes';
            const k = 1024;
            const sizes = ['bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        async uploadFile() {
            if (!this.selectedFile) return;
            
            this.uploading = true;
            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('has_headers', this.importOptions.hasHeaders);
            
            try {
                const response = await fetch('/data/preview', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.previewData = data.preview;
                    this.fileColumns = data.columns;
                    this.importStats.total = data.totalRows;
                    
                    // Auto-map common fields
                    this.autoMapFields();
                    
                    this.currentStep = 2;
                } else {
                    alert('Error uploading file: ' + data.message);
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('Error uploading file');
            } finally {
                this.uploading = false;
            }
        },
        
        autoMapFields() {
            const commonMappings = {
                'first_name': ['first_name', 'firstname', 'first name', 'fname'],
                'last_name': ['last_name', 'lastname', 'last name', 'lname', 'surname'],
                'email': ['email', 'email_address', 'e-mail', 'mail'],
                'phone': ['phone', 'phone_number', 'mobile', 'telephone', 'tel'],
                'company': ['company', 'organization', 'org', 'business'],
                'job_title': ['job_title', 'title', 'position', 'role'],
                'address': ['address', 'street', 'street_address'],
                'city': ['city', 'town'],
                'state': ['state', 'province', 'region'],
                'postal_code': ['postal_code', 'zip', 'zipcode', 'postcode'],
                'country': ['country'],
                'website': ['website', 'url', 'site'],
                'source': ['source', 'lead_source'],
                'status': ['status']
            };
            
            this.fileColumns.forEach(column => {
                const columnLower = column.toLowerCase().trim();
                
                for (const [field, patterns] of Object.entries(commonMappings)) {
                    if (patterns.some(pattern => columnLower.includes(pattern) || pattern.includes(columnLower))) {
                        this.fieldMapping[column] = field;
                        break;
                    }
                }
            });
        },
        
        get isEmailMapped() {
            return Object.values(this.fieldMapping).includes('email');
        },
        
        proceedToImport() {
            if (!this.isEmailMapped) return;
            this.currentStep = 3;
        },
        
        async startImport() {
            this.importing = true;
            this.importProgress = 0;
            
            const formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('mapping', JSON.stringify(this.fieldMapping));
            formData.append('options', JSON.stringify(this.importOptions));
            
            try {
                const response = await fetch('/data/import', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Simulate progress for demo
                    const interval = setInterval(() => {
                        this.importProgress += 10;
                        if (this.importProgress >= 100) {
                            clearInterval(interval);
                            this.importing = false;
                            this.importComplete = true;
                            this.importStats = data.stats;
                            this.importErrors = data.errors || [];
                        }
                    }, 500);
                } else {
                    alert('Error importing data: ' + data.message);
                    this.importing = false;
                }
            } catch (error) {
                console.error('Import error:', error);
                alert('Error importing data');
                this.importing = false;
            }
        },
        
        resetWizard() {
            this.currentStep = 1;
            this.selectedFile = null;
            this.uploading = false;
            this.importing = false;
            this.importComplete = false;
            this.importProgress = 0;
            this.previewData = [];
            this.fileColumns = [];
            this.fieldMapping = {};
            this.importStats = { total: 0, success: 0, errors: 0 };
            this.importErrors = [];
        }
    }
}
</script>
@endpush
@endsection