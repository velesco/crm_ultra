@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.webhook-logs.index') }}">Webhook Logs</a></li>
                    <li class="breadcrumb-item active">Webhook #{{ $webhookLog->id }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gradient fw-bold">Webhook Details</h1>
            <p class="text-muted mb-0">{{ ucfirst($webhookLog->webhook_type) }} webhook from {{ ucfirst($webhookLog->provider) }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($webhookLog->canRetry())
                <button type="button" class="btn btn-warning" onclick="retryWebhook()">
                    <i class="fas fa-redo"></i> Retry Webhook
                </button>
            @endif
            <a href="{{ route('admin.webhook-logs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Main Details --}}
        <div class="col-lg-8">
            {{-- Status Overview --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Status Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <div class="mb-2">
                                    <span class="badge {{ $webhookLog->status_badge_class }} fs-6 px-3 py-2">
                                        {{ ucfirst($webhookLog->status) }}
                                    </span>
                                </div>
                                <small class="text-muted">Current Status</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <div class="h5 mb-1">{{ $webhookLog->attempts }}</div>
                                <small class="text-muted">Attempts</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <div class="h5 mb-1">
                                    @if($webhookLog->processing_time)
                                        {{ number_format($webhookLog->processing_time, 2) }}ms
                                    @else
                                        -
                                    @endif
                                </div>
                                <small class="text-muted">Processing Time</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <div class="h5 mb-1">
                                    @if($webhookLog->response_code)
                                        <span class="badge {{ $webhookLog->response_code < 300 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $webhookLog->response_code }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </div>
                                <small class="text-muted">Response Code</small>
                            </div>
                        </div>
                    </div>
                    
                    @if($webhookLog->status === 'failed' && $webhookLog->next_retry_at)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            Next retry scheduled for: <strong>{{ $webhookLog->next_retry_at->format('M j, Y H:i:s') }}</strong>
                            ({{ $webhookLog->next_retry_at->diffForHumans() }})
                        </div>
                    @endif

                    @if($webhookLog->error_message)
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Error Message</h6>
                            <p class="mb-0">{{ $webhookLog->error_message }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Webhook Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Webhook Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <strong>Type:</strong>
                            <div class="mt-1">
                                <i class="{{ $webhookLog->type_icon }} me-2"></i>
                                {{ ucfirst($webhookLog->webhook_type) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong>Provider:</strong>
                            <div class="mt-1">
                                <i class="{{ $webhookLog->provider_icon }} me-2"></i>
                                {{ ucfirst($webhookLog->provider) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong>Event Type:</strong>
                            <div class="mt-1">
                                <i class="{{ $webhookLog->event_type_icon }} me-2"></i>
                                {{ ucfirst(str_replace('_', ' ', $webhookLog->event_type)) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong>Method:</strong>
                            <div class="mt-1">
                                <span class="badge bg-primary">{{ $webhookLog->method }}</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <strong>URL:</strong>
                            <div class="mt-1">
                                <code class="bg-light p-2 rounded d-block">{{ $webhookLog->url }}</code>
                            </div>
                        </div>
                        @if($webhookLog->reference_id && $webhookLog->reference_type)
                        <div class="col-md-6">
                            <strong>Reference:</strong>
                            <div class="mt-1">
                                {{ $webhookLog->reference_type }}: {{ $webhookLog->reference_id }}
                            </div>
                        </div>
                        @endif
                        @if($webhookLog->webhook_id)
                        <div class="col-md-6">
                            <strong>Webhook ID:</strong>
                            <div class="mt-1">
                                <code>{{ $webhookLog->webhook_id }}</code>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Webhook Received</h6>
                                <p class="text-muted mb-0">{{ $webhookLog->webhook_received_at->format('M j, Y H:i:s') }}</p>
                                <small class="text-muted">{{ $webhookLog->webhook_received_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        
                        @if($webhookLog->processed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker {{ $webhookLog->status === 'completed' ? 'bg-success' : 'bg-danger' }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Processing {{ $webhookLog->status === 'completed' ? 'Completed' : 'Failed' }}</h6>
                                <p class="text-muted mb-0">{{ $webhookLog->processed_at->format('M j, Y H:i:s') }}</p>
                                <small class="text-muted">{{ $webhookLog->processed_at->diffForHumans() }}</small>
                                @if($webhookLog->processing_time)
                                    <small class="text-info d-block">Took {{ number_format($webhookLog->processing_time, 2) }}ms</small>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($webhookLog->next_retry_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Next Retry Scheduled</h6>
                                <p class="text-muted mb-0">{{ $webhookLog->next_retry_at->format('M j, Y H:i:s') }}</p>
                                <small class="text-muted">{{ $webhookLog->next_retry_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Headers --}}
            @if($webhookLog->headers)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Request Headers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            @foreach($webhookLog->headers as $key => $value)
                            <tr>
                                <td class="fw-medium text-end" style="width: 200px;">{{ $key }}:</td>
                                <td><code class="text-muted">{{ is_array($value) ? implode(', ', $value) : $value }}</code></td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Payload --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Raw Payload</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="payloadFormat" id="payloadRaw" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="payloadRaw">Raw</label>
                        <input type="radio" class="btn-check" name="payloadFormat" id="payloadFormatted" autocomplete="off">
                        <label class="btn btn-outline-primary" for="payloadFormatted">Formatted</label>
                    </div>
                </div>
                <div class="card-body">
                    <pre id="payloadContent" class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>{{ $webhookLog->payload }}</code></pre>
                </div>
            </div>

            {{-- Processed Data --}}
            @if($webhookLog->processed_data)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Processed Data</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($webhookLog->processed_data, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
            @endif

            {{-- Response --}}
            @if($webhookLog->response)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Response</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>{{ $webhookLog->response }}</code></pre>
                </div>
            </div>
            @endif

            {{-- Error Context --}}
            @if($webhookLog->error_context)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0 text-danger">Error Context</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-danger bg-opacity-10 p-3 rounded"><code>{{ json_encode($webhookLog->error_context, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Quick Actions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($webhookLog->canRetry())
                            <button type="button" class="btn btn-warning" onclick="retryWebhook()">
                                <i class="fas fa-redo"></i> Retry This Webhook
                            </button>
                        @endif
                        <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard('payload')">
                            <i class="fas fa-copy"></i> Copy Payload
                        </button>
                        @if($webhookLog->processed_data)
                            <button type="button" class="btn btn-outline-info" onclick="copyToClipboard('processed')">
                                <i class="fas fa-copy"></i> Copy Processed Data
                            </button>
                        @endif
                        <a href="{{ route('admin.webhook-logs.export') }}?webhook_ids[]={{ $webhookLog->id }}" 
                           class="btn btn-outline-success">
                            <i class="fas fa-download"></i> Export This Log
                        </a>
                    </div>
                </div>
            </div>

            {{-- Metadata --}}
            @if($webhookLog->metadata)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Metadata</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($webhookLog->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
            @endif

            {{-- System Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <strong>IP Address:</strong>
                            <div class="mt-1">{{ $webhookLog->ip_address ?? 'Unknown' }}</div>
                        </div>
                        @if($webhookLog->user_agent)
                        <div class="col-12">
                            <strong>User Agent:</strong>
                            <div class="mt-1">
                                <small class="text-muted">{{ $webhookLog->user_agent }}</small>
                            </div>
                        </div>
                        @endif
                        <div class="col-12">
                            <strong>Created:</strong>
                            <div class="mt-1">{{ $webhookLog->created_at->format('M j, Y H:i:s') }}</div>
                        </div>
                        <div class="col-12">
                            <strong>Updated:</strong>
                            <div class="mt-1">{{ $webhookLog->updated_at->format('M j, Y H:i:s') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Related Webhooks --}}
            @if($relatedLogs->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Related Webhooks</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($relatedLogs as $related)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-medium">{{ ucfirst($related->event_type) }}</div>
                                <small class="text-muted">{{ $related->webhook_received_at->format('M j, H:i') }}</small>
                            </div>
                            <div>
                                <span class="badge {{ $related->status_badge_class }} me-2">
                                    {{ ucfirst($related->status) }}
                                </span>
                                <a href="{{ route('admin.webhook-logs.show', $related) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 3px solid #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
function retryWebhook() {
    if (!confirm('Are you sure you want to retry this webhook?')) return;
    
    fetch(`{{ route('admin.webhook-logs.retry', $webhookLog) }}`, {
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
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showToast('error', data.message || 'Failed to retry webhook');
        }
    })
    .catch(error => {
        console.error('Error retrying webhook:', error);
        showToast('error', 'Failed to retry webhook');
    });
}

function copyToClipboard(type) {
    let text = '';
    
    if (type === 'payload') {
        text = document.getElementById('payloadContent').textContent;
    } else if (type === 'processed') {
        text = JSON.stringify(@json($webhookLog->processed_data), null, 2);
    }
    
    navigator.clipboard.writeText(text).then(() => {
        showToast('success', 'Copied to clipboard');
    }).catch(err => {
        console.error('Error copying to clipboard:', err);
        showToast('error', 'Failed to copy to clipboard');
    });
}

// Format payload toggle
document.addEventListener('DOMContentLoaded', function() {
    const payloadContent = document.getElementById('payloadContent');
    const rawPayload = @json($webhookLog->payload);
    
    document.querySelectorAll('input[name="payloadFormat"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.id === 'payloadFormatted') {
                try {
                    const formatted = JSON.stringify(JSON.parse(rawPayload), null, 2);
                    payloadContent.innerHTML = `<code>${formatted}</code>`;
                } catch (e) {
                    payloadContent.innerHTML = `<code>${rawPayload}</code>`;
                }
            } else {
                payloadContent.innerHTML = `<code>${rawPayload}</code>`;
            }
        });
    });
});

function showToast(type, message) {
    // Implement your toast notification system here
    console.log(`${type}: ${message}`);
}
</script>
@endpush
