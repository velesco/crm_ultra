@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.user-management.index') }}">User Management</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.user-management.show', $user) }}">{{ $user->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.user-management.show', $user) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View User
            </a>
            <a href="{{ route('admin.user-management.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <form action="{{ route('admin.user-management.update', $user) }}" method="POST" id="editUserForm">
        @csrf
        @method('PUT')
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
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
                                       placeholder="+1 (555) 123-4567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control @error('department') is-invalid @enderror" 
                                       id="department" name="department" value="{{ old('department', $user->department) }}" 
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
                                       id="position" name="position" value="{{ old('position', $user->position) }}" 
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
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-lock"></i> Change Password (Optional)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Leave password fields empty to keep the current password unchanged.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Minimum 8 characters (leave empty to keep current)</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           id="password_confirmation" name="password_confirmation">
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
                                    <i class="fas fa-random"></i> Generate New Password
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
                                                   {{ in_array($role->name, old('roles', $userRoles)) ? 'checked' : '' }}>
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
                                                                       {{ in_array($permission->name, old('permissions', $userPermissions)) ? 'checked' : '' }}>
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
                                      placeholder="Any additional notes or information about this user...">{{ old('notes', $user->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Settings & Actions -->
            <div class="col-lg-4">
                <!-- Current User Info -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-info-circle"></i> Current User Info
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="avatar-lg mb-3">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                                 class="img-fluid rounded-circle" width="80" height="80">
                        </div>
                        <h6 class="mb-1">{{ $user->name }}</h6>
                        <p class="text-muted small mb-2">{{ $user->email }}</p>
                        
                        <div class="mb-2">
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                            
                            @if($user->email_verified_at)
                                <span class="badge bg-info">Verified</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </div>
                        
                        <div class="small text-muted">
                            <div>Created: {{ $user->created_at->format('M d, Y') }}</div>
                            <div>Last Login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</div>
                        </div>
                    </div>
                </div>

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
                                       name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                       {{ ($user->id === auth()->id() || $user->hasRole('super_admin')) ? 'disabled' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active Account</strong>
                                    <div class="small text-muted">User can log in and access the system</div>
                                    @if($user->id === auth()->id())
                                        <div class="small text-warning">Cannot deactivate your own account</div>
                                    @elseif($user->hasRole('super_admin'))
                                        <div class="small text-warning">Cannot deactivate super admin</div>
                                    @endif
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="email_verified" 
                                       name="email_verified" value="1" {{ old('email_verified', $user->email_verified_at ? true : false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_verified">
                                    <strong>Email Verified</strong>
                                    <div class="small text-muted">Mark email as verified</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bolt"></i> Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block mb-2">
                            <i class="fas fa-save"></i> Update User
                        </button>
                        
                        <a href="{{ route('admin.user-management.show', $user) }}" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-eye"></i> View User
                        </a>
                        
                        <a href="{{ route('admin.user-management.index') }}" class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                        
                        @if($user->id !== auth()->id() && !$user->hasRole('super_admin'))
                            <hr>
                            <button type="button" class="btn btn-{{ $user->is_active ? 'warning' : 'success' }} btn-block mb-2" 
                                    onclick="toggleUserStatus({{ $user->id }})">
                                <i class="fas fa-{{ $user->is_active ? 'user-times' : 'user-check' }}"></i> 
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                            </button>
                            
                            <button type="button" class="btn btn-danger btn-block" 
                                    onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Activity Summary -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-chart-line"></i> Activity Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-2">
                                <div class="h5 font-weight-bold text-primary">{{ $user->emailCampaigns()->count() }}</div>
                                <div class="small text-muted">Campaigns</div>
                            </div>
                            <div class="col-6 mb-2">
                                <div class="h5 font-weight-bold text-success">{{ $user->contactsCreated()->count() }}</div>
                                <div class="small text-muted">Contacts</div>
                            </div>
                            <div class="col-6">
                                <div class="h5 font-weight-bold text-info">{{ $user->contactSegments()->count() }}</div>
                                <div class="small text-muted">Segments</div>
                            </div>
                            <div class="col-6">
                                <div class="h5 font-weight-bold text-warning">{{ $user->login_count ?? 0 }}</div>
                                <div class="small text-muted">Logins</div>
                            </div>
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

.btn-block {
    width: 100%;
}

.avatar-lg img {
    object-fit: cover;
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
    alert('New password generated and filled in both fields!');
}

function toggleUserStatus(userId) {
    if (confirm('Are you sure you want to toggle this user\'s status?')) {
        $.ajax({
            url: `/admin/user-management/${userId}/toggle-status`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                alert('Error: ' + (response?.error || 'Failed to update user status'));
            }
        });
    }
}

function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/user-management/${userId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = $('meta[name="csrf-token"]').attr('content');
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}

$(document).ready(function() {
    // Form validation
    $('#editUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const passwordConfirm = $('#password_confirmation').val();
        
        if (password && password !== passwordConfirm) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password && password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return false;
        }
    });
});
</script>
@endpush
