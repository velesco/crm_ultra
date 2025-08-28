@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.system-logs.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i> Back to Logs
            </a>
            <div>
                <h1 class="h3 mb-0 text-gradient fw-bold">Log Details</h1>
                <p class="text-muted mb-0">System log entry #{{ $systemLog->id }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-share"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportLogAsJson()">
                        <i class="fas fa-code me-2"></i>Export as JSON
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="copyToClipboard()">
                        <i class="fas fa-copy me-2"></i>Copy to Clipboard
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Main Log Details --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title mb-0">Log Information</h5>
                        <span class="badge {{ $systemLog->level_badge_class }} fs-6">
                            {{ ucfirst($systemLog->level) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-muted">Category:</strong>
                            <div class="d-flex align-items-center mt-1">
                                <i class="{{ $systemLog->category_icon }} me-2"></i>
                                <span class="text-capitalize">{{ $systemLog->category }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">Action:</strong>
                            <div class="mt-1">
                                <code class="bg-light px-2 py-1 rounded">{{ $systemLog->action }}</code>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-muted">Occurred At:</strong>
                            <div class="mt-1">
                                {{ $systemLog->occurred_at->format('M d, Y \a\t H:i:s T') }}
                                <small class="text-muted d-block">
                                    {{ $systemLog->occurred_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-muted">User:</strong>
                            <div class="mt-1">
                                @if($systemLog->user)
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <small class="text-white fw-bold">{{ substr($systemLog->user->name, 0, 1) }}</small>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $systemLog->user->name }}</div>
                                            <small class="text-muted">{{ $systemLog->user->email }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong class="text-muted">Message:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            {{ $systemLog->message }}
                        </div>
                    </div>

                    @if($systemLog->description)
                        <div class="mb-3">
                            <strong class="text-muted">Description:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                {{ $systemLog->description }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Technical Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Technical Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">IP Address:</strong>
                            <div class="mt-1">
                                <code>{{ $systemLog->ip_address ?? 'N/A' }}</code>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Session ID:</strong>
                            <div class="mt-1">
                                <code class="small">{{ $systemLog->session_id ?? 'N/A' }}</code>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Request ID:</strong>
                            <div class="mt-1">
                                <code class="small">{{ $systemLog->request_id ?? 'N/A' }}</code>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Created At:</strong>
                            <div class="mt-1">
                                {{ $systemLog->created_at->format('M d, Y H:i:s T') }}
                            </div>
                        </div>
                    </div>

                    @if($systemLog->user_agent)
                        <div class="mb-3">
                            <strong class="text-muted">User Agent:</strong>
                            <div class="mt-2 p-2 bg-light rounded small font-monospace">
                                {{ $systemLog->user_agent }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Context and Metadata --}}
            @if($systemLog->context || $systemLog->metadata)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Context & Metadata</h5>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="dataView" id="tableView" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="tableView">Table</label>
                                <input type="radio" class="btn-check" name="dataView" id="jsonView" autocomplete="off">
                                <label class="btn btn-outline-primary" for="jsonView">JSON</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($systemLog->context)
                            <div class="mb-4">
                                <strong class="text-muted">Request Context:</strong>
                                <div id="context-table" class="mt-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            @foreach($systemLog->context as $key => $value)
                                                <tr>
                                                    <td class="bg-light fw-medium" style="width: 30%">{{ $key }}</td>
                                                    <td>{{ is_string($value) ? $value : json_encode($value) }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                                <div id="context-json" class="mt-2" style="display: none;">
                                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($systemLog->context, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        @endif

                        @if($systemLog->metadata)
                            <div>
                                <strong class="text-muted">Additional Metadata:</strong>
                                <div id="metadata-table" class="mt-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            @foreach($systemLog->metadata as $key => $value)
                                                <tr>
                                                    <td class="bg-light fw-medium" style="width: 30%">{{ $key }}</td>
                                                    <td>{{ is_string($value) ? $value : json_encode($value) }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                                <div id="metadata-json" class="mt-2" style="display: none;">
                                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($systemLog->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Related Logs --}}
            @if($relatedLogs->isNotEmpty())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0">
                            Related Activity
                            <span class="badge bg-secondary">{{ $relatedLogs->count() }}</span>
                        </h5>
                        <p class="text-muted small mb-0">
                            @if($systemLog->request_id)
                                Same request or within 5 minutes
                            @else
                                Same user within 5 minutes
                            @endif
                        </p>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($relatedLogs as $relatedLog)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <span class="badge {{ $relatedLog->level_badge_class }} badge-sm">
                                                {{ substr($relatedLog->level, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-medium small">{{ $relatedLog->action }}</div>
                                                    <div class="text-muted small">
                                                        <i class="{{ $relatedLog->category_icon }} me-1"></i>
                                                        {{ ucfirst($relatedLog->category) }}
                                                    </div>
                                                    <div class="text-muted small mt-1">
                                                        {{ Str::limit($relatedLog->message, 60) }}
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted">
                                                        {{ $relatedLog->occurred_at->format('H:i:s') }}
                                                    </small>
                                                    <div>
                                                        <a href="{{ route('admin.system-logs.show', $relatedLog) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Quick Stats --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    @if($systemLog->user)
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">User's Total Logs:</span>
                                <span class="fw-bold">{{ App\Models\SystemLog::where('user_id', $systemLog->user_id)->count() }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ ucfirst($systemLog->category) }} Logs Today:</span>
                            <span class="fw-bold">{{ App\Models\SystemLog::category($systemLog->category)->whereDate('created_at', today())->count() }}</span>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ ucfirst($systemLog->level) }} Logs Today:</span>
                            <span class="fw-bold">{{ App\Models\SystemLog::level($systemLog->level)->whereDate('created_at', today())->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="filterSimilar()">
                            <i class="fas fa-filter me-2"></i>Find Similar Logs
                        </button>
                        @if($systemLog->user)
                            <button type="button" class="btn btn-outline-info" onclick="viewUserActivity()">
                                <i class="fas fa-user me-2"></i>View User Activity
                            </button>
                        @endif
                        @if($systemLog->request_id)
                            <button type="button" class="btn btn-outline-secondary" onclick="traceRequest()">
                                <i class="fas fa-route me-2"></i>Trace Request
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.text-gradient {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.timeline-item {
    position: relative;
    padding-left: 1rem;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 2rem;
    width: 2px;
    height: calc(100% - 1rem);
    background: #e9ecef;
}

.badge-sm {
    font-size: 0.6rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

pre code {
    background-color: transparent;
    padding: 0;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
}

.table th {
    background: linear-gradient(45deg, #f8f9fa 0%, #e9ecef 100%);
    border: none;
    color: #495057;
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle between table and JSON view
    $('input[name="dataView"]').on('change', function() {
        const isJsonView = $('#jsonView').is(':checked');
        
        if (isJsonView) {
            $('#context-table, #metadata-table').hide();
            $('#context-json, #metadata-json').show();
        } else {
            $('#context-table, #metadata-table').show();
            $('#context-json, #metadata-json').hide();
        }
    });
});

function exportLogAsJson() {
    const logData = {
        id: {{ $systemLog->id }},
        level: '{{ $systemLog->level }}',
        category: '{{ $systemLog->category }}',
        action: '{{ $systemLog->action }}',
        message: `{{ addslashes($systemLog->message) }}`,
        description: `{{ addslashes($systemLog->description ?? '') }}`,
        user: @if($systemLog->user) {
            id: {{ $systemLog->user->id }},
            name: '{{ $systemLog->user->name }}',
            email: '{{ $systemLog->user->email }}'
        } @else null @endif,
        occurred_at: '{{ $systemLog->occurred_at->toISOString() }}',
        ip_address: '{{ $systemLog->ip_address }}',
        user_agent: `{{ addslashes($systemLog->user_agent ?? '') }}`,
        context: @json($systemLog->context),
        metadata: @json($systemLog->metadata)
    };
    
    const dataStr = JSON.stringify(logData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = `system-log-${logData.id}.json`;
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
}

function copyToClipboard() {
    const logText = `
System Log #{{ $systemLog->id }}
Level: {{ $systemLog->level }}
Category: {{ $systemLog->category }}
Action: {{ $systemLog->action }}
Message: {{ $systemLog->message }}
User: {{ $systemLog->user ? $systemLog->user->name : 'System' }}
Occurred At: {{ $systemLog->occurred_at->format('Y-m-d H:i:s T') }}
IP Address: {{ $systemLog->ip_address ?? 'N/A' }}
    `.trim();
    
    navigator.clipboard.writeText(logText).then(function() {
        showAlert('Log details copied to clipboard', 'success');
    }, function() {
        showAlert('Failed to copy to clipboard', 'danger');
    });
}

function filterSimilar() {
    const params = new URLSearchParams({
        category: '{{ $systemLog->category }}',
        action: '{{ $systemLog->action }}',
        level: '{{ $systemLog->level }}'
    });
    
    window.location.href = '{{ route("admin.system-logs.index") }}?' + params.toString();
}

function viewUserActivity() {
    @if($systemLog->user)
        const params = new URLSearchParams({
            user_id: '{{ $systemLog->user_id }}'
        });
        
        window.location.href = '{{ route("admin.system-logs.index") }}?' + params.toString();
    @endif
}

function traceRequest() {
    @if($systemLog->request_id)
        const params = new URLSearchParams({
            search: '{{ $systemLog->request_id }}'
        });
        
        window.location.href = '{{ route("admin.system-logs.index") }}?' + params.toString();
    @endif
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endpush
