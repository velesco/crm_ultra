@extends('layouts.app')

@section('title', 'Edit System Setting')

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
                    <li class="breadcrumb-item"><a href="{{ route('admin.settings.show', $systemSetting) }}">{{ $systemSetting->label }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center">
                <div class="icon-shape bg-gradient-warning text-white rounded-circle me-3">
                    <i class="fas fa-edit fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-0">Edit System Setting</h1>
                    <p class="text-muted mb-0">{{ $systemSetting->label }}</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.settings.show', $systemSetting) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eye me-2"></i>View Details
                </a>
                <a href="{{ route('admin.settings.index', ['group' => $systemSetting->group]) }}" 
                   class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- Setting Status Alerts -->
            @if(!$systemSetting->isEditable())
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-lock me-2"></i>
                    <strong>Read-Only Setting:</strong> This setting cannot be edited due to system restrictions.
                </div>
            @endif
            
            @if($systemSetting->requires_restart)
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Cache Clear Required:</strong> Changes to this setting will require clearing the application cache.
                </div>
            @endif
            
            @if($systemSetting->is_encrypted)
                <div class="alert alert-info mb-4">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Encrypted Setting:</strong> The value will be automatically encrypted when saved.
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Setting Information</h6>
                        <div class="text-muted small">
                            Created: {{ $systemSetting->created_at->format('M j, Y') }}
                            @if($systemSetting->createdBy)
                                by {{ $systemSetting->createdBy->name }}
                            @endif
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="{{ route('admin.settings.update', $systemSetting) }}" id="settingForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="key" class="form-label">Setting Key <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('key') is-invalid @enderror" 
                                           id="key" 
                                           name="key" 
                                           value="{{ old('key', $systemSetting->key) }}"
                                           placeholder="e.g., app.max_upload_size"
                                           pattern="^[a-z0-9._]+$"
                                           required>
                                    <div class="form-text">Use lowercase letters, numbers, dots, and underscores only</div>
                                    @error('key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="label" class="form-label">Display Label <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('label') is-invalid @enderror" 
                                           id="label" 
                                           name="label" 
                                           value="{{ old('label', $systemSetting->label) }}"
                                           placeholder="e.g., Maximum Upload Size"
                                           required>
                                    <div class="form-text">Human-readable name for this setting</div>
                                    @error('label')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="group" class="form-label">Group <span class="text-danger">*</span></label>
                                    <select class="form-select @error('group') is-invalid @enderror" 
                                            id="group" 
                                            name="group" 
                                            required>
                                        <option value="">Select a group...</option>
                                        @foreach($groups as $key => $name)
                                            <option value="{{ $key }}" {{ (old('group', $systemSetting->group) === $key) ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Category to organize this setting</div>
                                    @error('group')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Data Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="">Select data type...</option>
                                        <option value="string" {{ old('type', $systemSetting->type) === 'string' ? 'selected' : '' }}>String</option>
                                        <option value="integer" {{ old('type', $systemSetting->type) === 'integer' ? 'selected' : '' }}>Integer</option>
                                        <option value="boolean" {{ old('type', $systemSetting->type) === 'boolean' ? 'selected' : '' }}>Boolean</option>
                                        <option value="json" {{ old('type', $systemSetting->type) === 'json' ? 'selected' : '' }}>JSON</option>
                                        <option value="text" {{ old('type', $systemSetting->type) === 'text' ? 'selected' : '' }}>Text (Long)</option>
                                        <option value="encrypted" {{ old('type', $systemSetting->type) === 'encrypted' ? 'selected' : '' }}>Encrypted</option>
                                    </select>
                                    <div class="form-text">How the value should be stored and processed</div>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Value Input -->
                        <div class="mb-3">
                            <label for="value" class="form-label">Value</label>
                            
                            <!-- String/Integer/Encrypted input -->
                            <input type="text" 
                                   class="form-control @error('value') is-invalid @enderror" 
                                   id="value-text" 
                                   name="value" 
                                   value="{{ old('value', $systemSetting->is_encrypted ? '' : $systemSetting->value) }}"
                                   placeholder="{{ $systemSetting->is_encrypted ? 'Enter new value (leave empty to keep current)' : 'Enter setting value' }}">
                            
                            <!-- Boolean input -->
                            <select class="form-select @error('value') is-invalid @enderror" 
                                    id="value-boolean" 
                                    name="value" 
                                    style="display: none;">
                                <option value="1" {{ old('value', $systemSetting->value) == '1' ? 'selected' : '' }}>True</option>
                                <option value="0" {{ old('value', $systemSetting->value) == '0' ? 'selected' : '' }}>False</option>
                            </select>
                            
                            <!-- Text/JSON input -->
                            <textarea class="form-control @error('value') is-invalid @enderror" 
                                      id="value-textarea" 
                                      name="value" 
                                      rows="4" 
                                      style="display: none;"
                                      placeholder="Enter setting value...">{{ old('value', $systemSetting->is_encrypted ? '' : ($systemSetting->type === 'json' ? json_encode($systemSetting->value, JSON_PRETTY_PRINT) : $systemSetting->value)) }}</textarea>
                            
                            <div class="form-text" id="value-help">
                                @if($systemSetting->is_encrypted)
                                    Current value is encrypted. Leave empty to keep current value, or enter new value to replace.
                                @else
                                    The current or new value for this setting
                                @endif
                            </div>
                            @error('value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Describe what this setting controls...">{{ old('description', $systemSetting->description) }}</textarea>
                            <div class="form-text">Explain what this setting does and how it affects the system</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Advanced Options -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h6 class="mb-0">
                                    <a class="text-decoration-none" 
                                       data-bs-toggle="collapse" 
                                       href="#advancedOptions" 
                                       role="button" 
                                       aria-expanded="{{ $systemSetting->validation_rules || $systemSetting->options || $systemSetting->sort_order > 0 ? 'true' : 'false' }}">
                                        <i class="fas fa-chevron-{{ $systemSetting->validation_rules || $systemSetting->options || $systemSetting->sort_order > 0 ? 'down' : 'right' }} me-2" id="advanced-toggle"></i>
                                        Advanced Options
                                    </a>
                                </h6>
                            </div>
                            <div class="collapse {{ $systemSetting->validation_rules || $systemSetting->options || $systemSetting->sort_order > 0 ? 'show' : '' }}" id="advancedOptions">
                                <div class="card-body pt-2">
                                    <!-- Validation Rules -->
                                    <div class="mb-3">
                                        <label for="validation_rules" class="form-label">Validation Rules (JSON)</label>
                                        <textarea class="form-control @error('validation_rules') is-invalid @enderror" 
                                                  id="validation_rules" 
                                                  name="validation_rules" 
                                                  rows="3"
                                                  placeholder='{"required": true, "min": 1, "max": 100}'>{{ old('validation_rules', $systemSetting->validation_rules ? json_encode($systemSetting->validation_rules, JSON_PRETTY_PRINT) : '') }}</textarea>
                                        <div class="form-text">Laravel validation rules in JSON format</div>
                                        @error('validation_rules')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Options for select/radio inputs -->
                                    <div class="mb-3">
                                        <label for="options" class="form-label">Options (JSON)</label>
                                        <textarea class="form-control @error('options') is-invalid @enderror" 
                                                  id="options" 
                                                  name="options" 
                                                  rows="3"
                                                  placeholder='{"option1": "Label 1", "option2": "Label 2"}'>{{ old('options', $systemSetting->options ? json_encode($systemSetting->options, JSON_PRETTY_PRINT) : '') }}</textarea>
                                        <div class="form-text">Key-value pairs for select/radio options</div>
                                        @error('options')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Sort Order -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sort_order" class="form-label">Sort Order</label>
                                                <input type="number" 
                                                       class="form-control @error('sort_order') is-invalid @enderror" 
                                                       id="sort_order" 
                                                       name="sort_order" 
                                                       value="{{ old('sort_order', $systemSetting->sort_order) }}"
                                                       min="0">
                                                <div class="form-text">Lower numbers appear first</div>
                                                @error('sort_order')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Checkboxes -->
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="is_public" 
                                                       name="is_public" 
                                                       value="1" 
                                                       {{ old('is_public', $systemSetting->is_public) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_public">
                                                    Public Setting
                                                </label>
                                                <div class="form-text">Can be accessed by non-admin users</div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="is_encrypted" 
                                                       name="is_encrypted" 
                                                       value="1" 
                                                       {{ old('is_encrypted', $systemSetting->is_encrypted) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_encrypted">
                                                    Encrypt Value
                                                </label>
                                                <div class="form-text">Store value encrypted in database</div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="requires_restart" 
                                                       name="requires_restart" 
                                                       value="1" 
                                                       {{ old('requires_restart', $systemSetting->requires_restart) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="requires_restart">
                                                    Requires Restart
                                                </label>
                                                <div class="form-text">Changes require cache clear</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.settings.show', $systemSetting) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Setting
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const valueText = document.getElementById('value-text');
    const valueBoolean = document.getElementById('value-boolean');
    const valueTextarea = document.getElementById('value-textarea');
    const valueHelp = document.getElementById('value-help');
    const advancedToggle = document.getElementById('advanced-toggle');
    
    // Handle data type changes
    typeSelect.addEventListener('change', function() {
        const type = this.value;
        
        // Hide all value inputs
        valueText.style.display = 'none';
        valueBoolean.style.display = 'none';
        valueTextarea.style.display = 'none';
        
        // Reset names to prevent multiple submissions
        valueText.name = '';
        valueBoolean.name = '';
        valueTextarea.name = '';
        
        // Show appropriate input and set name
        switch (type) {
            case 'boolean':
                valueBoolean.style.display = 'block';
                valueBoolean.name = 'value';
                break;
                
            case 'json':
                valueTextarea.style.display = 'block';
                valueTextarea.name = 'value';
                valueTextarea.placeholder = '{"key": "value", "array": [1, 2, 3]}';
                break;
                
            case 'text':
                valueTextarea.style.display = 'block';
                valueTextarea.name = 'value';
                valueTextarea.placeholder = 'Enter long text content...';
                break;
                
            case 'integer':
                valueText.style.display = 'block';
                valueText.name = 'value';
                valueText.type = 'number';
                valueText.placeholder = 'Enter a number';
                break;
                
            case 'encrypted':
                valueText.style.display = 'block';
                valueText.name = 'value';
                valueText.type = 'password';
                valueText.placeholder = 'Enter sensitive data';
                break;
                
            default: // string
                valueText.style.display = 'block';
                valueText.name = 'value';
                valueText.type = 'text';
                valueText.placeholder = 'Enter setting value';
        }
    });
    
    // Trigger initial type change
    if (typeSelect.value) {
        typeSelect.dispatchEvent(new Event('change'));
    }
    
    // Handle advanced options toggle
    const advancedOptionsCollapse = document.getElementById('advancedOptions');
    advancedOptionsCollapse.addEventListener('show.bs.collapse', function() {
        advancedToggle.classList.remove('fa-chevron-right');
        advancedToggle.classList.add('fa-chevron-down');
    });
    
    advancedOptionsCollapse.addEventListener('hide.bs.collapse', function() {
        advancedToggle.classList.remove('fa-chevron-down');
        advancedToggle.classList.add('fa-chevron-right');
    });
    
    // Validate JSON fields
    const jsonFields = ['validation_rules', 'options'];
    jsonFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('blur', function() {
                if (this.value.trim()) {
                    try {
                        JSON.parse(this.value);
                        this.classList.remove('is-invalid');
                        const feedback = this.parentNode.querySelector('.invalid-feedback[data-json]');
                        if (feedback) {
                            feedback.remove();
                        }
                    } catch (e) {
                        this.classList.add('is-invalid');
                        let feedback = this.parentNode.querySelector('.invalid-feedback[data-json]');
                        if (!feedback) {
                            feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback';
                            feedback.dataset.json = 'true';
                            this.parentNode.appendChild(feedback);
                        }
                        feedback.textContent = 'Invalid JSON format: ' + e.message;
                    }
                } else {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentNode.querySelector('.invalid-feedback[data-json]');
                    if (feedback) {
                        feedback.remove();
                    }
                }
            });
        }
    });
    
    // Form submission validation
    document.getElementById('settingForm').addEventListener('submit', function(e) {
        // Confirm changes if requires restart
        const requiresRestart = document.getElementById('requires_restart').checked;
        if (requiresRestart) {
            if (!confirm('This setting requires clearing the application cache. Continue with the update?')) {
                e.preventDefault();
                return false;
            }
        }
    });
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

.form-text {
    font-size: 0.875em;
    color: #6c757d;
}

.card-header h6 {
    color: #495057;
    font-weight: 600;
}

#value-textarea {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.9em;
}

#validation_rules, #options {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.9em;
}

.collapse-toggle {
    transition: transform 0.2s ease;
}
</style>
@endpush
