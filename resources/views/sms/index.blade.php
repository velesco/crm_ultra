@extends('layouts.app')

@section('title', 'SMS Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 dark:text-white">SMS Management</h1>
            <p class="mb-0 text-gray-600 dark:text-gray-400">Send and manage SMS campaigns</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sms.create') }}" class="btn btn-primary">
                <i class="fas fa-paper-plane me-2"></i>Compose SMS
            </a>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkSmsModal">
                <i class="fas fa-users me-2"></i>Bulk SMS
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Sent
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_sent']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today Sent
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['today_sent']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Delivery Rate
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        {{ $stats['delivery_rate'] }}%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $stats['delivery_rate'] }}%" 
                                             aria-valuenow="{{ $stats['delivery_rate'] }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                This Month Cost
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['this_month_cost'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filters & Search</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearFilters()">
                Clear Filters
            </button>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sms.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Message content, contact name...">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="provider" class="form-label">Provider</label>
                    <select class="form-select" id="provider" name="provider">
                        <option value="">All Providers</option>
                        @foreach($stats['providers_stats'] as $provider)
                            <option value="{{ $provider->provider }}" 
                                    {{ request('provider') === $provider->provider ? 'selected' : '' }}>
                                {{ ucfirst($provider->provider) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SMS Messages Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">SMS Messages</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Provider</th>
                            <th>Cost</th>
                            <th>Sent At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3">
                                            {{ substr($message->contact->name ?? 'N/A', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold">
                                                {{ $message->contact->name ?? 'Unknown Contact' }}
                                            </div>
                                            @if($message->contact->email)
                                                <small class="text-muted">{{ $message->contact->email }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-monospace">{{ $message->phone_number }}</span>
                                </td>
                                <td>
                                    <div class="message-preview" style="max-width: 200px;">
                                        {{ Str::limit($message->message, 50) }}
                                        @if(strlen($message->message) > 50)
                                            <small class="text-muted d-block">
                                                {{ strlen($message->message) }} characters
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'pending' => 'warning',
                                            'sent' => 'success',
                                            'delivered' => 'success',
                                            'failed' => 'danger',
                                            'scheduled' => 'info',
                                            'cancelled' => 'secondary'
                                        ];
                                        $statusClass = $statusClasses[$message->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($message->status) }}
                                    </span>
                                    @if($message->status === 'scheduled' && $message->scheduled_at)
                                        <small class="text-muted d-block">
                                            {{ $message->scheduled_at->format('M j, Y g:i A') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($message->provider)
                                        <span class="badge bg-light text-dark">
                                            {{ ucfirst($message->provider) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($message->cost > 0)
                                        <span class="font-monospace">${{ number_format($message->cost, 4) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($message->sent_at)
                                        <span title="{{ $message->sent_at->format('Y-m-d H:i:s') }}">
                                            {{ $message->sent_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-muted">Not sent</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('sms.show', $message) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($message->status === 'scheduled')
                                            <a href="{{ route('sms.edit', $message) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="cancelSms({{ $message->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif

                                        @if($message->status === 'failed')
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="resendSms({{ $message->id }})">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @endif

                                        @if(in_array($message->status, ['scheduled', 'failed', 'draft']))
                                            <form action="{{ route('sms.destroy', $message) }}" method="POST" 
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to delete this SMS?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <div>No SMS messages found</div>
                                    <small>Start by <a href="{{ route('sms.create') }}">composing your first SMS</a></small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($messages->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $messages->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk SMS Modal -->
<div class="modal fade" id="bulkSmsModal" tabindex="-1" aria-labelledby="bulkSmsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkSmsModalLabel">Send Bulk SMS to Segment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkSmsForm" method="POST" action="{{ route('sms.send-to-segment') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="segment_id" class="form-label">Contact Segment</label>
                        <select class="form-select" id="segment_id" name="segment_id" required>
                            <option value="">Select a segment</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_message" class="form-label">Message</label>
                        <textarea class="form-control" id="bulk_message" name="message" rows="4" 
                                  placeholder="Enter your SMS message..." required maxlength="1600"></textarea>
                        <div class="form-text">
                            <span id="bulk-char-count">0</span>/1600 characters
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="bulk_provider_id" class="form-label">SMS Provider</label>
                            <select class="form-select" id="bulk_provider_id" name="provider_id">
                                <option value="">Default Provider</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bulk_schedule_at" class="form-label">Schedule At (Optional)</label>
                            <input type="datetime-local" class="form-control" id="bulk_schedule_at" name="schedule_at">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
    font-weight: 600;
}

.message-preview {
    word-break: break-word;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.progress-sm {
    height: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
function clearFilters() {
    window.location.href = '{{ route("sms.index") }}';
}

function cancelSms(smsId) {
    if (confirm('Are you sure you want to cancel this scheduled SMS?')) {
        fetch(`/sms/${smsId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to cancel SMS: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the SMS');
        });
    }
}

function resendSms(smsId) {
    if (confirm('Are you sure you want to resend this SMS?')) {
        fetch(`/sms/${smsId}/resend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to resend SMS: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while resending the SMS');
        });
    }
}

// Character counter for bulk SMS
document.getElementById('bulk_message').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('bulk-char-count').textContent = count;
    
    if (count > 1600) {
        this.style.borderColor = '#dc3545';
        document.getElementById('bulk-char-count').style.color = '#dc3545';
    } else {
        this.style.borderColor = '';
        document.getElementById('bulk-char-count').style.color = '';
    }
});
</script>
@endpush
