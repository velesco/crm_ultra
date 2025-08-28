@extends('layouts.app')

@section('title', 'API Key Details - ' . $apiKey->name)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-key text-primary"></i>
                        {{ $apiKey->name }}
                    </h1>
                    <p class="text-muted mb-0">
                        API Key Details & Usage Statistics
                        @if($apiKey->is_expired)
                            <span class="badge bg-danger ms-2">EXPIRED</span>
                        @elseif(!$apiKey->is_active)
                            <span class="badge bg-warning ms-2">INACTIVE</span>
                        @else
                            <span class="badge bg-success ms-2">ACTIVE</span>
                        @endif
                    </p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.api-keys.edit', $apiKey) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('admin.api-keys.regenerate', $apiKey) }}" style="display: inline;" 
                          onsubmit="return confirm('Are you sure? This will invalidate the current API key.')">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sync"></i> Regenerate
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.api-keys.destroy', $apiKey) }}" style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to delete this API key? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(session('api_key'))
        <!-- New API Key Alert -->
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-key"></i> API Key Generated Successfully!</h5>
            <p class="mb-3">Your new API key has been generated. <strong>This is the only time you'll see the full key - save it securely!</strong></p>
            <div class="row">
                <div class="col-md-10">
                    <div class="input-group">
                        <input type="text" class="form-control" id="fullApiKey" value="{{ session('api_key') }}" readonly>
                        <button class="btn btn-outline-success" type="button" onclick="copyToClipboard('fullApiKey')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Column - API Key Information -->
        <div class="col-lg-8">
            <!-- Basic Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-gray-600">Name:</td>
                                    <td>{{ $apiKey->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-600">Key:</td>
                                    <td>
                                        <code>{{ $apiKey->masked_key }}</code>
                                        <button class="btn btn-sm btn-outline-primary ms-2" 
                                                onclick="copyToClipboard('{{ $apiKey->masked_key }}')" 
                                                title="Copy masked key">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-600">Status:</td>
                                    <td>
                                        @php
                                            $statusClass = match($apiKey->status) {
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'suspended' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ ucfirst($apiKey->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-600">Environment:</td>
                                    <td>
                                        @php
                                            $envClass = match($apiKey->environment) {
                                                'production' => 'danger',
                                                'staging' => 'warning',
                                                'development' => 'info',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $envClass }}">
                                            {{ ucfirst($apiKey->environment) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-gray-600">Created:</td>
                                    <td>
                                        {{ $apiKey->created_at->format('M j, Y g:i A') }}
                                        <br><small class="text-muted">{{ $apiKey->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-600">Created By:</td>
                                    <td>
                                        @if($apiKey->createdBy)
                                            {{ $apiKey->createdBy->name }}
                                            <br><small class="text-muted">{{ $apiKey->createdBy->email }}</small>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-600">Last Updated:</td>
                                    <td>
                                        {{ $apiKey->updated_at->format('M j, Y g:i A') }}
                                        <br><small class="text-muted">{{ $apiKey->updated_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-gray-600">Expires:</td>
                                    <td>
                                        @if($apiKey->expires_at)
                                            @if($apiKey->is_expired)
                                                <span class="text-danger">
                                                    <i class="fas fa-times-circle"></i>
                                                    Expired on {{ $apiKey->expires_at->format('M j, Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">
                                                    {{ $apiKey->expires_at->format('M j, Y') }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">Never expires</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($apiKey->description)
                        <div class="mt-3">
                            <h6 class="fw-bold text-gray-600">Description:</h6>
                            <p class="text-muted">{{ $apiKey->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Permissions & Scopes Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-lock"></i> Permissions & Scopes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-gray-800 mb-3">API Permissions</h6>
                            @if($apiKey->permissions && count($apiKey->permissions) > 0)
                                @foreach($apiKey->permissions as $permission)
                                    <span class="badge bg-primary me-1 mb-2">{{ $permission }}</span>
                                @endforeach
                            @else
                                <p class="text-muted">No specific permissions set</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-gray-800 mb-3">Access Scopes</h6>
                            @if($apiKey->scopes && count($apiKey->scopes) > 0)
                                @foreach($apiKey->scopes as $scope)
                                    <span class="badge bg-info me-1 mb-2">{{ $scope }}</span>
                                @endforeach
                            @else
                                <p class="text-muted">No specific scopes set</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-shield-alt"></i> Security Settings
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-gray-800 mb-3">Rate Limits</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>Per Minute:</td>
                                    <td><span class="badge bg-secondary">{{ number_format($apiKey->rate_limit_per_minute) }}</span></td>
                                </tr>
                                <tr>
                                    <td>Per Hour:</td>
                                    <td><span class="badge bg-secondary">{{ number_format($apiKey->rate_limit_per_hour) }}</span></td>
                                </tr>
                                <tr>
                                    <td>Per Day:</td>
                                    <td><span class="badge bg-secondary">{{ number_format($apiKey->rate_limit_per_day) }}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-gray-800 mb-3">IP Restrictions</h6>
                            @if($apiKey->allowed_ips)
                                @php
                                    $ips = explode(',', $apiKey->allowed_ips);
                                @endphp
                                @foreach($ips as $ip)
                                    <code class="me-2 mb-1 d-inline-block">{{ trim($ip) }}</code>
                                @endforeach
                            @else
                                <p class="text-muted">No IP restrictions (any IP allowed)</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Statistics & Actions -->
        <div class="col-lg-4">
            <!-- Usage Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Usage Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="h2 font-weight-bold text-primary">{{ number_format($apiKey->usage_count) }}</h2>
                        <p class="text-muted mb-0">Total API Calls</p>
                    </div>

                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h4 class="h4 font-weight-bold text-info">{{ number_format($usageStats['requests_today']) }}</h4>
                                <p class="text-muted small mb-0">Today</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="h4 font-weight-bold text-success">{{ number_format($usageStats['requests_this_week']) }}</h4>
                            <p class="text-muted small mb-0">This Week</p>
                        </div>
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="h4 font-weight-bold text-warning">{{ number_format($usageStats['requests_this_month']) }}</h4>
                                <p class="text-muted small mb-0">This Month</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="h4 font-weight-bold text-secondary">{{ $usageStats['avg_requests_per_day'] }}</h4>
                            <p class="text-muted small mb-0">Avg/Day</p>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6 class="fw-bold text-gray-800 mb-2">Last Used:</h6>
                        @if($apiKey->last_used_at)
                            <p class="text-muted mb-0">
                                {{ $apiKey->last_used_at->format('M j, Y g:i A') }}
                                <br><small>{{ $apiKey->last_used_at->diffForHumans() }}</small>
                            </p>
                        @else
                            <p class="text-muted mb-0">Never used</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.api-keys.edit', $apiKey) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Settings
                        </a>

                        <form method="POST" action="{{ route('admin.api-keys.regenerate', $apiKey) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm w-100" 
                                    onclick="return confirm('Are you sure? This will invalidate the current API key.')">
                                <i class="fas fa-sync"></i> Regenerate Key
                            </button>
                        </form>

                        <button class="btn btn-outline-secondary btn-sm" onclick="exportApiKey()">
                            <i class="fas fa-download"></i> Export Configuration
                        </button>
                    </div>
                </div>
            </div>

            <!-- API Documentation Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book"></i> API Documentation
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Use this API key in your applications:</p>
                    
                    <h6 class="fw-bold">Authentication Header:</h6>
                    <div class="bg-light p-2 rounded mb-3">
                        <code>Authorization: Bearer YOUR_API_KEY</code>
                    </div>

                    <h6 class="fw-bold">Example cURL Request:</h6>
                    <div class="bg-light p-2 rounded">
                        <small>
                            <code>curl -H "Authorization: Bearer {{ $apiKey->masked_key }}" \<br>
                            &nbsp;&nbsp;&nbsp;&nbsp; {{ url('/api/contacts') }}</code>
                        </small>
                    </div>

                    <div class="mt-3">
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-book-open"></i> Full API Documentation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(elementIdOrText) {
    let text;
    if (typeof elementIdOrText === 'string' && elementIdOrText.includes('_')) {
        // It's an element ID
        const element = document.getElementById(elementIdOrText);
        text = element ? element.value : elementIdOrText;
    } else {
        // It's the text itself
        text = elementIdOrText;
    }
    
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>Copied to clipboard!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        document.body.appendChild(toast);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }).catch(function() {
        alert('Failed to copy to clipboard');
    });
}

function exportApiKey() {
    const apiKeyData = {
        name: '{{ $apiKey->name }}',
        key: '{{ $apiKey->masked_key }}',
        environment: '{{ $apiKey->environment }}',
        permissions: @json($apiKey->permissions ?? []),
        scopes: @json($apiKey->scopes ?? []),
        rate_limits: {
            per_minute: {{ $apiKey->rate_limit_per_minute }},
            per_hour: {{ $apiKey->rate_limit_per_hour }},
            per_day: {{ $apiKey->rate_limit_per_day }}
        },
        allowed_ips: '{{ $apiKey->allowed_ips ?? '' }}',
        created_at: '{{ $apiKey->created_at->format('Y-m-d H:i:s') }}',
        expires_at: '{{ $apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : null }}'
    };

    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(apiKeyData, null, 2));
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", "api_key_{{ $apiKey->name }}.json");
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
}
</script>
@endpush
