@extends('layouts.app')

@section('title', 'Contact Details')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                @if($contact->avatar)
                    <img class="h-16 w-16 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600" 
                         src="{{ Storage::url($contact->avatar) }}" 
                         alt="{{ $contact->first_name }} {{ $contact->last_name }}">
                @else
                    <div class="h-16 w-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center border-2 border-gray-300 dark:border-gray-600">
                        <span class="text-xl font-medium text-gray-700 dark:text-gray-300">
                            {{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1)) }}
                        </span>
                    </div>
                @endif
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $contact->first_name }} {{ $contact->last_name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if($contact->title && $contact->company)
                        {{ $contact->title }} at {{ $contact->company }}
                    @elseif($contact->company)
                        {{ $contact->company }}
                    @elseif($contact->title)
                        {{ $contact->title }}
                    @else
                        Contact Details
                    @endif
                </p>
                <div class="flex items-center mt-1">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                        @if($contact->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($contact->status === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                        @elseif($contact->status === 'unsubscribed') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @endif">
                        {{ ucfirst($contact->status) }}
                    </span>
                    @if($contact->tags && count($contact->tags) > 0)
                        <div class="ml-3 flex flex-wrap gap-1">
                            @foreach($contact->tags as $tag)
                                <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="flex space-x-3">
            <!-- Communication Buttons -->
            <div class="flex space-x-1">
                <a href="{{ route('communications.index', ['contact' => $contact->id, 'channel' => 'email']) }}" 
                   class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150"
                   title="Send Email">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </a>
                @if($contact->phone)
                    <a href="{{ route('communications.index', ['contact' => $contact->id, 'channel' => 'sms']) }}" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition ease-in-out duration-150"
                       title="Send SMS">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </a>
                    <a href="{{ route('communications.index', ['contact' => $contact->id, 'channel' => 'whatsapp']) }}" 
                       class="inline-flex items-center px-3 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 transition ease-in-out duration-150"
                       title="Send WhatsApp">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.106"/>
                        </svg>
                    </a>
                @endif
            </div>

            <!-- Action Buttons -->
            <a href="{{ route('contacts.edit', $contact) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Contact
            </a>
            
            <a href="{{ route('contacts.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Contacts
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Contact Information</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="mailto:{{ $contact->email }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500">
                                    {{ $contact->email }}
                                </a>
                            </dd>
                        </div>
                        
                        @if($contact->phone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="tel:{{ $contact->phone }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500">
                                    {{ $contact->phone }}
                                </a>
                            </dd>
                        </div>
                        @endif

                        @if($contact->company)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Company</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact->company }}</dd>
                        </div>
                        @endif

                        @if($contact->title)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Job Title</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact->title }}</dd>
                        </div>
                        @endif

                        @if($contact->address)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact->address }}</dd>
                        </div>
                        @endif

                        @if($contact->birthday)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Birthday</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact->birthday->format('F j, Y') }}</dd>
                        </div>
                        @endif

                        @if($contact->lead_source)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lead Source</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $contact->lead_source)) }}</dd>
                        </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact->created_at->format('M j, Y g:i A') }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $contact->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Custom Fields -->
            @if($contact->custom_fields && count($contact->custom_fields) > 0)
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Custom Fields</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($contact->custom_fields as $key => $value)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $key }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $value }}</dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($contact->notes)
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Notes</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $contact->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Activity</h3>
                </div>
                <div class="px-6 py-4">
                    @if($recentActivity && $recentActivity->count() > 0)
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($recentActivity as $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-600" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800
                                                    @if($activity->type === 'email') bg-blue-500
                                                    @elseif($activity->type === 'sms') bg-green-500
                                                    @elseif($activity->type === 'whatsapp') bg-green-400
                                                    @else bg-gray-400 @endif">
                                                    @if($activity->type === 'email')
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                        </svg>
                                                    @elseif($activity->type === 'sms' || $activity->type === 'whatsapp')
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $activity->description }}
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                            No recent activity found for this contact.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Quick Stats</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Emails Sent</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $stats['emails_sent'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Emails Opened</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $stats['emails_opened'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">SMS Sent</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $stats['sms_sent'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">WhatsApp Messages</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $stats['whatsapp_messages'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Last Activity</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            @if($contact->last_activity_at)
                                {{ $contact->last_activity_at->diffForHumans() }}
                            @else
                                Never
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Segments -->
            @if($contact->segments && $contact->segments->count() > 0)
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Segments</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-2">
                        @foreach($contact->segments as $segment)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $segment->name }}</p>
                                    @if($segment->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $segment->description }}</p>
                                    @endif
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded">
                                    {{ $segment->is_dynamic ? 'Dynamic' : 'Static' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Quick Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <a href="{{ route('communications.index', ['contact' => $contact->id]) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        View Communications
                    </a>
                    
                    <button onclick="duplicateContact()" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Duplicate Contact
                    </button>
                    
                    <button onclick="exportContact()" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Contact
                    </button>

                    @if($contact->status !== 'unsubscribed')
                        <button onclick="unsubscribeContact()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                            </svg>
                            Unsubscribe
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function duplicateContact() {
    if (confirm('Do you want to create a duplicate of this contact?')) {
        window.location.href = '{{ route("contacts.create") }}?duplicate={{ $contact->id }}';
    }
}

function exportContact() {
    // Create a temporary form to export this contact
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("data.export") }}';
    form.innerHTML = `
        @csrf
        <input type="hidden" name="type" value="contacts">
        <input type="hidden" name="contact_ids[]" value="{{ $contact->id }}">
        <input type="hidden" name="format" value="csv">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function unsubscribeContact() {
    if (confirm('Are you sure you want to unsubscribe this contact from all communications?')) {
        // Create a form to update contact status
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("contacts.update", $contact) }}';
        form.innerHTML = `
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="unsubscribed">
        `;
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
}
</script>
@endpush
@endsection