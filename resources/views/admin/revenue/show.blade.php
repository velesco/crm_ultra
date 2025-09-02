@extends('layouts.app')

@section('title', 'Revenue Transaction - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <!-- Breadcrumb -->
                    <nav class="mb-3">
                        <ol class="flex items-center space-x-2 text-sm">
                            <li>
                                <a href="{{ route('admin.revenue.index') }}" class="text-blue-600 hover:text-blue-800 transition duration-150">Revenue</a>
                            </li>
                            <li class="text-gray-400">/</li>
                            <li>
                                <a href="{{ route('admin.revenue.transactions') }}" class="text-blue-600 hover:text-blue-800 transition duration-150">Transactions</a>
                            </li>
                            <li class="text-gray-400">/</li>
                            <li class="text-gray-600">{{ $revenue->transaction_id }}</li>
                        </ol>
                    </nav>
                    
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-receipt text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-2xl font-bold text-gray-900">Transaction Details</h1>
                            <p class="text-sm text-gray-600">Complete transaction information and history</p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.revenue.edit', $revenue) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <a href="{{ route('admin.revenue.transactions') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Transaction Overview -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Transaction Overview</h3>
                            <span class="px-3 py-1 text-sm font-medium rounded-full
                                @if($revenue->status === 'completed') bg-green-100 text-green-800
                                @elseif($revenue->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($revenue->status === 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($revenue->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Transaction ID</label>
                                <div class="font-mono text-gray-900">{{ $revenue->transaction_id }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Reference ID</label>
                                <div class="text-gray-900">{{ $revenue->reference_id ?: 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Amount</label>
                                <div class="text-2xl font-bold text-green-600">{{ $revenue->formatted_amount }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Net Revenue</label>
                                <div class="text-2xl font-bold text-blue-600">{{ $revenue->formatted_net_revenue }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Type</label>
                                <div>
                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                        {{ ucfirst($revenue->type) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Channel</label>
                                <div class="flex items-center">
                                    <i class="{{ $revenue->channel_icon }} mr-2 text-gray-600"></i>
                                    <span class="text-gray-900">{{ ucfirst($revenue->channel) }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Revenue Date</label>
                                <div class="text-gray-900">{{ $revenue->revenue_date->format('F j, Y \a\t g:i A') }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Created</label>
                                <div class="text-gray-900">{{ $revenue->created_at->format('F j, Y \a\t g:i A') }}</div>
                            </div>
                        </div>

                        @if($revenue->notes)
                            <div class="mt-6">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Notes</label>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="text-gray-700 whitespace-pre-line">{{ $revenue->notes }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Financial Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Financial Breakdown</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Gross Amount:</span>
                                <span class="font-semibold text-gray-900">${{ number_format($revenue->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Cost:</span>
                                <span class="text-gray-900">-${{ number_format($revenue->cost, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Tax Amount:</span>
                                <span class="text-gray-900">-${{ number_format($revenue->tax_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Commission:</span>
                                <span class="text-gray-900">-${{ number_format($revenue->commission, 2) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-900">Net Revenue:</span>
                                    <span class="text-2xl font-bold text-green-600">${{ number_format($revenue->net_revenue, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metadata -->
                @if($revenue->metadata)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <div class="space-y-3">
                                    @foreach($revenue->metadata as $key => $value)
                                        <div class="flex flex-col sm:flex-row sm:justify-between py-2">
                                            <span class="text-gray-600 font-medium mb-1 sm:mb-0">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                            <div class="sm:text-right">
                                                @if(is_array($value) || is_object($value))
                                                    <pre class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                @else
                                                    <span class="text-gray-900">{{ $value }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Customer Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Customer Information</h3>
                    </div>
                    <div class="p-6">
                        @if($revenue->contact)
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        <a href="{{ route('contacts.show', $revenue->contact) }}" class="hover:text-blue-600 transition duration-150">
                                            {{ $revenue->contact->first_name }} {{ $revenue->contact->last_name }}
                                        </a>
                                    </div>
                                    <div class="text-sm text-gray-600">Registered Customer</div>
                                </div>
                            </div>
                            
                            <div class="space-y-3 pt-4 border-t border-gray-200">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Email:</span>
                                    <div class="mt-1">
                                        <a href="mailto:{{ $revenue->contact->email }}" class="text-blue-600 hover:text-blue-800 transition duration-150">
                                            {{ $revenue->contact->email }}
                                        </a>
                                    </div>
                                </div>
                                @if($revenue->contact->phone)
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Phone:</span>
                                        <div class="mt-1">
                                            <a href="tel:{{ $revenue->contact->phone }}" class="text-blue-600 hover:text-blue-800 transition duration-150">
                                                {{ $revenue->contact->phone }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                @if($revenue->contact->company)
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Company:</span>
                                        <div class="mt-1 text-gray-900">{{ $revenue->contact->company }}</div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-gray-400 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user-slash text-white"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $revenue->customer_name ?: 'Unknown Customer' }}</div>
                                    <div class="text-sm text-gray-600">Guest Customer</div>
                                </div>
                            </div>
                            
                            @if($revenue->customer_email)
                                <div class="pt-4 border-t border-gray-200">
                                    <span class="text-sm font-medium text-gray-700">Email:</span>
                                    <div class="mt-1">
                                        <a href="mailto:{{ $revenue->customer_email }}" class="text-blue-600 hover:text-blue-800 transition duration-150">
                                            {{ $revenue->customer_email }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Status Timeline</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            <div class="relative">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-plus text-white text-sm"></i>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">Transaction Created</h4>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $revenue->created_at->format('M d, Y \a\t g:i A') }}
                                            @if($revenue->creator)
                                                <br>by {{ $revenue->creator->first_name }} {{ $revenue->creator->last_name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                @if($revenue->confirmed_at || $revenue->refunded_at)
                                    <div class="absolute left-4 top-8 w-0.5 h-8 bg-gray-300"></div>
                                @endif
                            </div>

                            @if($revenue->confirmed_at)
                                <div class="relative">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-white text-sm"></i>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="text-sm font-medium text-gray-900">Transaction Confirmed</h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $revenue->confirmed_at->format('M d, Y \a\t g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                    @if($revenue->refunded_at)
                                        <div class="absolute left-4 top-8 w-0.5 h-8 bg-gray-300"></div>
                                    @endif
                                </div>
                            @endif

                            @if($revenue->refunded_at)
                                <div class="relative">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8 bg-red-600 rounded-full flex items-center justify-center">
                                            <i class="fas fa-undo text-white text-sm"></i>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <h4 class="text-sm font-medium text-gray-900">Transaction Refunded</h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $revenue->refunded_at->format('M d, Y \a\t g:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($revenue->status === 'pending')
                            <button type="button" onclick="confirmRevenue()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150">
                                <i class="fas fa-check mr-2"></i>Confirm Transaction
                            </button>
                        @endif

                        @if(in_array($revenue->status, ['confirmed', 'pending']))
                            <button type="button" onclick="refundRevenue()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150">
                                <i class="fas fa-undo mr-2"></i>Process Refund
                            </button>
                        @endif

                        <a href="{{ route('admin.revenue.edit', $revenue) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            <i class="fas fa-edit mr-2"></i>Edit Transaction
                        </a>

                        <button type="button" onclick="exportTransaction()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150">
                            <i class="fas fa-download mr-2"></i>Export Details
                        </button>

                        @if($revenue->contact)
                            <a href="{{ route('contacts.show', $revenue->contact) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                                <i class="fas fa-user mr-2"></i>View Customer
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Revenue Modal -->
<div x-data="{ showModal: false }" 
     x-show="showModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="showModal = false"
     id="confirmModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
             @click="showModal = false">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Revenue</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to confirm this revenue transaction?
                        </p>
                        <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                            <div class="flex">
                                <i class="fas fa-info-circle text-blue-400 mr-2 mt-0.5"></i>
                                <p class="text-sm text-blue-700">
                                    This will mark the transaction as confirmed and cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto transition duration-150">
                    Confirm Revenue
                </button>
                <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center px-4 py-2 bg-white text-gray-900 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto transition duration-150">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Refund Revenue Modal -->
<div x-data="{ showModal: false }" 
     x-show="showModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="showModal = false"
     id="refundModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
             @click="showModal = false">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Process Refund</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to refund this revenue transaction?
                        </p>
                        <div class="mt-3 p-3 bg-yellow-50 rounded-lg">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle text-yellow-400 mr-2 mt-0.5"></i>
                                <p class="text-sm text-yellow-700">
                                    This action will mark the transaction as refunded and cannot be undone.
                                </p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="refundReason" class="block text-sm font-medium text-gray-700">Refund Reason (Optional)</label>
                            <textarea id="refundReason" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" 
                                      placeholder="Enter the reason for this refund..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" id="refundBtn" class="w-full inline-flex justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto transition duration-150">
                    Process Refund
                </button>
                <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center px-4 py-2 bg-white text-gray-900 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto transition duration-150">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmRevenue() {
    document.querySelector('[x-data] [x-show="showModal"]').dispatchEvent(new CustomEvent('show-confirm-modal'));
}

function refundRevenue() {
    document.querySelector('[x-data] [x-show="showModal"]').dispatchEvent(new CustomEvent('show-refund-modal'));
}

document.getElementById('confirmBtn').addEventListener('click', function() {
    fetch(`{{ route('admin.revenue.confirm', $revenue) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error confirming revenue: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while confirming the revenue.');
    });
});

document.getElementById('refundBtn').addEventListener('click', function() {
    const reason = document.getElementById('refundReason').value;
    
    fetch(`{{ route('admin.revenue.refund', $revenue) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error processing refund: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the refund.');
    });
});

function exportTransaction() {
    window.location.href = `{{ route('admin.revenue.export') }}?type=single&transaction_id={{ $revenue->id }}`;
}
</script>
@endsection
