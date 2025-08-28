@extends('layouts.app')

@section('title', 'Create Backup')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-plus-circle text-primary me-2"></i>
                        Create New Backup
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">Admin</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.backups.index') }}">Backups</a>
                            </li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Backup Creation Wizard -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-magic"></i> Backup Creation Wizard
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.backups.store') }}" method="POST" id="backup-creation-form">
                        @csrf
                        
                        <!-- Step 1: Basic Information -->
                        <div class="wizard-step" id="step-1">
                            <div class="row">
                                <div class="col-12">
                                    <h4 class="step-title mb-3">
                                        <span class="step-number">1</span>
                                        Basic Information
                                    </h4>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-4">
                                        <label class="form-label">Backup Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               name="name" value="{{ old('name', 'backup_' . now()->format('Y_m_d_H_i_s')) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Choose a descriptive name for your backup
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Description (Optional)</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  name="description" rows="3" 
                                                  placeholder="Describe the purpose or content of this backup">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Backup Type Selection -->
                        <div class="wizard-step d-none" id="step-2">
                            <div class="row">
                                <div class="col-12">
                                    <h4 class="step-title mb-3">
                                        <span class="step-number">2</span>
                                        Select Backup Type
                                    </h4>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-lg-4 mb-3">
                                    <div class="card h-100 backup-type-card" data-type="full">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="fas fa-database fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Full Backup</h5>
                                            <p class="card-text text-muted">
                                                Complete system backup including database and files
                                            </p>
                                            <ul class="list-unstyled text-start small">
                                                <li><i class="fas fa-check text-success"></i> Database structure & data</li>
                                                <li><i class="fas fa-check text-success"></i> Application files</li>
                                                <li><i class="fas fa-check text-success"></i> Configuration files</li>
                                                <li><i class="fas fa-check text-success"></i> Storage files</li>
                                            </ul>
                                            <div class="mt-3">
                                                <input type="radio" class="form-check-input" name="type" value="full" id="type-full" required>
                                                <label class="form-check-label fw-semibold" for="type-full">
                                                    Select Full Backup
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 mb-3">
                                    <div class="card h-100 backup-type-card" data-type="database">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="fas fa-table fa-3x text-warning"></i>
                                            </div>
                                            <h5 class="card-title">Database Only</h5>
                                            <p class="card-text text-muted">
                                                Database backup with all tables and data
                                            </p>
                                            <ul class="list-unstyled text-start small">
                                                <li><i class="fas fa-check text-success"></i> All database tables</li>
                                                <li><i class="fas fa-check text-success"></i> Data and relationships</li>
                                                <li><i class="fas fa-check text-success"></i> Stored procedures</li>
                                                <li><i class="fas fa-times text-muted"></i> Files not included</li>
                                            </ul>
                                            <div class="mt-3">
                                                <input type="radio" class="form-check-input" name="type" value="database" id="type-database" required>
                                                <label class="form-check-label fw-semibold" for="type-database">
                                                    Select Database Only
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4 mb-3">
                                    <div class="card h-100 backup-type-card" data-type="files">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="fas fa-folder fa-3x text-info"></i>
                                            </div>
                                            <h5 class="card-title">Files Only</h5>
                                            <p class="card-text text-muted">
                                                Application and storage files backup
                                            </p>
                                            <ul class="list-unstyled text-start small">
                                                <li><i class="fas fa-check text-success"></i> Application files</li>
                                                <li><i class="fas fa-check text-success"></i> Configuration files</li>
                                                <li><i class="fas fa-check text-success"></i> Storage files</li>
                                                <li><i class="fas fa-times text-muted"></i> Database not included</li>
                                            </ul>
                                            <div class="mt-3">
                                                <input type="radio" class="form-check-input" name="type" value="files" id="type-files" required>
                                                <label class="form-check-label fw-semibold" for="type-files">
                                                    Select Files Only
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Backup Size Estimation -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info d-none" id="size-estimation">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <div>
                                                <strong>Estimated backup size:</strong> 
                                                <span id="estimated-size">Calculating...</span>
                                                <br>
                                                <small>Actual size may vary. Processing time depends on data volume.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Review & Confirm -->
                        <div class="wizard-step d-none" id="step-3">
                            <div class="row">
                                <div class="col-12">
                                    <h4 class="step-title mb-3">
                                        <span class="step-number">3</span>
                                        Review & Confirm
                                    </h4>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Backup Summary</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <dl class="row">
                                                        <dt class="col-sm-4">Name:</dt>
                                                        <dd class="col-sm-8" id="review-name">—</dd>
                                                        
                                                        <dt class="col-sm-4">Type:</dt>
                                                        <dd class="col-sm-8" id="review-type">—</dd>
                                                        
                                                        <dt class="col-sm-4">Description:</dt>
                                                        <dd class="col-sm-8" id="review-description">—</dd>
                                                    </dl>
                                                </div>
                                                <div class="col-md-6">
                                                    <dl class="row">
                                                        <dt class="col-sm-5">Estimated Size:</dt>
                                                        <dd class="col-sm-7" id="review-size">—</dd>
                                                        
                                                        <dt class="col-sm-5">Created By:</dt>
                                                        <dd class="col-sm-7">{{ auth()->user()->name }}</dd>
                                                        
                                                        <dt class="col-sm-5">Created At:</dt>
                                                        <dd class="col-sm-7">{{ now()->format('Y-m-d H:i:s') }}</dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning mt-3">
                                        <div class="d-flex">
                                            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                                            <div>
                                                <strong>Important:</strong> 
                                                <ul class="mb-0 mt-2">
                                                    <li>Backup creation may take several minutes depending on data size</li>
                                                    <li>Do not close this browser window during backup creation</li>
                                                    <li>System performance may be temporarily affected during backup</li>
                                                    <li>You will receive a notification when backup is complete</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary d-none" id="btn-previous">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                            <div></div>
                            <div>
                                <button type="button" class="btn btn-primary" id="btn-next">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                                <button type="submit" class="btn btn-success d-none" id="btn-create">
                                    <i class="fas fa-play"></i> Create Backup
                                </button>
                            </div>
                        </div>

                        <!-- Progress Indicator -->
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar" role="progressbar" style="width: 33%"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle"></i> Backup Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="backup-help" id="help-step-1">
                        <h6>Step 1: Basic Information</h6>
                        <p class="small text-muted">
                            Provide a clear name and optional description for your backup. 
                            Use naming conventions that help you identify backups later.
                        </p>
                        <p class="small text-muted">
                            <strong>Tip:</strong> Include date, purpose, or version information in the name.
                        </p>
                    </div>

                    <div class="backup-help d-none" id="help-step-2">
                        <h6>Step 2: Backup Type</h6>
                        <p class="small text-muted">Choose the appropriate backup type:</p>
                        <ul class="small text-muted">
                            <li><strong>Full:</strong> Complete system backup (recommended for major changes)</li>
                            <li><strong>Database:</strong> Just data and structure (faster, smaller)</li>
                            <li><strong>Files:</strong> Application files only (for code changes)</li>
                        </ul>
                    </div>

                    <div class="backup-help d-none" id="help-step-3">
                        <h6>Step 3: Final Review</h6>
                        <p class="small text-muted">
                            Review all backup settings before creation. Once started, 
                            the backup process cannot be stopped.
                        </p>
                        <p class="small text-muted">
                            <strong>Note:</strong> You can monitor backup progress from the main backup list.
                        </p>
                    </div>
                </div>
            </div>

            <!-- System Resources -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-server"></i> System Resources
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="small">Available Disk Space:</span>
                            <span class="small fw-semibold" id="available-space">Loading...</span>
                        </div>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar" id="disk-usage" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="small">Database Size:</span>
                            <span class="small fw-semibold" id="database-size">Loading...</span>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <div class="d-flex justify-content-between">
                            <span class="small">Files Size:</span>
                            <span class="small fw-semibold" id="files-size">Loading...</span>
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
.wizard-step {
    min-height: 300px;
}

