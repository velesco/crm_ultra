@extends('layouts.app')

@section('title', 'Edit SMS Provider - ' . $smsProvider->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Edit SMS Provider</h1>
                    <p class="text-muted mb-0">Update {{ $smsProvider->name }} configuration</p>
                </div>
                <div>
                    <a href="{{ route('sms.providers.show', $smsProvider) }}" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Provider
                    </a>
                    <a href="{{ route('sms.providers.index') }}" class="btn btn-light">
                        <i class="fas fa-list me-1"></i> All Providers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('sms.providers.update', $smsProvider) }}" method="POST" id="provider-form">
        @csrf
        @method('PUT')
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cog me-1"></i> Provider Configuration
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-lg-6">
                        <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Provider Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $smsProvider->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="provider_type" class="form-label">Provider Type *</label>
                            <select class="form-control @error('provider_type') is-invalid @enderror" 
                                    id="provider_type" name="provider_type" required>
                                <option value="twilio" {{ old('provider_type', $smsProvider->provider_type) == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                <option value="vonage" {{ old('provider_type', $smsProvider->provider_type) == 'vonage' ? 'selected' : '' }}>Vonage (Nexmo)</option>
                                <option value="orange" {{ old('provider_type', $smsProvider->provider_type) == 'orange' ? 'selected' : '' }}>Orange SMS</option>
                                <option value="textmagic" {{ old('provider_type', $smsProvider->provider_type) == 'textmagic' ? 'selected' : '' }}>TextMagic</option>
                                <option value="clickatell" {{ old('provider_type', $smsProvider->provider_type) == 'clickatell' ? 'selected' : '' }}>Clickatell</option>
                                <option value="custom" {{ old('provider_type', $smsProvider->provider_type) == 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                            @error('provider_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_url" class="form-label">API URL</label>
                            <input type="url" class="form-control @error('api_url') is-invalid @enderror" 
                                   id="api_url" name="api_url" value="{{ old('api_url', $smsProvider->api_url) }}">
                            @error('api_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key *</label>
                            <input type="password" class="form-control @error('api_key') is-invalid @enderror" 
                                   id="api_key" name="api_key" value="{{ old('api_key') }}" 
                                   placeholder="Leave blank to keep current key">
                            <div class="form-text">Leave blank to keep the current API key</div>
                            @error('api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_secret" class="form-label">API Secret</label>
                            <input type="password" class="form-control @error('api_secret') is-invalid @enderror" 
                                   id="api_secret" name="api_secret" value="{{ old('api_secret') }}" 
                                   placeholder="Leave blank to keep current secret">
                            <div class="form-text">Leave blank to keep the current API secret</div>
                            @error('api_secret')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sender_id" class="form-label">Sender ID</label>
                            <input type="text" class="form-control @error('sender_id') is-invalid @enderror" 
                                   id="sender_id" name="sender_id" value="{{ old('sender_id', $smsProvider->sender_id) }}" 
                                   placeholder="Your Company">
                            @error('sender_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Configuration & Limits -->
                    <div class="col-lg-6">
                        <h6 class="border-bottom pb-2 mb-3">Configuration & Limits</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="daily_limit" class="form-label">Daily Limit *</label>
                                <input type="number" class="form-control @error('daily_limit') is-invalid @enderror" 
                                       id="daily_limit" name="daily_limit" value="{{ old('daily_limit', $smsProvider->daily_limit) }}" 
                                       min="1" required>
                                @error('daily_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="hourly_limit" class="form-label">Hourly Limit *</label>
                                <input type="number" class="form-control @error('hourly_limit') is-invalid @enderror" 
                                       id="hourly_limit" name="hourly_limit" value="{{ old('hourly_limit', $smsProvider->hourly_limit) }}" 
                                       min="1" required>
                                @error('hourly_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cost_per_sms" class="form-label">Cost per SMS ($)</label>
                                <input type="number" class="form-control @error('cost_per_sms') is-invalid @enderror" 
                                       id="cost_per_sms" name="cost_per_sms" value="{{ old('cost_per_sms', $smsProvider->cost_per_sms) }}" 
                                       step="0.0001" min="0">
                                @error('cost_per_sms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority *</label>
                                <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                       id="priority" name="priority" value="{{ old('priority', $smsProvider->priority) }}" 
                                       min="1" max="100" required>
                                <div class="form-text">Lower number = higher priority</div>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', $smsProvider->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_default" 
                                       name="is_default" value="1" {{ old('is_default', $smsProvider->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_default">
                                    Set as default provider
                                </label>
                            </div>
                        </div>

                        <!-- Current Usage Stats -->
                        <div class="mt-4">
                            <h6 class="text-muted mb-3">Current Usage</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <h6 class="mb-0 text-primary">{{ $smsProvider->sent_today ?? 0 }}</h6>
                                        <small class="text-muted">Today</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <h6 class="mb-0 text-info">{{ $smsProvider->sent_this_hour ?? 0 }}</h6>
                                        <small class="text-muted">This Hour</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0 text-success">{{ $smsProvider->sms_messages_count ?? 0 }}</h6>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Configuration -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="accordion" id="advanced-config">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" 
                                            data-bs-toggle="collapse" data-bs-target="#advanced-settings">
                                        Advanced Settings
                                    </button>
                                </h2>
                                <div id="advanced-settings" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="webhook_url" class="form-label">Webhook URL</label>
                                                    <input type="url" class="form-control @error('webhook_url') is-invalid @enderror" 
                                                           id="webhook_url" name="webhook_url" value="{{ old('webhook_url', $smsProvider->webhook_url) }}">
                                                    @error('webhook_url')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="timeout" class="form-label">Timeout (seconds)</label>
                                                    <input type="number" class="form-control @error('timeout') is-invalid @enderror" 
                                                           id="timeout" name="timeout" value="{{ old('timeout', $smsProvider->timeout ?? 30) }}" 
                                                           min="5" max="300">
                                                    @error('timeout')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="additional_config" class="form-label">Additional Configuration (JSON)</label>
                                            <textarea class="form-control @error('additional_config') is-invalid @enderror" 
                                                      id="additional_config" name="additional_config" rows="4" 
                                                      placeholder='{"key": "value"}'>{!! old('additional_config', json_encode($smsProvider->additional_config ?? [], JSON_PRETTY_PRINT)) !!}</textarea>
                                            <div class="form-text">Optional JSON configuration for provider-specific settings</div>
                                            @error('additional_config')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="testConnection()">
                        <i class="fas fa-vial me-1"></i> Test Connection
                    </button>
                    
                    <div>
                        <a href="{{ route('sms.providers.show', $smsProvider) }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Provider
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Reset Counters Modal -->
    <div class="modal fade" id="resetCountersModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Usage Counters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reset the daily and hourly usage counters for this provider?</p>
                    <p class="text-muted small">Current usage: {{ $smsProvider->sent_today ?? 0 }} messages today, {{ $smsProvider->sent_this_hour ?? 0 }} this hour</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('sms.providers.reset-counters', $smsProvider) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">Reset Counters</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // JSON validation for additional config
    const additionalConfigTextarea = document.getElementById('additional_config');
    if (additionalConfigTextarea) {
        additionalConfigTextarea.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value) {
                try {
                    JSON.parse(value);
                    this.classList.remove('is-invalid');
                    const feedback = this.nextElementSibling.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.remove();
                    }
                } catch (e) {
                    this.classList.add('is-invalid');
                    let feedback = this.nextElementSibling.nextElementSibling;
                    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        this.parentNode.appendChild(feedback);
                    }
                    feedback.textContent = 'Invalid JSON format';
                }
            }
        });
    }
});

function testConnection() {
    const form = document.getElementById('provider-form');
    const formData = new FormData(form);
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Testing...';
    button.disabled = true;
    
    // Test connection
    fetch('{{ route("sms.providers.test", $smsProvider) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Connection Test Successful', data.message || 'Provider configuration is working correctly.');
        } else {
            showAlert('error', 'Connection Test Failed', data.message || 'Unable to connect with the provided configuration.');
        }
    })
    .catch(error => {
        showAlert('error', 'Connection Test Failed', 'An error occurred while testing the connection.');
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
