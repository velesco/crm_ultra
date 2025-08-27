@extends('layouts.app')

@section('title', 'Unified Inbox - Communications')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                Unified Inbox
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage all communications across email, SMS, and WhatsApp from one place
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <button type="button" onclick="openQuickSendModal()" 
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Quick Send
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Unread Messages
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $stats['unread_count'] ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Today's Emails
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $stats['emails_today'] ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-800 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Today's SMS
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $stats['sms_today'] ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Today's WhatsApp
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $stats['whatsapp_today'] ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" action="{{ route('communications.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">All Messages</option>
                            <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                            <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search messages..." 
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div class="flex justify-between">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    
                    <a href="{{ route('communications.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages List -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                Recent Communications
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                All conversations across email, SMS, and WhatsApp
            </p>
        </div>
        
        @if($communications->count() > 0)
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($communications as $communication)
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" 
                        onclick="window.location='{{ route('communications.conversation', $communication['contact'] ? $communication['contact']['id'] : '#') }}'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center min-w-0 flex-1">
                                <!-- Contact Avatar -->
                                <div class="flex-shrink-0">
                                    @if($communication['contact'] && $communication['contact']['avatar'])
                                        <img class="h-10 w-10 rounded-full" src="{{ $communication['contact']['avatar'] }}" alt="">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ $communication['contact'] ? strtoupper(substr($communication['contact']['first_name'] ?? 'U', 0, 1)) : 'U' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="ml-4 min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            @if($communication['contact'])
                                                {{ $communication['contact']['first_name'] ?? 'Unknown' }} {{ $communication['contact']['last_name'] ?? '' }}
                                            @else
                                                Unknown Contact
                                            @endif
                                        </p>
                                        <div class="ml-2 flex-shrink-0 flex items-center space-x-2">
                                            <!-- Channel Badge -->
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($communication['type'] == 'email') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200
                                                @elseif($communication['type'] == 'sms') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200
                                                @elseif($communication['type'] == 'whatsapp') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 @endif">
                                                @if($communication['type'] == 'email')
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                @elseif($communication['type'] == 'sms')
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                @elseif($communication['type'] == 'whatsapp')
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                @endif
                                                {{ ucfirst($communication['type']) }}
                                            </span>
                                            
                                            @if(!$communication['read_at'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200">
                                                    New
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-900 dark:text-white font-medium">
                                            {{ $communication['subject'] }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                            {{ $communication['snippet'] }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            {{ $communication['created_at']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ml-5 flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            
            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $communications->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No conversations</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by sending your first message.</p>
                <div class="mt-6">
                    <button type="button" onclick="openQuickSendModal()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Send Message
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Quick Send Modal -->
<div id="quickSendModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeQuickSendModal()"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-800">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                        Quick Send Message
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Send a message across any channel
                        </p>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="{{ route('communications.send') }}" class="mt-5 space-y-4">
                @csrf
                
                <!-- Channel Selection -->
                <div>
                    <label for="channel_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Channel</label>
                    <select id="channel_select" name="channel" required 
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select Channel</option>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>

                <!-- SMTP Configuration (for email) -->
                <div id="smtp_field" class="hidden">
                    <label for="smtp_config_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Send From Email</label>
                    <select id="smtp_config_select" name="smtp_config_id" 
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select Email Account</option>
                        <!-- Options will be loaded via AJAX -->
                    </select>
                </div>

                <!-- Contact Selection -->
                <div>
                    <label for="contact_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact</label>
                    <select id="contact_select" name="contact_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select Contact</option>
                        <!-- Options will be loaded via AJAX -->
                    </select>
                </div>

                <!-- Subject (for email) -->
                <div id="subject_field" class="hidden">
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                    <input type="text" name="subject" id="subject" 
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                    <textarea id="message" name="message" rows="4" required 
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Enter your message..."></textarea>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                        Send Message
                    </button>
                    <button type="button" onclick="closeQuickSendModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openQuickSendModal() {
    document.getElementById('quickSendModal').classList.remove('hidden');
    loadContacts();
    loadSmtpConfigs();
}

function closeQuickSendModal() {
    document.getElementById('quickSendModal').classList.add('hidden');
    document.getElementById('channel_select').value = '';
    document.getElementById('contact_select').value = '';
    document.getElementById('smtp_config_select').value = '';
    document.getElementById('subject').value = '';
    document.getElementById('message').value = '';
    document.getElementById('subject_field').classList.add('hidden');
    document.getElementById('smtp_field').classList.add('hidden');
}

function loadContacts() {
    fetch('{{ route('api.contacts') }}')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('contact_select');
            select.innerHTML = '<option value="">Select Contact</option>';
            data.forEach(contact => {
                const option = document.createElement('option');
                option.value = contact.id;
                option.textContent = `${contact.first_name || ''} ${contact.last_name || ''} (${contact.email || contact.phone})`.trim();
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading contacts:', error);
        });
}

function loadSmtpConfigs() {
    fetch('{{ route('api.smtp-configs') }}')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('smtp_config_select');
            select.innerHTML = '<option value="">Select Email Account</option>';
            data.forEach(config => {
                const option = document.createElement('option');
                option.value = config.id;
                option.textContent = `${config.name} (${config.from_email})`;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading SMTP configs:', error);
        });
}

// Show/hide fields based on channel selection
document.getElementById('channel_select').addEventListener('change', function() {
    const subjectField = document.getElementById('subject_field');
    const smtpField = document.getElementById('smtp_field');
    
    if (this.value === 'email') {
        subjectField.classList.remove('hidden');
        smtpField.classList.remove('hidden');
        // Make SMTP config required for email
        document.getElementById('smtp_config_select').required = true;
    } else {
        subjectField.classList.add('hidden');
        smtpField.classList.add('hidden');
        // Remove SMTP config requirement for other channels
        document.getElementById('smtp_config_select').required = false;
    }
});
</script>
@endpush
@endsection