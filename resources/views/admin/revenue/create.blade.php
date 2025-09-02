@extends('layouts.app')

@section('title', 'Create Revenue - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <nav class="flex text-sm text-gray-500 mb-3" aria-label="Breadcrumb">
                        <a href="{{ route('admin.revenue.index') }}" class="hover:text-gray-700 transition-colors duration-200">Revenue</a>
                        <span class="mx-2">/</span>
                        <a href="{{ route('admin.revenue.transactions') }}" class="hover:text-gray-700 transition-colors duration-200">Transactions</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900">Create</span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Revenue Transaction
                    </h1>
                    <p class="text-gray-600 mt-2">Add a new revenue transaction manually</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.revenue.transactions') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Transactions
                    </a>
                </div>
            </div>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-4xl">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <form action="{{ route('admin.revenue.store') }}" method="POST" id="revenueForm" class="space-y-8">
                        @csrf

                        {{-- Basic Information --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-3 mb-6 flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Basic Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        Amount <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">$</span>
                                        </div>
                                        <input type="number" name="amount" id="amount" 
                                               class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('amount') border-red-300 @enderror" 
                                               value="{{ old('amount') }}" step="0.01" min="0" required>
                                    </div>
                                    @error('amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                        Currency <span class="text-red-500">*</span>
                                    </label>
                                    <select name="currency" id="currency" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('currency') border-red-300 @enderror" required>
                                        <option value="USD" {{ old('currency', 'USD') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                        <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                        <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                        <option value="RON" {{ old('currency') === 'RON' ? 'selected' : '' }}>RON - Romanian Leu</option>
                                    </select>
                                    @error('currency')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Type <span class="text-red-500">*</span>
                                    </label>
                                    <select name="type" id="type" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 @enderror" required>
                                        <option value="">Select Type</option>
                                        @foreach(\App\Models\Revenue::getTypes() as $key => $label)
                                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="channel" class="block text-sm font-medium text-gray-700 mb-2">
                                        Channel <span class="text-red-500">*</span>
                                    </label>
                                    <select name="channel" id="channel" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('channel') border-red-300 @enderror" required>
                                        <option value="">Select Channel</option>
                                        @foreach(\App\Models\Revenue::getChannels() as $key => $label)
                                            <option value="{{ $key }}" {{ old('channel') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('channel')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="reference_id" class="block text-sm font-medium text-gray-700 mb-2">Reference ID</label>
                                    <input type="text" name="reference_id" id="reference_id" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('reference_id') border-red-300 @enderror" 
                                           value="{{ old('reference_id') }}" placeholder="External reference (invoice, order, etc.)">
                                    @error('reference_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Customer Information --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-3 mb-6 flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Customer Information
                            </h3>

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">Customer Type</label>
                                    <div class="flex rounded-lg border border-gray-300" id="customerTypeGroup">
                                        <label class="flex-1 relative flex cursor-pointer rounded-l-lg border-r border-gray-300 bg-white p-4 hover:bg-gray-50">
                                            <input type="radio" class="sr-only" name="customer_type" id="existing_customer" value="existing" 
                                                   {{ old('customer_type', 'manual') === 'existing' ? 'checked' : '' }}>
                                            <span class="flex items-center text-sm font-medium text-gray-900">
                                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                                Existing Contact
                                            </span>
                                        </label>
                                        <label class="flex-1 relative flex cursor-pointer rounded-r-lg bg-white p-4 hover:bg-gray-50">
                                            <input type="radio" class="sr-only" name="customer_type" id="manual_customer" value="manual" 
                                                   {{ old('customer_type', 'manual') === 'manual' ? 'checked' : '' }}>
                                            <span class="flex items-center text-sm font-medium text-gray-900">
                                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Manual Entry
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div id="existingContactField" class="hidden">
                                    <label for="contact_id" class="block text-sm font-medium text-gray-700 mb-2">Select Contact</label>
                                    <select name="contact_id" id="contact_id" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('contact_id') border-red-300 @enderror">
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
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="manualCustomerFields" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">Customer Name</label>
                                        <input type="text" name="customer_name" id="customer_name" 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('customer_name') border-red-300 @enderror" 
                                               value="{{ old('customer_name') }}" placeholder="Full name">
                                        @error('customer_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">Customer Email</label>
                                        <input type="email" name="customer_email" id="customer_email" 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('customer_email') border-red-300 @enderror" 
                                               value="{{ old('customer_email') }}" placeholder="customer@example.com">
                                        @error('customer_email')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Financial Details --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-3 mb-6 flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Financial Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="cost" class="block text-sm font-medium text-gray-700 mb-2">Cost</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">$</span>
                                        </div>
                                        <input type="number" name="cost" id="cost" 
                                               class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cost') border-red-300 @enderror" 
                                               value="{{ old('cost', 0) }}" step="0.01" min="0">
                                    </div>
                                    @error('cost')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">Associated costs (provider fees, etc.)</p>
                                </div>

                                <div>
                                    <label for="tax_amount" class="block text-sm font-medium text-gray-700 mb-2">Tax Amount</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">$</span>
                                        </div>
                                        <input type="number" name="tax_amount" id="tax_amount" 
                                               class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tax_amount') border-red-300 @enderror" 
                                               value="{{ old('tax_amount', 0) }}" step="0.01" min="0">
                                    </div>
                                    @error('tax_amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="commission" class="block text-sm font-medium text-gray-700 mb-2">Commission</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">$</span>
                                        </div>
                                        <input type="number" name="commission" id="commission" 
                                               class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('commission') border-red-300 @enderror" 
                                               value="{{ old('commission', 0) }}" step="0.01" min="0">
                                    </div>
                                    @error('commission')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <div class="text-blue-800">
                                        <strong>Net Revenue Calculation:</strong> 
                                        Amount - Cost - Tax Amount - Commission = 
                                        <span id="netRevenue" class="font-bold text-green-600">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Additional Information --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-3 mb-6 flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Additional Information
                            </h3>
                            <div class="space-y-6">
                                <div>
                                    <label for="revenue_date" class="block text-sm font-medium text-gray-700 mb-2">Revenue Date</label>
                                    <input type="datetime-local" name="revenue_date" id="revenue_date" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('revenue_date') border-red-300 @enderror" 
                                           value="{{ old('revenue_date', now()->format('Y-m-d\TH:i')) }}">
                                    @error('revenue_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">When was this revenue actually earned?</p>
                                </div>
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea name="notes" id="notes" rows="4" 
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-300 @enderror" 
                                              placeholder="Additional notes or description...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('admin.revenue.transactions') }}" 
                               class="inline-flex items-center px-6 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Create Revenue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Customer type toggle with Tailwind styling
document.addEventListener('DOMContentLoaded', function() {
    const existingRadio = document.getElementById('existing_customer');
    const manualRadio = document.getElementById('manual_customer');
    const existingField = document.getElementById('existingContactField');
    const manualFields = document.getElementById('manualCustomerFields');
    const contactSelect = document.getElementById('contact_id');
    const customerNameInput = document.getElementById('customer_name');
    const customerEmailInput = document.getElementById('customer_email');
    
    function updateRadioStyles() {
        const existingLabel = existingRadio.closest('label');
        const manualLabel = manualRadio.closest('label');
        
        if (existingRadio.checked) {
            existingLabel.classList.add('bg-blue-50', 'border-blue-200', 'ring-2', 'ring-blue-500');
            existingLabel.classList.remove('bg-white');
            manualLabel.classList.add('bg-white');
            manualLabel.classList.remove('bg-blue-50', 'border-blue-200', 'ring-2', 'ring-blue-500');
        } else {
            manualLabel.classList.add('bg-blue-50', 'border-blue-200', 'ring-2', 'ring-blue-500');
            manualLabel.classList.remove('bg-white');
            existingLabel.classList.add('bg-white');
            existingLabel.classList.remove('bg-blue-50', 'border-blue-200', 'ring-2', 'ring-blue-500');
        }
    }
    
    function toggleCustomerFields() {
        if (existingRadio.checked) {
            existingField.classList.remove('hidden');
            manualFields.classList.add('hidden');
            contactSelect.required = true;
            customerNameInput.required = false;
        } else {
            existingField.classList.add('hidden');
            manualFields.classList.remove('hidden');
            contactSelect.required = false;
            customerNameInput.required = true;
        }
        
        updateRadioStyles();
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
        const netRevenueElement = document.getElementById('netRevenue');
        netRevenueElement.textContent = '$' + netRevenue.toFixed(2);
        
        // Update color based on value
        if (netRevenue > 0) {
            netRevenueElement.className = 'font-bold text-green-600';
        } else if (netRevenue < 0) {
            netRevenueElement.className = 'font-bold text-red-600';
        } else {
            netRevenueElement.className = 'font-bold text-gray-600';
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
