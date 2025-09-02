@extends('layouts.app')

@section('title', 'Data Request Details')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Data Request Details</h1>
                    <p class="text-gray-600 mt-1">Review and process GDPR data request</p>
                </div>
                <a href="{{ route('admin.compliance.data-requests') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Requests
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Request Overview -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Request Overview</h3>
                            @php
                                $statusClass = match($dataRequest->status) {
                                    'completed' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-yellow-100 text-yellow-800'
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                                {{ ucfirst($dataRequest->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Request Information -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Request Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Request ID</dt>
                                        <dd class="text-sm text-gray-900 font-mono">#{{ $dataRequest->id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                                        <dd class="text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ ucfirst($dataRequest->request_type) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Submitted</dt>
                                        <dd class="text-sm text-gray-900">{{ $dataRequest->created_at->format('F j, Y \a\t g:i A') }}</dd>
                                    </div>
                                    @if($dataRequest->verified_at)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Verified</dt>
                                            <dd class="text-sm text-gray-900">{{ $dataRequest->verified_at->format('F j, Y \a\t g:i A') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            <!-- Requester Information -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Requester Information</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                        <dd class="text-sm text-gray-900 font-medium">{{ $dataRequest->full_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="text-sm text-gray-900">{{ $dataRequest->email }}</dd>
                                    </div>
                                    @if($dataRequest->contact)
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Contact Record</dt>
                                            <dd class="text-sm">
                                                <a href="{{ route('contacts.show', $dataRequest->contact) }}" 
                                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                                    View Contact Profile
                                                </a>
                                            </dd>
                                        </div>
                                    @else
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Contact Record</dt>
                                            <dd class="text-sm text-gray-500">No matching contact found</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Request Details -->
                        @if($dataRequest->request_details)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Additional Details</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ json_encode($dataRequest->request_details, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif

                        <!-- Rejection Reason -->
                        @if($dataRequest->rejection_reason)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Rejection Reason</h4>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <p class="text-sm text-red-800">{{ $dataRequest->rejection_reason }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                            Timeline
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <!-- Request Submitted -->
                                <li>
                                    <div class="relative pb-8">
                                        <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                        <div class="relative flex space-x-3">
                                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-plus text-white text-xs"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Request Submitted</div>
                                                    <div class="text-sm text-gray-500">{{ $dataRequest->created_at->format('F j, Y \a\t g:i A') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <!-- Request Verified -->
                                @if($dataRequest->verified_at)
                                <li>
                                    <div class="relative pb-8">
                                        <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                        <div class="relative flex space-x-3">
                                            <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-check text-white text-xs"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Request Verified</div>
                                                    <div class="text-sm text-gray-500">{{ $dataRequest->verified_at->format('F j, Y \a\t g:i A') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                <!-- Processing Started -->
                                @if($dataRequest->processed_at)
                                <li>
                                    <div class="relative pb-8">
                                        <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                        <div class="relative flex space-x-3">
                                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-cog text-white text-xs"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Processing Started</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $dataRequest->processed_at->format('F j, Y \a\t g:i A') }}
                                                        @if($dataRequest->processor)
                                                            <span class="text-gray-400">by {{ $dataRequest->processor->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                <!-- Request Completed -->
                                @if($dataRequest->completed_at)
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-check-circle text-white text-xs"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Request Completed</div>
                                                    <div class="text-sm text-gray-500">{{ $dataRequest->completed_at->format('F j, Y \a\t g:i A') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                <!-- Request Rejected -->
                                @if($dataRequest->status === 'rejected')
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-times text-white text-xs"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">Request Rejected</div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $dataRequest->updated_at->format('F j, Y \a\t g:i A') }}
                                                        @if($dataRequest->processor)
                                                            <span class="text-gray-400">by {{ $dataRequest->processor->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Requester Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Requester</h3>
                    </div>
                    <div class="p-6 text-center">
                        <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center text-2xl font-bold text-white mx-auto mb-4">
                            {{ strtoupper(substr($dataRequest->full_name ?? 'U', 0, 1)) }}
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">{{ $dataRequest->full_name ?? 'Unknown' }}</h4>
                        <p class="text-sm text-gray-500">{{ $dataRequest->email }}</p>
                        @if($dataRequest->contact)
                            <div class="mt-4">
                                <a href="{{ route('contacts.show', $dataRequest->contact) }}" 
                                   class="inline-flex items-center px-3 py-2 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">
                                    <i class="fas fa-user mr-2"></i>View Contact
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($dataRequest->canBeProcessed())
                            <button type="button" onclick="processRequest()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-play mr-2"></i>Process Request
                            </button>
                        @endif
                        
                        @if($dataRequest->status === 'pending')
                            <button type="button" onclick="rejectRequest()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <i class="fas fa-times mr-2"></i>Reject Request
                            </button>
                            
                            <button type="button" onclick="resendVerification()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <i class="fas fa-envelope mr-2"></i>Resend Verification
                            </button>
                        @endif
                        
                        @if($dataRequest->file_path)
                            <a href="{{ route('admin.compliance.download-export', basename($dataRequest->file_path)) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <i class="fas fa-download mr-2"></i>Download Export
                            </a>
                        @endif
                        
                        <button type="button" onclick="addNote()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-cyan-600 text-white text-sm font-medium rounded-lg hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <i class="fas fa-sticky-note mr-2"></i>Add Note
                        </button>
                        
                        <button type="button" onclick="exportDetails()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            <i class="fas fa-file-export mr-2"></i>Export Details
                        </button>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                            Statistics
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Processing Time</span>
                            <span class="text-sm font-medium text-gray-900">
                                @if($dataRequest->completed_at || $dataRequest->status === 'rejected')
                                    {{ $dataRequest->created_at->diffForHumans($dataRequest->completed_at ?? $dataRequest->updated_at, true) }}
                                @else
                                    {{ $dataRequest->created_at->diffForHumans() }}
                                @endif
                            </span>
                        </div>
                        
                        @if($dataRequest->expires_at)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Time to Expiry</span>
                                <span class="text-sm font-medium {{ $dataRequest->isExpired() ? 'text-red-600' : 'text-gray-900' }}">
                                    @if($dataRequest->isExpired())
                                        Expired {{ $dataRequest->expires_at->diffForHumans() }}
                                    @else
                                        {{ $dataRequest->expires_at->diffForHumans() }}
                                    @endif
                                </span>
                            </div>
                        @endif
                        
                        @if($dataRequest->file_path && file_exists(storage_path('app/' . $dataRequest->file_path)))
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Export File Size</span>
                                <span class="text-sm font-medium text-gray-900">{{ formatBytes(filesize(storage_path('app/' . $dataRequest->file_path))) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Process Request Modal -->
<div id="processModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                        <i class="fas fa-cog mr-2 text-blue-600"></i>
                        Process Data Request
                    </h3>
                    <button type="button" onclick="closeProcessModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="processForm" class="space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                            <span class="text-sm text-blue-800">
                                This will process the <strong>{{ $dataRequest->request_type }}</strong> request for <strong>{{ $dataRequest->full_name }}</strong>.
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Action</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="action" value="approve" checked 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="ml-3 text-sm text-gray-700">
                                    <i class="fas fa-check text-green-500 mr-1"></i> Approve & Process Request
                                </span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="action" value="reject" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="ml-3 text-sm text-gray-700">
                                    <i class="fas fa-times text-red-500 mr-1"></i> Reject Request
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <div id="rejectionReasonGroup" class="hidden">
                        <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                                  id="rejectionReason" name="rejection_reason" rows="3" 
                                  placeholder="Please provide a detailed reason for rejection..."></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-between">
                <button type="button" onclick="closeProcessModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="button" onclick="submitProcess()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>Process Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div id="noteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="note-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="note-modal-title">
                        <i class="fas fa-sticky-note mr-2 text-cyan-600"></i>
                        Add Note
                    </h3>
                    <button type="button" onclick="closeNoteModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="noteForm">
                    <div>
                        <label for="noteContent" class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500" 
                                  id="noteContent" name="note" rows="4" 
                                  placeholder="Add a note about this request..."></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-between">
                <button type="button" onclick="closeNoteModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    Cancel
                </button>
                <button type="button" onclick="submitNote()" 
                        class="inline-flex items-center px-4 py-2 bg-cyan-600 text-white text-sm font-medium rounded-lg hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <i class="fas fa-save mr-2"></i>Save Note
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function processRequest() {
    document.getElementById('processModal').classList.remove('hidden');
}

function closeProcessModal() {
    document.getElementById('processModal').classList.add('hidden');
}

function rejectRequest() {
    document.getElementById('reject').checked = true;
    document.getElementById('rejectionReasonGroup').classList.remove('hidden');
    document.getElementById('processModal').classList.remove('hidden');
}

function submitProcess() {
    const form = document.getElementById('processForm');
    const formData = new FormData(form);
    
    fetch('{{ route("admin.compliance.process-data-request", $dataRequest) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeProcessModal();
            showToast('success', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('error', data.message || 'Error processing request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Network error occurred');
    });
}

function resendVerification() {
    fetch('{{ route("admin.compliance.verify-data-request") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            token: '{{ $dataRequest->verification_token }}',
            email: '{{ $dataRequest->email }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Verification email sent successfully');
        } else {
            showToast('error', data.message || 'Error sending verification');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Network error occurred');
    });
}

function addNote() {
    document.getElementById('noteModal').classList.remove('hidden');
}

function closeNoteModal() {
    document.getElementById('noteModal').classList.add('hidden');
}

function submitNote() {
    const note = document.getElementById('noteContent').value.trim();
    
    if (!note) {
        showToast('warning', 'Please enter a note');
        return;
    }
    
    closeNoteModal();
    showToast('success', 'Note added successfully');
    document.getElementById('noteContent').value = '';
}

function exportDetails() {
    const requestData = {
        id: {{ $dataRequest->id }},
        type: '{{ $dataRequest->request_type }}',
        status: '{{ $dataRequest->status }}',
        requester: '{{ $dataRequest->full_name }}',
        email: '{{ $dataRequest->email }}',
        created_at: '{{ $dataRequest->created_at->toISOString() }}',
        @if($dataRequest->verified_at)
        verified_at: '{{ $dataRequest->verified_at->toISOString() }}',
        @endif
        @if($dataRequest->processed_at)
        processed_at: '{{ $dataRequest->processed_at->toISOString() }}',
        @endif
        @if($dataRequest->completed_at)
        completed_at: '{{ $dataRequest->completed_at->toISOString() }}',
        @endif
        request_details: @json($dataRequest->request_details),
        @if($dataRequest->rejection_reason)
        rejection_reason: @json($dataRequest->rejection_reason),
        @endif
    };
    
    const dataStr = JSON.stringify(requestData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    
    const url = URL.createObjectURL(dataBlob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `data-request-{{ $dataRequest->id }}-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function showToast(type, message) {
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

// Handle action radio buttons
document.addEventListener('DOMContentLoaded', function() {
    const actionRadios = document.querySelectorAll('input[name="action"]');
    const rejectionReasonGroup = document.getElementById('rejectionReasonGroup');
    
    actionRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'reject') {
                rejectionReasonGroup.classList.remove('hidden');
                document.getElementById('rejectionReason').required = true;
            } else {
                rejectionReasonGroup.classList.add('hidden');
                document.getElementById('rejectionReason').required = false;
            }
        });
    });
});
</script>
@endpush
