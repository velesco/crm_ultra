@extends('layouts.app')

@section('title', 'Conversation with ' . ($contact->first_name ?? 'Contact'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <nav class="flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="{{ route('communications.index') }}" class="text-gray-400 hover:text-gray-500">
                                <svg class="flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L8 6.414V19a1 1 0 001 1h2a1 1 0 001-1V6.414l4.293 4.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                                <span class="sr-only">Inbox</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('communications.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Communications
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500 dark:text-gray-400" aria-current="page">
                                {{ $contact->first_name ?? 'Contact' }} {{ $contact->last_name ?? '' }}
                            </span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <div class="mt-4 flex items-center">
                @if($contact->avatar)
                    <img class="h-16 w-16 rounded-full" src="{{ $contact->avatar }}" alt="">
                @else
                    <div class="h-16 w-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                        <span class="text-xl font-medium text-gray-700 dark:text-gray-300">
                            {{ strtoupper(substr($contact->first_name ?? 'U', 0, 1)) }}
                        </span>
                    </div>
                @endif
                <div class="ml-4">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                        {{ $contact->first_name ?? 'Unknown' }} {{ $contact->last_name ?? '' }}
                    </h2>
                    <div class="mt-1 flex items-center space-x-4">
                        @if($contact->email)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                {{ $contact->email }}
                            </p>
                        @endif
                        @if($contact->phone)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $contact->phone }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 flex space-x-3 md:ml-4 md:mt-0">
            <a href="{{ route('contacts.show', $contact->id) }}" 
               class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                View Profile
            </a>
            <button type="button" onclick="showComposeModal()" 
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Send Message
            </button>
        </div>
    </div>

    <!-- Conversation Statistics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Total Messages
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $stats['total_messages'] ?? 0 }}
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
                        <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Emails
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $stats['emails'] ?? 0 }}
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
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                SMS Messages
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $stats['sms'] ?? 0 }}
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
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                WhatsApp
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $stats['whatsapp'] ?? 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Channel Tabs -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showChannel('all')" 
                        class="channel-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active"
                        data-channel="all">
                    All Messages
                    <span class="bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300 ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $stats['total_messages'] ?? 0 }}
                    </span>
                </button>
                
                <button onclick="showChannel('email')" 
                        class="channel-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        data-channel="email">
                    Email
                    <span class="bg-blue-100 text-blue-900 dark:bg-blue-800 dark:text-blue-200 ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $stats['emails'] ?? 0 }}
                    </span>
                </button>
                
                <button onclick="showChannel('sms')" 
                        class="channel-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        data-channel="sms">
                    SMS
                    <span class="bg-yellow-100 text-yellow-900 dark:bg-yellow-800 dark:text-yellow-200 ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $stats['sms'] ?? 0 }}
                    </span>
                </button>
                
                <button onclick="showChannel('whatsapp')" 
                        class="channel-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        data-channel="whatsapp">
                    WhatsApp
                    <span class="bg-green-100 text-green-900 dark:bg-green-800 dark:text-green-200 ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $stats['whatsapp'] ?? 0 }}
                    </span>
                </button>
            </nav>
        </div>

        <!-- Messages Timeline -->
        <div class="px-6 py-6">
            @if($messages->count() > 0)
                <div class="flow-root">
                    <ul class="-mb-8" id="messages-timeline">
                        @foreach($messages as $index => $message)
                            <li class="message-item" data-channel="{{ $message['channel'] }}">
                                <div class="relative pb-8">
                                    @if($index < $messages->count() - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    @endif
                                    
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800
                                                @if($message['channel'] == 'email') bg-blue-500
                                                @elseif($message['channel'] == 'sms') bg-yellow-500
                                                @elseif($message['channel'] == 'whatsapp') bg-green-500
                                                @else bg-gray-500 @endif">
                                                @if($message['channel'] == 'email')
                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                @elseif($message['channel'] == 'sms')
                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                @elseif($message['channel'] == 'whatsapp')
                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-medium text-gray-900 dark:text-white">
                                                    {{ $message['direction'] == 'outbound' ? 'Sent' : 'Received' }}
                                                </span>
                                                <span class="ml-2 capitalize">{{ $message['channel'] }}</span>
                                                @if($message['subject'])
                                                    <span class="ml-2 font-medium">"{{ $message['subject'] }}"</span>
                                                @endif
                                                <time datetime="{{ $message['created_at'] }}" class="ml-2">
                                                    {{ \Carbon\Carbon::parse($message['created_at'])->format('M j, Y g:i A') }}
                                                </time>
                                            </div>
                                            
                                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                @if($message['channel'] == 'email')
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                        @if($message['subject'])
                                                            <div class="font-medium mb-2">{{ $message['subject'] }}</div>
                                                        @endif
                                                        <div class="prose prose-sm dark:prose-invert max-w-none">
                                                            {!! nl2br(e($message['content'])) !!}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                        {{ $message['content'] }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            @if($message['status'])
                                                <div class="mt-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($message['status'] == 'delivered') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200
                                                        @elseif($message['status'] == 'failed') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200
                                                        @elseif($message['status'] == 'sent') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200
                                                        @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 @endif">
                                                        {{ ucfirst($message['status']) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <!-- Pagination -->
                <div class="mt-6">
                    {{ $messages->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No messages</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Start a conversation with this contact.
                    </p>
                    <div class="mt-6">
                        <button type="button" onclick="showComposeModal()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Send First Message
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Compose Modal -->
<div id="composeModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeComposeModal()"></div>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-800">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                        Send Message to {{ $contact->first_name }} {{ $contact->last_name }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Choose a channel and compose your message
                        </p>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="{{ route('communications.send') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                
                <!-- Channel Selection -->
                <div>
                    <label for="compose_channel" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Channel</label>
                    <select id="compose_channel" name="channel" required 
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select Channel</option>
                        @if($contact->email)
                            <option value="email">Email ({{ $contact->email }})</option>
                        @endif
                        @if($contact->phone)
                            <option value="sms">SMS ({{ $contact->phone }})</option>
                            <option value="whatsapp">WhatsApp ({{ $contact->phone }})</option>
                        @endif
                    </select>
                </div>

                <!-- Subject (for email) -->
                <div id="compose_subject_field" class="hidden">
                    <label for="compose_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subject</label>
                    <input type="text" name="subject" id="compose_subject" 
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Message -->
                <div>
                    <label for="compose_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                    <textarea id="compose_message" name="message" rows="4" required 
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Enter your message..."></textarea>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                        Send Message
                    </button>
                    <button type="button" onclick="closeComposeModal()" 
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
function showComposeModal() {
    document.getElementById('composeModal').classList.remove('hidden');
}

function closeComposeModal() {
    document.getElementById('composeModal').classList.add('hidden');
    document.getElementById('compose_channel').value = '';
    document.getElementById('compose_subject').value = '';
    document.getElementById('compose_message').value = '';
    document.getElementById('compose_subject_field').classList.add('hidden');
}

// Show/hide subject field based on channel
document.getElementById('compose_channel').addEventListener('change', function() {
    const subjectField = document.getElementById('compose_subject_field');
    if (this.value === 'email') {
        subjectField.classList.remove('hidden');
    } else {
        subjectField.classList.add('hidden');
    }
});

// Channel filtering
let currentChannel = 'all';

function showChannel(channel) {
    currentChannel = channel;
    
    // Update tab appearance
    document.querySelectorAll('.channel-tab').forEach(tab => {
        tab.classList.remove('border-indigo-500', 'text-indigo-600');
        tab.classList.add('border-transparent', 'text-gray-500');
        tab.classList.remove('active');
    });
    
    document.querySelector(`[data-channel="${channel}"]`).classList.add('border-indigo-500', 'text-indigo-600', 'active');
    document.querySelector(`[data-channel="${channel}"]`).classList.remove('border-transparent', 'text-gray-500');
    
    // Show/hide messages
    document.querySelectorAll('.message-item').forEach(item => {
        if (channel === 'all' || item.getAttribute('data-channel') === channel) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Mark messages as read when viewed
window.addEventListener('load', function() {
    fetch(`{{ route('communications.markRead', $contact->id) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    });
});
</script>
@endpush
@endsection