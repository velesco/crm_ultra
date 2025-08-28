@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="icon-shape bg-gradient-primary text-white rounded-circle me-3">
                    <i class="fas fa-cogs fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-0">System Settings</h1>
                    <p class="text-muted mb-0">Manage global system configuration and preferences</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.settings.create', ['group' => $selectedGroup]) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Setting
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="exportSettings()">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <button type="button" class="btn btn-outline-warning" onclick="clearCache()">
                    <i class="fas fa-sync me-2"></i>Clear Cache
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Settings</h6>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <div class="icon-shape bg-gradient-primary text-white rounded">
                            <i class="fas fa-cog"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Public Settings</h6>
                            <h3 class="mb-0">{{ number_format($stats['public']) }}</h3>
                        </div>
                        <div class="icon-shape bg-gradient-success text-white rounded">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Encrypted Settings</h6>
                            <h3 class="mb-0">{{ number_format($stats['encrypted']) }}</h3>
                        </div>
                        <div class="icon-shape bg-gradient-warning text-white rounded">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Requires Restart</h6>
                            <h3 class="mb-0">{{ number_format($stats['requires_restart']) }}</h3>
                        </div>
                        <div class="icon-shape bg-gradient-danger text-white rounded">
                            <i class="fas fa-power-off"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Sidebar - Settings Groups -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="mb-0">Setting Groups</h6>
                </div>
                <div class="card-body pt-2">
                    <div class="nav nav-pills flex-column">
                        @foreach($groups as $key => $name)
                            <a href="{{ route('admin.settings.index', ['group' => $key, 'search' => $search]) }}" 
                               class="nav-link {{ $selectedGroup === $key ? 'active' : '' }} mb-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-{{ $key === 'general' ? 'cog' : ($key === 'email' ? 'envelope' : ($key === 'sms' ? 'sms' : ($key === 'whatsapp' ? 'whatsapp' : ($key === 'api' ? 'code' : ($key === 'security' ? 'shield-alt' : 'plug'))))) }} me-2"></i>
                                        {{ $name }}
                                    </span>
                                    @if(isset($stats['by_group'][$key]))
                                        <span class="badge bg-secondary">{{ $stats['by_group'][$key] }}</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content - Settings List -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-0">{{ $groups[$selectedGroup] ?? 'All' }} Settings</h6>
                        </div>
                        <div class="col-auto">
                            <!-- Search Form -->
                            <form method="GET" class="d-flex">
                                <input type="hidden" name="group" value="{{ $selectedGroup }}">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" value="{{ $search }}" 
                                           class="form-control" placeholder="Search settings..." style="width: 250px;">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if($search)
                                        <a href="{{ route('admin.settings.index', ['group' => $selectedGroup]) }}" 
                                           class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($settings->count() > 0)
                        <!-- Settings Table -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="40">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="select-all">
                                            </div>
                                        </th>
                                        <th>Setting</th>
                                        <th>Value</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Modified</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($settings as $setting)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input setting-checkbox" 
                                                           type="checkbox" value="{{ $setting->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <strong class="text-dark">{{ $setting->label }}</strong>
                                                    <small class="text-muted">{{ $setting->key }}</small>
                                                    @if($setting->description)
                                                        <small class="text-muted mt-1">{{ Str::limit($setting->description, 80) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="setting-value">
                                                    @if($setting->is_encrypted)
                                                        <span class="text-muted">••••••••</span>
                                                    @elseif($setting->type === 'boolean')
                                                        <span class="badge bg-{{ $setting->value ? 'success' : 'danger' }}">
                                                            {{ $setting->value ? 'Yes' : 'No' }}
                                                        </span>
                                                    @elseif($setting->type === 'json')
                                                        <code class="small">{{ Str::limit(json_encode($setting->value), 40) }}</code>
                                                    @else
                                                        <span class="text-dark">{{ Str::limit($setting->value ?? 'null', 40) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $setting->type }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    @if($setting->is_public)
                                                        <span class="badge bg-success badge-sm">Public</span>
                                                    @else
                                                        <span class="badge bg-secondary badge-sm">Private</span>
                                                    @endif
                                                    
                                                    @if($setting->is_encrypted)
                                                        <span class="badge bg-warning badge-sm">Encrypted</span>
                                                    @endif
                                                    
                                                    @if($setting->requires_restart)
                                                        <span class="badge bg-danger badge-sm">Restart Required</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-muted small">
                                                    {{ $setting->updated_at->format('M j, Y') }}
                                                    @if($setting->updatedBy)
                                                        <br>by {{ $setting->updatedBy->name }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.settings.show', $setting) }}" 
                                                       class="btn btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($setting->isEditable())
                                                        <a href="{{ route('admin.settings.edit', $setting) }}" 
                                                           class="btn btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deleteSetting({{ $setting->id }})" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        <span class="btn btn-outline-secondary disabled" title="Read Only">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($settings->hasPages())
                            <div class="card-footer bg-white border-0">
                                {{ $settings->links() }}
                            </div>
                        @endif
                        
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="icon-shape bg-light text-muted rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; line-height: 80px;">
                                <i class="fas fa-cog fa-2x"></i>
                            </div>
                            <h5 class="text-muted">No Settings Found</h5>
                            <p class="text-muted mb-4">
                                @if($search)
                                    No settings match your search criteria.
                                @else
                                    No settings configured for this group yet.
                                @endif
                            </p>
                            <a href="{{ route('admin.settings.create', ['group' => $selectedGroup]) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add First Setting
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionsForm" method="POST" action="{{ route('admin.settings.bulk-action') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Action</label>
                        <select class="form-select" id="bulkAction" name="action" required>
                            <option value="">Select an action...</option>
                            <option value="delete">Delete Selected</option>
                            <option value="toggle_public">Toggle Public/Private</option>
                            <option value="export">Export Selected</option>
                        </select>
                    </div>
                    <div id="selectedSettings"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Execute Action</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this setting? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Deleting system settings may affect application functionality.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Setting</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const settingCheckboxes = document.querySelectorAll('.setting-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            settingCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Update select all when individual checkboxes change
    settingCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.setting-checkbox:checked');
            selectAllCheckbox.checked = checkedBoxes.length === settingCheckboxes.length;
        });
    });

    // Show bulk actions when settings are selected
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('setting-checkbox')) {
            const checkedBoxes = document.querySelectorAll('.setting-checkbox:checked');
            
            // Show/hide bulk actions button
            let bulkBtn = document.getElementById('bulk-actions-btn');
            if (checkedBoxes.length > 0) {
                if (!bulkBtn) {
                    const toolbar = document.querySelector('.col-auto .btn-group');
                    bulkBtn = document.createElement('button');
                    bulkBtn.id = 'bulk-actions-btn';
                    bulkBtn.className = 'btn btn-outline-secondary';
                    bulkBtn.innerHTML = '<i class="fas fa-tasks me-2"></i>Bulk Actions';
                    bulkBtn.onclick = showBulkActions;
                    toolbar.appendChild(bulkBtn);
                }
            } else if (bulkBtn) {
                bulkBtn.remove();
            }
        }
    });
});

