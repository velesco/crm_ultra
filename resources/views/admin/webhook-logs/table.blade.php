@forelse($logs as $log)
<tr class="align-middle">
    <td>
        <div class="form-check">
            <input class="form-check-input webhook-checkbox" type="checkbox" value="{{ $log->id }}"
                   {{ $log->status === 'failed' ? '' : 'disabled' }}>
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <i class="{{ $log->type_icon }} me-2"></i>
            <div>
                <div class="fw-semibold">{{ ucfirst($log->webhook_type) }}</div>
                @if($log->webhook_id)
                    <small class="text-muted">ID: {{ $log->webhook_id }}</small>
                @endif
            </div>
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <i class="{{ $log->provider_icon }} me-2"></i>
            <span class="fw-medium">{{ ucfirst($log->provider) }}</span>
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <i class="{{ $log->event_type_icon }} me-2"></i>
            <span>{{ ucfirst(str_replace('_', ' ', $log->event_type)) }}</span>
        </div>
    </td>
    <td>
        <span class="badge {{ $log->status_badge_class }}">
            {{ ucfirst($log->status) }}
        </span>
        @if($log->status === 'failed' && $log->next_retry_at)
            <br><small class="text-muted">
                Retry: {{ $log->next_retry_at->format('M j, H:i') }}
            </small>
        @endif
    </td>
    <td>
        <div class="text-center">
            <span class="badge {{ $log->attempts > 1 ? 'bg-warning' : 'bg-light text-dark' }}">
                {{ $log->attempts }}
            </span>
            @if($log->attempts >= 5)
                <br><small class="text-danger">Max attempts</small>
            @endif
        </div>
    </td>
    <td>
        @if($log->processing_time)
            <div class="text-end">
                <span class="fw-medium">{{ number_format($log->processing_time, 2) }}ms</span>
                @if($log->processing_time > 5000)
                    <br><small class="text-warning">Slow</small>
                @elseif($log->processing_time < 100)
                    <br><small class="text-success">Fast</small>
                @endif
            </div>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        <div>
            <div class="fw-medium">{{ $log->webhook_received_at->format('M j, Y') }}</div>
            <small class="text-muted">{{ $log->webhook_received_at->format('H:i:s') }}</small>
        </div>
        @if($log->processed_at)
            <small class="text-success">
                Processed: {{ $log->processed_at->format('H:i:s') }}
            </small>
        @endif
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.webhook-logs.show', $log) }}" 
               class="btn btn-outline-primary btn-sm" title="View Details">
                <i class="fas fa-eye"></i>
            </a>
            @if($log->canRetry())
                <button type="button" class="btn btn-outline-warning btn-sm" 
                        onclick="retryWebhook({{ $log->id }})" title="Retry">
                    <i class="fas fa-redo"></i>
                </button>
            @endif
            @if($log->response_code && $log->response_code >= 400)
                <button type="button" class="btn btn-outline-danger btn-sm" 
                        onclick="showError({{ $log->id }})" title="View Error">
                    <i class="fas fa-exclamation-triangle"></i>
                </button>
            @endif
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-4">
        <div class="text-muted">
            <i class="fas fa-webhook fa-3x mb-3"></i>
            <h5>No webhook logs found</h5>
            <p>No webhooks match your current filter criteria.</p>
        </div>
    </td>
</tr>
@endforelse

<script>
function retryWebhook(webhookId) {
    if (!confirm('Are you sure you want to retry this webhook?')) return;
    
    fetch(`/admin/webhook-logs/${webhookId}/retry`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Webhook queued for retry');
            refreshWebhooks();
        } else {
            showToast('error', data.message || 'Failed to retry webhook');
        }
    })
    .catch(error => {
        console.error('Error retrying webhook:', error);
        showToast('error', 'Failed to retry webhook');
    });
}

function showError(webhookId) {
    // This would show an error modal with details
    // For now, just redirect to the show page
    window.location.href = `/admin/webhook-logs/${webhookId}`;
}
</script>