.step-title {
    color: #495057;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}

.step-number {
    background: #007bff;
    color: white;
    border-radius: 50%;
    padding: 8px 12px;
    font-weight: bold;
    margin-right: 10px;
}

.backup-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.backup-type-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.backup-type-card.selected {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.backup-type-card.selected .card-title {
    color: #007bff;
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.backup-help {
    transition: opacity 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 3;

// Initialize wizard
document.addEventListener('DOMContentLoaded', function() {
    updateWizardNavigation();
    loadSystemResources();
    
    // Handle backup type selection
    document.querySelectorAll('.backup-type-card').forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            const radio = this.querySelector('input[type="radio"]');
            
            // Clear all selections
            document.querySelectorAll('.backup-type-card').forEach(c => c.classList.remove('selected'));
            document.querySelectorAll('input[name="type"]').forEach(r => r.checked = false);
            
            // Select this card
            this.classList.add('selected');
            radio.checked = true;
            
            // Update size estimation
            updateSizeEstimation(type);
        });
    });
    
    // Handle form submission
    document.getElementById('backup-creation-form').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('btn-create');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';
        
        // Show progress notification
        showToast('Backup creation started. Please wait...', 'info');
    });
});

// Navigation functions
document.getElementById('btn-next').addEventListener('click', function() {
    if (validateCurrentStep()) {
        nextStep();
    }
});

