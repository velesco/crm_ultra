@extends('layouts.app')

@section('title', $smsProvider->name . ' - SMS Provider')

@push('styles')
<style>
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
    }
    
    .usage-progress {
        height: 8px;
        border-radius: 4px;
    }
    
    .provider-logo {
        width: 80px;
        height: 80px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        font-size: 1.5rem;
    }
    
    .status-badge {
        font-size: 0.9rem;
        padding: 8px 16px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="provider-logo me-3" style="background: {{ $smsProvider->getBrandColor() ?? '#6c757d' }}">
                        {{ strtoupper(substr($smsProvider->name, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="h3 mb-1">{{ $smsProvider->name }}</h1>
                        <p class="text-muted mb-0">{{ ucfirst($smsProvider->provider_type) }} SMS Provider</p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('sms.providers.index') }}" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <a href="{{ route('sms.providers.edit', $smsProvider) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Provider
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status and Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="badge status-badge {{ $smsProvider->is_active ? 'bg-success' : 'bg-secondary' }} me-3">
                                {{ $smsProvider->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($smsProvider->is_default)
                                <span class="badge bg-primary status-badge">Default Provider</span>
                            @endif
                        </div>
                        
                        <div class="btn-group">
                            <form action="{{ route('sms.providers.test', $smsProvider) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-vial me-1"></i> Test Connection
                                </button>
                            </form>
                            
                            <form action="{{ route('sms.providers.toggle-active', $smsProvider) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-{{ $smsProvider->is_active ? 'warning' : 'success' }} btn-sm">
                                    <i class="fas fa-{{ $smsProvider->is_active ? 'pause' : 'play' }} me-1"></i>
                                    {{ $smsProvider->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Usage Statistics -->
        <div class="col-lg-8">
            <!-- Daily Usage -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-1"></i> Usage Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-3">Daily Usage</h6>
                            @php
                                $dailyUsage = $smsProvider->sent_today ?? 0;
                                $dailyPercentage = $smsProvider->daily_limit > 0 ? min(100, ($dailyUsage / $smsProvider->daily_limit) * 100) : 0;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ number_format($dailyUsage) }} sent</span>
                                <span>{{ number_format($smsProvider->daily_limit) }} limit</span>
                            </div>
                            <div class="progress usage-progress">
                                <div class="progress-bar {{ $dailyPercentage > 90 ? 'bg-danger' : ($dailyPercentage > 70 ? 'bg-warning' : 'bg-success') }}" 
                                     style="width: {{ $dailyPercentage }}%"></div>
                            </div>
                            <small class="text-muted">{{ number_format($dailyPercentage, 1) }}% of daily limit used</small>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-3">Hourly Usage</h6>
                            @php
                                $hourlyUsage = $smsProvider->sent_this_hour ?? 0;
                                $hourlyPercentage = $smsProvider->hourly_limit > 0 ? min(100, ($hourlyUsage / $smsProvider->hourly_limit) * 100) : 0;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ number_format($hourlyUsage) }} sent</span>
                                <span>{{ number_format($smsProvider->hourly_limit) }} limit</span>
                            </div>
                            <div class="progress usage-progress">
                                <div class="progress-bar {{ $hourlyPercentage > 90 ? 'bg-danger' : ($hourlyPercentage > 70 ? 'bg-warning' : 'bg-success') }}" 
                                     style="width: {{ $hourlyPercentage }}%"></div>
                            </div>
                            <small class="text-muted">{{ number_format($hourlyPercentage, 1) }}% of hourly limit used</small>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary mb-1">{{ number_format($smsProvider->sms_messages_count ?? 0) }}</h4>
                                <small class="text-muted">Total Messages</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success mb-1">{{ number_format($stats['delivered'] ?? 0) }}</h4>
                                <small class="text-muted">Delivered</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning mb-1">{{ number_format($stats['pending'] ?? 0) }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger mb-1">{{ number_format($stats['failed'] ?? 0) }}</h4>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-history me-1"></i> Recent Messages</h6>
                    <a href="{{ route('sms.index') }}?provider={{ $smsProvider->id }}" class="btn btn-sm btn-outline-primary">
                        View All Messages
                    </a>
                </div>
                <div class="card-body">
                    @if($recentMessages && $recentMessages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Recipient</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Sent At</th>
                                        <th>Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMessages as $message)
                                    <tr>
                                        <td>
                                            <div>
                                                @if($message->contact)
                                                    <strong>{{ $message->contact->full_name }}</strong><br>
                                                @endif
                                                <small class="text-muted">{{ $message->to_number }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="max-width: 200px;">
                                                {{ Str::limit($message->message, 50) }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $message->getStatusColor() }}">
                                                {{ ucfirst($message->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $message->created_at->format('M j, H:i') }}</small>
                                        </td>
                                        <td>
                                            @if($message->cost)
                                                <small>${{ number_format($message->cost, 4) }}</small>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No messages sent with this provider yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Provider Configuration -->
        <div class="col-lg-4">
            <!-- Configuration Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cog me-1"></i> Configuration</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Provider Type:</strong></td>
                            <td>{{ ucfirst($smsProvider->provider_type) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Priority:</strong></td>
                            <td>{{ $smsProvider->priority }}</td>
                        </tr>
                        <tr>
                            <td><strong>API URL:</strong></td>
                            <td>
                                @if($smsProvider->api_url)
                                    <small class="text-muted">{{ Str::limit($smsProvider->api_url, 30) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Sender ID:</strong></td>
                            <td>{{ $smsProvider->sender_id ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Cost per SMS:</strong></td>
                            <td>
                                @if($smsProvider->cost_per_sms)
                                    ${{ number_format($smsProvider->cost_per_sms, 4) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Webhook URL:</strong></td>
                            <td>
                                @if($smsProvider->webhook_url)
                                    <small class="text-muted">{{ Str::limit($smsProvider->webhook_url, 25) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Timeout:</strong></td>
                            <td>{{ $smsProvider->timeout ?? 30 }}s</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-clock me-1"></i> Activity</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Provider Created</h6>
                                <small class="text-muted">{{ $smsProvider->created_at->format('M j, Y H:i') }}</small>
                            </div>
                        </div>
                        
                        @if($smsProvider->updated_at != $smsProvider->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Configuration Updated</h6>
                                <small class="text-muted">{{ $smsProvider->updated_at->format('M j, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($smsProvider->last_used_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Last Message Sent</h6>
                                <small class="text-muted">{{ $smsProvider->last_used_at->format('M j, Y H:i') }}</small>
                            </div>
                        </div>
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
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -21px;
        top: 20px;
        height: calc(100% - 20px);
        width: 2px;
        background: #e9ecef;
    }
    
    .timeline-marker {
        position: absolute;
        left: -25px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    .timeline-content h6 {
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>
@endpush
