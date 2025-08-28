{{-- Logs Table --}}
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th width="100">Level</th>
                <th width="120">Category</th>
                <th width="150">Date/Time</th>
                <th width="120">User</th>
                <th width="150">Action</th>
                <th>Message</th>
                <th width="100">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr class="log-row" data-level="{{ $log->level }}">
                    <td>
                        <span class="badge {{ $log->level_badge_class }}">
                            {{ ucfirst($log->level) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="{{ $log->category_icon }} me-2"></i>
                            <span class="text-capitalize">{{ $log->category }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="small">
                            <div class="fw-medium">{{ $log->occurred_at->format('M d, Y') }}</div>
                            <div class="text-muted">{{ $log->occurred_at->format('H:i:s') }}</div>
                        </div>
                    </td>
                    <td>
                        @if($log->user)
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <small class="text-white fw-bold">{{ substr($log->user->name, 0, 1) }}</small>
                                </div>
                                <div class="small">
                                    <div class="fw-medium">{{ $log->user->name }}</div>
                                    <div class="text-muted">{{ $log->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </td>
                    <td>
                        <code class="small">{{ $log->action }}</code>
                    </td>
                    <td>
                        <div class="log-message">
                            {{ Str::limit($log->message, 100) }}
                            @if($log->description)
                                <small class="text-muted d-block mt-1">
                                    {{ Str::limit($log->description, 80) }}
                                </small>
                            @endif
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.system-logs.show', $log) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           data-bs-toggle="tooltip" 
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-search fa-2x mb-3"></i>
                            <p class="mb-0">No logs found matching your criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($logs->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">
            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
        </div>
        <div>
            {{ $logs->appends(request()->query())->links() }}
        </div>
    </div>
@endif

<script>
// Initialize tooltips
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Add hover effects for log rows
    $('.log-row').on('mouseenter', function() {
        $(this).addClass('table-active');
    }).on('mouseleave', function() {
        $(this).removeClass('table-active');
    });
    
    // Color-code rows based on log level
    $('.log-row').each(function() {
        const level = $(this).data('level');
        if (level === 'error' || level === 'critical') {
            $(this).addClass('border-start border-danger border-3');
        } else if (level === 'warning') {
            $(this).addClass('border-start border-warning border-3');
        } else if (level === 'info') {
            $(this).addClass('border-start border-info border-3');
        }
    });
});
</script>
