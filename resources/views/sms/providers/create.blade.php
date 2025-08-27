@extends('layouts.app')

@section('title', 'Add SMS Provider')

@push('styles')
<style>
    .provider-option {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .provider-option:hover {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }
    
    .provider-option.selected {
        border-color: #0d6efd;
        background-color: #e7f3ff;
    }
    
    .provider-logo {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        font-size: 1.2rem;
    }
    
    .config-section {
        display: none;
    }
    
    .config-section.show {
        display: block;
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
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
                    <h1 class="h3 mb-1">Add SMS Provider</h1>
                    <p class="text-muted mb-0">Configure a new SMS service provider</p>
                </div>
                <div>
                    <a href="{{ route('sms.providers.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Back to Providers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('sms.providers.store') }}" method="POST" id="provider-form">
        @csrf
        
        <!-- Step 1: Choose Provider -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-server me-1"></i> Choose SMS Provider
                </h6>
            </div>
            <div class="card-body">
                <div class="row" id="provider-options">
                    @foreach(['twilio', 'vonage', 'orange', 'textmagic', 'clickatell', 'custom'] as $provider)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="provider-option card h-100 text-center p-3" data-provider="{{ $provider }}">
                            <div class="provider-logo mx-auto mb-3" style="background: {{ $providerColors[$provider] ?? '#6c757d' }}">
                                {{ strtoupper(substr($provider, 0, 2)) }}
                            </div>
                            <h6 class="mb-1">{{ ucfirst($provider) }}</h6>
                            <small class="text-muted">
                                @switch($provider)
                                    @case('twilio')
                                        Global SMS platform
                                        @break
                                    @case('vonage')
                                        Nexmo SMS service
                                        @break
                                    @case('orange')
                                        Orange SMS API
                                        @break
                                    @case('textmagic')
                                        Text messaging service
                                        @break
                                    @case('clickatell')
                                        SMS gateway
                                        @break
                                    @default
                                        Custom configuration
                                @endswitch
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <input type="hidden" name="provider_type" id="provider_type" required>
                @error('provider_type')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Step 2: Configuration -->
        <div class="config-section" id="config-section">
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
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="api_url" class="form-label">API URL</label>
                                <input type="url" class="form-control @error('api_url') is-invalid @enderror" 
                                       id="api_url" name="api_url" value="{{ old('api_url') }}">
                                @error('api_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="api_key" class="form-label">API Key *</label>
                                <input type="password" class="form-control @error('api_key') is-invalid @enderror" 
                                       id="api_key" name="api_key" value="{{ old('api_key') }}" required>
                                @error('api_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="api_secret" class="form-label">API Secret</label>
                                <input type="password" class="form-control @error('api_secret') is-invalid @enderror" 
                                       id="api_secret" name="api_secret" value="{{ old('api_secret') }}">
                                @error('api_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="sender_id" class="form-label">Sender ID</label>
                                <input type="text" class="form-control @error('sender_id') is-invalid @enderror" 
                                       id="sender_id" name="sender_id" value="{{ old('sender_id') }}" 
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
                                           id="daily_limit" name="daily_limit" value="{{ old('daily_limit', 1000) }}" 
                                           min="1" required>
                                    @error('daily_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="hourly_limit" class="form-label">Hourly Limit *</label>
                                    <input type="number" class="form-control @error('hourly_limit') is-invalid @enderror" 
                                           id="hourly_limit" name="hourly_limit" value="{{ old('hourly_limit', 100) }}" 
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
                                           id="cost_per_sms" name="cost_per_sms" value="{{ old('cost_per_sms') }}" 
                                           step="0.0001" min="0">
                                    @error('cost_per_sms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">Priority *</label>
                                    <input type="number" class="form-control @error('priority') is-invalid @enderror" 
                                           id="priority" name="priority" value="{{ old('priority', 1) }}" 
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
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_default" 
                                           name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        Set as default provider
                                    </label>
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
                                                               id="webhook_url" name="webhook_url" value="{{ old('webhook_url') }}">
                                                        @error('webhook_url')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="timeout" class="form-label">Timeout (seconds)</label>
                                                        <input type="number" class="form-control @error('timeout') is-invalid @enderror" 
                                                               id="timeout" name="timeout" value="{{ old('timeout', 30) }}" 
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
                                                          placeholder='{"key": "value"}'>{!! old('additional_config') !!}</textarea>
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
                            <a href="{{ route('sms.providers.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Provider
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const providerDefaults = {
    twilio: {
        name: 'Twilio',
        api_url: 'https://api.twilio.com',
        daily_limit: 5000,
        hourly_limit: 500
    },
    vonage: {
        name: 'Vonage (Nexmo)',
        api_url: 'https://rest.nexmo.com',
        daily_limit: 3000,
        hourly_limit: 300
    },
    orange: {
        name: 'Orange SMS',
        api_url: 'https://api.orange.com',
        daily_limit: 2000,
        hourly_limit: 200
    },
    textmagic: {
        name: 'TextMagic',
        api_url: 'https://rest.textmagic.com',
        daily_limit: 1000,
        hourly_limit: 100
    },
    clickatell: {
        name: 'Clickatell',
        api_url: 'https://platform.clickatell.com',
        daily_limit: 2500,
        hourly_limit: 250
    },
    custom: {
        name: 'Custom Provider',
        api_url: '',
        daily_limit: 1000,
        hourly_limit: 100
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Handle provider selection
    document.querySelectorAll('.provider-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove previous selection
            document.querySelectorAll('.provider-option').forEach(opt => opt.classList.remove('selected'));
            
            // Select current
            this.classList.add('selected');
            
            const provider = this.dataset.provider;
            document.getElementById('provider_type').value = provider;
            
            // Fill defaults
            if (providerDefaults[provider]) {
                const defaults = providerDefaults[provider];
                Object.keys(defaults).forEach(key => {
                    const input = document.getElementById(key);
                    if (input && !input.value) {
                        input.value = defaults[key];
                    }
                });
            }
            
            // Show configuration section
            const configSection = document.getElementById('config-section');
            configSection.classList.add('show');
            configSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    
    // JSON validation for additional config
    const additionalConfigTextarea = document.getElementById('additional_config');
    if (additionalConfigTextarea) {
        additionalConfigTextarea.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value) {
                try {
                    JSON.parse(value);
                    this.classList.remove('is-invalid');
                } catch (e) {
                    this.classList.add('is-invalid');
                    this.nextElementSibling.textContent = 'Invalid JSON format';
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
    
    // Test connection (you'll need to implement this endpoint)
    fetch('{{ route("sms.providers.test", "temp") }}'.replace('temp', 'test'), {
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
