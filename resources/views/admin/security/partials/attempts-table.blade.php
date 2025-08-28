<div class="table-responsive">
    <table class="table table-hover">
        <thead class="thead-light">
            <tr>
                <th width="50">
                    <input type="checkbox" id="selectAllAttempts" class="form-check-input">
                </th>
                <th>Type</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>Location</th>
                <th>Device</th>
                <th>User</th>
                <th>Time</th>
                <th>Status</th>
                <th width="120">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attempts as $attempt)
            <tr class="attempt-row" data-attempt-id="{{ $attempt->id }}">
                <td>
                    <input type="checkbox" class="form-check-input attempt-checkbox" value="{{ $attempt->id }}">
                </td>
                
                <!-- Type -->
                <td>
                    @switch($attempt->type)
                        @case('failed')
                            <span class="badge badge-danger">
                                <i class="fas fa-times-circle"></i> Failed
                            </span>
                            @break
                        @case('success')
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i> Success
                            </span>
                            @break
                        @case('blocked')
                            <span class="badge badge-warning">
                                <i class="fas fa-ban"></i> Blocked
                            </span>
                            @break
                    @endswitch
                </td>
                
                <!-- Email -->
                <td>
                    <div class="d-flex align-items-center">
                        <div>
                            <div class="font-weight-bold text-primary">
                                {{ Str::limit($attempt->email, 25) }}
                            </div>
                            @if($attempt->user)
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> {{ $attempt->user->name }}
                                </small>
                            @endif
                        </div>
                    </div>
                </td>
                
                <!-- IP Address -->
                <td>
                    <span class="font-monospace">{{ $attempt->ip_address }}</span>
                    @if(\App\Models\LoginAttempt::isBlocked($attempt->ip_address, 'ip'))
                        <div>
                            <span class="badge badge-danger badge-sm">
                                <i class="fas fa-ban"></i> IP Blocked
                            </span>
                        </div>
                    @endif
                </td>
                
                <!-- Location -->
                <td>
                    @if($attempt->location)
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $attempt->location }}
                        </small>
                    @else
                        <small class="text-muted">Unknown</small>
                    @endif
                </td>
                
                <!-- Device -->
                <td>
                    @if($attempt->device)
                        <small class="text-muted">
                            @if(Str::contains($attempt->device, 'Mobile'))
                                <i class="fas fa-mobile-alt"></i>
                            @elseif(Str::contains($attempt->device, 'Tablet'))
                                <i class="fas fa-tablet-alt"></i>
                            @else
                                <i class="fas fa-desktop"></i>
                            @endif
                            {{ $attempt->device }}
                        </small>
                    @else
                        <small class="text-muted">Unknown</small>
                    @endif
                </td>
                
                <!-- User -->
                <td>
                    @if($attempt->user)
                        <div class="d-flex align-items-center">
                            @if($attempt->user->avatar)
                                <img src="{{ $attempt->user->avatar }}" 
                                     class="rounded-circle me-2" 
                                     width="24" height="24" 
                                     alt="Avatar">
                            @else
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 24px; height: 24px; font-size: 10px; color: white;">
                                    {{ strtoupper(substr($attempt->user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="small font-weight-bold">{{ $attempt->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    ID: {{ $attempt->user->id }}
                                </div>
                            </div>
                        </div>
                    @else
                        <small class="text-muted">N/A</small>
                    @endif
                </td>
                
                <!-- Time -->
                <td>
                    <div class="small">
                        <div class="font-weight-bold text-dark">
                            {{ $attempt->created_at->format('M j, H:i') }}
                        </div>
                        <div class="text-muted">
                            {{ $attempt->created_at->diffForHumans() }}
                        </div>
                    </div>
                </td>
                
                <!-- Status -->
                <td>
                    @if($attempt->blocked_until)
                        @if($attempt->blocked_until > now())
                            <div class="small">
                                <span class="badge badge-danger">Blocked</span>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    Until: {{ $attempt->blocked_until->format('M j, H:i') }}
                                </div>
                            </div>
                        @else
                            <span class="badge badge-secondary">Expired</span>
                        @endif
                    @else
                        @switch($attempt->type)
                            @case('success')
                                <span class="badge badge-success">Active</span>
                                @break
                            @case('failed')
                                <span class="badge badge-light">Normal</span>
                                @break
                            @default
                                <span class="badge badge-light">-</span>
                        @endswitch
                    @endif
                </td>
                
                <!-- Actions -->
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-info btn-sm" 
                                onclick="quickView({{ $attempt->id }})" 
                                title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                        
                        @if($attempt->type === 'failed')
                            <!-- Block IP -->
                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                    onclick="blockIp('{{ $attempt->ip_address }}')" 
                                    title="Block IP">
                                <i class="fas fa-ban"></i>
                            </button>
                            
                            <!-- Block User -->
                            @if($attempt->email !== 'system_block')
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    onclick="blockUser('{{ $attempt->email }}')" 
                                    title="Block User">
                                <i class="fas fa-user-slash"></i>
                            </button>
                            @endif
                        @endif
                        
                        @if($attempt->blocked_until && $attempt->blocked_until > now())
                            <!-- Unblock actions -->
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-success btn-sm" 
                                        onclick="unblockIp('{{ $attempt->ip_address }}')" 
                                        title="Unblock IP">
                                    <i class="fas fa-unlock"></i>
                                </button>
                                
                                @if($attempt->email !== 'system_block')
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="unblockUser('{{ $attempt->email }}')" 
                                        title="Unblock User">
                                    <i class="fas fa-user-check"></i>
                                </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p class="h5">No login attempts found</p>
                        <p>Try adjusting your search filters or check back later.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($attempts->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="small text-muted">
        Showing {{ $attempts->firstItem() }} to {{ $attempts->lastItem() }} of {{ number_format($attempts->total()) }} results
    </div>
    <div>
        {{ $attempts->appends(request()->query())->links() }}
    </div>
</div>
@endif

<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#selectAllAttempts').on('change', function() {
        $('.attempt-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActions();
    });
    
    // Individual checkbox functionality
    $('.attempt-checkbox').on('change', function() {
        updateSelectAllState();
        updateBulkActions();
    });
    
    // Row click functionality (except on buttons)
    $('.attempt-row').on('click', function(e) {
        if (!$(e.target).closest('button, input, a').length) {
            const checkbox = $(this).find('.attempt-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });
});

function updateSelectAllState() {
    const total = $('.attempt-checkbox').length;
    const checked = $('.attempt-checkbox:checked').length;
    
    $('#selectAllAttempts').prop('indeterminate', checked > 0 && checked < total);
    $('#selectAllAttempts').prop('checked', checked === total && total > 0);
}

function updateBulkActions() {
    const selected = $('.attempt-checkbox:checked').length;
    
    if (selected > 0) {
        if (!$('#bulkActionsBar').length) {
            const bulkBar = `
                <div id="bulkActionsBar" class="alert alert-info mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${selected}</strong> attempts selected
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="bulkBlockIps()">
                                <i class="fas fa-ban"></i> Block IPs
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkBlockUsers()">
                                <i class="fas fa-user-slash"></i> Block Users
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('.table-responsive').before(bulkBar);
        } else {
            $('#bulkActionsBar strong').text(selected);
        }
    } else {
        $('#bulkActionsBar').remove();
    }
}

function clearSelection() {
    $('.attempt-checkbox, #selectAllAttempts').prop('checked', false);
    updateBulkActions();
}

function bulkBlockIps() {
    const selectedIps = [];
    $('.attempt-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const ip = row.find('td:nth-child(4) span').text().trim();
        if (ip && !selectedIps.includes(ip)) {
            selectedIps.push(ip);
        }
    });
    
    if (selectedIps.length === 0) {
        toastr.warning('No valid IP addresses selected');
        return;
    }
    
    if (confirm(`Block ${selectedIps.length} IP address(es)?`)) {
        // Implementation for bulk blocking IPs
        toastr.info(`Bulk IP blocking would be implemented here for ${selectedIps.length} IPs`);
    }
}

function bulkBlockUsers() {
    const selectedEmails = [];
    $('.attempt-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const email = row.find('td:nth-child(3) .text-primary').text().trim();
        if (email && email !== 'system_block' && !selectedEmails.includes(email)) {
            selectedEmails.push(email);
        }
    });
    
    if (selectedEmails.length === 0) {
        toastr.warning('No valid email addresses selected');
        return;
    }
    
    if (confirm(`Block ${selectedEmails.length} user(s)?`)) {
        // Implementation for bulk blocking users
        toastr.info(`Bulk user blocking would be implemented here for ${selectedEmails.length} users`);
    }
}
</script>
