@extends('layouts.app')

@section('title', 'Edit WhatsApp Session - ' . $whatsappSession->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Edit WhatsApp Session</h1>
                    <p class="text-muted mb-0">Update {{ $whatsappSession->name }} configuration</p>
                </div>
                <div>
                    <a href="{{ route('whatsapp.sessions.show', $whatsappSession) }}" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Session
                    </a>
                    <a href="{{ route('whatsapp.sessions.index') }}" class="btn btn-light">
                        <i class="fas fa-list me-1"></i> All Sessions
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($whatsappSession->status === 'connected')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Session is currently connected.</strong> Some changes may require disconnecting and reconnecting the session to take effect.
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('whatsapp.sessions.update', $whatsappSession) }}" method="POST" id="session-form">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Basic Configuration -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-1"></i> Basic Configuration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Session Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $whatsappSession->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{!! old('description', $whatsappSession->description) !!}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" 
                                               name="is_active" value="1" {{ old('is_active', $whatsappSession->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Session
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timeout_minutes" class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" class="form-control @error('timeout_minutes') is-invalid @enderror" 
                                           id="timeout_minutes" name="timeout_minutes" value="{{ old('timeout_minutes', $whatsappSession->timeout_minutes ?? 30) }}" 
                                           min="5" max="1440">
                                    <div class="form-text">Auto-disconnect if inactive for this duration</div>
                                    @error('timeout_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="max_retries" class="form-label">Max Connection Retries</label>
                                    <input type="number" class="form-control @error('max_retries') is-invalid @enderror" 
                                           id="max_retries" name="max_retries" value="{{ old('max_retries', $whatsappSession->max_retries ?? 3) }}" 
                                           min="1" max="10">
                                    @error('max_retries')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="user_agent" class="form-label">Custom User Agent</label>
                                    <input type="text" class="form-control @error('user_agent') is-invalid @enderror" 
                                           id="user_agent" name="user_agent" value="{{ old('user_agent', $whatsappSession->user_agent) }}" 
                                           placeholder="Leave blank for default">
                                    @error('user_agent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Session Options -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-sliders-h me-1"></i> Session Options
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="auto_reconnect" 
                                           name="auto_reconnect" value="1" {{ old('auto_reconnect', $whatsappSession->auto_reconnect) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_reconnect">
                                        <strong>Auto-reconnect on disconnect</strong>
                                        <div class="text-muted small">Automatically reconnect when connection is lost</div>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="save_media" 
                                           name="save_media" value="1" {{ old('save_media', $whatsappSession->save_media) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="save_media">
                                        <strong>Save received media files</strong>
                                        <div class="text-muted small">Store images, videos, and documents locally</div>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="log_messages" 
                                           name="log_messages" value="1" {{ old('log_messages', $whatsappSession->log_messages) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="log_messages">
                                        <strong>Log all messages</strong>
                                        <div class="text-muted small">Keep detailed logs of all conversations</div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="enable_groups" 
                                           name="enable_groups" value="1" {{ old('enable_groups', $whatsappSession->enable_groups) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_groups">
                                        <strong>Enable group messaging</strong>
                                        <div class="text-muted small">Allow sending/receiving group messages</div>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="enable_webhooks" 
                                           name="enable_webhooks" value="1" {{ old('enable_webhooks', $whatsappSession->enable_webhooks ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_webhooks">
                                        <strong>Enable webhooks</strong>
                                        <div class="text-muted small">Send webhook notifications for events</div>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="mark_read" 
                                           name="mark_read" value="1" {{ old('mark_read', $whatsappSession->mark_read ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="mark_read">
                                        <strong>Auto-mark messages as read</strong>
                                        <div class="text-muted small">Automatically mark incoming messages as read</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-star me-1"></i> Enabled Features
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $currentFeatures = old('features', $whatsappSession->features ?? []);
                                $availableFeatures = [
                                    'auto_reply' => ['title' => 'Auto-Reply', 'desc' => 'Automatically respond to incoming messages'],
                                    'contact_sync' => ['title' => 'Contact Sync', 'desc' => 'Sync WhatsApp contacts with CRM contacts'],
                                    'message_templates' => ['title' => 'Message Templates', 'desc' => 'Use predefined message templates'],
                                    'broadcast_messages' => ['title' => 'Broadcast Messages', 'desc' => 'Send messages to multiple contacts'],
                                    'read_receipts' => ['title' => 'Read Receipts', 'desc' => 'Track when messages are read'],
                                    'typing_indicator' => ['title' => 'Typing Indicator', 'desc' => 'Show typing indicator when composing']
                                ];
                            @endphp

                            @foreach($availableFeatures as $key => $feature)
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="feature_{{ $key }}" 
                                           name="features[]" value="{{ $key }}" {{ in_array($key, $currentFeatures) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="feature_{{ $key }}">
                                        <strong>{{ $feature['title'] }}</strong>
                                        <div class="text-muted small">{{ $feature['desc'] }}</div>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status & Actions -->
            <div class="col-lg-4">
                <!-- Current Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-1"></i> Current Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="mb-2">
                                <span class="badge bg-{{ $whatsappSession->getStatusColor() }} fs-6 px-3 py-2">
                                    <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                    {{ ucfirst($whatsappSession->status) }}
                                </span>
                            </div>
                            @if($whatsappSession->phone_number)
                                <div class="text-muted">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $whatsappSession->phone_number }}
                                </div>
                            @endif
                            @if($whatsappSession->profile_name)
                                <div class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $whatsappSession->profile_name }}
                                </div>
                            @endif
                        </div>

                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><small class="text-muted">Created:</small></td>
                                <td><small>{{ $whatsappSession->created_at->format('M j, Y') }}</small></td>
                            </tr>
                            @if($whatsappSession->connected_at)
                            <tr>
                                <td><small class="text-muted">Connected:</small></td>
                                <td><small>{{ $whatsappSession->connected_at->format('M j, Y') }}</small></td>
                            </tr>
                            @endif
                            @if($whatsappSession->last_activity_at)
                            <tr>
                                <td><small class="text-muted">Last Activity:</small></td>
                                <td><small>{{ $whatsappSession->last_activity_at->diffForHumans() }}</small></td>
                            </tr>
                            @endif
                            <tr>
                                <td><small class="text-muted">Messages:</small></td>
                                <td><small>{{ number_format($whatsappSession->whatsapp_messages_count ?? 0) }}</small></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Webhook Configuration -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-webhook me-1"></i> Webhook Configuration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="webhook_url" class="form-label">Webhook URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control-plaintext" 
                                       value="{{ url('/webhook/whatsapp/' . $whatsappSession->id) }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyWebhookUrl()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="form-text">This URL receives WhatsApp webhook events</div>
                        </div>

                        <div class="mb-3">
                            <label for="webhook_secret" class="form-label">Webhook Secret</label>
                            <input type="text" class="form-control @error('webhook_secret') is-invalid @enderror" 
                                   id="webhook_secret" name="webhook_secret" 
                                   value="{{ old('webhook_secret', $whatsappSession->webhook_secret) }}" 
                                   placeholder="Optional webhook secret">
                            <div class="form-text">Optional secret for webhook verification</div>
                            @error('webhook_secret')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($whatsappSession->status === 'connected')
                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="testWebhook()">
                                <i class="fas fa-vial me-1"></i> Test Webhook
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Danger Zone -->
                @if($whatsappSession->status !== 'connected')
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i> Danger Zone
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="card-text mb-3">
                            <strong>Delete Session</strong><br>
                            <small class="text-muted">Permanently delete this WhatsApp session and all associated data.</small>
                        </p>
                        
                        <form action="{{ route('whatsapp.sessions.destroy', $whatsappSession) }}" method="POST" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-trash me-1"></i> Delete Session
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Form Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @if($whatsappSession->status === 'connected')
                                <button type="button" class="btn btn-warning" onclick="confirmReconnect()">
                                    <i class="fas fa-sync me-1"></i> Save & Reconnect
                                </button>
                                @else
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                                @endif
                            </div>
                            
                            <div>
                                <a href="{{ route('whatsapp.sessions.show', $whatsappSession) }}" class="btn btn-light me-2">
                                    Cancel
                                </a>
                                @if($whatsappSession->status !== 'connected')
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Session
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="reconnectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reconnect Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Some changes require disconnecting and reconnecting the WhatsApp session.</p>
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This will temporarily disconnect your WhatsApp session. You'll need to scan the QR code again to reconnect.
                </p>
                <p>Do you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="saveAndReconnect()">
                    Save & Reconnect
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete form
    document.querySelector('.delete-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to delete this WhatsApp session? This action cannot be undone and will remove all message history.')) {
            this.submit();
        }
    });
});

function copyWebhookUrl() {
    const url = '{{ url("/webhook/whatsapp/" . $whatsappSession->id) }}';
    navigator.clipboard.writeText(url).then(function() {
        showAlert('success', 'Copied!', 'Webhook URL copied to clipboard');
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('success', 'Copied!', 'Webhook URL copied to clipboard');
    });
}

function confirmReconnect() {
    const modal = new bootstrap.Modal(document.getElementById('reconnectModal'));
    modal.show();
}

function saveAndReconnect() {
    const form = document.getElementById('session-form');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'reconnect';
    input.value = '1';
    form.appendChild(input);
    form.submit();
}

function testWebhook() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testing...';
    button.disabled = true;
    
    fetch('{{ route("whatsapp.sessions.test-webhook", $whatsappSession) }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Webhook Test Successful', data.message || 'Webhook is working correctly.');
        } else {
            showAlert('error', 'Webhook Test Failed', data.message || 'Unable to reach webhook endpoint.');
        }
    })
    .catch(error => {
        showAlert('error', 'Webhook Test Failed', 'An error occurred while testing the webhook.');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function showAlert(type, title, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
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
</script>
@endpush
