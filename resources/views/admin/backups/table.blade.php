<!-- Backups Table Content -->
@if($backups->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        <input type="checkbox" id="select-all" onchange="toggleSelectAll()" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Backup
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Type & Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Size & Duration
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Created
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @foreach($backups as $backup)
                    <tr class="hover:bg-gray-700 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="selected_backups[]" value="{{ $backup->id }}" 
                                   onchange="updateBulkActions()" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 mr-4">
                                    <div class="h-10 w-10 bg-gray-700 rounded-lg flex items-center justify-center">
                                        <i class="{{ $backup->type_icon }} text-{{ $backup->status === 'completed' ? 'green' : ($backup->status === 'failed' ? 'red' : 'yellow') }}-400"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center">
                                        <a href="{{ route('admin.backups.show', $backup) }}" 
                                           class="text-white font-medium hover:text-blue-400 transition-colors duration-200">
                                            {{ $backup->name }}
                                        </a>
                                        @if($backup->status === 'in_progress')
                                            <div class="ml-2">
                                                <svg class="w-4 h-4 animate-spin text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    @if($backup->description)
                                        <p class="text-sm text-gray-400 truncate mt-1">{{ $backup->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600">
                                    {{ ucfirst($backup->type) }}
                                </span>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $backup->status === 'completed' ? 'bg-green-900 text-green-200' : ($backup->status === 'failed' ? 'bg-red-900 text-red-200' : 'bg-yellow-900 text-yellow-200') }}">
                                        <i class="{{ $backup->status_icon }} mr-1"></i>
                                        {{ ucwords(str_replace('_', ' ', $backup->status)) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="text-white">
                                @if($backup->file_size)
                                    <span class="font-semibold">{{ $backup->formatted_file_size }}</span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </div>
                            @if($backup->formatted_duration)
                                <div class="text-gray-400 text-xs mt-1">
                                    {{ $backup->formatted_duration }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm">
                                <div class="text-white">{{ $backup->created_at->format('M j, Y') }}</div>
                                <div class="text-gray-400">{{ $backup->created_at->format('H:i') }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $backup->created_at->diffForHumans() }}</div>
                            </div>
                            @if($backup->creator)
                                <div class="flex items-center mt-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($backup->creator->name) }}&size=20&background=007bff&color=fff" 
                                         class="rounded-full mr-1" width="20" height="20" alt="Avatar">
                                    <span class="text-xs text-gray-400">{{ $backup->creator->name }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center space-x-2">
                                @if($backup->status === 'completed' && $backup->file_path)
                                    <a href="{{ route('admin.backups.download', $backup) }}" 
                                       class="inline-flex items-center px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg transition-colors duration-200"
                                       title="Download">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </a>
                                    <button type="button" 
                                            onclick="restoreBackup({{ $backup->id }})"
                                            class="inline-flex items-center px-2 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-xs rounded-lg transition-colors duration-200"
                                            title="Restore">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <a href="{{ route('admin.backups.show', $backup) }}" 
                                   class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-lg transition-colors duration-200"
                                   title="View Details">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>

                                @if($backup->canBeDeleted())
                                    <button type="button" 
                                            onclick="deleteBackupConfirm({{ $backup->id }}, '{{ $backup->name }}')"
                                            class="inline-flex items-center px-2 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition-colors duration-200"
                                            title="Delete">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif

                                <!-- Dropdown Menu -->
                                <div class="relative">
                                    <button type="button" 
                                            onclick="toggleDropdown('dropdown-{{ $backup->id }}')"
                                            class="inline-flex items-center px-2 py-1 bg-gray-600 hover:bg-gray-500 text-white text-xs rounded-lg transition-colors duration-200"
                                            title="More Actions">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                    <div id="dropdown-{{ $backup->id }}" class="hidden absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg ring-1 ring-gray-600 z-10 border border-gray-700">
                                        <div class="py-1">
                                            @if($backup->status === 'completed')
                                                <button onclick="validateBackupInTable({{ $backup->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                    </svg>
                                                    Validate
                                                </button>
                                                <button onclick="duplicateBackup({{ $backup->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                    Duplicate
                                                </button>
                                            @endif
                                            <button onclick="exportBackupInfo({{ $backup->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                Export Info
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($backups->hasPages())
        <div class="px-6 py-4 border-t border-gray-700 bg-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center text-sm text-gray-400">
                    <span>Showing {{ $backups->firstItem() }} to {{ $backups->lastItem() }} of {{ $backups->total() }} backups</span>
                </div>
                <div class="flex items-center space-x-2">
                    @if($backups->onFirstPage())
                        <span class="px-3 py-1 text-sm font-medium text-gray-500 bg-gray-700 border border-gray-600 rounded-lg cursor-not-allowed">
                            Previous
                        </span>
                    @else
                        <a href="{{ $backups->previousPageUrl() }}" class="px-3 py-1 text-sm font-medium text-gray-300 bg-gray-700 border border-gray-600 rounded-lg hover:bg-gray-600 hover:text-white transition-colors duration-200">
                            Previous
                        </a>
                    @endif

                    @foreach($backups->getUrlRange(1, $backups->lastPage()) as $page => $url)
                        @if($page == $backups->currentPage())
                            <span class="px-3 py-1 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 text-sm font-medium text-gray-300 bg-gray-700 border border-gray-600 rounded-lg hover:bg-gray-600 hover:text-white transition-colors duration-200">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    @if($backups->hasMorePages())
                        <a href="{{ $backups->nextPageUrl() }}" class="px-3 py-1 text-sm font-medium text-gray-300 bg-gray-700 border border-gray-600 rounded-lg hover:bg-gray-600 hover:text-white transition-colors duration-200">
                            Next
                        </a>
                    @else
                        <span class="px-3 py-1 text-sm font-medium text-gray-500 bg-gray-700 border border-gray-600 rounded-lg cursor-not-allowed">
                            Next
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <div class="mx-auto w-24 h-24 bg-gray-700 rounded-full flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 1.79 4 4 4h8c0-2.21-1.79-4-4-4H8c-2.21 0-4-1.79-4-4zm0 0c0 2.21 1.79 4 4 4h4c2.21 0 4-1.79 4-4V3c0-2.21-1.79-4-4-4H8c-2.21 0-4 1.79-4 4v4z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-300 mb-2">No backups found</h3>
        <p class="text-gray-400 mb-6">
            @if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to']))
                No backups match your current filters. Try adjusting your search criteria.
            @else
                You haven't created any backups yet. Create your first backup to get started.
            @endif
        </p>
        <div class="flex justify-center space-x-3">
            <a href="{{ route('admin.backups.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create First Backup
            </a>
            @if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to']))
                <a href="{{ route('admin.backups.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg font-medium text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </a>
            @endif
        </div>
    </div>
@endif

<script>
// Additional JavaScript functions for table actions
function restoreBackup(backupId) {
    if (!confirm('Are you sure you want to restore from this backup? This will overwrite your current data.')) return;
    
    fetch(`/admin/backups/${backupId}/restore`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Backup restore started successfully', 'success');
            setTimeout(() => refreshTable(), 2000);
        } else {
            showToast('Failed to start backup restore', 'error');
        }
    })
    .catch(error => {
        console.error('Restore error:', error);
        showToast('Failed to start backup restore', 'error');
    });
}

function deleteBackupConfirm(backupId, backupName) {
    if (!confirm(`Are you sure you want to delete backup "${backupName}"? This action cannot be undone.`)) return;
    
    fetch(`/admin/backups/${backupId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Backup deleted successfully', 'success');
            setTimeout(() => refreshTable(), 1000);
        } else {
            showToast('Failed to delete backup', 'error');
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Failed to delete backup', 'error');
    });
}

function validateBackupInTable(backupId) {
    showToast('Validating backup...', 'info');
    
    fetch(`/admin/backups/${backupId}/validate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            showToast('Backup validation successful', 'success');
        } else {
            showToast('Backup validation failed: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Validation error:', error);
        showToast('Failed to validate backup', 'error');
    });
}

function duplicateBackup(backupId) {
    if (!confirm('Create a duplicate of this backup?')) return;
    
    fetch(`/admin/backups/${backupId}/duplicate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Backup duplication started', 'success');
            setTimeout(() => refreshTable(), 2000);
        } else {
            showToast('Failed to duplicate backup', 'error');
        }
    })
    .catch(error => {
        console.error('Duplicate error:', error);
        showToast('Failed to duplicate backup', 'error');
    });
}

function exportBackupInfo(backupId) {
    window.open(`/admin/backups/${backupId}/export-info`, '_blank');
    showToast('Backup information exported', 'success');
}

// Toggle individual dropdowns
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const isHidden = dropdown.classList.contains('hidden');
    
    // Close all dropdowns first
    document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.classList.add('hidden'));
    
    // Toggle the clicked dropdown
    if (isHidden) {
        dropdown.classList.remove('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick^="toggleDropdown"]') && !e.target.closest('[id^="dropdown-"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Toast helper for table actions
function showToast(message, type = 'info') {
    const colors = {
        'success': 'bg-green-600',
        'error': 'bg-red-600',
        'warning': 'bg-yellow-600',
        'info': 'bg-blue-600'
    };
    
    const toastHtml = `
        <div class="fixed top-4 right-4 z-50 ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg animate-fade-in-down" role="alert">
            <span>${message}</span>
            <button type="button" class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.remove();">&times;</button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    setTimeout(function() {
        const toasts = document.querySelectorAll('.fixed.top-4.right-4');
        toasts.forEach(toast => toast.remove());
    }, 5000);
}
</script>