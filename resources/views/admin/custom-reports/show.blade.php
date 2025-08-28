@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>{{ $customReport->name }}
                    </h1>
                    <p class="text-muted mb-0">{{ $customReport->description ?? 'Custom report details and results' }}</p>
                </div>
                <div class="d-flex gap-2">
                    @if($customReport->canUserAccess(Auth::id()) && Auth::user()->can('update', $customReport))
                        <a href="{{ route('admin.custom-reports.edit', $customReport) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Edit Report
                        </a>
                    @endif
                    <button type="button" class="btn btn-info" id="executeBtn">
                        <i class="fas fa-play me-1"></i>Execute Report
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.custom-reports.index') }}">
                                <i class="fas fa-arrow-left me-2"></i>Back to Reports
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            @if($customReport->canUserAccess(Auth::id()))
                                <li><button type="button" class="dropdown-item" onclick="duplicateReport()">
                                    <i class="fas fa-copy me-2"></i>Duplicate Report
                                </button></li>
                                <li><a class="dropdown-item" href="{{ route('admin.custom-reports.export', $customReport) }}?format=csv">
                                    <i class="fas fa-download me-2"></i>Export as CSV
                                </a></li>
                            @endif
                            @if(Auth::user()->can('delete', $customReport))
                                <li><hr class="dropdown-divider"></li>
                                <li><button type="button" class="dropdown-item text-danger" onclick="deleteReport()">
                                    <i class="fas fa-trash me-2"></i>Delete Report
                                </button></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Information Cards -->
        <div class="col-12">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Category</div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        @php $categories = \App\Models\CustomReport::getCategories(); @endphp
                                        {{ $categories[$customReport->category] ?? $customReport->category }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-tag fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Data Source</div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        @php $dataSources = $customReport->getAvailableDataSources(); @endphp
                                        {{ $dataSources[$customReport->data_source]['label'] ?? $customReport->data_source }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-database fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Runs</div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">{{ number_format($customReport->run_count) }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-play fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Last Run</div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        {{ $customReport->last_run_at ? $customReport->last_run_at->diffForHumans() : 'Never' }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Configuration -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog me-2"></i>Report Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Visibility:</strong></div>
                        <div class="col-sm-8">
                            @php
                                $visibilityClass = match($customReport->visibility) {
                                    'public' => 'bg-success',
                                    'shared' => 'bg-warning text-dark',
                                    'private' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                                $visibilityIcon = match($customReport->visibility) {
                                    'public' => 'fas fa-globe',
                                    'shared' => 'fas fa-users', 
                                    'private' => 'fas fa-lock',
                                    default => 'fas fa-question'
                                };
                            @endphp
                            <span class="badge {{ $visibilityClass }}">
                                <i class="{{ $visibilityIcon }} me-1"></i>{{ ucfirst($customReport->visibility) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Status:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge {{ $customReport->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $customReport->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Columns:</strong></div>
                        <div class="col-sm-8">{{ count($customReport->columns) }} selected</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Filters:</strong></div>
                        <div class="col-sm-8">{{ count($customReport->filters ?? []) }} applied</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Created:</strong></div>
                        <div class="col-sm-8">{{ $customReport->created_at->format('M d, Y H:i') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Creator:</strong></div>
                        <div class="col-sm-8">{{ $customReport->creator->name }}</div>
                    </div>

                    @if($customReport->is_scheduled)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Scheduled:</strong></div>
                            <div class="col-sm-8">
                                <span class="badge bg-info">
                                    <i class="fas fa-clock me-1"></i>Yes
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Selected Columns -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-columns me-2"></i>Selected Columns
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($customReport->columns as $column)
                            <div class="col-6 mb-2">
                                <span class="badge bg-light text-dark border">
                                    {{ str_replace('_', ' ', ucwords($column, '_')) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Applied Filters -->
            @if(!empty($customReport->filters))
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-filter me-2"></i>Applied Filters
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($customReport->filters as $filter)
                            <div class="mb-2 p-2 bg-light rounded">
                                <small>
                                    <strong>{{ str_replace('_', ' ', ucwords($filter['column'], '_')) }}</strong> 
                                    <span class="text-muted">{{ $filter['operator'] }}</span> 
                                    <em class="text-primary">{{ $filter['value'] }}</em>
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Report Results -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>Report Results
                    </h6>
                    <div>
                        <button class="btn btn-sm btn-outline-info" id="refreshResults">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        @if($customReport->export_format === 'both' || $customReport->export_format === 'chart')
                            <button class="btn btn-sm btn-outline-primary" id="toggleChart">
                                <i class="fas fa-chart-bar"></i> Toggle Chart
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div id="reportResults">
                        @if(isset($reportData))
                            @include('admin.custom-reports.results', ['data' => $reportData, 'report' => $customReport])
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                                <h5>Report not executed yet</h5>
                                <p class="text-muted">Click "Execute Report" to run this report and view results.</p>
                                <button type="button" class="btn btn-primary" id="initialExecute">
                                    <i class="fas fa-play me-1"></i>Execute Report
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            @if(($customReport->export_format === 'both' || $customReport->export_format === 'chart') && !empty($customReport->chart_config))
                <div class="card shadow mb-4" id="chartSection" style="display: none;">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-bar me-2"></i>Chart Visualization
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="reportChart" width="400" height="200"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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

.card {
    border: 1px solid #e3e6f0;
}

.table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.result-stats {
    font-size: 0.9em;
    color: #6c757d;
}

.report-metadata {
    background: #f8f9fc;
    border-radius: 0.35rem;
    padding: 1rem;
    border: 1px solid #e3e6f0;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let reportChart = null;

$(document).ready(function() {
    // Execute report buttons
    $('#executeBtn, #initialExecute').click(function() {
        executeReport();
    });

    // Refresh results
    $('#refreshResults').click(function() {
        executeReport();
    });

    // Toggle chart
    $('#toggleChart').click(function() {
        $('#chartSection').toggle();
        if ($('#chartSection').is(':visible') && !reportChart) {
            loadChart();
        }
    });
});

function executeReport() {
    const btn = $('#executeBtn, #initialExecute');
    btn.prop('disabled', true);
    btn.find('i').addClass('fa-spin');

    $.post('{{ route("admin.custom-reports.execute", $customReport) }}', {
        _token: '{{ csrf_token() }}',
        limit: 100
    })
    .done(function(response) {
        if (response.success) {
            renderResults(response.data, response.metadata);
            showNotification('success', 'Report executed successfully!');
            
            // Update run count display
            updateRunCount();
            
            // Load chart if available
            if ($('#chartSection').length && $('#chartSection').is(':visible')) {
                loadChart();
            }
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        showNotification('error', response.error || 'Report execution failed');
        $('#reportResults').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${response.error || 'Failed to execute report'}
            </div>
        `);
    })
    .always(function() {
        btn.prop('disabled', false);
        btn.find('i').removeClass('fa-spin');
    });
}

function renderResults(data, metadata) {
    if (!data || data.length === 0) {
        $('#reportResults').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No data found matching the report criteria.
            </div>
        `);
        return;
    }

    const columns = Object.keys(data[0]);
    let html = `
        <div class="result-stats mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Found <strong>${metadata.total_rows}</strong> rows with <strong>${metadata.filters_applied}</strong> filters applied
            ${metadata.last_run ? ` • Last run: ${new Date(metadata.last_run).toLocaleString()}` : ''}
            ${metadata.run_count ? ` • Total runs: ${metadata.run_count}` : ''}
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        ${columns.map(col => `<th>${col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${data.slice(0, 50).map(row => `
                        <tr>
                            ${columns.map(col => `<td>${row[col] !== null ? row[col] : '<span class="text-muted">-</span>'}</td>`).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    if (data.length > 50) {
        html += `
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                Showing first 50 rows of ${data.length} total results. 
                <a href="{{ route('admin.custom-reports.export', $customReport) }}?format=csv" class="alert-link">
                    Export all data as CSV
                </a> to see complete results.
            </div>
        `;
    }
    
    $('#reportResults').html(html);
}

function loadChart() {
    $.get('{{ route("admin.custom-reports.chart-data", $customReport) }}')
    .done(function(response) {
        if (response.success && response.data && response.data.length > 0) {
            renderChart(response.data, response.config);
        } else {
            $('#chartSection .card-body').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No chart data available for this report.
                </div>
            `);
        }
    })
    .fail(function() {
        $('#chartSection .card-body').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Failed to load chart data.
            </div>
        `);
    });
}

function renderChart(data, config) {
    const ctx = document.getElementById('reportChart').getContext('2d');
    
    if (reportChart) {
        reportChart.destroy();
    }
    
    const chartConfig = {
        type: config.type || 'bar',
        data: {
            labels: data.map(item => item.x || item.label),
            datasets: [{
                label: 'Data',
                data: data.map(item => item.y || item.value),
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(133, 135, 150, 0.8)'
                ],
                borderColor: [
                    'rgba(78, 115, 223, 1)',
                    'rgba(28, 200, 138, 1)',
                    'rgba(54, 185, 204, 1)',
                    'rgba(246, 194, 62, 1)',
                    'rgba(231, 74, 59, 1)',
                    'rgba(133, 135, 150, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: '{{ $customReport->name }}'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };
    
    reportChart = new Chart(ctx, chartConfig);
}

function updateRunCount() {
    // Update the run count display in the stats card
    location.reload(); // Simple refresh - in production you might want to update via AJAX
}

function duplicateReport() {
    if (!confirm('Are you sure you want to duplicate this report?')) {
        return;
    }
    
    $.post('{{ route("admin.custom-reports.duplicate", $customReport) }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            showNotification('success', response.message);
            if (response.redirect) {
                setTimeout(() => {
                    window.location.href = response.redirect;
                }, 1000);
            }
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        showNotification('error', response.error || 'Duplication failed');
    });
}

function deleteReport() {
    if (!confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: '{{ route("admin.custom-reports.destroy", $customReport) }}',
        method: 'DELETE',
        data: { _token: '{{ csrf_token() }}' }
    })
    .done(function(response) {
        if (response.success) {
            showNotification('success', response.message);
            setTimeout(() => {
                window.location.href = '{{ route("admin.custom-reports.index") }}';
            }, 1000);
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        showNotification('error', response.error || 'Delete failed');
    });
}

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
</script>
@endpush
