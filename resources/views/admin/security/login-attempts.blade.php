@extends('layouts.app')

@section('title', 'Login Attempts')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                        <div>
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                                    <i class="fas fa-list-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Login Attempts</h1>
                                    <p class="text-gray-600 dark:text-gray-400">Detailed view of all login attempts and security events</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.security.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Dashboard
                            </a>
                            <a href="{{ route('admin.security.export') }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-download mr-2"></i>
                                Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-filter text-blue-600 dark:text-blue-400"></i>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
                    </div>
                </div>
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.security.login-attempts') }}" id="filtersForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <!-- Type Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                                <select name="type" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">All Types</option>
                                    <option value="failed" {{ request('type') === 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="success" {{ request('type') === 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="blocked" {{ request('type') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                </select>
                            </div>
                            
                            <!-- Email Filter -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input type="email" name="email" 
                                       value="{{ request('email') }}" 
                                       placeholder="Search by email..."
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <!-- IP Address Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Address</label>
                                <input type="text" name="ip_address" 
                                       value="{{ request('ip_address') }}" 
                                       placeholder="Search by IP..."
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <!-- Date From -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date From</label>
                                <input type="date" name="date_from" 
                                       value="{{ request('date_from') }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <!-- Date To -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date To</label>
                                <input type="date" name="date_to" 
                                       value="{{ request('date_to') }}"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap gap-2 pt-2">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-search mr-2"></i>
                                Search
                            </button>
                            
                            @if(request()->hasAny(['type', 'email', 'ip_address', 'date_from', 'date_to']))
                            <a href="{{ route('admin.security.login-attempts') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i>
                                Clear Filters
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Login Attempts Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Login Attempts</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                            {{ $attempts->total() }} total
                        </span>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="button" onclick="refreshTable()"
                                class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200">
                            <i class="fas fa-sync-alt mr-1"></i>
                            Refresh
                        </button>
                        <button type="button" onclick="toggleAutoRefresh()"
                                class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                            <i class="fas fa-play mr-1" id="autoRefreshIcon"></i>
                            Auto Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="attemptsTableContainer">
                @include('admin.security.partials.attempts-table', ['attempts' => $attempts])
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $attempts->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Block Actions Modal -->
<div id="blockActionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="blockModalTitle">Block Actions</h3>
            <button type="button" onclick="closeModal('blockActionsModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="blockActionForm" class="space-y-4">
            <input type="hidden" id="blockTargetValue" name="target_value">
            <input type="hidden" id="blockTargetType" name="target_type">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" id="blockTargetLabel">Target</label>
                <input type="text" id="displayBlockTarget" readonly
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Block Duration (hours)</label>
                <select name="duration" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="1">1 hour</option>
                    <option value="6">6 hours</option>
                    <option value="24" selected>24 hours</option>
                    <option value="168">1 week</option>
                    <option value="720">1 month</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason (optional)</label>
                <textarea name="reason" rows="3" placeholder="Reason for blocking..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('blockActionsModal')"
                        class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit" id="blockSubmitBtn"
                        class="flex-1 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors duration-200">
                    <i class="fas fa-ban mr-2"></i>Block
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick View Modal -->
<div id="quickViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border max-w-2xl shadow-lg rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Login Attempt Details</h3>
            <button type="button" onclick="closeModal('quickViewModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="quickViewContent" class="text-gray-900 dark:text-white">
            <!-- Content will be loaded here -->
        </div>
        <div class="flex justify-end pt-4">
            <button type="button" onclick="closeModal('quickViewModal')"
                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg transition-colors duration-200">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let autoRefreshInterval = null;
let isAutoRefreshActive = false;

$(document).ready(function() {
    setupEventHandlers();
});

function setupEventHandlers() {
    // Block action form
    $('#blockActionForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const targetType = $('#blockTargetType').val();
        
        let endpoint;
        let data = {};
        
        if (targetType === 'ip') {
            endpoint = '{{ route("admin.security.block-ip") }}';
            data.ip_address = $('#blockTargetValue').val();
        } else {
            endpoint = '{{ route("admin.security.block-user") }}';
            data.email = $('#blockTargetValue').val();
        }
        
        data.duration = formData.get('duration');
        data.reason = formData.get('reason');
        
        $.post(endpoint, data)
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    closeModal('blockActionsModal');
                    refreshTable();
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Error blocking target: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    });
}

function blockIp(ipAddress) {
    $('#blockTargetValue').val(ipAddress);
    $('#blockTargetType').val('ip');
    $('#displayBlockTarget').val(ipAddress);
    $('#blockTargetLabel').text('IP Address');
    $('#blockModalTitle').text('Block IP Address');
    $('#blockSubmitBtn').html('<i class="fas fa-ban mr-2"></i>Block IP');
    showModal('blockActionsModal');
}

function blockUser(email) {
    $('#blockTargetValue').val(email);
    $('#blockTargetType').val('user');
    $('#displayBlockTarget').val(email);
    $('#blockTargetLabel').text('Email Address');
    $('#blockModalTitle').text('Block User Email');
    $('#blockSubmitBtn').html('<i class="fas fa-ban mr-2"></i>Block User');
    showModal('blockActionsModal');
}

function unblockIp(ipAddress) {
    if (confirm(`Are you sure you want to unblock IP ${ipAddress}?`)) {
        $.post('{{ route("admin.security.unblock-ip") }}', { ip_address: ipAddress })
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    refreshTable();
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Error unblocking IP: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    }
}

function unblockUser(email) {
    if (confirm(`Are you sure you want to unblock user ${email}?`)) {
        $.post('{{ route("admin.security.unblock-user") }}', { email: email })
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    refreshTable();
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Error unblocking user: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    }
}

function quickView(attemptId) {
    $('#quickViewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    showModal('quickViewModal');
    
    // In a real implementation, you would fetch detailed info about the attempt
    // For now, we'll show a placeholder
    setTimeout(() => {
        $('#quickViewContent').html(`
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div><strong class="text-gray-700 dark:text-gray-300">Attempt ID:</strong> <span class="text-gray-900 dark:text-white">${attemptId}</span></div>
                    <div><strong class="text-gray-700 dark:text-gray-300">Status:</strong> <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">Failed</span></div>
                    <div><strong class="text-gray-700 dark:text-gray-300">Time:</strong> <span class="text-gray-900 dark:text-white">2 minutes ago</span></div>
                </div>
                <div class="space-y-2">
                    <div><strong class="text-gray-700 dark:text-gray-300">User Agent:</strong> <span class="text-gray-900 dark:text-white text-sm">Mozilla/5.0...</span></div>
                    <div><strong class="text-gray-700 dark:text-gray-300">Location:</strong> <span class="text-gray-900 dark:text-white">Bucharest, RO</span></div>
                    <div><strong class="text-gray-700 dark:text-gray-300">Device:</strong> <span class="text-gray-900 dark:text-white">Desktop</span></div>
                </div>
            </div>
        `);
    }, 500);
}

function refreshTable() {
    const currentUrl = window.location.href;
    
    $.get(currentUrl, { ajax: true })
        .done(function(html) {
            $('#attemptsTableContainer').html(html);
            showToast('info', 'Table refreshed');
        })
        .fail(function() {
            showToast('error', 'Error refreshing table');
        });
}

function toggleAutoRefresh() {
    if (isAutoRefreshActive) {
        clearInterval(autoRefreshInterval);
        isAutoRefreshActive = false;
        $('#autoRefreshIcon').removeClass('fa-stop').addClass('fa-play');
        showToast('info', 'Auto-refresh stopped');
    } else {
        autoRefreshInterval = setInterval(refreshTable, 10000); // Every 10 seconds
        isAutoRefreshActive = true;
        $('#autoRefreshIcon').removeClass('fa-play').addClass('fa-stop');
        showToast('success', 'Auto-refresh started (every 10s)');
    }
}

function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function showToast(type, message) {
    // Create toast notification
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'info' ? 'bg-blue-500' : 'bg-red-500';
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-300 translate-x-full`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Filter form auto-submit on change
$('#filtersForm select, #filtersForm input[type="date"]').on('change', function() {
    $('#filtersForm').submit();
});
</script>
@endpush
