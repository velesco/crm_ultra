@extends('layouts.app')

@section('title', 'Login Attempts')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-2 text-gray-800">
                                <i class="fas fa-list-alt text-primary"></i>
                                Login Attempts
                            </h1>
                            <p class="text-muted mb-0">Detailed view of all login attempts and security events</p>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('admin.security.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <a href="{{ route('admin.security.export') }}" class="btn btn-outline-success">
                                <i class="fas fa-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter"></i> Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.security.login-attempts') }}" id="filtersForm">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Type</label>
                                    <select name="type" class="form-control form-control-sm">
                                        <option value="">All Types</option>
                                        <option value="failed" {{ request('type') === 'failed' ? 'selected' : '' }}>Failed</option>
                                        <option value="success" {{ request('type') === 'success' ? 'selected' : '' }}>Success</option>
                                        <option value="blocked" {{ request('type') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Email</label>
                                    <input type="email" name="email" class="form-control form-control-sm" 
                                           value="{{ request('email') }}" placeholder="Search by email...">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small font-weight-bold">IP Address</label>
                                    <input type="text" name="ip_address" class="form-control form-control-sm" 
                                           value="{{ request('ip_address') }}" placeholder="Search by IP...">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Date From</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" 
                                           value="{{ request('date_from') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small font-weight-bold">Date To</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" 
                                           value="{{ request('date_to') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-1 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        @if(request()->hasAny(['type', 'email', 'ip_address', 'date_from', 'date_to']))
                        <div class="row mt-2">
                            <div class="col-12">
                                <a href="{{ route('admin.security.login-attempts') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Attempts Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Login Attempts 
                        <span class="badge badge-light ml-2">{{ $attempts->total() }} total</span>
                    </h6>
                    
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshTable()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="toggleAutoRefresh()">
                            <i class="fas fa-play" id="autoRefreshIcon"></i> Auto Refresh
                        </button>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div id="attemptsTableContainer">
                        @include('admin.security.partials.attempts-table', ['attempts' => $attempts])
                    </div>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer">
                    {{ $attempts->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block Actions Modal -->
<div class="modal fade" id="blockActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blockModalTitle">Block Actions</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="blockActionForm">
                <div class="modal-body">
                    <input type="hidden" id="blockTargetValue" name="target_value">
                    <input type="hidden" id="blockTargetType" name="target_type">
                    
                    <div class="form-group">
                        <label id="blockTargetLabel">Target</label>
                        <input type="text" class="form-control" id="displayBlockTarget" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Block Duration (hours)</label>
                        <select class="form-control" name="duration" required>
                            <option value="1">1 hour</option>
                            <option value="6">6 hours</option>
                            <option value="24" selected>24 hours</option>
                            <option value="168">1 week</option>
                            <option value="720">1 month</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Reason (optional)</label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Reason for blocking..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="blockSubmitBtn">
                        <i class="fas fa-ban"></i> Block
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Login Attempt Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="quickViewContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let autoRefreshInterval = null;
let isAutoRefreshActive = false;

$(document).ready(function() {
    setupEventHandlers();
});

function setupEventHandlers() {
    // Block action form
    $('#blockActionForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const targetType = $('#blockTargetType').val();
        
        let endpoint;
        let data = {};
        
        if (targetType === 'ip') {
            endpoint = '{{ route("admin.security.block-ip") }}';
            data.ip_address = $('#blockTargetValue').val();
        } else {
            endpoint = '{{ route("admin.security.block-user") }}';
            data.email = $('#blockTargetValue').val();
        }
        
        data.duration = formData.get('duration');
        data.reason = formData.get('reason');
        
        $.post(endpoint, data)
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#blockActionsModal').modal('hide');
                    refreshTable();
                }
            })
            .fail(function(xhr) {
                toastr.error('Error blocking target: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    });
}

function blockIp(ipAddress) {
    $('#blockTargetValue').val(ipAddress);
    $('#blockTargetType').val('ip');
    $('#displayBlockTarget').val(ipAddress);
    $('#blockTargetLabel').text('IP Address');
    $('#blockModalTitle').text('Block IP Address');
    $('#blockSubmitBtn').html('<i class="fas fa-ban"></i> Block IP');
    $('#blockActionsModal').modal('show');
}

function blockUser(email) {
    $('#blockTargetValue').val(email);
    $('#blockTargetType').val('user');
    $('#displayBlockTarget').val(email);
    $('#blockTargetLabel').text('Email Address');
    $('#blockModalTitle').text('Block User Email');
    $('#blockSubmitBtn').html('<i class="fas fa-ban"></i> Block User');
    $('#blockActionsModal').modal('show');
}

function unblockIp(ipAddress) {
    if (confirm(`Are you sure you want to unblock IP ${ipAddress}?`)) {
        $.post('{{ route("admin.security.unblock-ip") }}', { ip_address: ipAddress })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    refreshTable();
                }
            })
            .fail(function(xhr) {
                toastr.error('Error unblocking IP: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    }
}

function unblockUser(email) {
    if (confirm(`Are you sure you want to unblock user ${email}?`)) {
        $.post('{{ route("admin.security.unblock-user") }}', { email: email })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    refreshTable();
                }
            })
            .fail(function(xhr) {
                toastr.error('Error unblocking user: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    }
}

function quickView(attemptId) {
    $('#quickViewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $('#quickViewModal').modal('show');
    
    // In a real implementation, you would fetch detailed info about the attempt
    // For now, we'll show a placeholder
    setTimeout(() => {
        $('#quickViewContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <strong>Attempt ID:</strong> ${attemptId}<br>
                    <strong>Status:</strong> <span class="badge badge-danger">Failed</span><br>
                    <strong>Time:</strong> 2 minutes ago<br>
                </div>
                <div class="col-md-6">
                    <strong>User Agent:</strong> Mozilla/5.0...<br>
                    <strong>Location:</strong> Bucharest, RO<br>
                    <strong>Device:</strong> Desktop<br>
                </div>
            </div>
        `);
    }, 500);
}

function refreshTable() {
    const currentUrl = window.location.href;
    
    $.get(currentUrl, { ajax: true })
        .done(function(html) {
            $('#attemptsTableContainer').html(html);
            toastr.info('Table refreshed');
        })
        .fail(function() {
            toastr.error('Error refreshing table');
        });
}

function toggleAutoRefresh() {
    if (isAutoRefreshActive) {
        clearInterval(autoRefreshInterval);
        isAutoRefreshActive = false;
        $('#autoRefreshIcon').removeClass('fa-stop').addClass('fa-play');
        toastr.info('Auto-refresh stopped');
    } else {
        autoRefreshInterval = setInterval(refreshTable, 10000); // Every 10 seconds
        isAutoRefreshActive = true;
        $('#autoRefreshIcon').removeClass('fa-play').addClass('fa-stop');
        toastr.success('Auto-refresh started (every 10s)');
    }
}

// Filter form auto-submit on change
$('#filtersForm select, #filtersForm input[type="date"]').on('change', function() {
    $('#filtersForm').submit();
});
</script>
@endpush
