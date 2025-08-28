@extends('layouts.app')

@section('title', 'Security Dashboard')

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
                                <i class="fas fa-shield-alt text-primary"></i>
                                Security Dashboard
                            </h1>
                            <p class="text-muted mb-0">Monitor login attempts, blocked IPs, and security threats</p>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('admin.security.login-attempts') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> View All Attempts
                            </a>
                            <button type="button" class="btn btn-outline-secondary" onclick="clearOldAttempts()">
                                <i class="fas fa-trash"></i> Clear Old
                            </button>
                            <a href="{{ route('admin.security.export') }}" class="btn btn-outline-success">
                                <i class="fas fa-download"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Attempts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-attempts">
                                {{ number_format($stats['total_attempts']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Failed Attempts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="failed-attempts">
                                {{ number_format($stats['failed_attempts']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Blocked Attempts</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="blocked-attempts">
                                {{ number_format($stats['blocked_attempts']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Currently Blocked</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="currently-blocked">
                                {{ number_format($stats['currently_blocked']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Security Activity Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Security Activity</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="loadChartData('day')">24h</button>
                        <button type="button" class="btn btn-outline-primary" onclick="loadChartData('week')">7d</button>
                        <button type="button" class="btn btn-outline-primary" onclick="loadChartData('month')">30d</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="securityChart" height="320"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Statistics -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Today's Activity</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="small text-gray-500">Total Attempts</div>
                        <div class="h4 mb-0">{{ number_format($stats['today']['total']) }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-gray-500">Failed Attempts</div>
                        <div class="h5 mb-0 text-danger">{{ number_format($stats['today']['failed']) }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-gray-500">Blocked Attempts</div>
                        <div class="h5 mb-0 text-warning">{{ number_format($stats['today']['blocked']) }}</div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <div class="small text-gray-500">This Week</div>
                        <div class="h6 mb-0">{{ number_format($stats['this_week']['total']) }} total</div>
                        <div class="small text-danger">{{ number_format($stats['this_week']['failed']) }} failed</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="small text-gray-500">This Month</div>
                        <div class="h6 mb-0">{{ number_format($stats['this_month']['total']) }} total</div>
                        <div class="small text-danger">{{ number_format($stats['this_month']['failed']) }} failed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row -->
    <div class="row">
        <!-- Suspicious IPs -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Suspicious IP Addresses</h6>
                    <span class="badge badge-warning">Last 24h</span>
                </div>
                <div class="card-body">
                    @if(count($suspiciousIps) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Failed Attempts</th>
                                        <th>Last Attempt</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suspiciousIps as $ip)
                                    <tr>
                                        <td class="font-weight-bold">{{ $ip['ip_address'] }}</td>
                                        <td>
                                            <span class="badge badge-danger">{{ $ip['failed_count'] }}</span>
                                            <small class="text-muted">/ {{ $ip['attempt_count'] }}</small>
                                        </td>
                                        <td class="small text-muted">{{ \Carbon\Carbon::parse($ip['last_attempt'])->diffForHumans() }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" onclick="blockIp('{{ $ip['ip_address'] }}')">
                                                <i class="fas fa-ban"></i> Block
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shield-check fa-3x text-success mb-3"></i>
                            <p class="text-muted">No suspicious activity detected</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Failed Emails -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Top Failed Emails</h6>
                    <span class="badge badge-info">Last 7 days</span>
                </div>
                <div class="card-body">
                    @if(count($topFailedEmails) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Attempts</th>
                                        <th>Last Attempt</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topFailedEmails as $email)
                                    <tr>
                                        <td class="small">{{ Str::limit($email['email'], 25) }}</td>
                                        <td>
                                            <span class="badge badge-danger">{{ $email['attempt_count'] }}</span>
                                        </td>
                                        <td class="small text-muted">{{ \Carbon\Carbon::parse($email['last_attempt'])->diffForHumans() }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" onclick="blockUser('{{ $email['email'] }}')">
                                                <i class="fas fa-ban"></i> Block
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-check fa-3x text-success mb-3"></i>
                            <p class="text-muted">No failed login patterns detected</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Currently Blocked Users -->
    @if(count($blockedUsers) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Currently Blocked Users & IPs</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Email/IP</th>
                                    <th>IP Address</th>
                                    <th>Blocked Until</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($blockedUsers as $blocked)
                                <tr>
                                    <td class="font-weight-bold">{{ $blocked['email'] }}</td>
                                    <td class="small text-muted">{{ $blocked['ip_address'] }}</td>
                                    <td class="small">
                                        @if($blocked['blocked_until'])
                                            {{ \Carbon\Carbon::parse($blocked['blocked_until'])->format('M j, Y H:i') }}
                                            <div class="small text-danger">
                                                {{ \Carbon\Carbon::parse($blocked['blocked_until'])->diffForHumans() }}
                                            </div>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if(isset($blocked['metadata']['reason']))
                                            {{ $blocked['metadata']['reason'] }}
                                        @else
                                            Automatic block
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success" 
                                                onclick="unblockIp('{{ $blocked['ip_address'] }}')">
                                            <i class="fas fa-unlock"></i> Unblock IP
                                        </button>
                                        @if($blocked['email'] !== 'system_block')
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="unblockUser('{{ $blocked['email'] }}')">
                                            <i class="fas fa-user-unlock"></i> Unblock User
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Block IP Modal -->
<div class="modal fade" id="blockIpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Block IP Address</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="blockIpForm">
                <div class="modal-body">
                    <input type="hidden" id="blockIpAddress" name="ip_address">
                    
                    <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" class="form-control" id="displayIpAddress" readonly>
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
                        <textarea class="form-control" name="reason" rows="3" placeholder="Reason for blocking this IP..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban"></i> Block IP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Block User Modal -->
<div class="modal fade" id="blockUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Block User Email</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="blockUserForm">
                <div class="modal-body">
                    <input type="hidden" id="blockUserEmail" name="email">
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="text" class="form-control" id="displayUserEmail" readonly>
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
                        <textarea class="form-control" name="reason" rows="3" placeholder="Reason for blocking this user..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban"></i> Block User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Clear Old Attempts Modal -->
<div class="modal fade" id="clearOldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear Old Login Attempts</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="clearOldForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Delete attempts older than (days)</label>
                        <select class="form-control" name="days" required>
                            <option value="7">7 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="90">90 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This action cannot be undone. Old login attempts will be permanently deleted.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Clear Old Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
let securityChart;
let currentPeriod = 'day';

$(document).ready(function() {
    loadChartData('day');
    setupEventHandlers();
    
    // Auto refresh every 30 seconds
    setInterval(refreshStats, 30000);
});

function setupEventHandlers() {
    // Block IP form
    $('#blockIpForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.post('{{ route("admin.security.block-ip") }}', Object.fromEntries(formData))
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#blockIpModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                toastr.error('Error blocking IP: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    });
    
    // Block User form
    $('#blockUserForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.post('{{ route("admin.security.block-user") }}', Object.fromEntries(formData))
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#blockUserModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                toastr.error('Error blocking user: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    });
    
    // Clear Old form
    $('#clearOldForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.post('{{ route("admin.security.clear-old") }}', Object.fromEntries(formData))
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#clearOldModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                toastr.error('Error clearing data: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    });
}

function loadChartData(period) {
    currentPeriod = period;
    
    // Update button states
    $('.btn-group .btn').removeClass('active');
    $(event.target).addClass('active');
    
    $.get('{{ route("admin.security.chart-data") }}', { period: period })
        .done(function(data) {
            updateChart(data);
        })
        .fail(function() {
            toastr.error('Error loading chart data');
        });
}

function updateChart(data) {
    const ctx = document.getElementById('securityChart').getContext('2d');
    
    if (securityChart) {
        securityChart.destroy();
    }
    
    securityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.label),
            datasets: [{
                label: 'Failed Attempts',
                data: data.map(item => item.failed),
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.4
            }, {
                label: 'Successful Logins',
                data: data.map(item => item.success),
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4
            }, {
                label: 'Blocked Attempts',
                data: data.map(item => item.blocked),
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

function blockIp(ipAddress) {
    $('#blockIpAddress').val(ipAddress);
    $('#displayIpAddress').val(ipAddress);
    $('#blockIpModal').modal('show');
}

function blockUser(email) {
    $('#blockUserEmail').val(email);
    $('#displayUserEmail').val(email);
    $('#blockUserModal').modal('show');
}

function unblockIp(ipAddress) {
    if (confirm(`Are you sure you want to unblock IP ${ipAddress}?`)) {
        $.post('{{ route("admin.security.unblock-ip") }}', { ip_address: ipAddress })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1500);
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
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                toastr.error('Error unblocking user: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    }
}

function clearOldAttempts() {
    $('#clearOldModal').modal('show');
}

function refreshStats() {
    $.get('{{ route("admin.security.index") }}?ajax=1')
        .done(function(data) {
            $('#total-attempts').text(data.stats.total_attempts.toLocaleString());
            $('#failed-attempts').text(data.stats.failed_attempts.toLocaleString());
            $('#blocked-attempts').text(data.stats.blocked_attempts.toLocaleString());
            $('#currently-blocked').text(data.stats.currently_blocked.toLocaleString());
        })
        .fail(function() {
            // Silently fail - don't show error for auto-refresh
        });
}
</script>
@endpush
