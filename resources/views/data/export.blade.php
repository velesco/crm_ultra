@extends('layouts.app')

@section('title', 'Export Data')

@section('content')
<div class="space-y-6" x-data="exportManager()">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Export Data</h1>
            <p class="text-gray-600 dark:text-gray-400">Export your contacts and campaign data</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('data.import') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import Data
            </a>
            <a href="{{ route('data.history') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                View History
            </a>
        </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Export Options</h2>
        
        <form @submit.prevent="startExport()" class="space-y-6">
            @csrf
            
            <!-- Export Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    What would you like to export?
                </label>
                <div class="space-y-2">
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="exportType" value="contacts" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Contacts</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="exportType" value="campaigns" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Email Campaigns</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="exportType" value="sms" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">SMS Messages</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="exportType" value="segments" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Segments</span>
                    </label>
                </div>
            </div>

            <!-- Contacts Export Options -->
            <div x-show="exportType === 'contacts'" x-transition class="space-y-4">
                <h3 class="text-md font-medium text-gray-900 dark:text-white">Contact Export Settings</h3>
                
                <!-- Filter by Segment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Filter by Segment (Optional)
                    </label>
                    <select x-model="contactOptions.segmentId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Contacts</option>
                        @foreach($segments as $segment)
                            <option value="{{ $segment->id }}">{{ $segment->name }} ({{ $segment->contacts_count ?? 0 }} contacts)</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter by Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Filter by Status (Optional)
                    </label>
                    <select x-model="contactOptions.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                        <option value="unsubscribed">Unsubscribed</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Created After (Optional)
                        </label>
                        <input type="date" x-model="contactOptions.createdAfter" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Created Before (Optional)
                        </label>
                        <input type="date" x-model="contactOptions.createdBefore" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <!-- Fields to Include -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Fields to Include
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <template x-for="field in contactFields" :key="field.value">
                            <label class="inline-flex items-center">
                                <input type="checkbox" :value="field.value" x-model="contactOptions.fields" class="text-blue-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300" x-text="field.label"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <!-- File Format -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Export Format
                </label>
                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="fileFormat" value="csv" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">CSV</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="fileFormat" value="xlsx" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Excel (XLSX)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" x-model="fileFormat" value="json" class="text-blue-600">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">JSON</span>
                    </label>
                </div>
            </div>

            <!-- Export Preview -->
            <div x-show="exportPreview" x-transition class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Export Preview</h4>
                <div class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <p><strong>Type:</strong> <span x-text="exportType"></span></p>
                    <p><strong>Format:</strong> <span x-text="fileFormat.toUpperCase()"></span></p>
                    <p x-show="estimatedCount > 0"><strong>Estimated Records:</strong> <span x-text="estimatedCount.toLocaleString()"></span></p>
                    <p x-show="estimatedSize"><strong>Estimated Size:</strong> <span x-text="estimatedSize"></span></p>
                </div>
                <button type="button" @click="getPreview()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    Refresh Preview
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <button type="button" @click="getPreview()" class="btn-secondary" :disabled="!exportType">
                    Preview Export
                </button>
                <button type="submit" 
                        class="btn-primary"
                        :disabled="!exportType || exporting"
                        :class="{ 'opacity-50 cursor-not-allowed': !exportType || exporting }">
                    <span x-show="!exporting" class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Start Export
                    </span>
                    <span x-show="exporting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Exporting...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Exports -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Exports</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Export
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Type & Format
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Records
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentExports as $export)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $export->filename }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format($export->file_size / 1024, 1) }} KB
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $export->export_type === 'contacts' 
                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' 
                                        : ($export->export_type === 'campaigns' 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                        )
                                    }}">
                                    {{ ucfirst($export->export_type) }}
                                </span>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ strtoupper($export->file_format) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($export->record_count) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $export->created_at->format('M j, Y') }}
                            <div class="text-xs">{{ $export->created_at->format('g:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $export->status === 'completed' 
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                    : ($export->status === 'processing' 
                                        ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                    )
                                }}">
                                <div class="w-1.5 h-1.5 bg-current rounded-full mr-1.5"></div>
                                {{ ucfirst($export->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @if($export->status === 'completed' && $export->file_path)
                                <a href="{{ route('data.download', $export) }}" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                   title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                                @endif
                                
                                <form method="POST" action="{{ route('data.exports.destroy', $export) }}" 
                                      class="inline"
                                      onsubmit="return confirm('Delete this export?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm">No exports yet</p>
                            <p class="text-xs text-gray-400">Create your first export to get started</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportManager() {
    return {
        exportType: 'contacts',
        fileFormat: 'csv',
        exporting: false,
        exportPreview: false,
        estimatedCount: 0,
        estimatedSize: '',
        
        contactOptions: {
            segmentId: '',
            status: '',
            createdAfter: '',
            createdBefore: '',
            fields: ['first_name', 'last_name', 'email', 'phone']
        },
        
        contactFields: [
            { value: 'first_name', label: 'First Name' },
            { value: 'last_name', label: 'Last Name' },
            { value: 'email', label: 'Email' },
            { value: 'phone', label: 'Phone' },
            { value: 'company', label: 'Company' },
            { value: 'job_title', label: 'Job Title' },
            { value: 'address', label: 'Address' },
            { value: 'city', label: 'City' },
            { value: 'state', label: 'State' },
            { value: 'postal_code', label: 'Postal Code' },
            { value: 'country', label: 'Country' },
            { value: 'website', label: 'Website' },
            { value: 'source', label: 'Source' },
            { value: 'status', label: 'Status' },
            { value: 'created_at', label: 'Created Date' },
            { value: 'updated_at', label: 'Updated Date' },
            { value: 'notes', label: 'Notes' }
        ],
        
        async getPreview() {
            if (!this.exportType) return;
            
            try {
                const response = await fetch('/data/export/preview', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        type: this.exportType,
                        format: this.fileFormat,
                        options: this.getOptionsForType()
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.estimatedCount = data.estimatedCount;
                    this.estimatedSize = data.estimatedSize;
                    this.exportPreview = true;
                } else {
                    alert('Error getting preview: ' + data.message);
                }
            } catch (error) {
                console.error('Preview error:', error);
                alert('Error getting preview');
            }
        },
        
        async startExport() {
            if (!this.exportType) return;
            
            this.exporting = true;
            
            try {
                const response = await fetch('/data/export', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        type: this.exportType,
                        format: this.fileFormat,
                        options: this.getOptionsForType()
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.download_url) {
                        // Direct download
                        window.location.href = data.download_url;
                    } else {
                        // Export queued
                        alert('Export started! You will be notified when it\'s ready for download.');
                        setTimeout(() => window.location.reload(), 2000);
                    }
                } else {
                    alert('Error starting export: ' + data.message);
                }
            } catch (error) {
                console.error('Export error:', error);
                alert('Error starting export');
            } finally {
                this.exporting = false;
            }
        },
        
        getOptionsForType() {
            switch (this.exportType) {
                case 'contacts':
                    return this.contactOptions;
                default:
                    return {};
            }
        }
    }
}
</script>
@endpush
@endsection