document.getElementById('btn-previous').addEventListener('click', function() {
    previousStep();
});

function nextStep() {
    if (currentStep < totalSteps) {
        // Hide current step
        document.getElementById(`step-${currentStep}`).classList.add('d-none');
        document.getElementById(`help-step-${currentStep}`).classList.add('d-none');
        
        // Show next step
        currentStep++;
        document.getElementById(`step-${currentStep}`).classList.remove('d-none');
        document.getElementById(`help-step-${currentStep}`).classList.remove('d-none');
        
        // Update navigation
        updateWizardNavigation();
        
        // Update review data if on final step
        if (currentStep === 3) {
            updateReviewData();
        }
    }
}

function previousStep() {
    if (currentStep > 1) {
        // Hide current step
        document.getElementById(`step-${currentStep}`).classList.add('d-none');
        document.getElementById(`help-step-${currentStep}`).classList.add('d-none');
        
        // Show previous step
        currentStep--;
        document.getElementById(`step-${currentStep}`).classList.remove('d-none');
        document.getElementById(`help-step-${currentStep}`).classList.remove('d-none');
        
        // Update navigation
        updateWizardNavigation();
    }
}

function updateWizardNavigation() {
    const btnPrevious = document.getElementById('btn-previous');
    const btnNext = document.getElementById('btn-next');
    const btnCreate = document.getElementById('btn-create');
    const progressBar = document.querySelector('.progress-bar');
    
    // Update buttons visibility
    btnPrevious.classList.toggle('d-none', currentStep === 1);
    btnNext.classList.toggle('d-none', currentStep === totalSteps);
    btnCreate.classList.toggle('d-none', currentStep !== totalSteps);
    
    // Update progress bar
    const progress = (currentStep / totalSteps) * 100;
    progressBar.style.width = `${progress}%`;
}

function validateCurrentStep() {
    switch (currentStep) {
        case 1:
            const name = document.querySelector('input[name="name"]').value;
            if (!name.trim()) {
                showToast('Please enter a backup name', 'warning');
                return false;
            }
            return true;
            
        case 2:
            const type = document.querySelector('input[name="type"]:checked');
            if (!type) {
                showToast('Please select a backup type', 'warning');
                return false;
            }
            return true;
            
        default:
            return true;
    }
}

function updateReviewData() {
    const name = document.querySelector('input[name="name"]').value;
    const description = document.querySelector('textarea[name="description"]').value;
    const type = document.querySelector('input[name="type"]:checked')?.value;
    
    document.getElementById('review-name').textContent = name || '—';
    document.getElementById('review-description').textContent = description || 'No description';
    document.getElementById('review-type').textContent = type ? type.charAt(0).toUpperCase() + type.slice(1) + ' Backup' : '—';
    document.getElementById('review-size').textContent = document.getElementById('estimated-size').textContent;
}

function updateSizeEstimation(type) {
    document.getElementById('size-estimation').classList.remove('d-none');
    document.getElementById('estimated-size').textContent = 'Calculating...';
    
    // Simulate size calculation (replace with actual API call)
    setTimeout(() => {
        let estimatedSize;
        switch (type) {
            case 'full':
                estimatedSize = 'Approximately 150-500 MB';
                break;
            case 'database':
                estimatedSize = 'Approximately 50-150 MB';
                break;
            case 'files':
                estimatedSize = 'Approximately 100-350 MB';
                break;
        }
        document.getElementById('estimated-size').textContent = estimatedSize;
    }, 1000);
}

function loadSystemResources() {
    // Simulate loading system resources (replace with actual API call)
    setTimeout(() => {
        document.getElementById('available-space').textContent = '2.5 GB';
        document.getElementById('database-size').textContent = '125 MB';
        document.getElementById('files-size').textContent = '280 MB';
        
        // Update disk usage bar (example: 65% used)
        document.getElementById('disk-usage').style.width = '65%';
        
        if (65 > 80) {
            document.getElementById('disk-usage').classList.add('bg-warning');
        }
        if (65 > 90) {
            document.getElementById('disk-usage').classList.remove('bg-warning');
            document.getElementById('disk-usage').classList.add('bg-danger');
        }
    }, 500);
}

// Toast notification helper (if not already defined)
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type}" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    container.insertAdjacentHTML('beforeend', toastHtml);
    const toastEl = container.lastElementChild;
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}
</script>
@endpush
