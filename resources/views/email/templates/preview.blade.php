@extends('layouts.app')

@section('title', 'Preview Template - ' . $emailTemplate->name)

@push('styles')
<style>
    .preview-container {
        max-height: 80vh;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
    }
    
    .variable-badge {
        display: inline-block;
        background-color: #3b82f6;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        margin: 2px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Preview Template</h1>
                    <p class="text-muted mb-0">{{ $emailTemplate->name }}</p>
                </div>
                <div>
                    <a href="{{ route('email.templates.index') }}" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Templates
                    </a>
                    <a href="{{ route('email.templates.edit', $emailTemplate) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Variables Panel -->
        @if(count($emailTemplate->variables ?? []) > 0)
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tags me-1"></i> Template Variables
                    </h6>
                </div>
                <div class="card-body">
                    <form id="preview-form" method="POST" action="{{ route('email.templates.preview', $emailTemplate) }}">
                        @csrf
                        
                        @foreach($emailTemplate->variables as $variable)
                        <div class="mb-3">
                            <label for="var_{{ $variable }}" class="form-label small">
                                {{ ucfirst(str_replace('_', ' ', $variable)) }}
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-sm" 
                                id="var_{{ $variable }}"
                                name="variables[{{ $variable }}]" 
                                value="{{ $variables[$variable] ?? '' }}"
                                placeholder="Enter {{ $variable }}"
                            >
                        </div>
                        @endforeach
                        
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-refresh me-1"></i> Update Preview
                        </button>
                    </form>

                    <hr class="my-3">
                    
                    <div>
                        <h6 class="small mb-2">Available Variables:</h6>
                        @foreach($emailTemplate->variables as $variable)
                            <span class="variable-badge">{{ $variable }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Preview Panel -->
        <div class="col-lg-{{ count($emailTemplate->variables ?? []) > 0 ? '9' : '12' }}">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <i class="fas fa-eye me-1"></i> Email Preview
                        </h6>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="switchView('desktop')">
                            <i class="fas fa-desktop"></i> Desktop
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="switchView('mobile')">
                            <i class="fas fa-mobile-alt"></i> Mobile
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Email Subject -->
                    <div class="border-bottom p-3 bg-light">
                        <div class="row">
                            <div class="col-auto">
                                <strong>Subject:</strong>
                            </div>
                            <div class="col">
                                <span id="email-subject">{{ $preview['subject'] ?? $emailTemplate->subject }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Email Content Preview -->
                    <div class="preview-container" id="preview-container">
                        <div class="p-4" id="email-content">
                            {!! $preview['content'] ?? $emailTemplate->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-update preview on variable change
    const form = document.getElementById('preview-form');
    const inputs = form?.querySelectorAll('input[name^="variables"]');
    
    if (inputs) {
        inputs.forEach(input => {
            input.addEventListener('input', debounce(updatePreview, 1000));
        });
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function updatePreview() {
        if (!form) return;
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('email-subject').textContent = data.preview.subject;
                document.getElementById('email-content').innerHTML = data.preview.content;
            }
        })
        .catch(error => {
            console.error('Preview update failed:', error);
        });
    }
});

function switchView(view) {
    const container = document.getElementById('preview-container');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.closest('.btn').classList.add('active');
    
    if (view === 'mobile') {
        container.style.maxWidth = '375px';
        container.style.margin = '0 auto';
    } else {
        container.style.maxWidth = '';
        container.style.margin = '';
    }
}
</script>
@endpush
