@extends('layouts.app')

@section('title', 'Email Campaigns')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight dark:text-white">
                Email Campaigns
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage and track your email marketing campaigns
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <a href="{{ route('email.campaigns.create') }}" class="ml-3 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                New Campaign
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="bg-white overflow-hidden shadow rounded-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center dark:bg-blue-900">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 110 2h-1l-.867 12.142A2 2 0 0118.138 18H5.862a2 2 0 01-1.995-1.858L3 4H2a1 1 0 110-2h4z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Total</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $stats['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center dark:bg-yellow-900">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Draft</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $stats['draft'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center dark:bg-purple-900">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Scheduled</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $stats['scheduled'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-md flex items-center justify-center dark:bg-orange-900">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Active</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $stats['active'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg dark:bg-gray-800">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center dark:bg-green-900">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate dark:text-gray-400">Sent</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ $stats['sent'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 dark:bg-gray-800">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                </select>
            </div>
            
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search campaigns..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Campaigns Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden dark:bg-gray-800">
        <div class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <div class="bg-gray-50 dark:bg-gray-700">
                <div class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 grid grid-cols-12 gap-4">
                    <div class="col-span-3">Campaign</div>
                    <div class="col-span-2">Status</div>
                    <div class="col-span-2">Recipients</div>
                    <div class="col-span-2">Performance</div>
                    <div class="col-span-2">Date</div>
                    <div class="col-span-1">Actions</div>
                </div>
            </div>
            
            <div class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @forelse($campaigns as $campaign)
                <div class="px-6 py-4 whitespace-nowrap grid grid-cols-12 gap-4 items-center">
                    <!-- Campaign Info -->
                    <div class="col-span-3">
                        <div class="flex flex-col">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                <a href="{{ route('email.campaigns.show', $campaign) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $campaign->name }}
                                </a>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate" style="max-width: 250px;" title="{{ $campaign->subject }}">
                                {{ $campaign->subject }}
                            </div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                by {{ $campaign->creator->name }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-span-2">
                        @php
                            $statusClasses = [
                                'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                'active' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                'sent' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                'paused' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$campaign->status] ?? $statusClasses['draft'] }}">
                            {{ ucfirst($campaign->status) }}
                        </span>
                        @if($campaign->scheduled_at && $campaign->status === 'scheduled')
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $campaign->scheduled_at->format('M j, Y H:i') }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Recipients -->
                    <div class="col-span-2">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ number_format($campaign->total_recipients) }}
                        </div>
                        @if($campaign->sent_count > 0)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ number_format($campaign->sent_count) }} sent
                            </div>
                        @endif
                    </div>
                    
                    <!-- Performance -->
                    <div class="col-span-2">
                        @if($campaign->sent_count > 0)
                            <div class="text-xs space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Opens:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $campaign->open_rate }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Clicks:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $campaign->click_rate }}%</span>
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500">No data</span>
                        @endif
                    </div>
                    
                    <!-- Date -->
                    <div class="col-span-2">
                        <div class="text-sm text-gray-900 dark:text-white">
                            {{ $campaign->created_at->format('M j, Y') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $campaign->created_at->format('H:i') }}
                        </div>
                        @if($campaign->sent_at)
                            <div class="text-xs text-green-600 dark:text-green-400">
                                Sent: {{ $campaign->sent_at->format('M j H:i') }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Actions -->
                    <div class="col-span-1">
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 z-10 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-700" x-transition>
                                <div class="py-1">
                                    <a href="{{ route('email.campaigns.show', $campaign) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                                        View Details
                                    </a>
                                    @if($campaign->status === 'draft')
                                        <a href="{{ route('email.campaigns.edit', $campaign) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                                            Edit Campaign
                                        </a>
                                        <form action="{{ route('email.campaigns.send', $campaign) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-green-700 hover:bg-gray-100 dark:text-green-400 dark:hover:bg-gray-600" onclick="return confirm('Are you sure you want to send this campaign?')">
                                                Send Now
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($campaign->status, ['draft', 'scheduled']))
                                        <a href="#" onclick="showScheduleModal({{ $campaign->id }})" class="block px-4 py-2 text-sm text-blue-700 hover:bg-gray-100 dark:text-blue-400 dark:hover:bg-gray-600">
                                            Schedule
                                        </a>
                                    @endif
                                    @if($campaign->status === 'active')
                                        <form action="{{ route('email.campaigns.pause', $campaign) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-yellow-700 hover:bg-gray-100 dark:text-yellow-400 dark:hover:bg-gray-600">
                                                Pause Campaign
                                            </button>
                                        </form>
                                    @endif
                                    @if($campaign->status === 'paused')
                                        <form action="{{ route('email.campaigns.resume', $campaign) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-green-700 hover:bg-gray-100 dark:text-green-400 dark:hover:bg-gray-600">
                                                Resume Campaign
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('email.campaigns.duplicate', $campaign) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600">
                                            Duplicate
                                        </button>
                                    </form>
                                    @if($campaign->status === 'draft')
                                        <form action="{{ route('email.campaigns.destroy', $campaign) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-700 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-600" onclick="return confirm('Are you sure you want to delete this campaign?')">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 1.05a2 2 0 001.38-1.5L12 7l.73-.5a2 2 0 001.38 1.5L22 8M5 13v4a2 2 0 002 2h10a2 2 0 002-2v-4M5 13l7.89 1.05a2 2 0 001.38-1.5L14 13l.73-.5a2 2 0 001.38 1.5L21 13"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No campaigns found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first email campaign.</p>
                    <div class="mt-6">
                        <a href="{{ route('email.campaigns.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            New Campaign
                        </a>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($campaigns->hasPages())
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
            {{ $campaigns->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-600">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Schedule Campaign</h3>
            <form id="scheduleForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Schedule Date & Time</label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideScheduleModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showScheduleModal(campaignId) {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    form.action = `/email-campaigns/${campaignId}/schedule`;
    
    // Set minimum date to current time
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('scheduled_at').min = now.toISOString().slice(0, 16);
    
    modal.classList.remove('hidden');
}

function hideScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('scheduleModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideScheduleModal();
    }
});
</script>
@endpush

@endsection
