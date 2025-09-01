@extends('layouts.app')

@section('title', 'Detalii SMS')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Detalii SMS</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Mesaj trimis către {{ $sms->contact->name ?? 'Contact Necunoscut' }}
            </p>
        </div>
        <div>
            <a href="{{ route('sms.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Înapoi la Lista SMS
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Message Content -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Conținut Mesaj</h3>
                    @php
                        $statusConfig = [
                            'pending' => ['class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300', 'label' => 'În așteptare'],
                            'sent' => ['class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300', 'label' => 'Trimis'],
                            'delivered' => ['class' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300', 'label' => 'Livrat'],
                            'failed' => ['class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300', 'label' => 'Eșuat'],
                            'scheduled' => ['class' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300', 'label' => 'Programat'],
                            'cancelled' => ['class' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', 'label' => 'Anulat']
                        ];
                        $status = $statusConfig[$sms->status] ?? ['class' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', 'label' => ucfirst($sms->status)];
                    @endphp
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>
                </div>
                
                <div class="p-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-lg font-medium text-gray-900 dark:text-white">Către: {{ $sms->phone_number }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::length($sms->message) }} caractere</div>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 font-mono text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $sms->message }}</div>
                    </div>

                    <!-- Action Buttons -->
                    @if(in_array($sms->status, ['scheduled', 'failed']))
                        <div class="mt-6 flex flex-wrap gap-3">
                            @if($sms->status === 'scheduled')
                                <a href="{{ route('sms.edit', $sms) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Editează Mesajul
                                </a>
                                
                                <form action="{{ route('sms.destroy', $sms) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Ești sigur că vrei să anulezi acest SMS programat?')"
                                            class="inline-flex items-center px-4 py-2 border border-yellow-300 dark:border-yellow-600 rounded-lg text-sm font-medium text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Anulează
                                    </button>
                                </form>
                            @endif

                            @if($sms->status === 'failed')
                                <form action="{{ route('sms.resend', $sms) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Ești sigur că vrei să retrimiti acest SMS?')"
                                            class="inline-flex items-center px-4 py-2 border border-green-300 dark:border-green-600 rounded-lg text-sm font-medium text-green-700 dark:text-green-300 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Retrimite
                                    </button>
                                </form>
                            @endif

                            @if(in_array($sms->status, ['scheduled', 'failed']))
                                <form action="{{ route('sms.destroy', $sms) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Ești sigur că vrei să ștergi acest mesaj SMS?')"
                                            class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 rounded-lg text-sm font-medium text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Șterge
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informații Livrare</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                                <div class="mt-1 flex items-center space-x-2">
                                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $status['class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                    @if($sms->error_message)
                                        <span class="text-sm text-red-600 dark:text-red-400">{{ $sms->error_message }}</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Provider</label>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $sms->smsProvider->name ?? 'Provider Implicit' }}
                                </div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Cost</label>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                    @if($sms->cost > 0)
                                        ${{ number_format($sms->cost, 4) }}
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">Fără cost înregistrat</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Creat La</label>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $sms->created_at->format('j M Y, H:i') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ $sms->created_at->diffForHumans() }})
                                    </div>
                                </div>
                            </div>

                            @if($sms->scheduled_at)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Programat Pentru</label>
                                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $sms->scheduled_at->format('j M Y, H:i') }}
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            ({{ $sms->scheduled_at->diffForHumans() }})
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($sms->sent_at)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Trimis La</label>
                                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $sms->sent_at->format('j M Y, H:i') }}
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            ({{ $sms->sent_at->diffForHumans() }})
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($sms->provider_message_id)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">ID Mesaj Provider</label>
                                    <div class="mt-1 text-sm font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                        {{ $sms->provider_message_id }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Details -->
            @if($sms->metadata)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalii Tehnice</h3>
                    </div>
                    <div class="p-6">
                        @php
                            $metadata = json_decode($sms->metadata, true);
                        @endphp
                        
                        @if($metadata)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($metadata as $key => $value)
                                    <div>
                                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                                        <div class="mt-1 text-sm text-gray-900 dark:text-white">
                                            @if(is_array($value) || is_object($value))
                                                <pre class="bg-gray-100 dark:bg-gray-700 p-3 rounded text-xs overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                <div class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono text-sm">{{ $value }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">Nu sunt disponibile detalii tehnice.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informații Contact</h3>
                </div>
                <div class="p-6">
                    @if($sms->contact)
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-4">
                                {{ strtoupper(substr($sms->contact->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $sms->contact->name }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $sms->contact->email }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Număr Telefon</label>
                                <div class="mt-1">
                                    <a href="tel:{{ $sms->contact->phone }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ $sms->contact->phone }}
                                    </a>
                                </div>
                            </div>

                            @if($sms->contact->company)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Companie</label>
                                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $sms->contact->company }}</div>
                                </div>
                            @endif

                            @if($sms->contact->location)
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Locație</label>
                                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $sms->contact->location }}</div>
                                </div>
                            @endif

                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Creat</label>
                                <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $sms->contact->created_at->format('j M Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('contacts.show', $sms->contact) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Vezi Profil Contact
                            </a>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400">Informații contact indisponibile</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500">Contactul poate fi fost șters</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sender Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Expeditor</h3>
                </div>
                <div class="p-6">
                    @if($sms->user)
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                {{ strtoupper(substr($sms->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $sms->user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $sms->user->email }}</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center text-gray-500 dark:text-gray-400">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Informații expeditor indisponibile
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Acțiuni Rapide</h3>
                </div>
                <div class="p-6 space-y-3">
                    @if($sms->contact)
                        <a href="{{ route('sms.create', ['contact' => $sms->contact->id]) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-indigo-300 dark:border-indigo-600 rounded-lg text-sm font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Trimite Alt SMS
                        </a>
                    @endif
                    
                    <button type="button" 
                            onclick="copyMessage()"
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Copiază Mesaj
                    </button>
                    
                    <a href="{{ route('sms.index', ['search' => $sms->phone_number]) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Istoric SMS
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyMessage() {
    const messageContent = `{{ addslashes($sms->message) }}`;
    
    navigator.clipboard.writeText(messageContent).then(function() {
        // Show success notification
        showNotification('Mesaj copiat în clipboard!', 'success');
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = messageContent;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showNotification('Mesaj copiat în clipboard!', 'success');
        } catch (err) {
            showNotification('Eroare la copierea mesajului', 'error');
        }
        document.body.removeChild(textArea);
    });
}

function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 flex items-center p-4 rounded-lg shadow-lg transition-all transform translate-x-full ${
        type === 'success' 
            ? 'bg-green-100 border border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-200' 
            : 'bg-red-100 border border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-200'
    }`;
    
    notification.innerHTML = `
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${type === 'success' 
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>'
            }
        </svg>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>
@endpush
