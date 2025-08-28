@extends('layouts.app')

@section('title', 'Create New User')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create New User</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.user-management.index') }}">User Management</a></li>
                    <li class="breadcrumb-item active">Create User</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.user-management.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <form action="{{ route('admin.user-management.store') }}" method="POST" id="createUserForm">
        @csrf
        <div class="row">
            <!-- Left Column - Basic Information -->
            <div class="col-lg-8">
                <!-- Basic Information Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user"></i> Basic Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" 
                                       placeholder="+1 (555) 123-4567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control @error('department') is-invalid @enderror" 
                                       id="department" name="department" value="{{ old('department') }}" 
                                       placeholder="e.g., Marketing, Sales, IT">
                                @error('department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="position" class="form-label">Position/Job Title</label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror" 
                                       id="position" name="position" value="{{ old('position') }}" 
                                       placeholder="e.g., Marketing Manager, Sales Representative">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-lock"></i> Password Setup
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Minimum 8 characters</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           id="password_confirmation" name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-eye"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="generatePassword()">
                                    <i class="fas fa-random"></i> Generate Secure Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles & Permissions Section -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-shield"></i> Roles & Permissions
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Roles Section -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Roles</h6>
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="roles[]" value="{{ $role->name }}" 
                                                   id="role_{{ $role->id }}"
                                                   {{ collect(old('roles', []))->contains($role->name) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                <span class="fw-bold">{{ ucfirst($role->name) }}</span>
                                                @if($role->description)
                                                    <div class="small text-muted">{{ $role->description }}</div>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Direct Permissions Section -->
                        <div class="mb-3">
                            <h6 class="text-muted mb-3">Additional Direct Permissions</h6>
                            <div class="accordion" id="permissionsAccordion">
                                @foreach($permissions as $category => $categoryPermissions)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                            <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}" 
                                                    aria-expanded="false" aria-controls="collapse{{ $loop->index }}">
                                                {{ ucfirst($category) }} Permissions ({{ $categoryPermissions->count() }})
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse" 
                                             aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#permissionsAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    @foreach($categoryPermissions as $permission)
                                                        <div class="col-md-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       name="permissions[]" value="{{ $permission->name }}" 
                                                                       id="permission_{{ $permission->id }}"
                                                                       {{ collect(old('permissions', []))->contains($permission->name) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                    {{ str_replace('_', ' ', ucfirst($permission->name)) }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-sticky-note"></i> Additional Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Any additional notes or information about this user...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Settings & Actions -->
            <div class="col-lg-4">
                <!-- Account Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cogs"></i> Account Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Account</strong>
                                    <div class="small text-muted">User can log in and access the system</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="email_verified" 
                                       name="email_verified" value="1" {{ old('email_verified') ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_verified">
                                    <strong>Email Verified</strong>
                                    <div class="small text-muted">Mark email as verified (skip verification email)</div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="send_welcome_email" 
                                       name="send_welcome_email" value="1" {{ old('send_welcome_email', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="send_welcome_email">
                                    <strong>Send Welcome Email</strong>
                                    <div class="small text-muted">Send account details and welcome message</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-user-plus"></i> Create User
                        </button>
                        
                        <button type="button" class="btn btn-success btn-block mb-2" onclick="createAndAddAnother()">
                            <i class="fas fa-plus"></i> Create & Add Another
                        </button>
                        
                        <a href="{{ route('admin.user-management.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>

                <!-- Help & Tips -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-info-circle"></i> Help & Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="small text-muted">
                            <p><strong>Password Requirements:</strong></p>
                            <ul class="mb-2">
                                <li>Minimum 8 characters</li>
                                <li>Must be confirmed</li>
                            </ul>
                            
                            <p><strong>Roles vs Permissions:</strong></p>
                            <ul class="mb-2">
                                <li><strong>Roles</strong> are predefined groups of permissions</li>
                                <li><strong>Direct permissions</strong> are additional specific permissions</li>
                                <li>Users inherit all permissions from their assigned roles</li>
                            </ul>
                            
                            <p><strong>Account Status:</strong></p>
                            <ul class="mb-0">
                                <li><strong>Active:</strong> User can log in normally</li>
                                <li><strong>Inactive:</strong> User cannot log in</li>
                                <li><strong>Unverified:</strong> User must verify email first</li>
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
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
}

.form-check {
    padding-left: 1.5rem;
}

.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fc;
    color: #5a5c69;
}

.btn-block {
    width: 100%;
}

.input-group .btn {
    border-left: none;
}

.generated-password {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    border-radius: 0.25rem;
    padding: 0.5rem;
    margin-top: 0.5rem;
    font-family: monospace;
    display: none;
}
</style>
@endpush

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + '-eye');
    
    if (field.type === 'password') {
        field.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}

function generatePassword() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('password_confirmation').value = password;
    
    // Show generated password for copying
    if (!document.getElementById('generated-password-display')) {
        const display = document.createElement('div');
        display.id = 'generated-password-display';
        display.className = 'generated-password';
        display.innerHTML = `
            <strong>Generated Password:</strong> <code>${password}</code>
            <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="copyPassword('${password}')">
                <i class="fas fa-copy"></i> Copy
            </button>
        `;
        document.querySelector('[onclick="generatePassword()"]').parentNode.appendChild(display);
        display.style.display = 'block';
    }
}

function copyPassword(password) {
    navigator.clipboard.writeText(password).then(function() {
        alert('Password copied to clipboard!');
    });
}

function createAndAddAnother() {
    const form = document.getElementById('createUserForm');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'add_another';
    input.value = '1';
    form.appendChild(input);
    form.submit();
}

$(document).ready(function() {
    // Form validation
    $('#createUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const passwordConfirm = $('#password_confirmation').val();
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return false;
        }
    });
    
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = getPasswordStrength(password);
        updatePasswordStrength(strength);
    });
});

function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    return strength;
}

function updatePasswordStrength(strength) {
    const colors = ['danger', 'danger', 'warning', 'info', 'success', 'success'];
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    
    let indicator = $('#password-strength');
    if (indicator.length === 0) {
        indicator = $('<div id="password-strength" class="form-text"></div>');
        $('#password').parent().after(indicator);
    }
    
    if (strength > 0) {
        indicator.html(`Password Strength: <span class="text-${colors[strength]}">${labels[strength]}</span>`);
    } else {
        indicator.html('');
    }
}
</script>
@endpush
