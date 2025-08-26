@extends('layouts.app')

@section('title', 'Compose SMS')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 dark:text-white">Compose SMS</h1>
            <p class="mb-0 text-gray-600 dark:text-gray-400">Send SMS to selected contacts</p>
        </div>
        <div>
            <a href="{{ route('sms.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to SMS
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Form -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">SMS Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sms.store') }}" id="smsForm">
                        @csrf

                        <!-- Message Content -->
                        <div class="mb-4">
                            <label for="message" class="form-label">
                                Message <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="6" 
                                      placeholder="Enter your SMS message here..."
                                      maxlength="1600" required>{{ old('message') }}</textarea>
                            <div class="d-flex justify-content-between mt-2">
                                <div class="form-text">
                                    Use variables: {name}, {first_name}, {last_name}, {email}, {phone}, {company}
                                </div>
                                <small id="char-count" class="text-muted">0/1600 characters</small>
                            </div>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Recipients Selection -->
                        <div class="mb-4">
                            <label class="form-label">
                                Recipients <span class="text-danger">*</span>
                            </label>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Individual Contacts</h6>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="selectAllContacts()">
                                                    Select All
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                            <input type="text" class="form-control mb-3" id="contactSearch" 
                                                   placeholder="Search contacts..." onkeyup="filterContacts()">
                                            
                                            <div id="contactsList">
                                                @foreach($contacts as $contact)
                                                    <div class="contact-item mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input contact-checkbox" 
                                                                   type="checkbox" name="recipients[]" 
                                                                   value="{{ $contact->id }}" 
                                                                   id="contact_{{ $contact->id }}"
                                                                   {{ in_array($contact->id, old('recipients', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label w-100" for="contact_{{ $contact->id }}">
                                                                <div class="d-flex justify-content-between">
                                                                    <div>
                                                                        <strong>{{ $contact->name }}</strong>
                                                                        <br>
                                                                        <small class="text-muted">{{ $contact->phone }}</small>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header py-2">
                                            <h6 class="mb-0">Contact Segments</h6>
                                        </div>
                                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                            @foreach($segments as $segment)
                                                <div class="segment-item mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input segment-checkbox" 
                                                               type="checkbox" 
                                                               value="{{ $segment->id }}" 
                                                               id="segment_{{ $segment->id }}"
                                                               onchange="selectSegment({{ $segment->id }})">
                                                        <label class="form-check-label w-100" for="segment_{{ $segment->id }}">
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <strong>{{ $segment->name }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        {{ $segment->contacts->where('phone', '!=', '')->count() }} contacts with phone
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <span id="selected-count">0 recipients selected</span>
                                </div>
                            </div>

                            @error('recipients')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- SMS Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="provider_id" class="form-label">SMS Provider</label>
                                <select class="form-select @error('provider_id') is-invalid @enderror" 
                                        id="provider_id" name="provider_id">
                                    <option value="">Use Default Provider</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider->id }}" 
                                                {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                            {{ $provider->name }} 
                                            @if($provider->daily_limit)
                                                (Limit: {{ number_format($provider->daily_limit) }}/day)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('provider_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="send_type" class="form-label">Send Type</label>
                                <select class="form-select @error('send_type') is-invalid @enderror" 
                                        id="send_type" name="send_type" onchange="toggleSchedule()" required>
                                    <option value="now" {{ old('send_type', 'now') === 'now' ? 'selected' : '' }}>
                                        Send Now
                                    </option>
                                    <option value="scheduled" {{ old('send_type') === 'scheduled' ? 'selected' : '' }}>
                                        Schedule for Later
                                    </option>
                                    <option value="test" {{ old('send_type') === 'test' ? 'selected' : '' }}>
                                        Test Mode (Don't Actually Send)
                                    </option>
                                </select>
                                @error('send_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Schedule Options -->
                        <div class="mb-4" id="schedule-options" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="schedule_at" class="form-label">Schedule Date & Time</label>
                                    <input type="datetime-local" 
                                           class="form-control @error('schedule_at') is-invalid @enderror" 
                                           id="schedule_at" name="schedule_at" 
                                           value="{{ old('schedule_at') }}"
                                           min="{{ date('Y-m-d\TH:i') }}">
                                    @error('schedule_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="mt-4 pt-2">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-clock me-2"></i>
                                            Scheduled SMS will be sent automatically at the specified time.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-info" onclick="previewMessage()">
                                    <i class="fas fa-eye me-2"></i>Preview Message
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    <span id="send-btn-text">Send SMS</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Message Templates -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Message Templates</h6>
                </div>
                <div class="card-body">
                    @foreach($templates as $template)
                        <div class="template-item mb-3 p-3 border rounded">
                            <h6 class="mb-2">{{ $template['name'] }}</h6>
                            <p class="mb-2 small text-muted">{{ $template['content'] }}</p>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="useTemplate('{{ addslashes($template['content']) }}')">
                                Use Template
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">SMS Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Preview shows how the message will appear with variables replaced
                </div>
                <div id="preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Character counter
const messageField = document.getElementById('message');
const charCount = document.getElementById('char-count');

messageField.addEventListener('input', function() {
    const count = this.value.length;
    charCount.textContent = `${count}/1600 characters`;
    
    if (count > 1600) {
        charCount.className = 'text-danger';
        this.classList.add('is-invalid');
    } else if (count > 1400) {
        charCount.className = 'text-warning';
        this.classList.remove('is-invalid');
    } else {
        charCount.className = 'text-muted';
        this.classList.remove('is-invalid');
    }
});

// Toggle schedule options
function toggleSchedule() {
    const sendType = document.getElementById('send_type').value;
    const scheduleOptions = document.getElementById('schedule-options');
    const sendBtnText = document.getElementById('send-btn-text');
    
    if (sendType === 'scheduled') {
        scheduleOptions.style.display = 'block';
        sendBtnText.textContent = 'Schedule SMS';
    } else if (sendType === 'test') {
        scheduleOptions.style.display = 'none';
        sendBtnText.textContent = 'Create Test SMS';
    } else {
        scheduleOptions.style.display = 'none';
        sendBtnText.textContent = 'Send SMS';
    }
}

// Select all contacts
function selectAllContacts() {
    const checkboxes = document.querySelectorAll('.contact-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
    
    updateSelectedCount();
}

// Filter contacts
function filterContacts() {
    const searchTerm = document.getElementById('contactSearch').value.toLowerCase();
    const contactItems = document.querySelectorAll('.contact-item');
    
    contactItems.forEach(item => {
        const label = item.querySelector('label').textContent.toLowerCase();
        if (label.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Use template
function useTemplate(content) {
    document.getElementById('message').value = content;
    messageField.dispatchEvent(new Event('input'));
}

// Preview message
function previewMessage() {
    const message = document.getElementById('message').value;
    if (!message.trim()) {
        alert('Please enter a message to preview.');
        return;
    }
    
    let previewHtml = '<div class="message-preview" style="background: #e3f2fd; padding: 10px; border-radius: 5px; font-family: monospace; white-space: pre-wrap;">';
    previewHtml += message;
    previewHtml += '</div>';
    
    document.getElementById('preview-content').innerHTML = previewHtml;
    new bootstrap.Modal(document.getElementById('previewModal')).show();
}

// Update selected count
function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.contact-checkbox:checked').length;
    document.getElementById('selected-count').textContent = `${selectedCount} recipients selected`;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize character count
    messageField.dispatchEvent(new Event('input'));
    
    // Initialize schedule toggle
    toggleSchedule();
    
    // Add event listeners to contact checkboxes
    document.querySelectorAll('.contact-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Initialize selected count
    updateSelectedCount();
});
</script>
@endpush
