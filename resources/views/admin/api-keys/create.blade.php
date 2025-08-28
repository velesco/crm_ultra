@extends('layouts.app')

@section('title', 'Create API Key')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-plus text-primary"></i>
                        Create New API Key
                    </h1>
                    <p class="text-muted mb-0">Generate a new API key for external integrations</p>
                </div>
                <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to API Keys
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.api-keys.store') }}" id="createApiKeyForm">
        @csrf
        
        <!-- Step 1: Basic Information -->
        <div class="card shadow mb-4" id="step1">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <span class="step-indicator">Step 1 of 3:</span> Basic Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">API Key Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="e.g., Mobile App API Key"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Choose a descriptive name to identify this API key</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="environment" class="form-label">Environment <span class="text-danger">*</span></label>
                            <select class="form-select @error('environment') is-invalid @enderror" 
                                    id="environment" 
                                    name="environment" 
                                    required>
                                <option value="">Select Environment</option>
                                <option value="production" {{ old('environment') === 'production' ? 'selected' : '' }}>
                                    Production
                                </option>
                                <option value="staging" {{ old('environment') === 'staging' ? 'selected' : '' }}>
                                    Staging
                                </option>
                                <option value="development" {{ old('environment') === 'development' ? 'selected' : '' }}>
                                    Development
                                </option>
                            </select>
                            @error('environment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Describe the purpose of this API key...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Initial Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="expires_at" class="form-label">Expiration Date (Optional)</label>
                            <input type="datetime-local" 
                                   class="form-control @error('expires_at') is-invalid @enderror" 
                                   id="expires_at" 
                                   name="expires_at" 
                                   value="{{ old('expires_at') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Leave empty for keys that never expire</div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                        Next: Permissions <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Permissions & Scopes -->
        <div class="card shadow mb-4" id="step2" style="display: none;">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <span class="step-indicator">Step 2 of 3:</span> Permissions & Scopes
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-gray-800 mb-3">
                            <i class="fas fa-lock text-primary"></i> API Permissions
                        </h6>
                        <div class="mb-3">
                            @foreach($availablePermissions as $key => $label)
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           value="{{ $key }}" 
                                           id="permission_{{ $key }}" 
                                           name="permissions[]"
                                           {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission_{{ $key }}">
                                        <strong>{{ $key }}</strong>
                                        <br><small class="text-muted">{{ $label }}</small>
                                    </label>
                                </div>
                            @endforeach
                            @error('permissions')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-gray-800 mb-3">
                            <i class="fas fa-layer-group text-primary"></i> Access Scopes
                        </h6>
                        <div class="mb-3">
                            @foreach($availableScopes as $key => $label)
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           value="{{ $key }}" 
                                           id="scope_{{ $key }}" 
                                           name="scopes[]"
                                           {{ in_array($key, old('scopes', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="scope_{{ $key }}">
                                        <strong>{{ $label }}</strong>
                                        <br><small class="text-muted">Access to {{ strtolower($label) }} functionality</small>
                                    </label>
                                </div>
                            @endforeach
                            @error('scopes')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Permissions define specific actions the API key can perform, while scopes determine which modules can be accessed. Select only the minimum required permissions for security.
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)">
                        <i class="fas fa-arrow-left"></i> Previous: Basic Info
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                        Next: Security Settings <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Security Settings -->
        <div class="card shadow mb-4" id="step3" style="display: none;">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <span class="step-indicator">Step 3 of 3:</span> Security & Rate Limiting
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-gray-800 mb-3">
                            <i class="fas fa-shield-alt text-primary"></i> IP Restrictions
                        </h6>
                        <div class="mb-3">
                            <label for="allowed_ips" class="form-label">Allowed IP Addresses (Optional)</label>
                            <textarea class="form-control @error('allowed_ips') is-invalid @enderror" 
                                      id="allowed_ips" 
                                      name="allowed_ips" 
                                      rows="3" 
                                      placeholder="192.168.1.1, 10.0.0.1, 203.0.113.5">{{ old('allowed_ips') }}</textarea>
                            @error('allowed_ips')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter comma-separated IP addresses. Leave empty to allow any IP.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-gray-800 mb-3">
                            <i class="fas fa-tachometer-alt text-primary"></i> Rate Limiting
                        </h6>
                        <div class="mb-3">
                            <label for="rate_limit_per_minute" class="form-label">Requests per Minute</label>
                            <input type="number" 
                                   class="form-control @error('rate_limit_per_minute') is-invalid @enderror" 
                                   id="rate_limit_per_minute" 
                                   name="rate_limit_per_minute" 
                                   value="{{ old('rate_limit_per_minute', 60) }}" 
                                   min="1" 
                                   max="1000"
                                   required>
                            @error('rate_limit_per_minute')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="rate_limit_per_hour" class="form-label">Requests per Hour</label>
                            <input type="number" 
                                   class="form-control @error('rate_limit_per_hour') is-invalid @enderror" 
                                   id="rate_limit_per_hour" 
                                   name="rate_limit_per_hour" 
                                   value="{{ old('rate_limit_per_hour', 1000) }}" 
                                   min="1" 
                                   max="50000"
                                   required>
                            @error('rate_limit_per_hour')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="rate_limit_per_day" class="form-label">Requests per Day</label>
                            <input type="number" 
                                   class="form-control @error('rate_limit_per_day') is-invalid @enderror" 
                                   id="rate_limit_per_day" 
                                   name="rate_limit_per_day" 
                                   value="{{ old('rate_limit_per_day', 10000) }}" 
                                   min="1" 
                                   max="1000000"
                                   required>
                            @error('rate_limit_per_day')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Security Notice:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>The API key will be generated automatically and shown only once after creation</li>
                        <li>Store the API key securely - it cannot be recovered if lost</li>
                        <li>Rate limits help protect against abuse and ensure fair usage</li>
                        <li>IP restrictions add an extra layer of security</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)">
                        <i class="fas fa-arrow-left"></i> Previous: Permissions
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-key"></i> Create API Key
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Progress Indicator -->
    <div class="card shadow mb-4" id="progressCard">
        <div class="card-body py-2">
            <div class="row text-center">
                <div class="col-4">
                    <div class="progress-step active" id="progress1">
                        <i class="fas fa-info-circle"></i>
                        <span>Basic Info</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="progress-step" id="progress2">
                        <i class="fas fa-lock"></i>
                        <span>Permissions</span>
                    </div>
                </div>
                <div class="col-4">
                    <div class="progress-step" id="progress3">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.progress-step {
    padding: 10px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.progress-step.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: scale(1.05);
}

.progress-step i {
    display: block;
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.progress-step span {
    font-size: 0.9rem;
    font-weight: 600;
}

.step-indicator {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: bold;
}

.form-check {
    padding: 8px;
    margin-bottom: 8px;
    border: 1px solid #e3e6f0;
    border-radius: 5px;
    transition: all 0.2s ease;
}

.form-check:hover {
    background-color: #f8f9fc;
    border-color: #d1d3e2;
}

.form-check-input:checked + .form-check-label {
    color: #5a5c69;
}
</style>
@endpush

@push('scripts')
<script>
let currentStep = 1;

function nextStep(step) {
    // Validate current step
    if (!validateStep(currentStep)) {
        return;
    }

    // Hide current step
    document.getElementById('step' + currentStep).style.display = 'none';
    document.getElementById('progress' + currentStep).classList.remove('active');

    // Show next step
    currentStep = step;
    document.getElementById('step' + currentStep).style.display = 'block';
    document.getElementById('progress' + currentStep).classList.add('active');
}

function prevStep(step) {
    // Hide current step
    document.getElementById('step' + currentStep).style.display = 'none';
    document.getElementById('progress' + currentStep).classList.remove('active');

    // Show previous step
    currentStep = step;
    document.getElementById('step' + currentStep).style.display = 'block';
    document.getElementById('progress' + currentStep).classList.add('active');
}

function validateStep(step) {
    let isValid = true;
    
    if (step === 1) {
        // Validate basic information
        const name = document.getElementById('name').value.trim();
        const environment = document.getElementById('environment').value;
        const status = document.getElementById('status').value;
        
        if (!name) {
            showFieldError('name', 'API Key name is required');
            isValid = false;
        }
        if (!environment) {
            showFieldError('environment', 'Environment is required');
            isValid = false;
        }
        if (!status) {
            showFieldError('status', 'Status is required');
            isValid = false;
        }
    }
    
    return isValid;
}

function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('is-invalid');
    
    // Remove existing error message
    const existingError = field.parentNode.querySelector('.invalid-feedback.custom-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback custom-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
    
    // Remove error on input
    field.addEventListener('input', function() {
        this.classList.remove('is-invalid');
        const errorMsg = this.parentNode.querySelector('.invalid-feedback.custom-error');
        if (errorMsg) {
            errorMsg.remove();
        }
    }, { once: true });
}

// Auto-calculate rate limits based on environment
document.getElementById('environment').addEventListener('change', function() {
    const environment = this.value;
    const minuteField = document.getElementById('rate_limit_per_minute');
    const hourField = document.getElementById('rate_limit_per_hour');
    const dayField = document.getElementById('rate_limit_per_day');
    
    switch(environment) {
        case 'production':
            minuteField.value = 100;
            hourField.value = 5000;
            dayField.value = 50000;
            break;
        case 'staging':
            minuteField.value = 60;
            hourField.value = 1000;
            dayField.value = 10000;
            break;
        case 'development':
            minuteField.value = 30;
            hourField.value = 500;
            dayField.value = 5000;
            break;
    }
});

// Form submission
document.getElementById('createApiKeyForm').addEventListener('submit', function(e) {
    // Final validation
    if (!validateStep(1)) {
        e.preventDefault();
        nextStep(1);
        return;
    }
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating API Key...';
    submitButton.disabled = true;
    
    // Reset button if there are validation errors (will be handled by Laravel)
    setTimeout(() => {
        if (submitButton.disabled) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    }, 5000);
});
</script>
@endpush
