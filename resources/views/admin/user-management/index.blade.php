@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <!-- Header with actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">User Management</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active">User Management</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                <i class="fas fa-tasks"></i> Bulk Actions
            </button>
            <a href="{{ route('admin.user-management.export') }}" class="btn btn-outline-success">
                <i class="fas fa-download"></i> Export CSV
            </a>
            <a href="{{ route('admin.user-management.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Create User
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6">
            <div class="card border-left-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-left-success shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-left-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Inactive</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactive_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-left-info shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_verification'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-left-secondary shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['new_this_month'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-left-dark shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['roles_distribution']->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.user-management.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Name, email, phone...">
                </div>

                <div class="col-md-2">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-control" id="role" name="role">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Verification</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            @if(request()->hasAny(['search', 'role', 'status', 'date_from', 'date_to']))
                <div class="mt-2">
                    <a href="{{ route('admin.user-management.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Users ({{ $users->total() }})</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="sortDropdown" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-sort"></i> Sort
                </button>
                <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => 'asc']) }}">
                        Name (A-Z)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => 'desc']) }}">
                        Name (Z-A)</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'desc']) }}">
                        Newest First</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'asc']) }}">
                        Oldest First</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'last_login_at', 'sort_order' => 'desc']) }}">
                        Recent Login</a></li>
                </ul>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="50">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th>User</th>
                                <th>Roles</th>
                                <th>Department</th>
                                <th>Activity</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input user-checkbox" type="checkbox" 
                                                   value="{{ $user->id }}" name="user_ids[]">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                                                     class="img-fluid rounded-circle" width="40" height="40">
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <div class="small text-muted">{{ $user->email }}</div>
                                                @if($user->phone)
                                                    <div class="small text-muted">{{ $user->phone }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->roles->count() > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary me-1">{{ ucfirst($role->name) }}</span>
                                            @endforeach
                                        @else
                                            <span class="badge bg-secondary">No Role</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->department)
                                            <div class="fw-bold">{{ $user->department }}</div>
                                        @endif
                                        @if($user->position)
                                            <div class="small text-muted">{{ $user->position }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><strong>Campaigns:</strong> {{ $user->email_campaigns_count }}</div>
                                            <div><strong>Contacts:</strong> {{ $user->contacts_created_count }}</div>
                                            <div><strong>Segments:</strong> {{ $user->contact_segments_count }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($user->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                            
                                            @if($user->email_verified_at)
                                                <span class="badge bg-info mt-1">Verified</span>
                                            @else
                                                <span class="badge bg-warning mt-1">Pending</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div>{{ $user->created_at->format('M d, Y') }}</div>
                                            <div class="text-muted">{{ $user->created_at->diffForHumans() }}</div>
                                            @if($user->last_login_at)
                                                <div class="text-success">Last: {{ $user->last_login_at->diffForHumans() }}</div>
                                            @else
                                                <div class="text-muted">Never logged in</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.user-management.show', $user) }}" 
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.user-management.edit', $user) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->id !== auth()->id() && !$user->hasRole('super_admin'))
                                                <button type="button" class="btn btn-sm btn-outline-{{ $user->is_active ? 'danger' : 'success' }}" 
                                                        onclick="toggleUserStatus({{ $user->id }})" title="Toggle Status">
                                                    <i class="fas fa-{{ $user->is_active ? 'user-times' : 'user-check' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <div class="text-muted">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} 
                        of {{ $users->total() }} results
                    </div>
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                    <h5>No Users Found</h5>
                    <p class="text-muted">No users match your current filters.</p>
                    @if(request()->hasAny(['search', 'role', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('admin.user-management.index') }}" class="btn btn-outline-primary">
                            Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bulkActionForm" method="POST" action="{{ route('admin.user-management.bulk-action') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkActionModalLabel">Bulk Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulk_action" class="form-label">Select Action</label>
                        <select class="form-control" id="bulk_action" name="action" required>
                            <option value="">Choose action...</option>
                            <option value="activate">Activate Users</option>
                            <option value="deactivate">Deactivate Users</option>
                            <option value="delete">Delete Users</option>
                            <option value="assign_role">Assign Role</option>
                            <option value="remove_role">Remove Role</option>
                        </select>
                    </div>

                    <div class="mb-3" id="role_selection" style="display: none;">
                        <label for="bulk_role" class="form-label">Select Role</label>
                        <select class="form-control" id="bulk_role" name="role">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Selected users: <span id="selectedCount">0</span>
                    </div>

                    <div class="alert alert-warning" id="warningAlert" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="warningText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="bulkActionSubmit" disabled>
                        Execute Action
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.avatar-sm img {
    object-fit: cover;
}

.badge {
    font-size: 0.7rem;
}

.table th {
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
}

.border-left-dark {
    border-left: 0.25rem solid #343a40 !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select All functionality
    $('#selectAll').change(function() {
        $('.user-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });

    $('.user-checkbox').change(function() {
        updateSelectedCount();
        $('#selectAll').prop('checked', $('.user-checkbox:checked').length === $('.user-checkbox').length);
    });

    // Bulk action modal
    $('#bulk_action').change(function() {
        const action = $(this).val();
        const roleSelection = $('#role_selection');
        const warningAlert = $('#warningAlert');
        const warningText = $('#warningText');

        if (action === 'assign_role' || action === 'remove_role') {
            roleSelection.show();
        } else {
            roleSelection.hide();
        }

        // Show warnings for destructive actions
        if (action === 'delete') {
            warningText.text('This action cannot be undone. Deleted users will be permanently removed.');
            warningAlert.show();
        } else if (action === 'deactivate') {
            warningText.text('Deactivated users will not be able to log in until reactivated.');
            warningAlert.show();
        } else {
            warningAlert.hide();
        }
    });

    // Bulk action form submission
    $('#bulkActionForm').submit(function(e) {
        const selectedUsers = $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedUsers.length === 0) {
            e.preventDefault();
            alert('Please select at least one user.');
            return false;
        }

        // Add selected user IDs to form
        selectedUsers.forEach(function(userId) {
            $('<input>').attr({
                type: 'hidden',
                name: 'user_ids[]',
                value: userId
            }).appendTo('#bulkActionForm');
        });

        return confirm('Are you sure you want to perform this action on ' + selectedUsers.length + ' user(s)?');
    });
});

function updateSelectedCount() {
    const count = $('.user-checkbox:checked').length;
    $('#selectedCount').text(count);
    $('#bulkActionSubmit').prop('disabled', count === 0);
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
</script>
@endpush
