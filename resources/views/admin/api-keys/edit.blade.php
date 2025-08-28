@extends('layouts.app')

@section('title', 'Edit API Key - ' . $apiKey->name)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit text-primary"></i>
                        Edit API Key: {{ $apiKey->name }}
                    </h1>
                    <p class="text-muted mb-0">Update API key settings and permissions</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.api-keys.show', $apiKey) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.api-keys.update', $apiKey) }}" id="editApiKeyForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Left Column - Main Settings -->
            <div class="col-lg-8">
                <!-- Basic Information Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle"></i> Basic Information
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
                                           value="{{ old('name', $apiKey->name) }}" 
                                           placeholder="e.g., Mobile App API Key"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                        <option value="production" {{ old('environment', $apiKey->environment) === 'production' ? 'selected' : '' }}>
                                            Production
                                        </option>
                                        <option value="staging" {{ old('environment', $apiKey->environment) === 'staging' ? 'selected' : '' }}>
                                            Staging
                                        </option>
                                        <option value="development" {{ old('environment', $apiKey->environment) === 'development' ? 'selected' : '' }}>
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
                                              placeholder="Describe the purpose of this API key...">{{ old('description', $apiKey->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="active" {{ old('status', $apiKey->status) === 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>
                                        <option value="inactive" {{ old('status', $apiKey->status) === 'inactive' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                        <option value="suspended" {{ old('status', $apiKey->status) === 'suspended' ? 'selected' : '' }}>
                                            Suspended
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
                                           value="{{ old('expires_at', $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d\TH:i') : '') }}"
                                           min="{{ now()->format('Y-m-d\TH:i') }}">
                                    @error('expires_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Leave empty for keys that never expire</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions & Scopes Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-lock"></i> Permissions & Scopes
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
                                                   {{ in_array($key, old('permissions', $apiKey->permissions ?? [])) ? 'checked' : '' }}>
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
                                                   {{ in_array($key, old('scopes', $apiKey->scopes ?? [])) ? 'checked' : '' }}>
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
                    </div>
                </div>

                <!-- Security Settings Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-shield-alt"></i> Security & Rate Limiting
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
                                              placeholder="192.168.1.1, 10.0.0.1, 203.0.113.5">{{ old('allowed_ips', $apiKey->allowed_ips) }}</textarea>
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
                                           value="{{ old('rate_limit_per_minute', $apiKey->rate_limit_per_minute) }}" 
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
                                           value="{{ old('rate_limit_per_hour', $apiKey->rate_limit_per_hour) }}" 
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
                                           value="{{ old('rate_limit_per_day', $apiKey->rate_limit_per_day) }}" 
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
                                <li>Rate limits help protect against abuse and ensure fair usage</li>
                                <li>IP restrictions add an extra layer of security</li>
                                <li>Changes will take effect immediately after saving</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Information & Actions -->
            <div class="col-lg-4">
                <!-- Current API Key Info Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-key"></i> Current API Key
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Masked Key:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $apiKey->masked_key }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="copyToClipboard('{{ $apiKey->masked_key }}')" 
                                        title="Copy to clipboard">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status:</label>
                            <br>
                            @php
                                $statusClass = match($apiKey->status) {
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'suspended' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($apiKey->status) }}</span>
                            @if($apiKey->is_expired)
                                <span class="badge bg-danger ms-1">Expired</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Usage:</label>
                            <p class="mb-0">{{ number_format($apiKey->usage_count) }} total calls</p>
                            @if($apiKey->last_used_at)
                                <small class="text-muted">Last used {{ $apiKey->last_used_at->diffForHumans() }}</small>
                            @else
                                <small class="text-muted">Never used</small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tools"></i> Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>

                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset Form
                            </button>

                            <hr>

                            <a href="{{ route('admin.api-keys.show', $apiKey) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye"></i> View Details
                            </a>

                            <form method="POST" action="{{ route('admin.api-keys.regenerate', $apiKey) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm w-100" 
                                        onclick="return confirm('Are you sure? This will generate a new API key and invalidate the current one.')">
                                    <i class="fas fa-sync"></i> Regenerate Key
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-question-circle"></i> Help & Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="fw-bold">Environment Guidelines:</h6>
                            <ul class="small mb-0">
                                <li><strong>Production:</strong> Live applications, highest security</li>
                                <li><strong>Staging:</strong> Testing environment, moderate security</li>
                                <li><strong>Development:</strong> Development only, lower limits</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold">Rate Limiting:</h6>
                            <ul class="small mb-0">
                                <li>Set appropriate limits to prevent abuse</li>
                                <li>Higher limits for production environments</li>
                                <li>Monitor usage patterns regularly</li>
                            </ul>
                        </div>

                        <div>
                            <h6 class="fw-bold">Security Tips:</h6>
                            <ul class="small mb-0">
                                <li>Use IP restrictions when possible</li>
                                <li>Grant minimum required permissions</li>
                                <li>Set expiration dates for temporary access</li>
                                <li>Rotate keys regularly for security</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
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

.card-header h6 {
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
<script>
// Copy to clipboard functionality
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>Copied to clipboard!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        document.body.appendChild(toast);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }).catch(function() {
        alert('Failed to copy to clipboard');
    });
}

// Reset form to original values
function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.getElementById('editApiKeyForm').reset();
        
        // Reset checkboxes to their original state
        @if($apiKey->permissions)
            const originalPermissions = @json($apiKey->permissions);
            document.querySelectorAll('input[name="permissions[]"]').forEach(checkbox => {
                checkbox.checked = originalPermissions.includes(checkbox.value);
            });
        @endif

        @if($apiKey->scopes)
            const originalScopes = @json($apiKey->scopes);
            document.querySelectorAll('input[name="scopes[]"]').forEach(checkbox => {
                checkbox.checked = originalScopes.includes(checkbox.value);
            });
        @endif
    }
}

// Form submission with loading state
document.getElementById('editApiKeyForm').addEventListener('submit', function(e) {
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving Changes...';
    submitButton.disabled = true;
    
    // Reset button if there are validation errors (will be handled by Laravel)
    setTimeout(() => {
        if (submitButton.disabled) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    }, 5000);
});

// Auto-calculate rate limits based on environment
document.getElementById('environment').addEventListener('change', function() {
    const environment = this.value;
    const minuteField = document.getElementById('rate_limit_per_minute');
    const hourField = document.getElementById('rate_limit_per_hour');
    const dayField = document.getElementById('rate_limit_per_day');
    
    // Only update if current values are defaults or empty
    const isDefaultValues = (
        minuteField.value == 60 || minuteField.value == 100 || minuteField.value == 30 || minuteField.value == '' ||
        hourField.value == 1000 || hourField.value == 5000 || hourField.value == 500 || hourField.value == '' ||
        dayField.value == 10000 || dayField.value == 50000 || dayField.value == 5000 || dayField.value == ''
    );
    
    if (isDefaultValues && confirm('Update rate limits based on the selected environment?')) {
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
    }
});
</script>
@endpush
