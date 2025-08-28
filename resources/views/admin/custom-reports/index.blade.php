@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>Custom Reports
                    </h1>
                    <p class="text-muted mb-0">Create and manage custom reports with advanced filtering and visualization</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkActionModal" id="bulkActionBtn" style="display: none;">
                        <i class="fas fa-tasks me-1"></i>Bulk Actions
                    </button>
                    <a href="{{ route('admin.custom-reports.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>New Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="col-12">
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Reports</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_reports'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        My Reports</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['my_reports'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-chart fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Public Reports</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['public_reports'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-share-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Active Reports</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_reports'] ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-1"></i>Filters & Search
                    </h6>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="collapse" id="filtersCollapse">
                    <div class="card-body">
                        <form method="GET" id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Report name or description..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach(\App\Models\CustomReport::getCategories() as $key => $label)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Data Source</label>
                                <select name="data_source" class="form-select">
                                    <option value="">All Sources</option>
                                    @php $dataSources = (new \App\Models\CustomReport())->getAvailableDataSources(); @endphp
                                    @foreach($dataSources as $key => $info)
                                        <option value="{{ $key }}" {{ request('data_source') == $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="">All Visibility</option>
                                    <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Private</option>
                                    <option value="shared" {{ request('visibility') == 'shared' ? 'selected' : '' }}>Shared</option>
                                    <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Public</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sort By</label>
                                <select name="sort_by" class="form-select">
                                    <option value="updated_at" {{ request('sort_by', 'updated_at') == 'updated_at' ? 'selected' : '' }}>Updated</option>
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Created</option>
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                    <option value="run_count" {{ request('sort_by') == 'run_count' ? 'selected' : '' }}>Usage</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Order</label>
                                <select name="sort_direction" class="form-select">
                                    <option value="desc" {{ request('sort_direction', 'desc') == 'desc' ? 'selected' : '' }}>DESC</option>
                                    <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>ASC</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Apply Filters
                                </button>
                                <a href="{{ route('admin.custom-reports.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear All
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-1"></i>Custom Reports ({{ $reports->total() }})
                    </h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                            <i class="fas fa-check-square me-1"></i>Select All
                        </button>
                        <button class="btn btn-sm btn-outline-info" id="refreshBtn">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="reportsTable">
                        @include('admin.custom-reports.table', ['reports' => $reports])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionForm">
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select" required>
                            <option value="">Select action...</option>
                            <option value="delete">Delete Reports</option>
                            <option value="activate">Activate Reports</option>
                            <option value="deactivate">Deactivate Reports</option>
                            <option value="make_private">Make Private</option>
                            <option value="make_shared">Make Shared</option>
                        </select>
                    </div>
                    <div id="selectedCount" class="text-muted mb-3"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="executeActionBtn">Execute Action</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-category {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

.report-actions {
    white-space: nowrap;
}

.data-source-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.visibility-badge {
    font-size: 0.75em;
}

.run-stats {
    font-size: 0.85em;
    color: #6c757d;
}

.checkbox-selection:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.table tbody tr:hover {
    background-color: #f8f9fc;
}

.card {
    border: 1px solid #e3e6f0;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>
@endpush

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
            $('#bulkActionBtn').show();
            $('#selectedCount').text(`${selectedReports.length} report(s) selected`);
        } else {
            $('#bulkActionBtn').hide();
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
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');
        
        $.post('{{ route("admin.custom-reports.bulk-action") }}', {
            _token: '{{ csrf_token() }}',
            action: action,
            report_ids: selectedReports
        })
        .done(function(response) {
            if (response.success) {
                showNotification('success', response.message);
                refreshTable();
                $('#bulkActionModal').modal('hide');
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
        $('#refreshBtn').find('i').addClass('fa-spin');
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        
        $.get(url.toString())
        .done(function(response) {
            $('#reportsTable').html(response.html);
        })
        .always(function() {
            $('#refreshBtn').find('i').removeClass('fa-spin');
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
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.alert('close');
        }, 5000);
    }
});
</script>
@endpush
