@forelse($recentLogs as $log)
<div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
    <div class="me-3">
        <i class="{{ $log->type_icon }} fa-2x text-primary"></i>
    </div>
    <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-1">{{ ucfirst($log->webhook_type) }} - {{ ucfirst($log->event_type) }}</h6>
                <p class="mb-1 text-muted">{{ ucfirst($log->provider) }}</p>
                <small class="text-muted">{{ $log->webhook_received_at->diffForHumans() }}</small>
            </div>
            <div class="text-end">
                <span class="badge {{ $log->status_badge_class }} mb-2">{{ ucfirst($log->status) }}</span>
                @if($log->processing_time)
                    <div><small class="text-muted">{{ number_format($log->processing_time, 2) }}ms</small></div>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="text-center py-4">
    <i class="fas fa-webhook fa-3x text-muted mb-3"></i>
    <p class="text-muted">No recent webhook activity</p>
</div>
@endforelse
