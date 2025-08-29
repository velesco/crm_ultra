@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="flex justify-between items-start mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.system-logs.index') }}" 
               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Logs
            </a>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Log Details</h1>
                <p class="text-gray-600 mt-1">System log entry #{{ $systemLog->id }}</p>
            </div>
        </div>
        <div class="relative">
            <button type="button" 
                    onclick="toggleDropdown('exportDropdown')"
                    class="inline-flex items-center px-4 py-2 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                </svg>
                Export
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="exportDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                    <button onclick="exportLogAsJson()" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                        Export as JSON
                    </button>
                    <button onclick="copyToClipboard()" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copy to Clipboard
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Log Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <h3 class="text-lg font-medium text-gray-900">Log Information</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $systemLog->level_badge_class }}">
                            {{ ucfirst($systemLog->level) }}
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Category:</label>
                            <div class="flex items-center">
                                <i class="{{ $systemLog->category_icon }} text-gray-400 mr-2"></i>
                                <span class="text-gray-900 capitalize">{{ $systemLog->category }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Action:</label>
                            <span class="inline-block bg-gray-100 text-gray-800 text-sm font-mono px-2 py-1 rounded">{{ $systemLog->action }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Occurred At:</label>
                            <div>
                                <div class="text-gray-900">{{ $systemLog->occurred_at->format('M d, Y \a\t H:i:s T') }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ $systemLog->occurred_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">User:</label>
                            <div>
                                @if($systemLog->user)
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-white text-sm font-medium">{{ substr($systemLog->user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 font-medium">{{ $systemLog->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $systemLog->user->email }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500">System</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Message:</label>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-900">{{ $systemLog->message }}</p>
                        </div>
                    </div>

                    @if($systemLog->description)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-500 mb-2">Description:</label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-900">{{ $systemLog->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Technical Details --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Technical Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">IP Address:</label>
                            <span class="inline-block bg-gray-100 text-gray-800 text-sm font-mono px-2 py-1 rounded">{{ $systemLog->ip_address ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Session ID:</label>
                            <span class="inline-block bg-gray-100 text-gray-800 text-xs font-mono px-2 py-1 rounded">{{ $systemLog->session_id ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Request ID:</label>
                            <span class="inline-block bg-gray-100 text-gray-800 text-xs font-mono px-2 py-1 rounded">{{ $systemLog->request_id ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created At:</label>
                            <div class="text-gray-900">{{ $systemLog->created_at->format('M d, Y H:i:s T') }}</div>
                        </div>
                    </div>

                    @if($systemLog->user_agent)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-500 mb-2">User Agent:</label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-xs font-mono text-gray-800 break-all">{{ $systemLog->user_agent }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Context and Metadata --}}
            @if($systemLog->context || $systemLog->metadata)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Context & Metadata</h3>
                            <div class="flex space-x-1" role="group">
                                <input type="radio" id="tableView" name="dataView" class="sr-only peer" checked>
                                <label for="tableView" class="inline-flex items-center px-3 py-1 text-xs font-medium border border-gray-300 rounded-l-md bg-white text-gray-700 hover:bg-gray-50 cursor-pointer peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">
                                    Table
                                </label>
                                <input type="radio" id="jsonView" name="dataView" class="sr-only peer">
                                <label for="jsonView" class="inline-flex items-center px-3 py-1 text-xs font-medium border border-gray-300 rounded-r-md bg-white text-gray-700 hover:bg-gray-50 cursor-pointer peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600">
                                    JSON
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($systemLog->context)
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-500 mb-2">Request Context:</label>
                                <div id="context-table">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($systemLog->context as $key => $value)
                                                    <tr>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50 w-1/3">{{ $key }}</td>
                                                        <td class="px-4 py-3 text-sm text-gray-900">{{ is_string($value) ? $value : json_encode($value) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="context-json" class="hidden">
                                    <pre class="bg-gray-50 rounded-lg p-4 text-sm overflow-x-auto"><code>{{ json_encode($systemLog->context, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        @endif

                        @if($systemLog->metadata)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Additional Metadata:</label>
                                <div id="metadata-table">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($systemLog->metadata as $key => $value)
                                                    <tr>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50 w-1/3">{{ $key }}</td>
                                                        <td class="px-4 py-3 text-sm text-gray-900">{{ is_string($value) ? $value : json_encode($value) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="metadata-json" class="hidden">
                                    <pre class="bg-gray-50 rounded-lg p-4 text-sm overflow-x-auto"><code>{{ json_encode($systemLog->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Related Logs --}}
            @if($relatedLogs->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Related Activity
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 ml-2">{{ $relatedLogs->count() }}</span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            @if($systemLog->request_id)
                                Same request or within 5 minutes
                            @else
                                Same user within 5 minutes
                            @endif
                        </p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($relatedLogs as $relatedLog)
                                <div class="relative">
                                    <div class="flex">
                                        <div class="flex-shrink-0 mr-3">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium {{ $relatedLog->level_badge_class }}">
                                                {{ substr($relatedLog->level, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">{{ $relatedLog->action }}</div>
                                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                                        <i class="{{ $relatedLog->category_icon }} mr-1"></i>
                                                        {{ ucfirst($relatedLog->category) }}
                                                    </div>
                                                    <div class="text-sm text-gray-600 mt-1">
                                                        {{ Str::limit($relatedLog->message, 60) }}
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-2 ml-4">
                                                    <span class="text-xs text-gray-500">
                                                        {{ $relatedLog->occurred_at->format('H:i:s') }}
                                                    </span>
                                                    <a href="{{ route('admin.system-logs.show', $relatedLog) }}" 
                                                       class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="absolute left-3 top-8 w-px h-8 bg-gray-200"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Quick Stats --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Stats</h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($systemLog->user)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">User's Total Logs:</span>
                                <span class="text-lg font-semibold text-gray-900">{{ App\Models\SystemLog::where('user_id', $systemLog->user_id)->count() }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">{{ ucfirst($systemLog->category) }} Logs Today:</span>
                            <span class="text-lg font-semibold text-gray-900">{{ App\Models\SystemLog::category($systemLog->category)->whereDate('created_at', today())->count() }}</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">{{ ucfirst($systemLog->level) }} Logs Today:</span>
                            <span class="text-lg font-semibold text-gray-900">{{ App\Models\SystemLog::level($systemLog->level)->whereDate('created_at', today())->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <button type="button" 
                                onclick="filterSimilar()"
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Find Similar Logs
                        </button>
                        @if($systemLog->user)
                            <button type="button" 
                                    onclick="viewUserActivity()"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                View User Activity
                            </button>
                        @endif
                        @if($systemLog->request_id)
                            <button type="button" 
                                    onclick="traceRequest()"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                </svg>
                                Trace Request
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
input[type="radio"]:checked + label {
    background-color: #4f46e5;
    color: white;
    border-color: #4f46e5;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle between table and JSON view
    $('input[name="dataView"]').on('change', function() {
        const isJsonView = $('#jsonView').is(':checked');
        
        if (isJsonView) {
            $('#context-table, #metadata-table').hide();
            $('#context-json, #metadata-json').show();
        } else {
            $('#context-table, #metadata-table').show();
            $('#context-json, #metadata-json').hide();
        }
    });

    // Close dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.relative').length) {
            $('.hidden').addClass('hidden');
        }
    });
});

function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.classList.toggle('hidden');
}

function exportLogAsJson() {
    const logData = {
        id: {{ $systemLog->id }},
        level: '{{ $systemLog->level }}',
        category: '{{ $systemLog->category }}',
        action: '{{ $systemLog->action }}',
        message: `{{ addslashes($systemLog->message) }}`,
        description: `{{ addslashes($systemLog->description ?? '') }}`,
        user: @if($systemLog->user) {
            id: {{ $systemLog->user->id }},
            name: '{{ $systemLog->user->name }}',
            email: '{{ $systemLog->user->email }}'
        } @else null @endif,
        occurred_at: '{{ $systemLog->occurred_at->toISOString() }}',
        ip_address: '{{ $systemLog->ip_address }}',
        user_agent: `{{ addslashes($systemLog->user_agent ?? '') }}`,
        context: @json($systemLog->context),
        metadata: @json($systemLog->metadata)
    };
    
    const dataStr = JSON.stringify(logData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = `system-log-${logData.id}.json`;
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    // Hide dropdown
    document.getElementById('exportDropdown').classList.add('hidden');
}

function copyToClipboard() {
    const logText = `
System Log #{{ $systemLog->id }}
Level: {{ $systemLog->level }}
Category: {{ $systemLog->category }}
Action: {{ $systemLog->action }}
Message: {{ $systemLog->message }}
User: {{ $systemLog->user ? $systemLog->user->name : 'System' }}
Occurred At: {{ $systemLog->occurred_at->format('Y-m-d H:i:s T') }}
IP Address: {{ $systemLog->ip_address ?? 'N/A' }}
    `.trim();
    
    navigator.clipboard.writeText(logText).then(function() {
        showAlert('Log details copied to clipboard', 'success');
    }, function() {
        showAlert('Failed to copy to clipboard', 'danger');
    });
    
    // Hide dropdown
    document.getElementById('exportDropdown').classList.add('hidden');
}

function filterSimilar() {
    const params = new URLSearchParams({
        category: '{{ $systemLog->category }}',
        action: '{{ $systemLog->action }}',
        level: '{{ $systemLog->level }}'
    });
    
    window.location.href = '{{ route("admin.system-logs.index") }}?' + params.toString();
}

function viewUserActivity() {
    @if($systemLog->user)
        const params = new URLSearchParams({
            user_id: '{{ $systemLog->user_id }}'
        });
        
        window.location.href = '{{ route("admin.system-logs.index") }}?' + params.toString();
    @endif
}

function traceRequest() {
    @if($systemLog->request_id)
        const params = new URLSearchParams({
            search: '{{ $systemLog->request_id }}'
        });
        
        window.location.href = '{{ route("admin.system-logs.index") }}?' + params.toString();
    @endif
}

function showAlert(message, type) {
    const colors = {
        'success': 'bg-green-100 border-green-400 text-green-700',
        'danger': 'bg-red-100 border-red-400 text-red-700',
        'info': 'bg-blue-100 border-blue-400 text-blue-700'
    };
    
    const alertHtml = `
        <div class="fixed top-4 right-4 z-50 border ${colors[type]} px-4 py-3 rounded animate-fade-in-down" role="alert">
            <span>${message}</span>
            <button type="button" class="float-right ml-4 text-lg font-bold leading-none" onclick="$(this).parent().remove();">&times;</button>
        </div>
    `;
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.fixed.top-4.right-4').remove();
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
