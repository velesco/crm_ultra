<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="relative px-6 py-3">
                    <input type="checkbox" id="selectAllAttempts" 
                           class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700">
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Device</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
            @forelse($attempts as $attempt)
            <tr class="attempt-row hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" data-attempt-id="{{ $attempt->id }}">
                <td class="relative px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="attempt-checkbox absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700" value="{{ $attempt->id }}">
                </td>
                
                <!-- Type -->
                <td class="px-6 py-4 whitespace-nowrap">
                    @switch($attempt->type)
                        @case('failed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">
                                <i class="fas fa-times-circle mr-1"></i>
                                Failed
                            </span>
                            @break
                        @case('success')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200">
                                <i class="fas fa-check-circle mr-1"></i>
                                Success
                            </span>
                            @break
                        @case('blocked')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200">
                                <i class="fas fa-ban mr-1"></i>
                                Blocked
                            </span>
                            @break
                    @endswitch
                </td>
                
                <!-- Email -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div>
                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                {{ Str::limit($attempt->email, 25) }}
                            </div>
                            @if($attempt->user)
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-user mr-1"></i>{{ $attempt->user->name }}
                                </div>
                            @endif
                        </div>
                    </div>
                </td>
                
                <!-- IP Address -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-mono text-gray-900 dark:text-white">{{ $attempt->ip_address }}</div>
                    @if(\App\Models\LoginAttempt::isBlocked($attempt->ip_address, 'ip'))
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 mt-1">
                            <i class="fas fa-ban mr-1"></i>IP Blocked
                        </span>
                    @endif
                </td>
                
                <!-- Location -->
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($attempt->location)
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            {{ $attempt->location }}
                        </div>
                    @else
                        <span class="text-sm text-gray-400 dark:text-gray-500">Unknown</span>
                    @endif
                </td>
                
                <!-- Device -->
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($attempt->device)
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if(Str::contains($attempt->device, 'Mobile'))
                                <i class="fas fa-mobile-alt mr-1"></i>
                            @elseif(Str::contains($attempt->device, 'Tablet'))
                                <i class="fas fa-tablet-alt mr-1"></i>
                            @else
                                <i class="fas fa-desktop mr-1"></i>
                            @endif
                            {{ $attempt->device }}
                        </div>
                    @else
                        <span class="text-sm text-gray-400 dark:text-gray-500">Unknown</span>
                    @endif
                </td>
                
                <!-- User -->
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($attempt->user)
                        <div class="flex items-center">
                            @if($attempt->user->avatar)
                                <img src="{{ $attempt->user->avatar }}" 
                                     class="h-6 w-6 rounded-full mr-3" 
                                     alt="Avatar">
                            @else
                                <div class="h-6 w-6 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-xs font-medium text-white">
                                        {{ strtoupper(substr($attempt->user->name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $attempt->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $attempt->user->id }}</div>
                            </div>
                        </div>
                    @else
                        <span class="text-sm text-gray-400 dark:text-gray-500">N/A</span>
                    @endif
                </td>
                
                <!-- Time -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                        {{ $attempt->created_at->format('M j, H:i') }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $attempt->created_at->diffForHumans() }}
                    </div>
                </td>
                
                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($attempt->blocked_until)
                        @if($attempt->blocked_until > now())
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">
                                    Blocked
                                </span>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Until: {{ $attempt->blocked_until->format('M j, H:i') }}
                                </div>
                            </div>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                Expired
                            </span>
                        @endif
                    @else
                        @switch($attempt->type)
                            @case('success')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200">
                                    Active
                                </span>
                                @break
                            @case('failed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    Normal
                                </span>
                                @break
                            @default
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    -
                                </span>
                        @endswitch
                    @endif
                </td>
                
                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-1">
                        <button type="button" onclick="quickView({{ $attempt->id }})" title="Quick View"
                                class="inline-flex items-center p-1.5 border border-transparent rounded-md text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors duration-200">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                        
                        @if($attempt->type === 'failed')
                            <!-- Block IP -->
                            <button type="button" onclick="blockIp('{{ $attempt->ip_address }}')" title="Block IP"
                                    class="inline-flex items-center p-1.5 border border-transparent rounded-md text-yellow-600 hover:bg-yellow-50 dark:text-yellow-400 dark:hover:bg-yellow-900/50 transition-colors duration-200">
                                <i class="fas fa-ban text-sm"></i>
                            </button>
                            
                            <!-- Block User -->
                            @if($attempt->email !== 'system_block')
                            <button type="button" onclick="blockUser('{{ $attempt->email }}')" title="Block User"
                                    class="inline-flex items-center p-1.5 border border-transparent rounded-md text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors duration-200">
                                <i class="fas fa-user-slash text-sm"></i>
                            </button>
                            @endif
                        @endif
                        
                        @if($attempt->blocked_until && $attempt->blocked_until > now())
                            <!-- Unblock actions -->
                            <button type="button" onclick="unblockIp('{{ $attempt->ip_address }}')" title="Unblock IP"
                                    class="inline-flex items-center p-1.5 border border-transparent rounded-md text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/50 transition-colors duration-200">
                                <i class="fas fa-unlock text-sm"></i>
                            </button>
                            
                            @if($attempt->email !== 'system_block')
                            <button type="button" onclick="unblockUser('{{ $attempt->email }}')" title="Unblock User"
                                    class="inline-flex items-center p-1.5 border border-transparent rounded-md text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors duration-200">
                                <i class="fas fa-user-check text-sm"></i>
                            </button>
                            @endif
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-6 py-12 text-center">
                    <div class="text-gray-500 dark:text-gray-400">
                        <i class="fas fa-search text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No login attempts found</h3>
                        <p class="text-gray-500 dark:text-gray-400">Try adjusting your search filters or check back later.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($attempts->hasPages())
<div class="flex items-center justify-between mt-6">
    <div class="text-sm text-gray-700 dark:text-gray-300">
        Showing {{ $attempts->firstItem() }} to {{ $attempts->lastItem() }} of {{ number_format($attempts->total()) }} results
    </div>
    <div>
        {{ $attempts->appends(request()->query())->links() }}
    </div>
</div>
@endif

<script>
$(document).ready(function() {
    // Select all checkbox functionality
    $('#selectAllAttempts').on('change', function() {
        $('.attempt-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActions();
    });
    
    // Individual checkbox functionality
    $('.attempt-checkbox').on('change', function(e) {
        e.stopPropagation(); // Prevent row click
        updateSelectAllState();
        updateBulkActions();
    });
    
    // Row click functionality (except on buttons and checkboxes)
    $('.attempt-row').on('click', function(e) {
        if (!$(e.target).closest('button, input, a').length) {
            const checkbox = $(this).find('.attempt-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });
});

function updateSelectAllState() {
    const total = $('.attempt-checkbox').length;
    const checked = $('.attempt-checkbox:checked').length;
    
    const selectAllCheckbox = document.getElementById('selectAllAttempts');
    if (selectAllCheckbox) {
        selectAllCheckbox.indeterminate = checked > 0 && checked < total;
        selectAllCheckbox.checked = checked === total && total > 0;
    }
}

function updateBulkActions() {
    const selected = $('.attempt-checkbox:checked').length;
    
    if (selected > 0) {
        if (!$('#bulkActionsBar').length) {
            const bulkBar = $(`
                <div id="bulkActionsBar" class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-blue-800 dark:text-blue-200">
                            <strong class="selected-count">${selected}</strong> attempts selected
                        </div>
                        <div class="flex space-x-2">
                            <button type="button" onclick="bulkBlockIps()" 
                                    class="inline-flex items-center px-3 py-2 border border-yellow-300 dark:border-yellow-600 rounded-md text-sm font-medium bg-white dark:bg-gray-800 text-yellow-700 dark:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors duration-200">
                                <i class="fas fa-ban mr-2"></i>Block IPs
                            </button>
                            <button type="button" onclick="bulkBlockUsers()" 
                                    class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-600 rounded-md text-sm font-medium bg-white dark:bg-gray-800 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                                <i class="fas fa-user-slash mr-2"></i>Block Users
                            </button>
                            <button type="button" onclick="clearSelection()" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            `);
            $('.overflow-x-auto').before(bulkBar);
        } else {
            $('#bulkActionsBar .selected-count').text(selected);
        }
    } else {
        $('#bulkActionsBar').remove();
    }
}

function clearSelection() {
    $('.attempt-checkbox, #selectAllAttempts').prop('checked', false);
    document.getElementById('selectAllAttempts').indeterminate = false;
    updateBulkActions();
}

function bulkBlockIps() {
    const selectedIps = [];
    $('.attempt-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const ip = row.find('td:nth-child(4) .font-mono').text().trim();
        if (ip && !selectedIps.includes(ip)) {
            selectedIps.push(ip);
        }
    });
    
    if (selectedIps.length === 0) {
        showToast('warning', 'No valid IP addresses selected');
        return;
    }
    
    if (confirm(`Block ${selectedIps.length} IP address(es)?`)) {
        // Implementation for bulk blocking IPs
        showToast('info', `Bulk IP blocking would be implemented here for ${selectedIps.length} IPs`);
    }
}

function bulkBlockUsers() {
    const selectedEmails = [];
    $('.attempt-checkbox:checked').each(function() {
        const row = $(this).closest('tr');
        const email = row.find('td:nth-child(3) .text-blue-600').text().trim();
        if (email && email !== 'system_block' && !selectedEmails.includes(email)) {
            selectedEmails.push(email);
        }
    });
    
    if (selectedEmails.length === 0) {
        showToast('warning', 'No valid email addresses selected');
        return;
    }
    
    if (confirm(`Block ${selectedEmails.length} user(s)?`)) {
        // Implementation for bulk blocking users
        showToast('info', `Bulk user blocking would be implemented here for ${selectedEmails.length} users`);
    }
}

function showToast(type, message) {
    // Create toast notification
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'info' ? 'bg-blue-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-red-500';
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
</script>
