@extends('layouts.app')

@section('title', 'SMS Providers')

@push('styles')
<style>
    .provider-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .provider-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.15);
    }
    
    .provider-logo {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
    }
    
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">SMS Providers</h1>
                    <p class="text-muted mb-0">Manage your SMS service providers and configurations</p>
                </div>
                <div>
                    <a href="{{ route('sms.providers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Provider
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Total Providers</h6>
                            <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-server fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Active</h6>
                            <h3 class="mb-0">{{ $stats['active'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Inactive</h6>
                            <h3 class="mb-0">{{ $stats['inactive'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pause-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Messages Sent</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_messages'] ?? 0) }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-paper-plane fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Providers List -->
    <div class="row">
        @forelse($providers as $provider)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card provider-card h-100">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-center">
                            <div class="provider-logo me-3" style="background: {{ $provider->getBrandColor() }}">
                                {{ strtoupper(substr($provider->name, 0, 2)) }}
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $provider->name }}</h6>
                                <small class="text-muted">{{ ucfirst($provider->provider_type) }}</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('sms.providers.show', $provider) }}">
                                        <i class="fas fa-eye me-2"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('sms.providers.edit', $provider) }}">
                                        <i class="fas fa-edit me-2"></i> Edit
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('sms.providers.test', $provider) }}" method="POST" class="d-inline test-provider-form">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-info">
                                            <i class="fas fa-vial me-2"></i> Test Connection
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('sms.providers.toggle-active', $provider) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item {{ $provider->is_active ? 'text-warning' : 'text-success' }}">
                                            <i class="fas fa-{{ $provider->is_active ? 'pause' : 'play' }} me-2"></i>
                                            {{ $provider->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('sms.providers.destroy', $provider) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Status -->
                    <div class="d-flex align-items-center mb-3">
                        <span class="status-indicator {{ $provider->is_active ? 'bg-success' : 'bg-secondary' }} me-2"></span>
                        <span class="badge {{ $provider->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $provider->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($provider->is_default)
                            <span class="badge bg-primary ms-2">Default</span>
                        @endif
                    </div>

                    <!-- Configuration Details -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="mb-0">{{ $provider->priority }}</h6>
                                <small class="text-muted">Priority</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h6 class="mb-0">{{ number_format($provider->daily_limit) }}</h6>
                                <small class="text-muted">Daily Limit</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h6 class="mb-0">{{ number_format($provider->sms_messages_count ?? 0) }}</h6>
                            <small class="text-muted">Messages Sent</small>
                        </div>
                    </div>

                    <!-- Usage Progress -->
                    @if($provider->daily_limit > 0)
                    <div class="mb-3">
                        @php
                            $dailyUsage = $provider->sent_today ?? 0;
                            $usagePercentage = min(100, ($dailyUsage / $provider->daily_limit) * 100);
                        @endphp
                        <div class="d-flex justify-content-between mb-1">
                            <small>Daily Usage</small>
                            <small>{{ $dailyUsage }} / {{ number_format($provider->daily_limit) }}</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $usagePercentage > 90 ? 'bg-danger' : ($usagePercentage > 70 ? 'bg-warning' : 'bg-primary') }}" 
                                 style="width: {{ $usagePercentage }}%"></div>
                        </div>
                    </div>
                    @endif

                    <!-- Cost per SMS -->
                    @if($provider->cost_per_sms)
                    <div class="text-center">
                        <small class="text-muted">Cost per SMS: </small>
                        <strong>${{ number_format($provider->cost_per_sms, 4) }}</strong>
                    </div>
                    @endif
                </div>
                
                <div class="card-footer bg-transparent">
                    <div class="row">
                        <div class="col">
                            <a href="{{ route('sms.providers.show', $provider) }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-eye me-1"></i> View Details
                            </a>
                        </div>
                        <div class="col">
                            <a href="{{ route('sms.providers.edit', $provider) }}" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-edit me-1"></i> Configure
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-server fa-3x text-muted"></i>
                    </div>
                    <h5>No SMS Providers</h5>
                    <p class="text-muted mb-4">You haven't configured any SMS providers yet. Add your first provider to start sending SMS messages.</p>
                    <a href="{{ route('sms.providers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add SMS Provider
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle test provider forms
    document.querySelectorAll('.test-provider-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = this.querySelector('button');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Testing...';
            button.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Test successful!', data.message);
                } else {
                    showAlert('error', 'Test failed!', data.message);
                }
            })
            .catch(error => {
                showAlert('error', 'Test failed!', 'Unable to test provider connection.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    });
    
    // Handle delete forms
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this SMS provider? This action cannot be undone.')) {
                this.submit();
            }
        });
    });
});

function showAlert(type, title, message) {
    // Simple alert implementation - you can replace with your preferred notification system
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
    alert.innerHTML = `
        <strong>${title}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endpush
