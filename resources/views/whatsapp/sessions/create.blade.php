@extends('layouts.app')

@section('title', 'Create WhatsApp Session')

@push('styles')
<style>
    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
    }
    
    .step {
        flex: 1;
        text-align: center;
        position: relative;
    }
    
    .step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 60%;
        width: 80%;
        height: 2px;
        background: #e9ecef;
        z-index: 1;
    }
    
    .step.active:not(:last-child)::after {
        background: #0d6efd;
    }
    
    .step-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        position: relative;
        z-index: 2;
        margin-bottom: 0.5rem;
    }
    
    .step.active .step-circle {
        background: #0d6efd;
        color: white;
    }
    
    .step.completed .step-circle {
        background: #198754;
        color: white;
    }
    
    .config-section {
        display: none;
    }
    
    .config-section.active {
        display: block;
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .feature-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1rem;
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .feature-card:hover {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }
    
    .feature-card.selected {
        border-color: #0d6efd;
        background-color: #e7f3ff;
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
                    <h1 class="h3 mb-1">Create WhatsApp Session</h1>
                    <p class="text-muted mb-0">Set up a new WhatsApp Web session for messaging</p>
                </div>
                <div>
                    <a href="{{ route('whatsapp.sessions.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Back to Sessions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Step Indicator -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="step-indicator">
                <div class="step active" data-step="1">
                    <div class="step-circle">1</div>
                    <div class="step-title">Basic Info</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-circle">2</div>
                    <div class="step-title">Configuration</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-circle">3</div>
                    <div class="step-title">Features</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-circle">4</div>
                    <div class="step-title">Review</div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('whatsapp.sessions.store') }}" method="POST" id="session-form">
        @csrf
        
        <!-- Step 1: Basic Information -->
        <div class="config-section active" data-step="1">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-1"></i> Basic Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Session Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="e.g., Main WhatsApp, Support Team">
                                <div class="form-text">A descriptive name to identify this session</div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Optional description for this session">{!! old('description') !!}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Session Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Session
                                    </label>
                                </div>
                                <div class="form-text">Activate this session after creation</div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-lightbulb me-1"></i> How it works
                                </h6>
                                <p class="mb-2">WhatsApp sessions allow you to:</p>
                                <ul class="mb-0">
                                    <li>Connect your WhatsApp account via QR code</li>
                                    <li>Send and receive messages through CRM Ultra</li>
                                    <li>Automate customer communications</li>
                                    <li>Track message history and analytics</li>
                                </ul>
                            </div>

                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Important Notes
                                </h6>
                                <ul class="mb-0">
                                    <li>Only one active session per WhatsApp number</li>
                                    <li>Session will disconnect if WhatsApp Web is used elsewhere</li>
                                    <li>Requires stable internet connection</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="nextStep()">
                            Next Step <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Configuration -->
        <div class="config-section" data-step="2">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-1"></i> Session Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="webhook_url" class="form-label">Webhook URL</label>
                                <input type="url" class="form-control @error('webhook_url') is-invalid @enderror" 
                                       id="webhook_url" name="webhook_url" value="{{ old('webhook_url', url('/webhook/whatsapp')) }}" 
                                       readonly>
                                <div class="form-text">URL for receiving WhatsApp webhooks (auto-generated)</div>
                                @error('webhook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="timeout_minutes" class="form-label">Session Timeout (minutes)</label>
                                <input type="number" class="form-control @error('timeout_minutes') is-invalid @enderror" 
                                       id="timeout_minutes" name="timeout_minutes" value="{{ old('timeout_minutes', 30) }}" 
                                       min="5" max="1440">
                                <div class="form-text">Auto-disconnect if inactive for this duration</div>
                                @error('timeout_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="max_retries" class="form-label">Max Connection Retries</label>
                                <input type="number" class="form-control @error('max_retries') is-invalid @enderror" 
                                       id="max_retries" name="max_retries" value="{{ old('max_retries', 3) }}" 
                                       min="1" max="10">
                                <div class="form-text">Number of times to retry failed connections</div>
                                @error('max_retries')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Session Options</label>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="auto_reconnect" 
                                           name="auto_reconnect" value="1" {{ old('auto_reconnect', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_reconnect">
                                        Auto-reconnect on disconnect
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="save_media" 
                                           name="save_media" value="1" {{ old('save_media', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="save_media">
                                        Save received media files
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="log_messages" 
                                           name="log_messages" value="1" {{ old('log_messages', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="log_messages">
                                        Log all messages
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="enable_groups" 
                                           name="enable_groups" value="1" {{ old('enable_groups') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_groups">
                                        Enable group messaging
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="user_agent" class="form-label">Custom User Agent</label>
                                <input type="text" class="form-control @error('user_agent') is-invalid @enderror" 
                                       id="user_agent" name="user_agent" value="{{ old('user_agent') }}" 
                                       placeholder="Leave blank for default">
                                <div class="form-text">Custom user agent string (optional)</div>
                                @error('user_agent')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-light" onclick="prevStep()">
                            <i class="fas fa-arrow-left me-1"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">
                            Next Step <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Features -->
        <div class="config-section" data-step="3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-star me-1"></i> Enable Features
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Select the features you want to enable for this WhatsApp session:</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="feature-card" data-feature="auto_reply">
                                <div class="d-flex align-items-start">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="feature_auto_reply" 
                                               name="features[]" value="auto_reply" {{ in_array('auto_reply', old('features', [])) ? 'checked' : '' }}>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Auto-Reply</h6>
                                        <p class="text-muted small mb-0">Automatically respond to incoming messages with predefined replies</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="feature-card" data-feature="contact_sync">
                                <div class="d-flex align-items-start">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="feature_contact_sync" 
                                               name="features[]" value="contact_sync" {{ in_array('contact_sync', old('features', ['contact_sync'])) ? 'checked' : '' }}>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Contact Sync</h6>
                                        <p class="text-muted small mb-0">Automatically sync WhatsApp contacts with CRM contacts</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="feature-card" data-feature="message_templates">
                                <div class="d-flex align-items-start">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="feature_message_templates" 
                                               name="features[]" value="message_templates" {{ in_array('message_templates', old('features', ['message_templates'])) ? 'checked' : '' }}>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Message Templates</h6>
                                        <p class="text-muted small mb-0">Use predefined message templates for quick responses</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="feature-card" data-feature="broadcast_messages">
                                <div class="d-flex align-items-start">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="feature_broadcast_messages" 
                                               name="features[]" value="broadcast_messages" {{ in_array('broadcast_messages', old('features', [])) ? 'checked' : '' }}>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Broadcast Messages</h6>
                                        <p class="text-muted small mb-0">Send messages to multiple contacts simultaneously</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="feature-card" data-feature="read_receipts">
                                <div class="d-flex align-items-start">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="feature_read_receipts" 
                                               name="features[]" value="read_receipts" {{ in_array('read_receipts', old('features', ['read_receipts'])) ? 'checked' : '' }}>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Read Receipts</h6>
                                        <p class="text-muted small mb-0">Track when messages are read by recipients</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="feature-card" data-feature="typing_indicator">
                                <div class="d-flex align-items-start">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="checkbox" id="feature_typing_indicator" 
                                               name="features[]" value="typing_indicator" {{ in_array('typing_indicator', old('features', [])) ? 'checked' : '' }}>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Typing Indicator</h6>
                                        <p class="text-muted small mb-0">Show typing indicator when composing messages</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-light" onclick="prevStep()">
                            <i class="fas fa-arrow-left me-1"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">
                            Review <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Review -->
        <div class="config-section" data-step="4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-check-circle me-1"></i> Review & Create
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <h6 class="mb-3">Session Summary</h6>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td width="200"><strong>Session Name:</strong></td>
                                    <td id="review-name">—</td>
                                </tr>
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td id="review-description">—</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td id="review-status">—</td>
                                </tr>
                                <tr>
                                    <td><strong>Timeout:</strong></td>
                                    <td id="review-timeout">—</td>
                                </tr>
                                <tr>
                                    <td><strong>Max Retries:</strong></td>
                                    <td id="review-retries">—</td>
                                </tr>
                                <tr>
                                    <td><strong>Features:</strong></td>
                                    <td id="review-features">—</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-lg-4">
                            <div class="alert alert-success">
                                <h6 class="alert-heading">
                                    <i class="fab fa-whatsapp me-1"></i> Next Steps
                                </h6>
                                <p class="mb-2">After creating the session:</p>
                                <ol class="mb-0">
                                    <li>Start the session</li>
                                    <li>Scan QR code with WhatsApp</li>
                                    <li>Begin messaging contacts</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-light" onclick="prevStep()">
                            <i class="fas fa-arrow-left me-1"></i> Previous
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fab fa-whatsapp me-1"></i> Create Session
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 4;

document.addEventListener('DOMContentLoaded', function() {
    // Handle feature card clicks
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('click', function() {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('selected', checkbox.checked);
        });
    });

    // Initialize feature cards
    document.querySelectorAll('.feature-card input[type="checkbox"]:checked').forEach(checkbox => {
        checkbox.closest('.feature-card').classList.add('selected');
    });

    updateReview();
});

function nextStep() {
    if (currentStep < totalSteps) {
        // Hide current step
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('completed');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');

        // Show next step
        currentStep++;
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');

        if (currentStep === totalSteps) {
            updateReview();
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        // Hide current step
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');

        // Show previous step
        currentStep--;
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('completed');
    }
}

function updateReview() {
    // Update review section with form data
    document.getElementById('review-name').textContent = document.getElementById('name').value || '—';
    document.getElementById('review-description').textContent = document.getElementById('description').value || '—';
    document.getElementById('review-status').textContent = document.getElementById('is_active').checked ? 'Active' : 'Inactive';
    document.getElementById('review-timeout').textContent = document.getElementById('timeout_minutes').value + ' minutes';
    document.getElementById('review-retries').textContent = document.getElementById('max_retries').value;
    
    // Update features
    const features = [];
    document.querySelectorAll('input[name="features[]"]:checked').forEach(feature => {
        const label = document.querySelector(`label[for="${feature.id}"]`);
        if (label) {
            features.push(label.textContent.trim());
        }
    });
    document.getElementById('review-features').textContent = features.length > 0 ? features.join(', ') : 'None selected';
}
</script>
@endpush
