@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">User Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.user-management.index') }}">User Management</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.user-management.edit', $user) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <a href="{{ route('admin.user-management.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - User Info -->
        <div class="col-lg-4">
            <!-- User Profile Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                id="userActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="userActionsDropdown">
                            <li><a class="dropdown-item" href="{{ route('admin.user-management.edit', $user) }}">
                                <i class="fas fa-edit"></i> Edit User</a></li>
                            @if($user->id !== auth()->id() && !$user->hasRole('super_admin'))
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="toggleUserStatus({{ $user->id }})">
                                    <i class="fas fa-{{ $user->is_active ? 'user-times' : 'user-check' }}"></i> 
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }} User</a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                    <i class="fas fa-trash"></i> Delete User</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-lg mb-3">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                             class="img-fluid rounded-circle" width="120" height="120">
                    </div>
                    <h5 class="font-weight-bold mb-2">{{ $user->name }}</h5>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    <!-- Status Badges -->
                    <div class="mb-3">
                        @if($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                        
                        @if($user->email_verified_at)
                            <span class="badge bg-info">Verified</span>
                        @else
                            <span class="badge bg-warning">Pending Verification</span>
                        @endif
                    </div>

                    <!-- User Details -->
                    <div class="text-left">
                        @if($user->phone)
                            <div class="mb-2">
                                <i class="fas fa-phone text-muted me-2"></i>
                                <span>{{ $user->phone }}</span>
                            </div>
                        @endif
                        
                        @if($user->department)
                            <div class="mb-2">
                                <i class="fas fa-building text-muted me-2"></i>
                                <span>{{ $user->department }}</span>
                            </div>
                        @endif
                        
                        @if($user->position)
                            <div class="mb-2">
                                <i class="fas fa-briefcase text-muted me-2"></i>
                                <span>{{ $user->position }}</span>
                            </div>
                        @endif
                        
                        <div class="mb-2">
                            <i class="fas fa-calendar-alt text-muted me-2"></i>
                            <span>Joined {{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        @if($user->last_login_at)
                            <div class="mb-2">
                                <i class="fas fa-clock text-muted me-2"></i>
                                <span>Last login {{ $user->last_login_at->diffForHumans() }}</span>
                            </div>
                        @else
                            <div class="mb-2">
                                <i class="fas fa-clock text-muted me-2"></i>
                                <span class="text-muted">Never logged in</span>
                            </div>
                        @endif
                    </div>

                    @if($user->notes)
                        <div class="mt-3 pt-3 border-top">
                            <h6 class="text-muted mb-2">Notes</h6>
                            <p class="small text-left">{{ $user->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Roles & Permissions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Roles & Permissions</h6>
                </div>
                <div class="card-body">
                    <!-- Roles -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Roles</h6>
                        @if($user->roles->count() > 0)
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary me-1 mb-1">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        @else
                            <span class="badge bg-secondary">No Roles Assigned</span>
                        @endif
                    </div>

                    <!-- Direct Permissions -->
                    <div>
                        <h6 class="text-muted mb-2">Direct Permissions</h6>
                        @if($user->permissions->count() > 0)
                            @foreach($user->permissions as $permission)
                                <span class="badge bg-success me-1 mb-1">{{ $permission->name }}</span>
                            @endforeach
                        @else
                            <span class="badge bg-secondary">No Direct Permissions</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activity Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 font-weight-bold text-primary">{{ $stats['email_campaigns'] }}</div>
                            <div class="small text-muted">Email Campaigns</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 font-weight-bold text-success">{{ $stats['contacts_created'] }}</div>
                            <div class="small text-muted">Contacts Created</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 font-weight-bold text-info">{{ $stats['segments_created'] }}</div>
                            <div class="small text-muted">Segments Created</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 font-weight-bold text-warning">{{ $stats['total_logins'] }}</div>
                            <div class="small text-muted">Total Logins</div>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="small text-muted text-center">
                            <div>Account Age: {{ $stats['account_age'] }}</div>
                            <div>Last Login: {{ $stats['last_login'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Activity Feed -->
        <div class="col-lg-8">
            <!-- Recent Activity Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshActivity()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="activity-container">
                        @if($recentActivity->count() > 0)
                            <div class="timeline">
                                @foreach($recentActivity as $activity)
                                    <div class="timeline-item mb-3">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center">
                                                    <i class="{{ $activity['icon'] }} {{ $activity['color'] }}"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="fw-bold">{{ $activity['title'] }}</div>
                                                <div class="small text-muted">{{ $activity['date']->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                                <h5>No Recent Activity</h5>
                                <p class="text-muted">This user hasn't performed any tracked activities yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- System Information Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold">User ID:</td>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Created:</td>
                                    <td>{{ $user->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Updated:</td>
                                    <td>{{ $user->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @if($user->email_verified_at)
                                    <tr>
                                        <td class="fw-bold">Email Verified:</td>
                                        <td>{{ $user->email_verified_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                @if($user->createdBy)
                                    <tr>
                                        <td class="fw-bold">Created By:</td>
                                        <td>
                                            <a href="{{ route('admin.user-management.show', $user->createdBy) }}" 
                                               class="text-decoration-none">
                                                {{ $user->createdBy->name }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                @if($user->updatedBy)
                                    <tr>
                                        <td class="fw-bold">Updated By:</td>
                                        <td>
                                            <a href="{{ route('admin.user-management.show', $user->updatedBy) }}" 
                                               class="text-decoration-none">
                                                {{ $user->updatedBy->name }}
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                                @if($user->timezone)
                                    <tr>
                                        <td class="fw-bold">Timezone:</td>
                                        <td>{{ $user->timezone }}</td>
                                    </tr>
                                @endif
                                @if($user->language)
                                    <tr>
                                        <td class="fw-bold">Language:</td>
                                        <td>{{ $user->language }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-lg img {
    object-fit: cover;
}

.avatar-sm {
    width: 40px;
    height: 40px;
}

.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -15px;
    width: 2px;
    background-color: #e3e6f0;
}

.badge {
    font-size: 0.75rem;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
}
</style>
@endpush

@push('scripts')
<script>
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

function refreshActivity() {
    const container = $('#activity-container');
    const button = $('[onclick="refreshActivity()"]');
    const icon = button.find('i');
    
    // Show loading state
    icon.addClass('fa-spin');
    button.prop('disabled', true);
    
    $.ajax({
        url: `/admin/user-management/{{ $user->id }}/activity`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let html = '';
                if (response.activity.length > 0) {
                    html = '<div class="timeline">';
                    response.activity.forEach(function(activity) {
                        html += `
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center">
                                            <i class="${activity.icon} ${activity.color}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="fw-bold">${activity.title}</div>
                                        <div class="small text-muted">${activity.formatted_date}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    html = `
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                            <h5>No Recent Activity</h5>
                            <p class="text-muted">This user hasn't performed any tracked activities yet.</p>
                        </div>
                    `;
                }
                container.html(html);
            }
        },
        error: function(xhr) {
            console.error('Failed to refresh activity:', xhr);
        },
        complete: function() {
            // Remove loading state
            icon.removeClass('fa-spin');
            button.prop('disabled', false);
        }
    });
}
</script>
@endpush
