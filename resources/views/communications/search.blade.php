@extends('layouts.app')

@section('title', 'Search Communications')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                Search Communications
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Search across all messages from email, SMS, and WhatsApp
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('communications.index') }}" 
               class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Inbox
            </a>
        </div>
    </div>

    <!-- Advanced Search Form -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                Advanced Search
            </h3>
            
            <form method="GET" action="{{ route('communications.search') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Search Query -->
                    <div class="lg:col-span-2">
                        <label for="query" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search Query</label>
                        <input type="text" name="query" id="query" value="{{ request('query') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="Search messages, subjects, contact names...">
                    </div>

                    <!-- Channel Filter -->
                    <div>
                        <label for="channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Channel</label>
                        <select id="channel" name="channel" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Channels</option>
                            <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>SMS</option>
                            <option value="whatsapp" {{ request('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                        </select>
                    </div>

                    <!-- Direction Filter -->
                    <div>
                        <label for="direction" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Direction</label>
                        <select id="direction" name="direction" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Messages</option>
                            <option value="inbound" {{ request('direction') == 'inbound' ? 'selected' : '' }}>Received</option>
                            <option value="outbound" {{ request('direction') == 'outbound' ? 'selected' : '' }}>Sent</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Statuses</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>

                    <!-- Contact Filter -->
                    <div>
                        <label for="contact" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact</label>
                        <input type="text" name="contact" id="contact" value="{{ request('contact') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="Contact name or email">
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Date</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Sort Options -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort By</label>
                        <select id="sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Most Relevant</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        @if(isset($results))
                            Found {{ $results->total() }} result{{ $results->total() != 1 ? 's' : '' }}
                        @endif
                    </div>
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('communications.search') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Clear All
                        </a>
                        
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    @if(isset($results))
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                    Search Results
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    {{ $results->total() }} result{{ $results->total() != 1 ? 's' : '' }} found
                    @if(request('query'))
                        for "{{ request('query') }}"
                    @endif
                </p>
            </div>

            @if($results->count() > 0)
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($results as $message)
                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div class="flex items-center space-x-4">
                                <!-- Channel Icon -->
                                <div class="flex-shrink-0">
                                    <span class="h-10 w-10 rounded-full flex items-center justify-center
                                        @if($message['channel'] == 'email') bg-blue-100 dark:bg-blue-800
                                        @elseif($message['channel'] == 'sms') bg-yellow-100 dark:bg-yellow-800
                                        @elseif($message['channel'] == 'whatsapp') bg-green-100 dark:bg-green-800
                                        @else bg-gray-100 dark:bg-gray-800 @endif">
                                        @if($message['channel'] == 'email')
                                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        @elseif($message['channel'] == 'sms')
                                            <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        @elseif($message['channel'] == 'whatsapp')
                                            <svg class="h-5 w-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $message['contact_name'] ?? 'Unknown Contact' }}
                                            </p>
                                            
                                            <!-- Direction Badge -->
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                @if($message['direction'] == 'inbound') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200
                                                @else bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200 @endif">
                                                {{ $message['direction'] == 'inbound' ? 'Received' : 'Sent' }}
                                            </span>

                                            <!-- Status Badge -->
                                            @if($message['status'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    @if($message['status'] == 'delivered') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200
                                                    @elseif($message['status'] == 'failed') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200
                                                    @elseif($message['status'] == 'sent') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 @endif">
                                                    {{ ucfirst($message['status']) }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <time class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($message['created_at'])->format('M j, Y g:i A') }}
                                            </time>
                                            
                                            <a href="{{ route('communications.show', $message['contact_id']) }}" 
                                               class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        @if($message['subject'] && $message['channel'] == 'email')
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $message['subject'] }}
                                            </p>
                                        @endif
                                        
                                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                            {!! $message['highlighted_content'] ?? Str::limit($message['content'], 150) !!}
                                        </p>
                                    </div>

                                    @if($message['contact_email'] || $message['contact_phone'])
                                        <div class="mt-2 flex items-center space-x-4 text-xs text-gray-400 dark:text-gray-500">
                                            @if($message['contact_email'])
                                                <span>{{ $message['contact_email'] }}</span>
                                            @endif
                                            @if($message['contact_phone'])
                                                <span>{{ $message['contact_phone'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $results->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No results found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Try adjusting your search criteria or search terms.
                    </p>
                </div>
            @endif
        </div>
    @else
        <!-- Search Tips -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4">
                    Search Tips
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                            Search Examples
                        </h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• "meeting tomorrow" - Find messages about meetings</li>
                            <li>• "john@example.com" - Find messages from/to specific email</li>
                            <li>• "invoice" - Find all messages mentioning invoices</li>
                            <li>• "urgent" - Find urgent communications</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                            Advanced Features
                        </h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Use date filters for specific time periods</li>
                            <li>• Filter by channel (Email, SMS, WhatsApp)</li>
                            <li>• Search by contact name or information</li>
                            <li>• Filter by message status and direction</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
// Auto-submit search when Enter is pressed in search field
document.getElementById('query').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        this.form.submit();
    }
});

// Highlight search terms in results
@if(request('query'))
    document.addEventListener('DOMContentLoaded', function() {
        const searchTerm = '{{ request('query') }}';
        if (searchTerm) {
            highlightSearchTerms(searchTerm);
        }
    });
    
    function highlightSearchTerms(term) {
        const elements = document.querySelectorAll('.line-clamp-2');
        elements.forEach(element => {
            const text = element.textContent;
            const regex = new RegExp(`(${term})`, 'gi');
            const highlightedText = text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800">$1</mark>');
            element.innerHTML = highlightedText;
        });
    }
@endif
</script>
@endpush
@endsection