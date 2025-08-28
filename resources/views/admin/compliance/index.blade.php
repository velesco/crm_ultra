@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h3 mb-1 text-gray-800">Compliance Dashboard</h2>
                    <p class="text-muted mb-0">GDPR compliance monitoring and data protection management</p>
                </div>
                <div class="d-flex gap-2">
                    <button id="runAuditBtn" class="btn btn-primary btn-sm">
                        <i class="fas fa-search mr-1"></i>
                        Run Audit
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                            <i class="fas fa-cog mr-1"></i>
                            Actions
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('admin.compliance.data-requests') }}">
                                <i class="fas fa-file-text mr-2"></i>
                                Manage Data Requests
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.compliance.retention-policies') }}">
                                <i class="fas fa-clock mr-2"></i>
                                Retention Policies
                            </a>
                            <a class="dropdown-item" href="{{ route('admin.compliance.consent-logs') }}">
                                <i class="fas fa-shield-check mr-2"></i>
                                Consent Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compliance Score Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Compliance Score
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h3 mb-0 mr-3 font-weight-bold {{ $stats['compliance_score'] >= 80 ? 'text-success' : ($stats['compliance_score'] >= 60 ? 'text-warning' : 'text-danger') }}">
                                        {{ $stats['compliance_score'] }}%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar {{ $stats['compliance_score'] >= 80 ? 'bg-success' : ($stats['compliance_score'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                             role="progressbar" style="width: {{ $stats['compliance_score'] }}%" 
                                             aria-valuenow="{{ $stats['compliance_score'] }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-check fa-2x {{ $stats['compliance_score'] >= 80 ? 'text-success' : ($stats['compliance_score'] >= 60 ? 'text-warning' : 'text-danger') }}"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            @if($stats['compliance_score'] >= 90)
                                <i class="fas fa-check-circle text-success mr-1"></i>
                                Excellent compliance - minimal issues detected
                            @elseif($stats['compliance_score'] >= 80)
                                <i class="fas fa-check-circle text-success mr-1"></i>
                                Good compliance - minor issues to address
                            @elseif($stats['compliance_score'] >= 60)
                                <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                Moderate compliance - several issues need attention
                            @else
                                <i class="fas fa-times-circle text-danger mr-1"></i>
                                Poor compliance - immediate action required
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Data Requests -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Data Requests
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['data_requests']['total'] }}
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    @if($stats['data_requests']['pending'] > 0)
                                        <span class="text-warning">{{ $stats['data_requests']['pending'] }} pending</span>
                                    @endif
                                    @if($stats['data_requests']['overdue'] > 0)
                                        <span class="text-danger ml-1">{{ $stats['data_requests']['overdue'] }} overdue</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-text fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consent Logs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Consent Records
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['consent_logs']['total'] }}
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    <span class="text-success">{{ $stats['consent_logs']['given'] }} active</span>
                                    @if($stats['consent_logs']['withdrawn'] > 0)
                                        <span class="text-warning ml-1">{{ $stats['consent_logs']['withdrawn'] }} withdrawn</span>
                                    @endif
                                    @if($stats['consent_logs']['expired'] > 0)
                                        <span class="text-danger ml-1">{{ $stats['consent_logs']['expired'] }} expired</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Retention Policies -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Retention Policies
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['retention_policies']['total'] }}
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">
                                    <span class="text-success">{{ $stats['retention_policies']['active'] }} active</span>
                                    @if($stats['retention_policies']['overdue_executions'] > 0)
                                        <span class="text-danger ml-1">{{ $stats['retention_policies']['overdue_executions'] }} overdue</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                        Quick Actions
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('admin.compliance.data-requests') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-text mr-1"></i>
                            View Requests
                        </a>
                        <button id="executeRetentionBtn" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-clock mr-1"></i>
                            Execute Policies
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Data Requests -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Data Requests</h6>
                    <a href="{{ route('admin.compliance.data-requests') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($recentRequests->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentRequests as $request)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas {{ $request->getRequestTypeIcon() }} mr-2 text-muted"></i>
                                                <span class="font-weight-medium">
                                                    {{ $request->contact ? $request->contact->full_name : $request->email }}
                                                </span>
                                                <span class="badge badge-{{ $request->getStatusBadgeClass() }} ml-2">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ ucfirst($request->request_type) }} request • 
                                                {{ $request->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted" data-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#">
                                                    <i class="fas fa-eye mr-2"></i>
                                                    View Details
                                                </a>
                                                @if($request->canBeProcessed())
                                                    <a class="dropdown-item" href="#">
                                                        <i class="fas fa-play mr-2"></i>
                                                        Process Request
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-text fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No recent data requests</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Consent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Consent Activity</h6>
                    <a href="{{ route('admin.compliance.consent-logs') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($recentConsents->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentConsents as $consent)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas {{ $consent->getConsentTypeIcon() }} mr-2 text-muted"></i>
                                                <span class="font-weight-medium">
                                                    {{ $consent->contact->full_name ?? 'Unknown Contact' }}
                                                </span>
                                                <span class="badge badge-{{ $consent->getStatusBadgeClass() }} ml-2">
                                                    {{ ucfirst($consent->status) }}
                                                </span>
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ str_replace('_', ' ', ucfirst($consent->consent_type)) }} • 
                                                {{ $consent->given_at ? $consent->given_at->diffForHumans() : 'Not given' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shield-check fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No recent consent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Results Modal -->
    <div class="modal fade" id="auditModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-search mr-2"></i>
                        Compliance Audit Results
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="auditResults">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Running compliance audit...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="exportAuditBtn">
                        <i class="fas fa-download mr-1"></i>
                        Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Run audit
    document.getElementById('runAuditBtn').addEventListener('click', function() {
        $('#auditModal').modal('show');
        
        fetch('/admin/compliance/audit')
            .then(response => response.json())
            .then(data => {
                document.getElementById('auditResults').innerHTML = generateAuditReport(data);
            })
            .catch(error => {
                document.getElementById('auditResults').innerHTML = 
                    '<div class="alert alert-danger">Error running audit: ' + error.message + '</div>';
            });
    });

    // Execute retention policies
    document.getElementById('executeRetentionBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to execute all active retention policies? This action cannot be undone.')) {
            // Implementation for batch execution would go here
            alert('Retention policy execution would be implemented here');
        }
    });

    function generateAuditReport(data) {
        let html = '<div class="row">';
        
        // Data Requests Issues
        html += '<div class="col-md-6 mb-3">';
        html += '<h6><i class="fas fa-file-text mr-2"></i>Data Requests</h6>';
        html += '<ul class="list-unstyled">';
        html += '<li><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Overdue requests: ' + data.data_requests.overdue + '</li>';
        html += '<li><i class="fas fa-clock text-info mr-2"></i>Pending verification: ' + data.data_requests.pending_verification + '</li>';
        html += '<li><i class="fas fa-cog text-primary mr-2"></i>Pending processing: ' + data.data_requests.pending_processing + '</li>';
        html += '</ul>';
        html += '</div>';
        
        // Consent Compliance
        html += '<div class="col-md-6 mb-3">';
        html += '<h6><i class="fas fa-shield-check mr-2"></i>Consent Compliance</h6>';
        html += '<ul class="list-unstyled">';
        html += '<li><i class="fas fa-user-times text-danger mr-2"></i>Contacts without consent: ' + data.consent_compliance.contacts_without_consent + '</li>';
        html += '<li><i class="fas fa-clock text-warning mr-2"></i>Expired consents: ' + data.consent_compliance.expired_consents + '</li>';
        html += '<li><i class="fas fa-ban text-secondary mr-2"></i>Withdrawn consents: ' + data.consent_compliance.withdrawn_consents + '</li>';
        html += '</ul>';
        html += '</div>';
        
        // Retention Compliance
        html += '<div class="col-md-12 mb-3">';
        html += '<h6><i class="fas fa-database mr-2"></i>Data Retention Compliance</h6>';
        html += '<ul class="list-unstyled">';
        html += '<li><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Policies not executed: ' + data.retention_compliance.policies_not_executed + '</li>';
        html += '<li><i class="fas fa-trash text-danger mr-2"></i>Records pending deletion: ' + data.retention_compliance.overdue_deletions + '</li>';
        html += '</ul>';
        html += '</div>';
        
        html += '</div>';
        
        return html;
    }
});
</script>
@endpush
@endsection