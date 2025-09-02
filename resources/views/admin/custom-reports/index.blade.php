@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg">
                            <i class="fas fa-chart-bar text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Custom Reports</h1>
                            <p class="text-gray-600 mt-1">Create and manage custom reports with advanced filtering and visualization</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" 
                            x-data
                            @click="$dispatch('open-modal', 'bulk-action-modal')"
                            class="hidden px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                            id="bulkActionBtn">
                        <i class="fas fa-tasks mr-2"></i>Bulk Actions
                    </button>
                    <a href="{{ route('admin.custom-reports.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <i class="fas fa-plus mr-2"></i>New Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total Reports</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_reports'] ?? 0 }}</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-chart-bar text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">My Reports</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['my_reports'] ?? 0 }}</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-chart text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-green-500 to-green-600"></div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Public Reports</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['public_reports'] ?? 0 }}</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-share-alt text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-cyan-500 to-cyan-600"></div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">Active Reports</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['active_reports'] ?? 0 }}</p>
                        </div>
                        <div class="flex items-center justify-center w-14 h-14 bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-amber-500 to-amber-600"></div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8" x-data="{ filtersOpen: false }">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg">
                        <i class="fas fa-filter text-white"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Filters & Search</h3>
                </div>
                <button @click="filtersOpen = !filtersOpen"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <i class="fas fa-chevron-down transform transition-transform duration-200" 
                       :class="{ 'rotate-180': filtersOpen }"></i>
                </button>
            </div>
            <div x-show="filtersOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="p-6 border-t border-gray-100">
                <form method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                    <div class="xl:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Reports</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200" 
                                   placeholder="Report name or description..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\CustomReport::getCategories() as $key => $label)
                                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Source</label>
                        <select name="data_source" class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                            <option value="">All Sources</option>
                            @php $dataSources = (new \App\Models\CustomReport())->getAvailableDataSources(); @endphp
                            @foreach($dataSources as $key => $info)
                                <option value="{{ $key }}" {{ request('data_source') == $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Visibility</label>
                        <select name="visibility" class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                            <option value="">All Visibility</option>
                            <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Private</option>
                            <option value="shared" {{ request('visibility') == 'shared' ? 'selected' : '' }}>Shared</option>
                            <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Public</option>
                        </select>
                    </div>

                    <div class="xl:col-span-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                                <select name="sort_by" class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                    <option value="updated_at" {{ request('sort_by', 'updated_at') == 'updated_at' ? 'selected' : '' }}>Updated</option>
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Created</option>
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                    <option value="run_count" {{ request('sort_by') == 'run_count' ? 'selected' : '' }}>Usage</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                                <select name="sort_direction" class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                    <option value="desc" {{ request('sort_direction', 'desc') == 'desc' ? 'selected' : '' }}>DESC</option>
                                    <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>ASC</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2 flex items-end gap-3">
                                <button type="submit" 
                                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                                    <i class="fas fa-search mr-2"></i>Apply Filters
                                </button>
                                <a href="{{ route('admin.custom-reports.index') }}" 
                                   class="px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                                    <i class="fas fa-times mr-2"></i>Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg">
                        <i class="fas fa-list text-white"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Custom Reports ({{ $reports->total() }})</h3>
                </div>
                <div class="flex items-center gap-3">
                    <button id="selectAllBtn"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <i class="fas fa-check-square mr-2"></i>Select All
                    </button>
                    <button id="refreshBtn"
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div id="reportsTable" class="overflow-hidden">
                @include('admin.custom-reports.table', ['reports' => $reports])
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div x-data="{ open: false }" 
     @open-modal.window="if ($event.detail === 'bulk-action-modal') open = true"
     @close-modal.window="if ($event.detail === 'bulk-action-modal') open = false"
     x-show="open"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="open = false"></div>

        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Bulk Actions</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="bulkActionForm">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Action</label>
                    <select name="action" 
                            class="block w-full px-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200" 
                            required>
                        <option value="">Choose an action...</option>
                        <option value="delete">Delete Reports</option>
                        <option value="activate">Activate Reports</option>
                        <option value="deactivate">Deactivate Reports</option>
                        <option value="make_private">Make Private</option>
                        <option value="make_shared">Make Shared</option>
                    </select>
                </div>
                <div id="selectedCount" class="text-sm text-gray-600 mb-6 p-3 bg-blue-50 rounded-lg"></div>
            </form>

            <div class="flex justify-end gap-3">
                <button @click="open = false" 
                        class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                    Cancel
                </button>
                <button id="executeActionBtn"
                        class="px-6 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                    Execute Action
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedReports = [];
    
    // Handle individual checkbox selection
    $(document).on('change', '.checkbox-selection', function() {
        const reportId = $(this).val();
        if ($(this).is(':checked')) {
            selectedReports.push(reportId);
        } else {
            selectedReports = selectedReports.filter(id => id !== reportId);
        }
        updateBulkActionButton();
    });
    
    // Handle select all checkbox
    $('#selectAllBtn').click(function() {
        const checkboxes = $('.checkbox-selection');
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        
        checkboxes.prop('checked', !allChecked);
        selectedReports = allChecked ? [] : checkboxes.map(function() { return $(this).val(); }).get();
        updateBulkActionButton();
    });
    
    // Update bulk action button visibility
    function updateBulkActionButton() {
        if (selectedReports.length > 0) {
            $('#bulkActionBtn').removeClass('hidden');
            $('#selectedCount').text(`${selectedReports.length} report(s) selected`);
        } else {
            $('#bulkActionBtn').addClass('hidden');
        }
    }
    
    // Execute bulk action
    $('#executeActionBtn').click(function() {
        const action = $('select[name="action"]').val();
        if (!action || selectedReports.length === 0) {
            return;
        }
        
        if (action === 'delete' && !confirm('Are you sure you want to delete the selected reports?')) {
            return;
        }
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Processing...');
        
        $.post('{{ route("admin.custom-reports.bulk-action") }}', {
            _token: '{{ csrf_token() }}',
            action: action,
            report_ids: selectedReports
        })
        .done(function(response) {
            if (response.success) {
                showNotification('success', response.message);
                refreshTable();
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'bulk-action-modal' }));
                selectedReports = [];
                updateBulkActionButton();
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showNotification('error', response.error || 'Bulk action failed');
        })
        .always(function() {
            $('#executeActionBtn').prop('disabled', false).html('Execute Action');
        });
    });
    
    // Refresh table
    $('#refreshBtn').click(function() {
        refreshTable();
    });
    
    function refreshTable() {
        $('#refreshBtn').find('i').addClass('animate-spin');
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        
        $.get(url.toString())
        .done(function(response) {
            $('#reportsTable').html(response.html);
        })
        .always(function() {
            $('#refreshBtn').find('i').removeClass('animate-spin');
        });
    }
    
    // Handle quick actions
    $(document).on('click', '.action-duplicate', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        
        $(this).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.post(url, { _token: '{{ csrf_token() }}' })
        .done(function(response) {
            if (response.success) {
                showNotification('success', response.message);
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showNotification('error', response.error || 'Duplication failed');
        })
        .always(function() {
            location.reload();
        });
    });
    
    // Handle delete action
    $(document).on('click', '.action-delete', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this report?')) {
            return;
        }
        
        const url = $(this).data('url');
        
        $.ajax({
            url: url,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' }
        })
        .done(function(response) {
            if (response.success) {
                showNotification('success', response.message);
                refreshTable();
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showNotification('error', response.error || 'Delete failed');
        });
    });
    
    // Auto-submit filter form on change
    $('#filterForm select').change(function() {
        $('#filterForm').submit();
    });
    
    function showNotification(type, message) {
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
        
        const notification = $(`
            <div class="fixed top-4 right-4 z-50 flex items-center p-4 ${bgColor} text-white rounded-xl shadow-2xl transform transition-all duration-500 translate-x-full">
                <i class="fas fa-${icon} mr-3"></i>
                <span class="font-medium">${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="$(this).parent().remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Animate in
        setTimeout(() => notification.removeClass('translate-x-full'), 100);
        
        // Auto remove
        setTimeout(() => {
            notification.addClass('translate-x-full');
            setTimeout(() => notification.remove(), 500);
        }, 5000);
    }
});
</script>
@endpush