function showBulkActions() {
    const checkedBoxes = document.querySelectorAll('.setting-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one setting.');
        return;
    }
    
    // Update the form with selected settings
    const selectedSettings = Array.from(checkedBoxes).map(cb => cb.value);
    const container = document.getElementById('selectedSettings');
    container.innerHTML = '';
    
    selectedSettings.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'settings[]';
        input.value = id;
        container.appendChild(input);
    });
    
    // Show modal
    new bootstrap.Modal(document.getElementById('bulkActionsModal')).show();
}

function executeBulkAction() {
    const form = document.getElementById('bulkActionsForm');
    const action = document.getElementById('bulkAction').value;
    
    if (!action) {
        alert('Please select an action.');
        return;
    }
    
    if (action === 'delete') {
        if (!confirm('Are you sure you want to delete the selected settings? This action cannot be undone.')) {
            return;
        }
    }
    
    form.submit();
}

function deleteSetting(settingId) {
    document.getElementById('deleteForm').action = `/admin/settings/${settingId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportSettings() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = "{{ route('admin.settings.export') }}?" + params.toString();
}

function clearCache() {
    if (confirm('Are you sure you want to clear the system cache? This will refresh all cached settings.')) {
        fetch("{{ route('admin.settings.clear-cache') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                // Show success toast or notification
                const toast = document.createElement('div');
                toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
                toast.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(toast);
                
                setTimeout(() => toast.remove(), 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to clear cache. Please try again.');
        });
    }
}
</script>
@endpush

@push('styles')
<style>
.icon-shape {
    width: 48px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.setting-value {
    max-width: 200px;
    word-wrap: break-word;
}

.badge-sm {
    font-size: 0.7em;
}

.nav-pills .nav-link {
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.nav-pills .nav-link:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}

.nav-pills .nav-link.active {
    background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary) 100%);
}

.table tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}
</style>
@endpush
