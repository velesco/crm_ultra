@extends('layouts.app')

@section('title', 'API Keys Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                API Keys Management
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage API keys for external integrations and third-party access</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.api-keys.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New API Key
            </a>
            <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium rounded-md transition-colors" onclick="openExportModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 border-l-4 border-indigo-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide mb-1">
                        Total API Keys
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $statistics['total'] }}</div>
                </div>
                <div class="ml-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-l-4 border-green-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">
                        Active Keys
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $statistics['active'] }}</div>
                </div>
                <div class="ml-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-l-4 border-yellow-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide mb-1">
                        Expired Keys
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $statistics['expired'] }}</div>
                </div>
                <div class="ml-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border-l-4 border-cyan-500 rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <div class="text-xs font-bold text-cyan-600 dark:text-cyan-400 uppercase tracking-wide mb-1">
                        Total API Calls
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($statistics['total_usage']) }}</div>
                </div>
                <div class="ml-4">
                    <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Search & Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.api-keys.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" id="search" 
                               placeholder="Search by name, description..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="environment" class="form-label">Environment</label>
                        <select name="environment" id="environment" class="form-select">
                            <option value="">All Environments</option>
                            <option value="production" {{ request('environment') === 'production' ? 'selected' : '' }}>Production</option>
                            <option value="staging" {{ request('environment') === 'staging' ? 'selected' : '' }}>Staging</option>
                            <option value="development" {{ request('environment') === 'development' ? 'selected' : '' }}>Development</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="expiry" class="form-label">Expiry Status</label>
                        <select name="expiry" id="expiry" class="form-select">
                            <option value="">All</option>
                            <option value="expired" {{ request('expiry') === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="expiring_soon" {{ request('expiry') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                            <option value="never_expires" {{ request('expiry') === 'never_expires' ? 'selected' : '' }}>Never Expires</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="usage" class="form-label">Usage</label>
                        <select name="usage" id="usage" class="form-select">
                            <option value="">All</option>
                            <option value="unused" {{ request('usage') === 'unused' ? 'selected' : '' }}>Unused (30d)</option>
                            <option value="active" {{ request('usage') === 'active' ? 'selected' : '' }}>Active (30d)</option>
                            <option value="high_usage" {{ request('usage') === 'high_usage' ? 'selected' : '' }}>High Usage</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- API Keys Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">API Keys List</h6>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cogs"></i> Bulk Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('activate')">
                        <i class="fas fa-check text-success"></i> Activate Selected
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('deactivate')">
                        <i class="fas fa-pause text-warning"></i> Deactivate Selected
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('suspend')">
                        <i class="fas fa-ban text-danger"></i> Suspend Selected
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="bulkAction('delete')">
                        <i class="fas fa-trash"></i> Delete Selected
                    </a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            @if($apiKeys->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Name</th>
                                <th>Key</th>
                                <th>Status</th>
                                <th>Environment</th>
                                <th>Permissions</th>
                                <th>Usage</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($apiKeys as $apiKey)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $apiKey->id }}">
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $apiKey->name }}</div>
                                        @if($apiKey->description)
                                            <small class="text-muted">{{ Str::limit($apiKey->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="text-muted">{{ $apiKey->masked_key }}</code>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($apiKey->status) {
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'suspended' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ ucfirst($apiKey->status) }}
                                        </span>
                                        @if($apiKey->is_expired)
                                            <span class="badge bg-danger ms-1">Expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $envClass = match($apiKey->environment) {
                                                'production' => 'danger',
                                                'staging' => 'warning',
                                                'development' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $envClass }}">
                                            {{ ucfirst($apiKey->environment) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($apiKey->permissions && count($apiKey->permissions) > 0)
                                            <span class="badge bg-primary">{{ count($apiKey->permissions) }} permissions</span>
                                        @else
                                            <span class="text-muted">No permissions</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ number_format($apiKey->usage_count) }}</span>
                                        @if($apiKey->last_used_at)
                                            <br><small class="text-muted">Last: {{ $apiKey->last_used_at->format('M j, Y') }}</small>
                                        @else
                                            <br><small class="text-muted">Never used</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($apiKey->expires_at)
                                            @if($apiKey->is_expired)
                                                <span class="text-danger">Expired</span>
                                            @else
                                                <span class="text-muted">{{ $apiKey->expires_at->format('M j, Y') }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Never expires</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.api-keys.show', $apiKey) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.api-keys.edit', $apiKey) }}" 
                                               class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.api-keys.destroy', $apiKey) }}" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this API key?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $apiKeys->firstItem() }} to {{ $apiKeys->lastItem() }} of {{ $apiKeys->total() }} results
                    </div>
                    {{ $apiKeys->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-key fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No API Keys Found</h5>
                    <p class="text-muted">No API keys match your current filters.</p>
                    <a href="{{ route('admin.api-keys.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Your First API Key
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export API Keys</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('admin.api-keys.export') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="export_status" class="form-label">Status</label>
                            <select name="status" id="export_status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="export_environment" class="form-label">Environment</label>
                            <select name="environment" id="export_environment" class="form-select">
                                <option value="">All Environments</option>
                                <option value="production">Production</option>
                                <option value="staging">Staging</option>
                                <option value="development">Development</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download"></i> Export CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Bulk actions
function bulkAction(action) {
    const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one API key');
        return;
    }

    const actionText = action === 'delete' ? 'delete' : action;
    if (confirm(`Are you sure you want to ${actionText} ${selectedIds.length} selected API key(s)?`)) {
        fetch('/admin/api-keys/bulk-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                action: action,
                api_keys: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>
@endpush
