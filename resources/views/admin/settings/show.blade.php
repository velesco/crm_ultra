@extends('layouts.app')

@section('title', 'System Setting Details')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">System Settings</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index', ['group' => $systemSetting->group]) }}">{{ ucfirst($systemSetting->group) }}</a></li>
                    <li class="breadcrumb-item active">{{ $systemSetting->label }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center">
                <div class="icon-shape bg-gradient-primary text-white rounded-circle me-3">
                    <i class="fas fa-cog fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-0">{{ $systemSetting->label }}</h1>
                    <p class="text-muted mb-0">{{ $systemSetting->key }}</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.settings.index', ['group' => $systemSetting->group]) }}" 
                   class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to {{ ucfirst($systemSetting->group) }}
                </a>
                @if($systemSetting->isEditable())
                    <a href="{{ route('admin.settings.edit', $systemSetting) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Setting
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Setting Details -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Setting Details</h6>
                        <div class="d-flex gap-2">
                            @if($systemSetting->is_public)
                                <span class="badge bg-success">Public</span>
                            @else
                                <span class="badge bg-secondary">Private</span>
                            @endif
                            
                            @if($systemSetting->is_encrypted)
                                <span class="badge bg-warning">Encrypted</span>
                            @endif
                            
                            @if($systemSetting->requires_restart)
                                <span class="badge bg-danger">Restart Required</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Key Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label text-muted">Setting Key</label>
                                <div class="d-flex align-items-center">
                                    <code class="bg-light p-2 rounded flex-grow-1">{{ $systemSetting->key }}</code>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                            onclick="copyToClipboard('{{ $systemSetting->key }}')" title="Copy Key">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label text-muted">Data Type</label>
                                <div>
                                    <span class="badge bg-info fs-6">{{ $systemSetting->type }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Value Display -->
                    <div class="mb-4">
                        <label class="form-label text-muted">Current Value</label>
                        <div class="value-display">
                            @if($systemSetting->is_encrypted)
                                <div class="alert alert-warning d-flex align-items-center">
                                    <i class="fas fa-lock me-2"></i>
                                    <span>This value is encrypted and cannot be displayed for security reasons.</span>
                                </div>
                            @elseif($systemSetting->type === 'boolean')
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $systemSetting->value ? 'success' : 'danger' }} me-2 fs-6">
                                        {{ $systemSetting->value ? 'True' : 'False' }}
                                    </span>
                                    <i class="fas fa-{{ $systemSetting->value ? 'check-circle text-success' : 'times-circle text-danger' }}"></i>
                                </div>
                            @elseif($systemSetting->type === 'json')
                                <div class="position-relative">
                                    <pre class="bg-light p-3 rounded language-json"><code id="json-value">{{ json_encode($systemSetting->value, JSON_PRETTY_PRINT) }}</code></pre>
                                    <button type="button" class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2" 
                                            onclick="copyToClipboard(document.getElementById('json-value').textContent)" title="Copy JSON">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            @elseif($systemSetting->type === 'text')
                                <div class="position-relative">
                                    <div class="bg-light p-3 rounded" style="white-space: pre-wrap; max-height: 300px; overflow-y: auto;">{{ $systemSetting->value ?: 'No value set' }}</div>
                                    @if($systemSetting->value)
                                        <button type="button" class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2" 
                                                onclick="copyToClipboard('{{ addslashes($systemSetting->value) }}')" title="Copy Text">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="d-flex align-items-center">
                                    <code class="bg-light p-2 rounded flex-grow-1">{{ $systemSetting->value ?? 'null' }}</code>
                                    @if($systemSetting->value)
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                                onclick="copyToClipboard('{{ addslashes($systemSetting->value) }}')" title="Copy Value">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($systemSetting->description)
                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label text-muted">Description</label>
                            <div class="bg-light p-3 rounded">
                                {{ $systemSetting->description }}
                            </div>
                        </div>
                    @endif

                    <!-- Advanced Configuration -->
                    @if($systemSetting->validation_rules || $systemSetting->options)
                        <div class="card bg-light border-0">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-0">Advanced Configuration</h6>
                            </div>
                            <div class="card-body">
                                @if($systemSetting->validation_rules)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Validation Rules</label>
                                        <pre class="bg-white p-2 rounded border"><code>{{ json_encode($systemSetting->validation_rules, JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                @endif
                                
                                @if($systemSetting->options)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Available Options</label>
                                        <pre class="bg-white p-2 rounded border"><code>{{ json_encode($systemSetting->options, JSON_PRETTY_PRINT) }}</code></pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Information -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">Usage Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted">PHP Access</label>
                                <div class="d-flex align-items-center">
                                    <code class="bg-light p-2 rounded flex-grow-1 small">SystemSetting::get('{{ $systemSetting->key }}')</code>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                            onclick="copyToClipboard('SystemSetting::get(\'{{ $systemSetting->key }}\')')" title="Copy Code">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted">Helper Function</label>
                                <div class="d-flex align-items-center">
                                    <code class="bg-light p-2 rounded flex-grow-1 small">setting('{{ $systemSetting->key }}')</code>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                                            onclick="copyToClipboard('setting(\'{{ $systemSetting->key }}\')')" title="Copy Code">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($systemSetting->is_public)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Public Setting:</strong> This setting can be accessed by non-admin users and may be cached for better performance.
                        </div>
                    @endif
                    
                    @if($systemSetting->requires_restart)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Restart Required:</strong> Changes to this setting require clearing the application cache or restarting the application to take effect.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <!-- Meta Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">Meta Information</h6>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="form-label text-muted">Group</label>
                        <div>
                            <span class="badge bg-secondary fs-6">{{ ucfirst(str_replace('_', ' ', $systemSetting->group)) }}</span>
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <label class="form-label text-muted">Sort Order</label>
                        <div class="text-dark">{{ $systemSetting->sort_order }}</div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <label class="form-label text-muted">Editable</label>
                        <div>
                            @if($systemSetting->isEditable())
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-danger">No</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="info-item mb-3">
                        <label class="form-label text-muted">Created</label>
                        <div class="text-dark">{{ $systemSetting->created_at->format('M j, Y g:i A') }}</div>
                        @if($systemSetting->createdBy)
                            <div class="text-muted small">by {{ $systemSetting->createdBy->name }}</div>
                        @endif
                    </div>
                    
                    <div class="info-item mb-0">
                        <label class="form-label text-muted">Last Modified</label>
                        <div class="text-dark">{{ $systemSetting->updated_at->format('M j, Y g:i A') }}</div>
                        @if($systemSetting->updatedBy)
                            <div class="text-muted small">by {{ $systemSetting->updatedBy->name }}</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($systemSetting->isEditable())
                            <a href="{{ route('admin.settings.edit', $systemSetting) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Setting
                            </a>
                            
                            <button type="button" class="btn btn-outline-danger" onclick="deleteSetting()">
                                <i class="fas fa-trash me-2"></i>Delete Setting
                            </button>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-lock me-2"></i>
                                <small>This setting is read-only and cannot be modified.</small>
                            </div>
                        @endif
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="exportSetting()">
                            <i class="fas fa-download me-2"></i>Export Setting
                        </button>
                        
                        <a href="{{ route('admin.settings.create', ['group' => $systemSetting->group]) }}" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Add Similar Setting
                        </a>
                    </div>
                </div>
            </div>
            
            @if($relatedSettings->count() > 0)
                <!-- Related Settings -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0">Related Settings in {{ ucfirst($systemSetting->group) }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($relatedSettings as $related)
                                <a href="{{ route('admin.settings.show', $related) }}" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <strong class="text-dark">{{ $related->label }}</strong>
                                            <div class="text-muted small">{{ $related->key }}</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-light text-dark">{{ $related->type }}</span>
                                            @if($related->is_encrypted)
                                                <i class="fas fa-lock text-warning ms-1" title="Encrypted"></i>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="{{ route('admin.settings.index', ['group' => $systemSetting->group]) }}" 
                           class="btn btn-sm btn-outline-primary">
                            View All {{ ucfirst($systemSetting->group) }} Settings
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if($systemSetting->isEditable())
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the setting <strong>{{ $systemSetting->label }}</strong>?</p>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone and may affect application functionality.
                    </div>
                    <div class="bg-light p-3 rounded">
                        <strong>Setting:</strong> {{ $systemSetting->key }}<br>
                        <strong>Current Value:</strong> 
                        @if($systemSetting->is_encrypted)
                            ••••••••
                        @else
                            {{ $systemSetting->getFormattedValue() }}
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.settings.destroy', $systemSetting) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Setting
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success toast
        showToast('Copied to clipboard!', 'success');
    }).catch(function(err) {
        console.error('Failed to copy text: ', err);
        showToast('Failed to copy to clipboard', 'error');
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

function deleteSetting() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportSetting() {
    // Create export data
    const settingData = {
        key: '{{ $systemSetting->key }}',
        value: {!! $systemSetting->is_encrypted ? '"[ENCRYPTED]"' : json_encode($systemSetting->value) !!},
        type: '{{ $systemSetting->type }}',
        group: '{{ $systemSetting->group }}',
        label: '{{ $systemSetting->label }}',
        description: {!! json_encode($systemSetting->description) !!},
        validation_rules: {!! json_encode($systemSetting->validation_rules) !!},
        options: {!! json_encode($systemSetting->options) !!},
        is_public: {{ $systemSetting->is_public ? 'true' : 'false' }},
        is_encrypted: {{ $systemSetting->is_encrypted ? 'true' : 'false' }},
        requires_restart: {{ $systemSetting->requires_restart ? 'true' : 'false' }},
        sort_order: {{ $systemSetting->sort_order }}
    };
    
    // Download as JSON
    const dataStr = JSON.stringify(settingData, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `setting-${settingData.key.replace(/\./g, '-')}-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showToast('Setting exported successfully!', 'success');
}

// Enhance JSON syntax highlighting
document.addEventListener('DOMContentLoaded', function() {
    const jsonElement = document.getElementById('json-value');
    if (jsonElement) {
        // Simple JSON syntax highlighting
        let jsonText = jsonElement.textContent;
        jsonText = jsonText
            .replace(/"([^"]+)":/g, '<span style="color: #0066cc;">"$1"</span>:')
            .replace(/: "([^"]*)"/g, ': <span style="color: #008800;">"$1"</span>')
            .replace(/: (true|false|null)/g, ': <span style="color: #cc6600;">$1</span>')
            .replace(/: (\d+)/g, ': <span style="color: #cc0000;">$1</span>');
        
        jsonElement.innerHTML = jsonText;
    }
});
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

.info-item {
    margin-bottom: 1rem;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-item .form-label {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.value-display {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
}

.value-display .alert {
    margin-bottom: 0;
}

pre {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    font-size: 0.875rem;
    margin: 0;
}

code {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.list-group-item-action:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.badge.fs-6 {
    font-size: 0.875rem !important;
}

.position-fixed.alert {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
</style>
@endpush
