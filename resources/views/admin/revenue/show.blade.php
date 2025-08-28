@extends('layouts.app')

@section('title', 'Revenue Transaction - Admin')

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
                            <li class="breadcrumb-item active">{{ $revenue->transaction_id }}</li>
                        </ol>
                    </nav>
                    <h1 class="h2 mb-1">
                        <i class="fas fa-receipt me-2 text-success"></i>
                        Transaction Details
                    </h1>
                </div>
                <div>
                    <a href="{{ route('admin.revenue.edit', $revenue) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.revenue.transactions') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Transaction Overview -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Transaction Overview</h5>
                        <span class="badge {{ $revenue->status_badge }} fs-6">
                            {{ ucfirst($revenue->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">TRANSACTION ID</label>
                            <div class="font-monospace">{{ $revenue->transaction_id }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">REFERENCE ID</label>
                            <div>{{ $revenue->reference_id ?: 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">AMOUNT</label>
                            <div class="h4 text-success mb-0">{{ $revenue->formatted_amount }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">NET REVENUE</label>
                            <div class="h4 text-primary mb-0">{{ $revenue->formatted_net_revenue }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">TYPE</label>
                            <div>
                                <span class="badge bg-secondary">{{ ucfirst($revenue->type) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">CHANNEL</label>
                            <div class="d-flex align-items-center">
                                <i class="{{ $revenue->channel_icon }} me-2"></i>
                                {{ ucfirst($revenue->channel) }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">REVENUE DATE</label>
                            <div>{{ $revenue->revenue_date->format('F j, Y \a\t g:i A') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">CREATED</label>
                            <div>{{ $revenue->created_at->format('F j, Y \a\t g:i A') }}</div>
                        </div>
                    </div>

                    @if($revenue->notes)
                        <div class="mt-4">
                            <label class="form-label text-muted small fw-bold">NOTES</label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($revenue->notes)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Financial Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Gross Amount:</td>
                                    <td class="text-end fw-bold">${{ number_format($revenue->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Cost:</td>
                                    <td class="text-end">-${{ number_format($revenue->cost, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tax Amount:</td>
                                    <td class="text-end">-${{ number_format($revenue->tax_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Commission:</td>
                                    <td class="text-end">-${{ number_format($revenue->commission, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted fw-bold">Net Revenue:</td>
                                    <td class="text-end fw-bold text-success h5 mb-0">
                                        ${{ number_format($revenue->net_revenue, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Metadata -->
            @if($revenue->metadata)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0">Additional Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    @foreach($revenue->metadata as $key => $value)
                                        <tr>
                                            <td class="text-muted">{{ ucwords(str_replace('_', ' ', $key)) }}:</td>
                                            <td>
                                                @if(is_array($value) || is_object($value))
                                                    <pre class="small mb-0">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Customer Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    @if($revenue->contact)
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <div class="fw-bold">
                                    <a href="{{ route('contacts.show', $revenue->contact) }}" class="text-decoration-none">
                                        {{ $revenue->contact->first_name }} {{ $revenue->contact->last_name }}
                                    </a>
                                </div>
                                <div class="text-muted small">Registered Customer</div>
                            </div>
                        </div>
                        
                        <div class="border-top pt-3">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <strong>Email:</strong><br>
                                    <a href="mailto:{{ $revenue->contact->email }}" class="text-decoration-none">
                                        {{ $revenue->contact->email }}
                                    </a>
                                </div>
                                @if($revenue->contact->phone)
                                    <div class="col-12 mb-2">
                                        <strong>Phone:</strong><br>
                                        <a href="tel:{{ $revenue->contact->phone }}" class="text-decoration-none">
                                            {{ $revenue->contact->phone }}
                                        </a>
                                    </div>
                                @endif
                                @if($revenue->contact->company)
                                    <div class="col-12 mb-2">
                                        <strong>Company:</strong><br>
                                        {{ $revenue->contact->company }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user-slash text-white"></i>
                            </div>
                            <div>
                                <div class="fw-bold">{{ $revenue->customer_name ?: 'Unknown Customer' }}</div>
                                <div class="text-muted small">Guest Customer</div>
                            </div>
                        </div>
                        
                        @if($revenue->customer_email)
                            <div class="border-top pt-3">
                                <strong>Email:</strong><br>
                                <a href="mailto:{{ $revenue->customer_email }}" class="text-decoration-none">
                                    {{ $revenue->customer_email }}
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Status Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Transaction Created</h6>
                                <p class="text-muted small mb-0">
                                    {{ $revenue->created_at->format('M d, Y \a\t g:i A') }}
                                    @if($revenue->creator)
                                        <br>by {{ $revenue->creator->first_name }} {{ $revenue->creator->last_name }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($revenue->confirmed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Transaction Confirmed</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $revenue->confirmed_at->format('M d, Y \a\t g:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($revenue->refunded_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Transaction Refunded</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $revenue->refunded_at->format('M d, Y \a\t g:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($revenue->status === 'pending')
                            <button type="button" class="btn btn-success" onclick="confirmRevenue()">
                                <i class="fas fa-check me-2"></i>Confirm Transaction
                            </button>
                        @endif

                        @if(in_array($revenue->status, ['confirmed', 'pending']))
                            <button type="button" class="btn btn-warning" onclick="refundRevenue()">
                                <i class="fas fa-undo me-2"></i>Process Refund
                            </button>
                        @endif

                        <a href="{{ route('admin.revenue.edit', $revenue) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-2"></i>Edit Transaction
                        </a>

                        <button type="button" class="btn btn-outline-secondary" onclick="exportTransaction()">
                            <i class="fas fa-download me-2"></i>Export Details
                        </button>

                        @if($revenue->contact)
                            <a href="{{ route('contacts.show', $revenue->contact) }}" class="btn btn-outline-info">
                                <i class="fas fa-user me-2"></i>View Customer
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
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
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    This will mark the transaction as confirmed and cannot be undone.
                </div>
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
                <h5 class="modal-title">Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to refund this revenue transaction?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action will mark the transaction as refunded and cannot be undone.
                </div>
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

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    padding-left: 20px;
}

.timeline-content h6 {
    font-size: 0.875rem;
    font-weight: 600;
}
</style>

<script>
function confirmRevenue() {
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function refundRevenue() {
    document.getElementById('refundReason').value = '';
    new bootstrap.Modal(document.getElementById('refundModal')).show();
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
