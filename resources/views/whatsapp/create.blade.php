@extends('layouts.app')

@section('title', 'Compune Mesaj WhatsApp')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Compune Mesaj WhatsApp</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Trimite un mesaj WhatsApp către contactele tale
            </p>
        </div>
        <div>
            <a href="{{ route('whatsapp.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Înapoi la Chat
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <form action="{{ route('whatsapp.send.post') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Mesaj Nou WhatsApp</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Completează detaliile pentru a trimite mesajul</p>
            </div>

            <div class="px-6 space-y-6">
                <!-- Message Type Selection -->
                <div x-data="{ messageType: 'individual' }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tip Mesaj</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                               :class="messageType === 'individual' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : ''">
                            <input type="radio" 
                                   name="send_type" 
                                   value="individual" 
                                   x-model="messageType"
                                   class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Contact Individual</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Trimite la un singur contact</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                               :class="messageType === 'contacts' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : ''">
                            <input type="radio" 
                                   name="send_type" 
                                   value="contacts" 
                                   x-model="messageType"
                                   class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Contacte Multiple</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Selectează contacte specifice</div>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                               :class="messageType === 'segment' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : ''">
                            <input type="radio" 
                                   name="send_type" 
                                   value="segment" 
                                   x-model="messageType"
                                   class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">Segment</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Trimite la un segment întreg</div>
                            </div>
                        </label>
                    </div>

                    <!-- Individual Contact Selection -->
                    <div x-show="messageType === 'individual'" class="mt-4">
                        <label for="contact_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Selectează Contact
                        </label>
                        <select name="contact_id" 
                                id="contact_id" 
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Alege un contact...</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}" {{ old('contact_id') == $contact->id ? 'selected' : '' }}>
                                    {{ $contact->name }} ({{ $contact->whatsapp ?: $contact->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('contact_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Multiple Contacts Selection -->
                    <div x-show="messageType === 'contacts'" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Selectează Contacte ({{ count($contacts) }} disponibile)
                        </label>
                        <div class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-3 bg-gray-50 dark:bg-gray-700">
                            @foreach($contacts as $contact)
                                <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer">
                                    <input type="checkbox" 
                                           name="contact_ids[]" 
                                           value="{{ $contact->id }}"
                                           {{ in_array($contact->id, old('contact_ids', [])) ? 'checked' : '' }}
                                           class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                                    <div class="ml-2">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $contact->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $contact->whatsapp ?: $contact->phone }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('contact_ids')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Segment Selection -->
                    <div x-show="messageType === 'segment'" class="mt-4">
                        <label for="segment_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Selectează Segment
                        </label>
                        <select name="segment_id" 
                                id="segment_id" 
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Alege un segment...</option>
                            @foreach($segments as $segment)
                                <option value="{{ $segment->id }}" {{ old('segment_id') == $segment->id ? 'selected' : '' }}>
                                    {{ $segment->name }} ({{ $segment->contacts_count ?? 0 }} contacte)
                                </option>
                            @endforeach
                        </select>
                        @error('segment_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Message Content -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Mesajul
                    </label>
                    <textarea name="message" 
                              id="message" 
                              rows="6" 
                              maxlength="4096"
                              placeholder="Scrie mesajul tău aici..."
                              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white resize-none">{{ old('message') }}</textarea>
                    <div class="mt-1 flex justify-between items-center">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            <span id="char-count">0</span>/4096 caractere
                        </span>
                        @error('message')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Message Type and Media -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="message_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tip Mesaj
                        </label>
                        <select name="message_type" 
                                id="message_type" 
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="text" {{ old('message_type') === 'text' ? 'selected' : '' }}>Text</option>
                            <option value="image" {{ old('message_type') === 'image' ? 'selected' : '' }}>Imagine</option>
                            <option value="document" {{ old('message_type') === 'document' ? 'selected' : '' }}>Document</option>
                            <option value="audio" {{ old('message_type') === 'audio' ? 'selected' : '' }}>Audio</option>
                        </select>
                    </div>

                    <div>
                        <label for="media_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            URL Media (opțional)
                        </label>
                        <input type="url" 
                               name="media_url" 
                               id="media_url" 
                               value="{{ old('media_url') }}"
                               placeholder="https://example.com/media.jpg"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @error('media_url')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Schedule Option -->
                <div>
                    <div class="flex items-center mb-4">
                        <input type="checkbox" 
                               id="schedule_message" 
                               name="schedule_message" 
                               value="1" 
                               {{ old('schedule_message') ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                        <label for="schedule_message" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Programează mesajul pentru mai târziu
                        </label>
                    </div>
                    
                    <div id="schedule-fields" style="display: none;">
                        <label for="schedule_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Data și Ora Programării
                        </label>
                        <input type="datetime-local" 
                               name="schedule_at" 
                               id="schedule_at" 
                               value="{{ old('schedule_at') }}"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @error('schedule_at')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-end space-x-3 rounded-b-xl">
                <a href="{{ route('whatsapp.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                    Anulează
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Trimite Mesaj WhatsApp
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Character counter
document.getElementById('message').addEventListener('input', function() {
    const charCount = this.value.length;
    document.getElementById('char-count').textContent = charCount;
    
    if (charCount > 4096) {
        document.getElementById('char-count').classList.add('text-red-600');
    } else {
        document.getElementById('char-count').classList.remove('text-red-600');
    }
});

// Schedule checkbox toggle
document.getElementById('schedule_message').addEventListener('change', function() {
    const scheduleFields = document.getElementById('schedule-fields');
    if (this.checked) {
        scheduleFields.style.display = 'block';
        // Set minimum date to now
        const now = new Date();
        const dateString = now.toISOString().slice(0, 16);
        document.getElementById('schedule_at').min = dateString;
    } else {
        scheduleFields.style.display = 'none';
    }
});

// Initialize character counter on page load
document.addEventListener('DOMContentLoaded', function() {
    const messageField = document.getElementById('message');
    const charCount = messageField.value.length;
    document.getElementById('char-count').textContent = charCount;
    
    // Initialize schedule fields visibility
    const scheduleCheckbox = document.getElementById('schedule_message');
    if (scheduleCheckbox.checked) {
        document.getElementById('schedule-fields').style.display = 'block';
    }
});
</script>
@endpush
