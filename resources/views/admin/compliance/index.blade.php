@extends('layouts.app')

@section('title', 'Compliance Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl shadow-lg text-white overflow-hidden">
                <div class="p-8 text-center">
                    <i class="fas fa-shield-check text-5xl mb-4 opacity-75"></i>
                    <h1 class="text-3xl font-bold mb-2">Compliance Dashboard</h1>
                    <p class="text-lg opacity-75">GDPR compliance monitoring and data protection management</p>
                </div>
                <div class="px-8 pb-6">
                    <div class="flex flex-wrap gap-3 justify-center">
                        <button id="runAuditBtn" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 font-medium rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>
                            Run Audit
                        </button>
                        
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-indigo-800 text-white font-medium rounded-lg hover:bg-indigo-900 transition-colors duration-200">
                                <i class="fas fa-cogs mr-2"></i>
                                Actions
                                <i class="fas fa-chevron-down ml-2"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 x-transition:enter="transition ease-out duration-200" 
                                 x-transition:enter-start="opacity-0 transform scale-95" 
                                 x-transition:enter-end="opacity-100 transform scale-100" 
                                 x-transition:leave="transition ease-in duration-75" 
                                 x-transition:leave-start="opacity-100 transform scale-100" 
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 top-12 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <a href="{{ route('admin.compliance.data-requests') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-file-text mr-2"></i>
                                    Manage Data Requests
                                </a>
                                <a href="{{ route('admin.compliance.retention-policies') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-clock mr-2"></i>
                                    Retention Policies
                                </a>
                                <a href="{{ route('admin.compliance.consent-logs') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
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
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-xs font-medium text-blue-600 uppercase mb-2">
                                Compliance Score
                            </div>
                            <div class="flex items-center">
                                <div class="text-3xl font-bold mr-4 {{ $stats['compliance_score'] >= 80 ? 'text-green-600' : ($stats['compliance_score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $stats['compliance_score'] }}%
                                </div>
                                <div class="flex-1 max-w-md">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $stats['compliance_score'] >= 80 ? 'bg-green-500' : ($stats['compliance_score'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                             style="width: {{ $stats['compliance_score'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="inline-flex items-center text-sm {{ $stats['compliance_score'] >= 80 ? 'text-green-700' : ($stats['compliance_score'] >= 60 ? 'text-yellow-700' : 'text-red-700') }}">
                                    @if($stats['compliance_score'] >= 90)
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Excellent compliance - minimal issues detected
                                    @elseif($stats['compliance_score'] >= 80)
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Good compliance - minor issues to address
                                    @elseif($stats['compliance_score'] >= 60)
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Moderate compliance - several issues need attention
                                    @else
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Poor compliance - immediate action required
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="ml-6">
                            <i class="fas fa-shield-check text-6xl {{ $stats['compliance_score'] >= 80 ? 'text-green-500' : ($stats['compliance_score'] >= 60 ? 'text-yellow-500' : 'text-red-500') }} opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Data Requests -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-xs font-medium text-blue-600 uppercase mb-1">
                                Data Requests
                            </div>
                            <div class="text-2xl font-bold text-gray-900 mb-2">
                                {{ $stats['data_requests']['total'] }}
                            </div>
                            <div class="space-y-1">
                                @if($stats['data_requests']['pending'] > 0)
                                    <div class="text-xs text-yellow-600">{{ $stats['data_requests']['pending'] }} pending</div>
                                @endif
                                @if($stats['data_requests']['overdue'] > 0)
                                    <div class="text-xs text-red-600">{{ $stats['data_requests']['overdue'] }} overdue</div>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4">
                            <i class="fas fa-file-text text-2xl text-blue-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consent Logs -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-xs font-medium text-green-600 uppercase mb-1">
                                Consent Records
                            </div>
                            <div class="text-2xl font-bold text-gray-900 mb-2">
                                {{ $stats['consent_logs']['total'] }}
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-green-600">{{ $stats['consent_logs']['given'] }} active</div>
                                @if($stats['consent_logs']['withdrawn'] > 0)
                                    <div class="text-xs text-yellow-600">{{ $stats['consent_logs']['withdrawn'] }} withdrawn</div>
                                @endif
                                @if($stats['consent_logs']['expired'] > 0)
                                    <div class="text-xs text-red-600">{{ $stats['consent_logs']['expired'] }} expired</div>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4">
                            <i class="fas fa-shield-check text-2xl text-green-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Retention Policies -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-xs font-medium text-yellow-600 uppercase mb-1">
                                Retention Policies
                            </div>
                            <div class="text-2xl font-bold text-gray-900 mb-2">
                                {{ $stats['retention_policies']['total'] }}
                            </div>
                            <div class="space-y-1">
                                <div class="text-xs text-green-600">{{ $stats['retention_policies']['active'] }} active</div>
                                @if($stats['retention_policies']['overdue_executions'] > 0)
                                    <div class="text-xs text-red-600">{{ $stats['retention_policies']['overdue_executions'] }} overdue</div>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4">
                            <i class="fas fa-clock text-2xl text-yellow-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-gray-500 overflow-hidden">
                <div class="p-6">
                    <div class="text-xs font-medium text-gray-600 uppercase mb-3">
                        Quick Actions
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('admin.compliance.data-requests') }}" 
                           class="block w-full px-3 py-2 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-center">
                            <i class="fas fa-file-text mr-1"></i>
                            View Requests
                        </a>
                        <button id="executeRetentionBtn" 
                                class="w-full px-3 py-2 text-sm bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition-colors">
                            <i class="fas fa-clock mr-1"></i>
                            Execute Policies
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Data Requests -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Data Requests</h3>
                        <a href="{{ route('admin.compliance.data-requests') }}" 
                           class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            View All
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-200">
                    @if($recentRequests->count() > 0)
                        @foreach($recentRequests as $request)
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <i class="fas {{ $request->getRequestTypeIcon() }} mr-2 text-gray-400"></i>
                                            <span class="font-medium text-gray-900">
                                                {{ $request->contact ? $request->contact->full_name : $request->email }}
                                            </span>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 'bg-' . $request->getStatusBadgeClass() . '-100 text-' . $request->getStatusBadgeClass() . '-800' }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500">
                                            {{ ucfirst($request->request_type) }} request • 
                                            {{ $request->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="relative ml-4" x-data="{ open: false }">
                                        <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div x-show="open" @click.away="open = false" 
                                             x-transition:enter="transition ease-out duration-200" 
                                             x-transition:enter-start="opacity-0 transform scale-95" 
                                             x-transition:enter-end="opacity-100 transform scale-100" 
                                             x-transition:leave="transition ease-in duration-75" 
                                             x-transition:leave-start="opacity-100 transform scale-100" 
                                             x-transition:leave-end="opacity-0 transform scale-95"
                                             class="absolute right-0 top-10 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <i class="fas fa-eye mr-2"></i>
                                                View Details
                                            </a>
                                            @if($request->canBeProcessed())
                                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <i class="fas fa-play mr-2"></i>
                                                    Process Request
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-12 text-center">
                            <i class="fas fa-file-text text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No recent data requests</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Consent Activity -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Consent Activity</h3>
                        <a href="{{ route('admin.compliance.consent-logs') }}" 
                           class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            View All
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-200">
                    @if($recentConsents->count() > 0)
                        @foreach($recentConsents as $consent)
                            <div class="p-6">
                                <div class="flex items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <i class="fas {{ $consent->getConsentTypeIcon() }} mr-2 text-gray-400"></i>
                                            <span class="font-medium text-gray-900">
                                                {{ $consent->contact->full_name ?? 'Unknown Contact' }}
                                            </span>
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 'bg-' . $consent->getStatusBadgeClass() . '-100 text-' . $consent->getStatusBadgeClass() . '-800' }}">
                                                {{ ucfirst($consent->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500">
                                            {{ str_replace('_', ' ', ucfirst($consent->consent_type)) }} • 
                                            {{ $consent->given_at ? $consent->given_at->diffForHumans() : 'Not given' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-12 text-center">
                            <i class="fas fa-shield-check text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No recent consent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audit Results Modal -->
<div id="auditModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                        <i class="fas fa-search mr-2 text-indigo-600"></i>
                        Compliance Audit Results
                    </h3>
                    <button type="button" onclick="closeAuditModal()" 
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="auditResults" class="mt-4">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner animate-spin text-2xl text-gray-400"></i>
                        <p class="mt-2 text-gray-500">Running compliance audit...</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-between">
                <button type="button" onclick="closeAuditModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Close
                </button>
                <button type="button" id="exportAuditBtn" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <i class="fas fa-download mr-2"></i>
                    Export Report
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Run audit
    document.getElementById('runAuditBtn').addEventListener('click', function() {
        showAuditModal();
        
        fetch('/admin/compliance/audit')
            .then(response => response.json())
            .then(data => {
                document.getElementById('auditResults').innerHTML = generateAuditReport(data);
            })
            .catch(error => {
                document.getElementById('auditResults').innerHTML = 
                    '<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">Error running audit: ' + error.message + '</div>';
            });
    });

    // Execute retention policies
    document.getElementById('executeRetentionBtn').addEventListener('click', function() {
        if (confirm('Are you sure you want to execute all active retention policies? This action cannot be undone.')) {
            // Implementation for batch execution would go here
            showToast('Retention policy execution would be implemented here', 'info');
        }
    });

    function showAuditModal() {
        document.getElementById('auditModal').classList.remove('hidden');
    }

    function closeAuditModal() {
        document.getElementById('auditModal').classList.add('hidden');
    }

    window.closeAuditModal = closeAuditModal;

    function generateAuditReport(data) {
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
        
        // Data Requests Issues
        html += '<div class="bg-white rounded-lg border border-gray-200 p-6">';
        html += '<h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">';
        html += '<i class="fas fa-file-text mr-2 text-blue-500"></i>Data Requests</h4>';
        html += '<div class="space-y-3">';
        html += '<div class="flex items-center"><i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i><span class="text-sm">Overdue requests: ' + data.data_requests.overdue + '</span></div>';
        html += '<div class="flex items-center"><i class="fas fa-clock text-blue-500 mr-2"></i><span class="text-sm">Pending verification: ' + data.data_requests.pending_verification + '</span></div>';
        html += '<div class="flex items-center"><i class="fas fa-cog text-purple-500 mr-2"></i><span class="text-sm">Pending processing: ' + data.data_requests.pending_processing + '</span></div>';
        html += '</div></div>';
        
        // Consent Compliance
        html += '<div class="bg-white rounded-lg border border-gray-200 p-6">';
        html += '<h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">';
        html += '<i class="fas fa-shield-check mr-2 text-green-500"></i>Consent Compliance</h4>';
        html += '<div class="space-y-3">';
        html += '<div class="flex items-center"><i class="fas fa-user-times text-red-500 mr-2"></i><span class="text-sm">Contacts without consent: ' + data.consent_compliance.contacts_without_consent + '</span></div>';
        html += '<div class="flex items-center"><i class="fas fa-clock text-yellow-500 mr-2"></i><span class="text-sm">Expired consents: ' + data.consent_compliance.expired_consents + '</span></div>';
        html += '<div class="flex items-center"><i class="fas fa-ban text-gray-500 mr-2"></i><span class="text-sm">Withdrawn consents: ' + data.consent_compliance.withdrawn_consents + '</span></div>';
        html += '</div></div>';
        
        html += '</div>';
        
        // Retention Compliance
        html += '<div class="bg-white rounded-lg border border-gray-200 p-6 mt-6">';
        html += '<h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">';
        html += '<i class="fas fa-database mr-2 text-indigo-500"></i>Data Retention Compliance</h4>';
        html += '<div class="space-y-3">';
        html += '<div class="flex items-center"><i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i><span class="text-sm">Policies not executed: ' + data.retention_compliance.policies_not_executed + '</span></div>';
        html += '<div class="flex items-center"><i class="fas fa-trash text-red-500 mr-2"></i><span class="text-sm">Records pending deletion: ' + data.retention_compliance.overdue_deletions + '</span></div>';
        html += '</div></div>';
        
        return html;
    }

    function showToast(message, type = 'info') {
        const toastClasses = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'info': 'bg-blue-500',
            'warning': 'bg-yellow-500'
        }[type] || 'bg-blue-500';
        
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${toastClasses} text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
        toast.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 5000);
    }
});
</script>
@endpush
