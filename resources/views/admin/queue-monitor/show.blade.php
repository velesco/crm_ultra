@extends('layouts.app')

@section('title', 'Job Details')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Job Header Card -->
        <div class="mb-8">
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg text-white overflow-hidden">
                <div class="p-8 text-center">
                    <i class="fas fa-cog text-5xl mb-4 opacity-75"></i>
                    <h2 class="text-3xl font-bold mb-2">{{ class_basename($jobDetails['name'] ?? 'Unknown Job') }}</h2>
                    <p class="text-lg mb-4 opacity-75">Job ID: {{ $jobDetails['id'] }}</p>
                    @php
                        $statusClasses = match($jobDetails['status'] ?? 'pending') {
                            'completed' => 'bg-green-500 text-white',
                            'failed' => 'bg-red-500 text-white',
                            default => 'bg-yellow-500 text-black'
                        };
                    @endphp
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-base font-medium {{ $statusClasses }}">
                        {{ ucfirst($jobDetails['status'] ?? 'pending') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Job Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Basic Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Basic Information
                    </h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Job ID</span>
                        <span class="text-sm text-gray-900">{{ $jobDetails['id'] }}</span>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Job Name</span>
                        <span class="text-sm font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded">
                            {{ $jobDetails['name'] ?? 'Unknown' }}
                        </span>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Queue</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $jobDetails['queue'] ?? 'default' }}
                        </span>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Status</span>
                        @php
                            $statusBadgeClasses = match($jobDetails['status'] ?? 'pending') {
                                'completed' => 'bg-green-100 text-green-800',
                                'failed' => 'bg-red-100 text-red-800',
                                default => 'bg-yellow-100 text-yellow-800'
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClasses }}">
                            {{ ucfirst($jobDetails['status'] ?? 'pending') }}
                        </span>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Attempts</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $jobDetails['attempts'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Timing Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                        Timing Information
                    </h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-medium text-gray-500">Started At</span>
                            <div class="text-right">
                                @if($jobDetails['started_at'])
                                    <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($jobDetails['started_at'])->format('Y-m-d H:i:s') }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($jobDetails['started_at'])->diffForHumans() }}</div>
                                @else
                                    <span class="text-sm text-gray-500">Not started</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-medium text-gray-500">Finished At</span>
                            <div class="text-right">
                                @if($jobDetails['finished_at'])
                                    <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($jobDetails['finished_at'])->format('Y-m-d H:i:s') }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($jobDetails['finished_at'])->diffForHumans() }}</div>
                                @else
                                    <span class="text-sm text-gray-500">Not finished</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-start">
                            <span class="text-sm font-medium text-gray-500">Failed At</span>
                            <div class="text-right">
                                @if($jobDetails['failed_at'])
                                    <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($jobDetails['failed_at'])->format('Y-m-d H:i:s') }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($jobDetails['failed_at'])->diffForHumans() }}</div>
                                @else
                                    <span class="text-sm text-gray-500">Not failed</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-500">Runtime</span>
                        @if($jobDetails['runtime'])
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $jobDetails['runtime'] }}ms
                            </span>
                        @else
                            <span class="text-sm text-gray-500">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-tools text-blue-500 mr-2"></i>
                        Actions
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-3">
                        @if($jobDetails['status'] === 'failed')
                            <button type="button" onclick="retryJob('{{ $jobDetails['id'] }}')" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                                <i class="fas fa-redo mr-2"></i>Retry Job
                            </button>
                            <button type="button" onclick="deleteJob('{{ $jobDetails['id'] }}')" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete Job
                            </button>
                        @endif
                        <a href="{{ route('admin.queue-monitor.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Monitor
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payload Information -->
        @if($jobDetails['payload'])
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-code text-blue-500 mr-2"></i>
                            Job Payload
                        </h3>
                        <button type="button" onclick="copyToClipboard('payloadContent')" 
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="payloadContent" class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <pre class="text-sm font-mono text-gray-800">{{ json_encode($jobDetails['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Exception Information -->
        @if($jobDetails['exception'])
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-red-900 flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            Exception Details
                        </h3>
                        <button type="button" onclick="copyToClipboard('exceptionContent')" 
                                class="inline-flex items-center px-3 py-1.5 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="exceptionContent" class="bg-red-50 border border-red-200 rounded-lg p-4 max-h-72 overflow-y-auto">
                        <pre class="text-sm font-mono text-red-800">{{ $jobDetails['exception'] }}</pre>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Job Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                    Job Statistics
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 mb-1">{{ $jobDetails['attempts'] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Total Attempts</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 mb-1">{{ $jobDetails['runtime'] ? $jobDetails['runtime'] . 'ms' : 'N/A' }}</div>
                        <div class="text-sm text-gray-500">Execution Time</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 mb-1">{{ $jobDetails['queue'] ?? 'default' }}</div>
                        <div class="text-sm text-gray-500">Queue Name</div>
                    </div>
                    <div class="text-center">
                        @php
                            $statusTextColor = match($jobDetails['status'] ?? 'pending') {
                                'completed' => 'text-green-600',
                                'failed' => 'text-red-600',
                                default => 'text-yellow-600'
                            };
                        @endphp
                        <div class="text-2xl font-bold {{ $statusTextColor }} mb-1">{{ ucfirst($jobDetails['status'] ?? 'pending') }}</div>
                        <div class="text-sm text-gray-500">Current Status</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent || element.innerText;
    
    navigator.clipboard.writeText(text).then(function() {
        showToast('Content copied to clipboard!', 'success');
    }, function(err) {
        console.error('Could not copy text: ', err);
        showToast('Failed to copy content', 'error');
    });
}

function retryJob(id) {
    if (!confirm('Are you sure you want to retry this job?')) {
        return;
    }
    
    fetch(`{{ route("admin.queue-monitor.retry", ":id") }}`.replace(':id', id), {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Job retried successfully', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("admin.queue-monitor.index") }}';
            }, 2000);
        } else {
            showToast(data.message || 'Failed to retry job', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to retry job', 'error');
        console.error(error);
    });
}

function deleteJob(id) {
    if (!confirm('Are you sure you want to delete this failed job? This action cannot be undone.')) {
        return;
    }
    
    fetch(`{{ route("admin.queue-monitor.delete", ":id") }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Job deleted successfully', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("admin.queue-monitor.index") }}';
            }, 2000);
        } else {
            showToast(data.message || 'Failed to delete job', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to delete job', 'error');
        console.error(error);
    });
}

function showToast(message, type = 'info') {
    const toastClasses = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'info': 'bg-blue-500',
        'warning': 'bg-yellow-500'
    }[type] || 'bg-blue-500';
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${toastClasses} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    toast.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}
</script>
@endpush
