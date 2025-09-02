@if($reports->count() > 0)
<div class="overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden lg:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-blue-500 to-purple-600">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <input type="checkbox" 
                               id="selectAll"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Report Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Data Source</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Visibility</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Usage</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Last Run</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($reports as $index => $report)
                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition-colors duration-200">
                    <td class="px-6 py-4">
                        <input type="checkbox" 
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 checkbox-selection" 
                               value="{{ $report->id }}">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.custom-reports.show', $report) }}" 
                                       class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                        {{ $report->name }}
                                    </a>
                                    @if(!$report->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                @if($report->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($report->description, 80) }}</p>
                                @endif
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-user"></i>
                                        <span>{{ $report->creator->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-calendar"></i>
                                        <span>{{ $report->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $dataSources = (new \App\Models\CustomReport())->getAvailableDataSources();
                            $sourceInfo = $dataSources[$report->data_source] ?? null;
                        @endphp
                        @if($sourceInfo)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-blue-100 to-purple-100 text-blue-800">
                                {{ $sourceInfo['label'] }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $report->data_source }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $categories = \App\Models\CustomReport::getCategories();
                            $categoryLabel = $categories[$report->category] ?? $report->category;
                            $badgeClass = match($report->category) {
                                'general' => 'bg-blue-100 text-blue-800',
                                'contacts' => 'bg-green-100 text-green-800',
                                'campaigns' => 'bg-yellow-100 text-yellow-800',
                                'revenue' => 'bg-cyan-100 text-cyan-800',
                                'system' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                            {{ $categoryLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $visibilityClass = match($report->visibility) {
                                'public' => 'bg-green-100 text-green-800',
                                'shared' => 'bg-yellow-100 text-yellow-800',
                                'private' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $visibilityIcon = match($report->visibility) {
                                'public' => 'fas fa-globe',
                                'shared' => 'fas fa-users',
                                'private' => 'fas fa-lock',
                                default => 'fas fa-question'
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $visibilityClass }}">
                            <i class="{{ $visibilityIcon }} mr-1"></i>{{ ucfirst($report->visibility) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            <div class="font-medium">{{ number_format($report->run_count) }} runs</div>
                            @if($report->is_scheduled)
                                <div class="flex items-center gap-1 text-xs text-blue-600 mt-1">
                                    <i class="fas fa-clock"></i>
                                    <span>Scheduled</span>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($report->last_run_at)
                            <div class="text-sm text-gray-900">
                                <div>{{ $report->last_run_at->diffForHumans() }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $report->last_run_at->format('M d, Y H:i') }}</div>
                            </div>
                        @else
                            <span class="text-sm text-gray-500">Never</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-cog mr-1"></i>
                                <i class="fas fa-chevron-down ml-1 text-xs" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                                 style="display: none;">
                                <a href="{{ route('admin.custom-reports.show', $report) }}" 
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-eye mr-3 text-gray-400"></i>View Report
                                </a>
                                @if($report->canUserAccess(Auth::id()))
                                    <button type="button" 
                                            class="w-full flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 text-left action-execute" 
                                            data-url="{{ route('admin.custom-reports.execute', $report) }}">
                                        <i class="fas fa-play mr-3 text-gray-400"></i>Execute Now
                                    </button>
                                @endif
                                <hr class="my-2 border-gray-100">
                                @if(Auth::user()->can('update', $report))
                                    <a href="{{ route('admin.custom-reports.edit', $report) }}" 
                                       class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                        <i class="fas fa-edit mr-3 text-gray-400"></i>Edit
                                    </a>
                                @endif
                                @if($report->canUserAccess(Auth::id()))
                                    <button type="button"
                                            class="w-full flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 text-left action-duplicate" 
                                            onclick="window.location.href='{{ route('admin.custom-reports.duplicate', $report) }}'">
                                        <i class="fas fa-copy mr-3 text-gray-400"></i>Duplicate
                                    </button>
                                    <a href="{{ route('admin.custom-reports.export', $report) }}?format=csv" 
                                       class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                        <i class="fas fa-download mr-3 text-gray-400"></i>Export CSV
                                    </a>
                                @endif
                                <hr class="my-2 border-gray-100">
                                @if(Auth::user()->can('delete', $report))
                                    <button type="button" 
                                            class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200 text-left action-delete" 
                                            data-url="{{ route('admin.custom-reports.destroy', $report) }}">
                                        <i class="fas fa-trash mr-3"></i>Delete
                                    </button>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="block lg:hidden space-y-4">
        @foreach($reports as $report)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="checkbox" 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 checkbox-selection" 
                                       value="{{ $report->id }}">
                                <a href="{{ route('admin.custom-reports.show', $report) }}" 
                                   class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                    {{ $report->name }}
                                </a>
                                @if(!$report->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            @if($report->description)
                                <p class="text-sm text-gray-600 mb-3">{{ Str::limit($report->description, 120) }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        @php
                            $dataSources = (new \App\Models\CustomReport())->getAvailableDataSources();
                            $sourceInfo = $dataSources[$report->data_source] ?? null;
                        @endphp
                        @if($sourceInfo)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-blue-100 to-purple-100 text-blue-800">
                                {{ $sourceInfo['label'] }}
                            </span>
                        @endif

                        @php
                            $categories = \App\Models\CustomReport::getCategories();
                            $categoryLabel = $categories[$report->category] ?? $report->category;
                            $badgeClass = match($report->category) {
                                'general' => 'bg-blue-100 text-blue-800',
                                'contacts' => 'bg-green-100 text-green-800',
                                'campaigns' => 'bg-yellow-100 text-yellow-800',
                                'revenue' => 'bg-cyan-100 text-cyan-800',
                                'system' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                            {{ $categoryLabel }}
                        </span>

                        @php
                            $visibilityClass = match($report->visibility) {
                                'public' => 'bg-green-100 text-green-800',
                                'shared' => 'bg-yellow-100 text-yellow-800',
                                'private' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $visibilityIcon = match($report->visibility) {
                                'public' => 'fas fa-globe',
                                'shared' => 'fas fa-users',
                                'private' => 'fas fa-lock',
                                default => 'fas fa-question'
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $visibilityClass }}">
                            <i class="{{ $visibilityIcon }} mr-1"></i>{{ ucfirst($report->visibility) }}
                        </span>
                    </div>

                    <!-- Stats & Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-user"></i>
                                <span>{{ $report->creator->name }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-play"></i>
                                <span>{{ number_format($report->run_count) }} runs</span>
                            </div>
                        </div>
                        
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-cog"></i>
                            </button>
                            <!-- Same dropdown content as desktop version -->
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
                                 style="display: none;">
                                <!-- Same dropdown items as above -->
                                <a href="{{ route('admin.custom-reports.show', $report) }}" 
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-eye mr-3 text-gray-400"></i>View Report
                                </a>
                                @if($report->canUserAccess(Auth::id()))
                                    <button type="button" 
                                            class="w-full flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200 text-left action-execute" 
                                            data-url="{{ route('admin.custom-reports.execute', $report) }}">
                                        <i class="fas fa-play mr-3 text-gray-400"></i>Execute Now
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Pagination -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
    <div class="text-sm text-gray-600">
        Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} results
    </div>
    <div>
        {{ $reports->appends(request()->query())->links() }}
    </div>
</div>
@else
<div class="text-center py-16">
    <div class="flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mx-auto mb-6">
        <i class="fas fa-chart-bar text-blue-500 text-3xl"></i>
    </div>
    <h5 class="text-xl font-semibold text-gray-900 mb-2">No custom reports found</h5>
    <p class="text-gray-600 mb-6">Create your first custom report to get started with advanced analytics.</p>
    <a href="{{ route('admin.custom-reports.create') }}" 
       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
        <i class="fas fa-plus mr-2"></i>Create Report
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
        const originalHtml = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Executing...');
        
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
            btn.html(originalHtml);
        });
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
