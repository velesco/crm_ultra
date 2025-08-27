@extends('layouts.app')

@section('title', $whatsappSession->name . ' - WhatsApp Session')

@push('styles')
<style>
    .session-header {
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
    }
    
    .status-indicator {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .qr-code {
        max-width: 200px;
        border-radius: 10px;
        border: 3px solid #25d366;
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -21px;
        top: 20px;
        height: calc(100% - 20px);
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-marker {
        position: absolute;
        left: -25px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    .pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="session-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="h3 mb-2 text-white">
                            <i class="fab fa-whatsapp me-2"></i>
                            {{ $whatsappSession->name }}
                        </h1>
                        <p class="mb-1 opacity-75">{{ $whatsappSession->description ?: 'WhatsApp Business Session' }}</p>
                        @if($whatsappSession->phone_number)
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-phone me-1"></i>
                                {{ $whatsappSession->phone_number }}
                            </p>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="mb-2">
                            <span class="status-indicator bg-{{ $whatsappSession->getStatusColor() }}"></span>
                            <span class="badge bg-{{ $whatsappSession->getStatusColor() }} fs-6">
                                {{ ucfirst($whatsappSession->status) }}
                            </span>
                            @if($whatsappSession->status === 'connected')
                                <span class="badge bg-success ms-2 pulse">Online</span>
                            @endif
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('whatsapp.sessions.edit', $whatsappSession) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            @if($whatsappSession->status === 'connected')
                                <a href="{{ route('whatsapp.index') }}?session={{ $whatsappSession->id }}" class="btn btn-success btn-sm">
                                    <i class="fab fa-whatsapp me-1"></i> Chat
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Quick Actions</h6>
                        
                        <div class="btn-group">
                            @if($whatsappSession->status === 'pending')
                                <form action="{{ route('whatsapp.sessions.start', $whatsappSession) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-play me-1"></i> Start Session
                                    </button>
                                </form>
                            @endif
                            
                            @if($whatsappSession->status === 'active')
                                <button class="btn btn-info btn-sm" onclick="showQRCode()">
                                    <i class="fas fa-qrcode me-1"></i> Show QR Code
                                </button>
                            @endif
                            
                            @if($whatsappSession->status === 'connected')
                                <button class="btn btn-outline-info btn-sm" onclick="checkStatus()">
                                    <i class="fas fa-sync me-1"></i> Check Status
                                </button>
                                <form action="{{ route('whatsapp.sessions.stop', $whatsappSession) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-stop me-1"></i> Disconnect
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('whatsapp.sessions.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-list me-1"></i> All Sessions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Session Statistics -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ number_format($stats['total_messages'] ?? 0) }}</h3>
                            <small>Total Messages</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ number_format($stats['sent_messages'] ?? 0) }}</h3>
                            <small>Sent</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ number_format($stats['received_messages'] ?? 0) }}</h3>
                            <small>Received</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ number_format($stats['contacts'] ?? 0) }}</h3>
                            <small>Contacts</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-comments me-1"></i> Recent Messages
                    </h6>
                    <a href="{{ route('whatsapp.history') }}?session={{ $whatsappSession->id }}" class="btn btn-sm btn-outline-primary">
                        View All Messages
                    </a>
                </div>
                <div class="card-body">
                    @if($recentMessages && $recentMessages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Contact</th>
                                        <th>Message</th>
                                        <th>Direction</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMessages as $message)
                                    <tr>
                                        <td>
                                            @if($message->contact)
                                                <div>
                                                    <strong>{{ $message->contact->full_name }}</strong><br>
                                                    <small class="text-muted">{{ $message->from_number ?? $message->to_number }}</small>
                                                </div>
                                            @else
                                                <small class="text-muted">{{ $message->from_number ?? $message->to_number }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="max-width: 250px;">
                                                @if($message->message_type === 'text')
                                                    {{ Str::limit($message->message_body, 50) }}
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($message->message_type) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($message->direction === 'inbound')
                                                <i class="fas fa-arrow-down text-success"></i> In
                                            @else
                                                <i class="fas fa-arrow-up text-primary"></i> Out
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $message->getStatusColor() }}">
                                                {{ ucfirst($message->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $message->created_at->format('M j, H:i') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No messages in this session yet.</p>
                            @if($whatsappSession->status === 'connected')
                                <a href="{{ route('whatsapp.index') }}?session={{ $whatsappSession->id }}" class="btn btn-primary">
                                    <i class="fab fa-whatsapp me-1"></i> Start Messaging
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Session Details & Timeline -->
        <div class="col-lg-4">
            <!-- Configuration Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-1"></i> Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Active:</strong></td>
                            <td>
                                <span class="badge bg-{{ $whatsappSession->is_active ? 'success' : 'secondary' }}">
                                    {{ $whatsappSession->is_active ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Timeout:</strong></td>
                            <td>{{ $whatsappSession->timeout_minutes ?? 30 }} minutes</td>
                        </tr>
                        <tr>
                            <td><strong>Max Retries:</strong></td>
                            <td>{{ $whatsappSession->max_retries ?? 3 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Auto-reconnect:</strong></td>
                            <td>
                                <span class="badge bg-{{ $whatsappSession->auto_reconnect ? 'success' : 'secondary' }}">
                                    {{ $whatsappSession->auto_reconnect ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Save Media:</strong></td>
                            <td>
                                <span class="badge bg-{{ $whatsappSession->save_media ? 'success' : 'secondary' }}">
                                    {{ $whatsappSession->save_media ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Log Messages:</strong></td>
                            <td>
                                <span class="badge bg-{{ $whatsappSession->log_messages ? 'success' : 'secondary' }}">
                                    {{ $whatsappSession->log_messages ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    @if($whatsappSession->features && count($whatsappSession->features) > 0)
                    <div class="mt-3">
                        <h6 class="text-muted mb-2">Enabled Features:</h6>
                        @foreach($whatsappSession->features as $feature)
                            <span class="badge bg-primary me-1 mb-1">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-1"></i> Activity Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Session Created</h6>
                                <small class="text-muted">{{ $whatsappSession->created_at->format('M j, Y H:i') }}</small>
                            </div>
                        </div>
                        
                        @if($whatsappSession->connected_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">First Connection</h6>
                                <small class="text-muted">{{ $whatsappSession->connected_at->format('M j, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($whatsappSession->last_activity_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Last Activity</h6>
                                <small class="text-muted">{{ $whatsappSession->last_activity_at->format('M j, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($whatsappSession->disconnected_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Last Disconnect</h6>
                                <small class="text-muted">{{ $whatsappSession->disconnected_at->format('M j, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fab fa-whatsapp text-success me-2"></i>
                    Connect WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-loading" class="mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading QR Code...</span>
                    </div>
                    <p class="mt-2 text-muted">Generating QR Code...</p>
                </div>
                
                <div id="qr-content" class="d-none">
                    <img id="qr-image" class="qr-code mb-3" alt="WhatsApp QR Code">
                    <p class="text-muted">
                        <strong>Scan this QR code with WhatsApp on your phone</strong>
                    </p>
                    <ol class="text-start text-muted small">
                        <li>Open WhatsApp on your phone</li>
                        <li>Go to Settings → Linked Devices</li>
                        <li>Tap "Link a Device"</li>
                        <li>Scan this QR code</li>
                    </ol>
                </div>
                
                <div id="qr-error" class="d-none">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <p class="text-muted">Failed to load QR code. Please try again.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="refreshQRCode()">
                    <i class="fas fa-refresh me-1"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let qrRefreshInterval = null;

function showQRCode() {
    const modal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
    
    // Reset modal state
    document.getElementById('qr-loading').classList.remove('d-none');
    document.getElementById('qr-content').classList.add('d-none');
    document.getElementById('qr-error').classList.add('d-none');
    
    modal.show();
    
    // Load QR code
    refreshQRCode();
    
    // Set up auto-refresh
    qrRefreshInterval = setInterval(refreshQRCode, 10000);
    
    // Clear interval when modal is closed
    document.getElementById('qrCodeModal').addEventListener('hidden.bs.modal', function() {
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
            qrRefreshInterval = null;
        }
    });
}

function refreshQRCode() {
    fetch('{{ route("whatsapp.sessions.qr", $whatsappSession) }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('qr-loading').classList.add('d-none');
        
        if (data.success && data.qr) {
            document.getElementById('qr-image').src = data.qr;
            document.getElementById('qr-content').classList.remove('d-none');
            document.getElementById('qr-error').classList.add('d-none');
        } else if (data.connected) {
            document.getElementById('qr-content').classList.add('d-none');
            document.getElementById('qr-error').classList.remove('d-none');
            document.getElementById('qr-error').innerHTML = `
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-success">WhatsApp session is now connected!</p>
            `;
            
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            document.getElementById('qr-error').classList.remove('d-none');
        }
    })
    .catch(error => {
        document.getElementById('qr-loading').classList.add('d-none');
        document.getElementById('qr-error').classList.remove('d-none');
        console.error('QR code loading error:', error);
    });
}

function checkStatus() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Checking...';
    button.disabled = true;
    
    fetch('{{ route("whatsapp.sessions.status", $whatsappSession) }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status_changed) {
            window.location.reload();
        } else {
            showAlert('info', 'Status Check', `Session status: ${data.status}`);
        }
    })
    .catch(error => {
        showAlert('error', 'Status Check Failed', 'Unable to check session status.');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function showAlert(type, title, message) {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    alert.innerHTML = `
        <strong>${title}</strong><br>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Auto-refresh session status every 30 seconds
setInterval(() => {
    fetch('{{ route("whatsapp.sessions.status", $whatsappSession) }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status_changed) {
            window.location.reload();
        }
    })
    .catch(() => {}); // Silent fail for background checks
}, 30000);
</script>
@endpush
