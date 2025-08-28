@if($reports->count() > 0)
<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th style="width: 40px;">
                    <input type="checkbox" class="form-check-input" id="selectAll">
                </th>
                <th>Report Details</th>
                <th>Data Source</th>
                <th>Category</th>
                <th>Visibility</th>
                <th>Usage</th>
                <th>Last Run</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reports as $report)
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input checkbox-selection" value="{{ $report->id }}">
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0">
                                <a href="{{ route('admin.custom-reports.show', $report) }}" class="text-decoration-none">
                                    {{ $report->name }}
                                </a>
                                @if(!$report->is_active)
                                    <span class="badge bg-secondary ms-1">Inactive</span>
                                @endif
                            </h6>
                            @if($report->description)
                                <small class="text-muted">{{ Str::limit($report->description, 80) }}</small>
                            @endif
                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="fas fa-user fa-xs me-1"></i>{{ $report->creator->name }}
                                    <i class="fas fa-calendar fa-xs ms-2 me-1"></i>{{ $report->created_at->format('M d, Y') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    @php
                        $dataSources = (new \App\Models\CustomReport())->getAvailableDataSources();
                        $sourceInfo = $dataSources[$report->data_source] ?? null;
                    @endphp
                    @if($sourceInfo)
                        <span class="badge data-source-badge">
                            {{ $sourceInfo['label'] }}
                        </span>
                    @else
                        <span class="badge bg-secondary">{{ $report->data_source }}</span>
                    @endif
                </td>
                <td>
                    @php
                        $categories = \App\Models\CustomReport::getCategories();
                        $categoryLabel = $categories[$report->category] ?? $report->category;
                        $badgeClass = match($report->category) {
                            'general' => 'bg-primary',
                            'contacts' => 'bg-success',
                            'campaigns' => 'bg-warning',
                            'revenue' => 'bg-info',
                            'system' => 'bg-dark',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} badge-category">
                        {{ $categoryLabel }}
                    </span>
                </td>
                <td>
                    @php
                        $visibilityClass = match($report->visibility) {
                            'public' => 'bg-success',
                            'shared' => 'bg-warning',
                            'private' => 'bg-secondary',
                            default => 'bg-secondary'
                        };
                        $visibilityIcon = match($report->visibility) {
                            'public' => 'fas fa-globe',
                            'shared' => 'fas fa-users',
                            'private' => 'fas fa-lock',
                            default => 'fas fa-question'
                        };
                    @endphp
                    <span class="badge {{ $visibilityClass }} visibility-badge">
                        <i class="{{ $visibilityIcon }} me-1"></i>{{ ucfirst($report->visibility) }}
                    </span>
                </td>
                <td>
                    <div class="run-stats">
                        <strong>{{ number_format($report->run_count) }}</strong> runs
                        @if($report->is_scheduled)
                            <br><i class="fas fa-clock fa-xs text-primary"></i> Scheduled
                        @endif
                    </div>
                </td>
                <td>
                    @if($report->last_run_at)
                        <div class="run-stats">
                            {{ $report->last_run_at->diffForHumans() }}
                            <br><small class="text-muted">{{ $report->last_run_at->format('M d, Y H:i') }}</small>
                        </div>
                    @else
                        <span class="text-muted">Never</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group report-actions" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.custom-reports.show', $report) }}">
                                    <i class="fas fa-eye me-2"></i>View Report
                                </a>
                            </li>
                            @if($report->canUserAccess(Auth::id()))
                                <li>
                                    <button type="button" class="dropdown-item action-execute" data-url="{{ route('admin.custom-reports.execute', $report) }}">
                                        <i class="fas fa-play me-2"></i>Execute Now
                                    </button>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            @if(Auth::user()->can('update', $report))
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.custom-reports.edit', $report) }}">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                </li>
                            @endif
                            @if($report->canUserAccess(Auth::id()))
                                <li>
                                    <a class="dropdown-item action-duplicate" href="{{ route('admin.custom-reports.duplicate', $report) }}">
                                        <i class="fas fa-copy me-2"></i>Duplicate
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.custom-reports.export', $report) }}?format=csv">
                                        <i class="fas fa-download me-2"></i>Export CSV
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            @if(Auth::user()->can('delete', $report))
                                <li>
                                    <button type="button" class="dropdown-item text-danger action-delete" data-url="{{ route('admin.custom-reports.destroy', $report) }}">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="card-footer d-flex justify-content-between align-items-center">
    <div class="text-muted">
        Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} results
    </div>
    <div>
        {{ $reports->links() }}
    </div>
</div>
@else
<div class="text-center py-5">
    <div class="mb-3">
        <i class="fas fa-chart-bar fa-3x text-muted"></i>
    </div>
    <h5 class="text-muted">No custom reports found</h5>
    <p class="text-muted mb-4">Create your first custom report to get started with advanced analytics.</p>
    <a href="{{ route('admin.custom-reports.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Create Report
    </a>
</div>
@endif

<script>
$(document).ready(function() {
    // Handle execute action
    $('.action-execute').click(function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const btn = $(this);
        
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Executing...');
        
        $.post(url, { _token: '{{ csrf_token() }}' })
        .done(function(response) {
            if (response.success) {
                showNotification('success', 'Report executed successfully!');
                // Optionally redirect to show page
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            showNotification('error', response.error || 'Execution failed');
        })
        .always(function() {
            btn.html('<i class="fas fa-play me-2"></i>Execute Now');
        });
    });
});
</script>
