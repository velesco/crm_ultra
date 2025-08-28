                        <button type="button" class="btn btn-danger btn-block mb-2" onclick="rejectRequest()">
                            <i class="fas fa-times me-2"></i>Reject Request
                        </button>
                    @endif
                    
                    @if($dataRequest->status === 'pending')
                        <button type="button" class="btn btn-warning btn-block mb-2" onclick="resendVerification()">
                            <i class="fas fa-envelope me-2"></i>Resend Verification
                        </button>
                    @endif
                    
                    @if($dataRequest->file_path)
                        <a href="{{ route('admin.compliance.download-export', basename($dataRequest->file_path)) }}" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-download me-2"></i>Download Export
                        </a>
                    @endif
                    
                    <button type="button" class="btn btn-info btn-block mb-2" onclick="addNote()">
                        <i class="fas fa-sticky-note me-2"></i>Add Note
                    </button>
                    
                    <button type="button" class="btn btn-secondary btn-block" onclick="exportDetails()">
                        <i class="fas fa-file-export me-2"></i>Export Details
                    </button>
                </div>
            </div>

            <!-- Request Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock me-2"></i>Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Request Submitted</h6>
                                <p class="timeline-text">{{ $dataRequest->created_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                        
                        @if($dataRequest->verified_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Request Verified</h6>
                                <p class="timeline-text">{{ $dataRequest->verified_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($dataRequest->processed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Processing Started</h6>
                                <p class="timeline-text">
                                    {{ $dataRequest->processed_at->format('F j, Y \a\t g:i A') }}
                                    @if($dataRequest->processor)
                                        <br><small class="text-muted">by {{ $dataRequest->processor->name }}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif
                        
                        @if($dataRequest->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Request Completed</h6>
                                <p class="timeline-text">{{ $dataRequest->completed_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($dataRequest->status === 'rejected')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Request Rejected</h6>
                                <p class="timeline-text">
                                    {{ $dataRequest->updated_at->format('F j, Y \a\t g:i A') }}
                                    @if($dataRequest->processor)
                                        <br><small class="text-muted">by {{ $dataRequest->processor->name }}</small>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Request Statistics -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Request Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Processing Time:</span>
                            <strong>
                                @if($dataRequest->completed_at || $dataRequest->status === 'rejected')
                                    {{ $dataRequest->created_at->diffForHumans($dataRequest->completed_at ?? $dataRequest->updated_at, true) }}
                                @else
                                    {{ $dataRequest->created_at->diffForHumans() }}
                                @endif
                            </strong>
                        </div>
                    </div>
                    
                    @if($dataRequest->expires_at)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Time to Expiry:</span>
                            <strong class="{{ $dataRequest->isExpired() ? 'text-danger' : '' }}">
                                @if($dataRequest->isExpired())
                                    Expired {{ $dataRequest->expires_at->diffForHumans() }}
                                @else
                                    {{ $dataRequest->expires_at->diffForHumans() }}
                                @endif
                            </strong>
                        </div>
                    </div>
                    @endif
                    
                    @if($dataRequest->file_path && file_exists(storage_path('app/' . $dataRequest->file_path)))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Export File Size:</span>
                            <strong>{{ formatBytes(filesize(storage_path('app/' . $dataRequest->file_path))) }}</strong>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Process Request Modal -->
<div class="modal fade" id="processModal" tabindex="-1" aria-labelledby="processModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="processModalLabel">
                    <i class="fas fa-cog me-2"></i>Process Data Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="processForm">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This will process the <strong>{{ $dataRequest->request_type }}</strong> request for <strong>{{ $dataRequest->full_name }}</strong>.
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Action</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="approve" value="approve" checked>
                                <label class="form-check-label" for="approve">
                                    <i class="fas fa-check text-success me-1"></i> Approve & Process Request
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="reject" value="reject">
                                <label class="form-check-label" for="reject">
                                    <i class="fas fa-times text-danger me-1"></i> Reject Request
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3" id="rejectionReasonGroup" style="display: none;">
                        <label for="rejectionReason">Rejection Reason *</label>
                        <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="3" 
                                  placeholder="Please provide a detailed reason for rejection..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitProcess()">
                    <i class="fas fa-save me-2"></i>Process Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalLabel">
                    <i class="fas fa-sticky-note me-2"></i>Add Note
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noteForm">
                    <div class="form-group">
                        <label for="noteContent">Note</label>
                        <textarea class="form-control" id="noteContent" name="note" rows="4" 
                                  placeholder="Add a note about this request..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitNote()">
                    <i class="fas fa-save me-2"></i>Save Note
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function processRequest() {
    $('#processModal').modal('show');
}

function rejectRequest() {
    document.getElementById('reject').checked = true;
    document.getElementById('rejectionReasonGroup').style.display = 'block';
    $('#processModal').modal('show');
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
            $('#processModal').modal('hide');
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
    $('#noteModal').modal('show');
}

function submitNote() {
    const note = document.getElementById('noteContent').value.trim();
    
    if (!note) {
        showToast('warning', 'Please enter a note');
        return;
    }
    
    // This would typically save to database - for now just show success
    $('#noteModal').modal('hide');
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
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

// Handle action radio buttons
document.addEventListener('DOMContentLoaded', function() {
    const actionRadios = document.querySelectorAll('input[name="action"]');
    const rejectionReasonGroup = document.getElementById('rejectionReasonGroup');
    
    actionRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'reject') {
                rejectionReasonGroup.style.display = 'block';
                document.getElementById('rejectionReason').required = true;
            } else {
                rejectionReasonGroup.style.display = 'none';
                document.getElementById('rejectionReason').required = false;
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    margin: 0 auto;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #e3e6f0;
}

.timeline-content {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #4e73df;
}

.timeline-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #5a5c69;
}

.timeline-text {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 0;
}

.bg-danger-soft {
    background-color: #f8d7da;
}

.btn-block {
    width: 100%;
}

@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -12px;
    }
}
</style>
@endpush
