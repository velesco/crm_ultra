{{-- Logs Table --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($logs as $log)
                <tr class="log-row hover:bg-gray-50 transition-colors duration-200 {{ $log->level === 'error' || $log->level === 'critical' ? 'border-l-4 border-red-500' : ($log->level === 'warning' ? 'border-l-4 border-yellow-500' : ($log->level === 'info' ? 'border-l-4 border-blue-500' : '')) }}" 
                    data-level="{{ $log->level }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $log->level_badge_class }}">
                            {{ ucfirst($log->level) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <i class="{{ $log->category_icon }} text-gray-400 mr-2"></i>
                            <span class="text-sm text-gray-900 capitalize">{{ $log->category }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            <div class="font-medium">{{ $log->occurred_at->format('M d, Y') }}</div>
                            <div class="text-gray-500">{{ $log->occurred_at->format('H:i:s') }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->user)
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white text-xs font-medium">{{ substr($log->user->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $log->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $log->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-sm text-gray-500">System</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-block bg-gray-100 text-gray-800 text-xs font-mono px-2 py-1 rounded">{{ $log->action }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            {{ Str::limit($log->message, 100) }}
                            @if($log->description)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ Str::limit($log->description, 80) }}
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.system-logs.show', $log) }}" 
                           class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                           title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No logs found</h3>
                            <p class="mt-1 text-sm text-gray-500">No logs match your current search criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($logs->hasPages())
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                @if($logs->onFirstPage())
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                        Previous
                    </span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                @endif

                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                @else
                    <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                        Next
                    </span>
                @endif
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $logs->firstItem() }}</span> to <span class="font-medium">{{ $logs->lastItem() }}</span> of <span class="font-medium">{{ $logs->total() }}</span> results
                    </p>
                </div>
                <div>
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endif

<script>
// Add hover effects and color coding
$(document).ready(function() {
    // Color-code rows based on log level - already done with Tailwind classes in the template
    
    // Add smooth hover transitions (already handled by Tailwind hover: classes)
    
    // Optional: Add click-to-expand functionality for long messages
    $('.log-row').on('click', '.text-sm.text-gray-900', function(e) {
        e.preventDefault();
        const $message = $(this);
        const fullText = $message.attr('data-full-text');
        
        if (fullText && fullText !== $message.text()) {
            $message.text(fullText);
        }
    });
    
    // Store full text for expansion (if needed)
    $('.log-row .text-sm.text-gray-900').each(function() {
        const $this = $(this);
        const fullText = $this.text();
        if (fullText.length > 100) {
            $this.attr('data-full-text', fullText);
        }
    });
});
</script>
