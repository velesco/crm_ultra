@extends('layouts.app')

@section('title', 'Create Revenue - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.revenue.index') }}" class="text-decoration-none">Revenue</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.revenue.transactions') }}" class="text-decoration-none">Transactions</a>
                            </li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-plus me-2 text-success"></i>
                        Create Revenue Transaction
                    </h1>
                    <p class="text-muted">
                        Add a new revenue transaction manually
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.revenue.transactions') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.revenue.store') }}" method="POST" id="revenueForm">
                        @csrf

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                    Basic Information
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           value="{{ old('amount') }}" step="0.01" min="0" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                <select name="currency" id="currency" class="form-select @error('currency') is-invalid @enderror" required>
                                    <option value="USD" {{ old('currency', 'USD') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="RON" {{ old('currency') === 'RON' ? 'selected' : '' }}>RON - Romanian Leu</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    @foreach(\App\Models\Revenue::getTypes() as $key => $label)
                                        <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="channel" class="form-label">Channel <span class="text-danger">*</span></label>
                                <select name="channel" id="channel" class="form-select @error('channel') is-invalid @enderror" required>
                                    <option value="">Select Channel</option>
                                    @foreach(\App\Models\Revenue::getChannels() as $key => $label)
                                        <option value="{{ $key }}" {{ old('channel') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('channel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="reference_id" class="form-label">Reference ID</label>
                                <input type="text" name="reference_id" id="reference_id" class="form-control @error('reference_id') is-invalid @enderror" 
                                       value="{{ old('reference_id') }}" placeholder="External reference (invoice, order, etc.)">
                                @error('reference_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    Customer Information
                                </h5>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="customer_type" class="form-label">Customer Type</label>
                                <div class="btn-group w-100" role="group" id="customerTypeGroup">
                                    <input type="radio" class="btn-check" name="customer_type" id="existing_customer" value="existing" 
                                           {{ old('customer_type', 'manual') === 'existing' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="existing_customer">
                                        <i class="fas fa-address-book me-1"></i>Existing Contact
                                    </label>
                                    <input type="radio" class="btn-check" name="customer_type" id="manual_customer" value="manual" 
                                           {{ old('customer_type', 'manual') === 'manual' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary" for="manual_customer">
                                        <i class="fas fa-keyboard me-1"></i>Manual Entry
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3" id="existingContactField" style="display: none;">
                                <label for="contact_id" class="form-label">Select Contact</label>
                                <select name="contact_id" id="contact_id" class="form-select @error('contact_id') is-invalid @enderror">
                                    <option value="">Choose a contact...</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->id }}" 
                                                data-email="{{ $contact->email }}"
                                                {{ old('contact_id') == $contact->id ? 'selected' : '' }}>
                                            {{ $contact->first_name }} {{ $contact->last_name }} - {{ $contact->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('contact_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="manualCustomerFields">
                                <div class="col-md-6 mb-3">
                                    <label for="customer_name" class="form-label">Customer Name</label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                                           value="{{ old('customer_name') }}" placeholder="Full name">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customer_email" class="form-label">Customer Email</label>
                                    <input type="email" name="customer_email" id="customer_email" class="form-control @error('customer_email') is-invalid @enderror" 
                                           value="{{ old('customer_email') }}" placeholder="customer@example.com">
                                    @error('customer_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Financial Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-calculator me-2 text-primary"></i>
                                    Financial Details
                                </h5>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cost" class="form-label">Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="cost" id="cost" class="form-control @error('cost') is-invalid @enderror" 
                                           value="{{ old('cost', 0) }}" step="0.01" min="0">
                                </div>
                                @error('cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Associated costs (provider fees, etc.)</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tax_amount" class="form-label">Tax Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="tax_amount" id="tax_amount" class="form-control @error('tax_amount') is-invalid @enderror" 
                                           value="{{ old('tax_amount', 0) }}" step="0.01" min="0">
                                </div>
                                @error('tax_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="commission" class="form-label">Commission</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="commission" id="commission" class="form-control @error('commission') is-invalid @enderror" 
                                           value="{{ old('commission', 0) }}" step="0.01" min="0">
                                </div>
                                @error('commission')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="fas fa-calculator me-2"></i>
                                    <div>
                                        <strong>Net Revenue Calculation:</strong> 
                                        Amount - Cost - Tax Amount - Commission = 
                                        <span id="netRevenue" class="fw-bold text-success">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-calendar me-2 text-primary"></i>
                                    Additional Information
                                </h5>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="revenue_date" class="form-label">Revenue Date</label>
                                <input type="datetime-local" name="revenue_date" id="revenue_date" class="form-control @error('revenue_date') is-invalid @enderror" 
                                       value="{{ old('revenue_date', now()->format('Y-m-d\TH:i')) }}">
                                @error('revenue_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">When was this revenue actually earned?</div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="4" 
                                          placeholder="Additional notes or description...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.revenue.transactions') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-1"></i>Create Revenue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Customer type toggle
document.addEventListener('DOMContentLoaded', function() {
    const existingRadio = document.getElementById('existing_customer');
    const manualRadio = document.getElementById('manual_customer');
    const existingField = document.getElementById('existingContactField');
    const manualFields = document.getElementById('manualCustomerFields');
    const contactSelect = document.getElementById('contact_id');
    const customerNameInput = document.getElementById('customer_name');
    const customerEmailInput = document.getElementById('customer_email');
    
    function toggleCustomerFields() {
        if (existingRadio.checked) {
            existingField.style.display = 'block';
            manualFields.style.display = 'none';
            contactSelect.required = true;
            customerNameInput.required = false;
        } else {
            existingField.style.display = 'none';
            manualFields.style.display = 'block';
            contactSelect.required = false;
            customerNameInput.required = true;
        }
        
        calculateNetRevenue();
    }
    
    existingRadio.addEventListener('change', toggleCustomerFields);
    manualRadio.addEventListener('change', toggleCustomerFields);
    
    // Auto-fill customer details when contact is selected
    contactSelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            customerEmailInput.value = selectedOption.dataset.email || '';
        } else {
            customerEmailInput.value = '';
        }
    });
    
    // Net revenue calculation
    function calculateNetRevenue() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const cost = parseFloat(document.getElementById('cost').value) || 0;
        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
        const commission = parseFloat(document.getElementById('commission').value) || 0;
        
        const netRevenue = amount - cost - tax - commission;
        document.getElementById('netRevenue').textContent = '$' + netRevenue.toFixed(2);
        
        // Update color based on value
        const element = document.getElementById('netRevenue');
        if (netRevenue > 0) {
            element.className = 'fw-bold text-success';
        } else if (netRevenue < 0) {
            element.className = 'fw-bold text-danger';
        } else {
            element.className = 'fw-bold text-secondary';
        }
    }
    
    // Add event listeners for calculation inputs
    ['amount', 'cost', 'tax_amount', 'commission'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', calculateNetRevenue);
    });
    
    // Initialize the form
    toggleCustomerFields();
    calculateNetRevenue();
});

// Form validation
document.getElementById('revenueForm').addEventListener('submit', function(e) {
    const customerType = document.querySelector('input[name="customer_type"]:checked').value;
    const contactId = document.getElementById('contact_id').value;
    const customerName = document.getElementById('customer_name').value.trim();
    
    if (customerType === 'existing' && !contactId) {
        e.preventDefault();
        alert('Please select a contact or switch to manual entry.');
        return;
    }
    
    if (customerType === 'manual' && !customerName) {
        e.preventDefault();
        alert('Please enter a customer name.');
        return;
    }
});
</script>
@endsection
