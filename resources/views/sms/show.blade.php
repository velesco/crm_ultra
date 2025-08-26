@extends('layouts.app')

@section('title', 'SMS Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 dark:text-white">SMS Details</h1>
            <p class="mb-0 text-gray-600 dark:text-gray-400">
                Message sent to {{ $smsMessage->contact->name ?? 'Unknown Contact' }}
            </p>
        </div>
        <div>
            <a href="{{ route('sms.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to SMS List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Message Content -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Message Content</h6>
                    @php
                        $statusClasses = [
                            'pending' => 'warning',
                            'sent' => 'success',
                            'delivered' => 'success',
                            'failed' => 'danger',
                            'scheduled' => 'info',
                            'cancelled' => 'secondary'
                        ];
                        $statusClass = $statusClasses[$smsMessage->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusClass }} fs-6">
                        {{ ucfirst($smsMessage->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="message-display p-4 bg-light border rounded">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-sms fa-2x text-primary"></i>
                            </div>
                            <div>
                                <strong>To: {{ $smsMessage->phone_number }}</strong>
                                <br>
                                <small class="text-muted">{{ strlen($smsMessage->message) }} characters</small>
                            </div>
                        </div>
                        <div class="message-content" style="font-family: monospace; white-space: pre-wrap; background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;">
                            {{ $smsMessage->message }}
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4">
                        <div class="btn-group" role="group">
                            @if($smsMessage->status === 'scheduled')
                                <a href="{{ route('sms.edit', $smsMessage) }}" class="btn btn-primary">
                                    <i class="fas fa-edit me-2"></i>Edit Message
                                </a>
                                <form action="{{ route('sms.cancel', $smsMessage) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" 
                                            onclick="return confirm('Are you sure you want to cancel this scheduled SMS?')">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                </form>
                            @endif

                            @if($smsMessage->status === 'failed')
                                <form action="{{ route('sms.resend', $smsMessage) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Are you sure you want to resend this SMS?')">
                                        <i class="fas fa-redo me-2"></i>Resend
                                    </button>
                                </form>
                            @endif

                            @if(in_array($smsMessage->status, ['scheduled', 'failed', 'draft']))
                                <form action="{{ route('sms.destroy', $smsMessage) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this SMS message?')">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Delivery Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group mb-4">
                                <label class="text-muted small mb-1">Status</label>
                                <div class="fw-bold">
                                    <span class="badge bg-{{ $statusClass }} me-2">
                                        {{ ucfirst($smsMessage->status) }}
                                    </span>
                                    @if($smsMessage->error_message)
                                        <small class="text-danger">{{ $smsMessage->error_message }}</small>
                                    @endif
                                </div>
                            </div>

                            <div class="info-group mb-4">
                                <label class="text-muted small mb-1">Provider</label>
                                <div class="fw-bold">
                                    {{ $smsMessage->provider ?? 'Default Provider' }}
                                </div>
                            </div>

                            <div class="info-group mb-4">
                                <label class="text-muted small mb-1">Cost</label>
                                <div class="fw-bold">
                                    @if($smsMessage->cost > 0)
                                        ${{ number_format($smsMessage->cost, 4) }}
                                    @else
                                        <span class="text-muted">No cost recorded</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-group mb-4">
                                <label class="text-muted small mb-1">Created At</label>
                                <div class="fw-bold">
                                    {{ $smsMessage->created_at->format('M j, Y \a\t g:i A') }}
                                    <small class="text-muted d-block">
                                        ({{ $smsMessage->created_at->diffForHumans() }})
                                    </small>
                                </div>
                            </div>

                            @if($smsMessage->scheduled_at)
                                <div class="info-group mb-4">
                                    <label class="text-muted small mb-1">Scheduled For</label>
                                    <div class="fw-bold">
                                        {{ $smsMessage->scheduled_at->format('M j, Y \a\t g:i A') }}
                                        <small class="text-muted d-block">
                                            ({{ $smsMessage->scheduled_at->diffForHumans() }})
                                        </small>
                                    </div>
                                </div>
                            @endif

                            @if($smsMessage->sent_at)
                                <div class="info-group mb-4">
                                    <label class="text-muted small mb-1">Sent At</label>
                                    <div class="fw-bold">
                                        {{ $smsMessage->sent_at->format('M j, Y \a\t g:i A') }}
                                        <small class="text-muted d-block">
                                            ({{ $smsMessage->sent_at->diffForHumans() }})
                                        </small>
                                    </div>
                                </div>
                            @endif

                            @if($smsMessage->provider_message_id)
                                <div class="info-group mb-4">
                                    <label class="text-muted small mb-1">Provider Message ID</label>
                                    <div class="fw-bold font-monospace">
                                        {{ $smsMessage->provider_message_id }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            @if($smsMessage->metadata)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Technical Details</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $metadata = json_decode($smsMessage->metadata, true);
                        @endphp
                        
                        @if($metadata)
                            <div class="row">
                                @foreach($metadata as $key => $value)
                                    <div class="col-md-6 mb-3">
                                        <div class="info-group">
                                            <label class="text-muted small mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                                            <div class="fw-bold">
                                                @if(is_array($value) || is_object($value))
                                                    <pre class="small mb-0">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No technical details available.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contact Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
                </div>
                <div class="card-body">
                    @if($smsMessage->contact)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-lg rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3">
                                {{ substr($smsMessage->contact->name, 0, 1) }}
                            </div>
                            <div>
                                <h5 class="mb-1">{{ $smsMessage->contact->name }}</h5>
                                <p class="mb-0 text-muted">{{ $smsMessage->contact->email }}</p>
                            </div>
                        </div>

                        <div class="contact-details">
                            <div class="info-group mb-3">
                                <label class="text-muted small mb-1">Phone Number</label>
                                <div class="fw-bold">
                                    <a href="tel:{{ $smsMessage->contact->phone }}" class="text-decoration-none">
                                        {{ $smsMessage->contact->phone }}
                                    </a>
                                </div>
                            </div>

                            @if($smsMessage->contact->company)
                                <div class="info-group mb-3">
                                    <label class="text-muted small mb-1">Company</label>
                                    <div class="fw-bold">{{ $smsMessage->contact->company }}</div>
                                </div>
                            @endif

                            @if($smsMessage->contact->location)
                                <div class="info-group mb-3">
                                    <label class="text-muted small mb-1">Location</label>
                                    <div class="fw-bold">{{ $smsMessage->contact->location }}</div>
                                </div>
                            @endif

                            <div class="info-group mb-3">
                                <label class="text-muted small mb-1">Contact Created</label>
                                <div class="fw-bold">
                                    {{ $smsMessage->contact->created_at->format('M j, Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('contacts.show', $smsMessage->contact) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-user me-2"></i>View Contact Profile
                            </a>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-user-slash fa-3x mb-3"></i>
                            <div>Contact information not available</div>
                            <small>The contact may have been deleted</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sender Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sender Information</h6>
                </div>
                <div class="card-body">
                    @if($smsMessage->user)
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3">
                                {{ substr($smsMessage->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold">{{ $smsMessage->user->name }}</div>
                                <small class="text-muted">{{ $smsMessage->user->email }}</small>
                            </div>
                        </div>
                    @else
                        <div class="text-muted">
                            <i class="fas fa-user-times me-2"></i>
                            Sender information not available
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($smsMessage->contact)
                            <a href="{{ route('sms.create', ['contact' => $smsMessage->contact->id]) }}" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-sms me-2"></i>Send Another SMS
                            </a>
                            <a href="{{ route('email.campaigns.create', ['contact' => $smsMessage->contact->id]) }}" 
                               class="btn btn-outline-success">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </a>
                        @endif
                        
                        <button type="button" class="btn btn-outline-info" onclick="copyMessage()">
                            <i class="fas fa-copy me-2"></i>Copy Message
                        </button>
                        
                        <a href="{{ route('sms.index', ['phone' => $smsMessage->phone_number]) }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-history me-2"></i>SMS History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-lg {
    width: 60px;
    height: 60px;
    font-size: 20px;
    font-weight: 600;
}

.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
    font-weight: 600;
}

.info-group {
    padding: 10px 0;
    border-bottom: 1px solid #f1f1f1;
}

.info-group:last-child {
    border-bottom: none;
}

.message-content {
    line-height: 1.6;
    font-size: 14px;
}

.contact-details .info-group {
    padding: 8px 0;
}
</style>
@endpush

@push('scripts')
<script>
function copyMessage() {
    const messageContent = `{{ addslashes($smsMessage->message) }}`;
    navigator.clipboard.writeText(messageContent).then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto text-success">Success</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Message copied to clipboard!
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }).catch(function() {
        alert('Failed to copy message to clipboard');
    });
}
</script>
@endpush
