@extends('layouts.app')

@section('title', 'Revenue Transactions - Admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-receipt me-2 text-success"></i>
                        Revenue Transactions
                    </h1>
                    <p class="text-muted">
                        Detailed revenue tracking and transaction management
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.revenue.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Revenue
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportTransactions()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Transactions</div>
                            <div class="h5 mb-0 text-success">{{ number_format($summaryStats['total_transactions']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Amount</div>
                            <div class="h5 mb-0 text-primary">${{ number_format($summaryStats['total_amount'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Confirmed</div>
                            <div class="h5 mb-0 text-success">${{ number_format($summaryStats['confirmed_amount'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Pending</div>
                            <div class="h5 mb-0 text-warning">${{ number_format($summaryStats['pending_amount'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.revenue.transactions') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" onchange="submitFilter()">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\Revenue::getStatuses() as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="channel" class="form-label">Channel</label>
                        <select name="channel" id="channel" class="form-select" onchange="submitFilter()">
                            <option value="">All Channels</option>
                            @foreach(\App\Models\Revenue::getChannels() as $key => $label)
                                <option value="{{ $key }}" {{ request('channel') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="form-select" onchange="submitFilter()">
                            <option value="">All Types</option>
                            @foreach(\App\Models\Revenue::getTypes() as $key => $label)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Transaction ID, Customer..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Range</label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="date" name="date_from" class="form-control" 
                                       value="{{ request('date_from') }}" onchange="submitFilter()">
                            </div>
                            <div class="col">
                                <input type="date" name="date_to" class="form-control" 
                                       value="{{ request('date_to') }}" onchange="submitFilter()">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <a href="{{ route('admin.revenue.transactions') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenues as $revenue)
                            <tr>
                                <td>
                                    <div class="font-monospace small">{{ $revenue->transaction_id }}</div>
                                    @if($revenue->reference_id)
                                        <div class="text-muted small">Ref: {{ $revenue->reference_id }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($revenue->contact)
                                        <div>
                                            <a href="{{ route('contacts.show', $revenue->contact) }}" class="text-decoration-none">
                                                {{ $revenue->contact->first_name }} {{ $revenue->contact->last_name }}
                                            </a>
                                        </div>
                                        <div class="text-muted small">{{ $revenue->contact->email }}</div>
                                    @else
                                        <div>{{ $revenue->customer_name ?: 'N/A' }}</div>
                                        @if($revenue->customer_email)
                                            <div class="text-muted small">{{ $revenue->customer_email }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $revenue->formatted_amount }}</div>
                                    @if($revenue->cost > 0)
                                        <div class="text-muted small">Net: {{ $revenue->formatted_net_revenue }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($revenue->type) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="{{ $revenue->channel_icon }} me-1"></i>
                                        {{ ucfirst($revenue->channel) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $revenue->status_badge }}">
                                        {{ ucfirst($revenue->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $revenue->revenue_date->format('M d, Y') }}</div>
                                    <div class="text-muted small">{{ $revenue->revenue_date->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.revenue.show', $revenue) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($revenue->status === 'pending')
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="confirmRevenue({{ $revenue->id }})" title="Confirm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        @if(in_array($revenue->status, ['confirmed', 'pending']))
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="refundRevenue({{ $revenue->id }})" title="Refund">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.revenue.edit', $revenue) }}" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-receipt fa-2x mb-3"></i>
                                        <div>No revenue transactions found</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($revenues->hasPages())
            <div class="card-footer">
                {{ $revenues->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Confirm Revenue Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Revenue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to confirm this revenue transaction?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmBtn">Confirm Revenue</button>
            </div>
        </div>
    </div>
</div>

<!-- Refund Revenue Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Refund Revenue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to refund this revenue transaction?</p>
                <div class="mb-3">
                    <label for="refundReason" class="form-label">Refund Reason (Optional)</label>
                    <textarea id="refundReason" class="form-control" rows="3" 
                              placeholder="Enter the reason for this refund..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="refundBtn">Process Refund</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentRevenueId = null;

function submitFilter() {
    document.getElementById('filterForm').submit();
}

function confirmRevenue(revenueId) {
    currentRevenueId = revenueId;
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function refundRevenue(revenueId) {
    currentRevenueId = revenueId;
    document.getElementById('refundReason').value = '';
    new bootstrap.Modal(document.getElementById('refundModal')).show();
}

document.getElementById('confirmBtn').addEventListener('click', function() {
    if (!currentRevenueId) return;
    
    fetch(`/admin/revenue/${currentRevenueId}/confirm`, {
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
    if (!currentRevenueId) return;
    
    const reason = document.getElementById('refundReason').value;
    
    fetch(`/admin/revenue/${currentRevenueId}/refund`, {
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

function exportTransactions() {
    const params = new URLSearchParams(window.location.search);
    params.set('type', 'transactions');
    
    window.location.href = `{{ route('admin.revenue.export') }}?${params.toString()}`;
}
</script>
@endsection
