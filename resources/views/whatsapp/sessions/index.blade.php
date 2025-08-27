@extends('layouts.app')

@section('title', 'WhatsApp Sessions')

@push('styles')
<style>
    .session-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .session-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.15);
    }
    
    .session-status {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .qr-code {
        max-width: 150px;
        border-radius: 8px;
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">WhatsApp Sessions</h1>
                    <p class="text-muted mb-0">Manage WhatsApp Web sessions and connections</p>
                </div>
                <div>
                    <a href="{{ route('whatsapp.sessions.create') }}" class="btn btn-primary">
                        <i class="fab fa-whatsapp me-1"></i> New Session
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Total Sessions</h6>
                            <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fab fa-whatsapp fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Active Sessions</h6>
                            <h3 class="mb-0">{{ $stats['active'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Messages Sent</h6>
                            <h3 class="mb-0">{{ number_format($stats['messages_sent'] ?? 0) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Pending</h6>
                            <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="row">
        @forelse($sessions as $session)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card session-card h-100">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">{{ $session->name }}</h6>
                            <small class="text-muted">{{ $session->phone_number ?: 'No phone number' }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('whatsapp.sessions.show', $session) }}">
                                        <i class="fas fa-eye me-2"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('whatsapp.sessions.edit', $session) }}">
                                        <i class="fas fa-edit me-2"></i> Edit
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                @if($session->status === 'pending')
                                <li>
                                    <form action="{{ route('whatsapp.sessions.start', $session) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-success">
                                            <i class="fas fa-play me-2"></i> Start Session
                                        </button>
                                    </form>
                                </li>
                                @endif
                                
                                @if($session->status === 'active')
                                <li>
                                    <button class="dropdown-item text-info" onclick="showQRCode('{{ $session->id }}')">
                                        <i class="fas fa-qrcode me-2"></i> Show QR Code
                                    </button>
                                </li>
                                <li>
                                    <form action="{{ route('whatsapp.sessions.stop', $session) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-warning">
                                            <i class="fas fa-stop me-2"></i> Stop Session
                                        </button>
                                    </form>
                                </li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('whatsapp.sessions.destroy', $session) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Status -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="session-status {{ $session->getStatusColor() }} me-2"></span>
                        <span class="badge bg-{{ $session->getStatusColor() }}">
                            {{ ucfirst($session->status) }}
                        </span>
                        @if($session->is_active && $session->status === 'connected')
                            <span class="badge bg-success ms-2 pulse">Online</span>
                        @endif
                    </div>

                    <!-- Connection Info -->
                    @if($session->status === 'connected' && $session->phone_number)
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-phone-alt text-success me-2"></i>
                            <span>{{ $session->phone_number }}</span>
                        </div>
                        @if($session->profile_name)
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user text-info me-2"></i>
                            <span>{{ $session->profile_name }}</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Stats -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="mb-0">{{ number_format($session->whatsapp_messages_count ?? 0) }}</h6>
                                <small class="text-muted">Messages</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="mb-0">{{ $session->contacts_count ?? 0 }}</h6>
                                <small class="text-muted">Contacts</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">{{ $session->getDaysConnected() }}</h6>
                            <small class="text-muted">Days</small>
                        </div>
                    </div>

                    <!-- Last Activity -->
                    @if($session->last_activity_at)
                    <div class="text-center">
                        <small class="text-muted">
                            Last activity: {{ $session->last_activity_at->diffForHumans() }}
                        </small>
                    </div>
                    @endif
                </div>
                
                <div class="card-footer bg-transparent">
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('whatsapp.sessions.show', $session) }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-eye me-1"></i> Details
                            </a>
                        </div>
                        <div class="col">
                            @if($session->status === 'connected')
                                <a href="{{ route('whatsapp.index') }}?session={{ $session->id }}" class="btn btn-success btn-sm w-100">
                                    <i class="fab fa-whatsapp me-1"></i> Chat
                                </a>
                            @elseif($session->status === 'pending')
                                <button class="btn btn-warning btn-sm w-100" onclick="startSession('{{ $session->id }}')">
                                    <i class="fas fa-play me-1"></i> Connect
                                </button>
                            @else
                                <a href="{{ route('whatsapp.sessions.edit', $session) }}" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-cog me-1"></i> Configure
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fab fa-whatsapp fa-4x text-success"></i>
                    </div>
                    <h5>No WhatsApp Sessions</h5>
                    <p class="text-muted mb-4">You haven't created any WhatsApp sessions yet. Create your first session to start sending WhatsApp messages.</p>
                    <a href="{{ route('whatsapp.sessions.create') }}" class="btn btn-primary">
                        <i class="fab fa-whatsapp me-1"></i> Create WhatsApp Session
                    </a>
                </div>
            </div>
        </div>
        @endforelse
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
let currentSessionId = null;
let qrRefreshInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    // Handle delete forms
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this WhatsApp session? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
});

function startSession(sessionId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/whatsapp-sessions/${sessionId}/start`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    form.appendChild(csrfToken);
    document.body.appendChild(form);
    form.submit();
}

function showQRCode(sessionId) {
    currentSessionId = sessionId;
    const modal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
    
    // Reset modal state
    document.getElementById('qr-loading').classList.remove('d-none');
    document.getElementById('qr-content').classList.add('d-none');
    document.getElementById('qr-error').classList.add('d-none');
    
    modal.show();
    
    // Load QR code
    refreshQRCode();
    
    // Set up auto-refresh
    qrRefreshInterval = setInterval(refreshQRCode, 10000); // Refresh every 10 seconds
    
    // Clear interval when modal is closed
    document.getElementById('qrCodeModal').addEventListener('hidden.bs.modal', function() {
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
            qrRefreshInterval = null;
        }
    });
}

function refreshQRCode() {
    if (!currentSessionId) return;
    
    fetch(`/whatsapp-sessions/${currentSessionId}/qr`, {
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
            // Session is already connected
            document.getElementById('qr-content').classList.add('d-none');
            document.getElementById('qr-error').classList.remove('d-none');
            document.getElementById('qr-error').innerHTML = `
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-success">WhatsApp session is now connected!</p>
            `;
            
            // Refresh the page after a delay
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

// Auto-refresh session status every 30 seconds
setInterval(() => {
    if (document.querySelectorAll('.session-card').length > 0) {
        window.location.reload();
    }
}, 30000);
</script>
@endpush
