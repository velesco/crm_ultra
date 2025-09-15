@extends('layouts.app')

@section('title', 'Creează Campanie Email')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Creează Campanie Email</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Configurează și lansează o nouă campanie de marketing prin email
            </p>
        </div>
        <a href="{{ route('email.campaigns.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Înapoi la campanii
        </a>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('email.campaigns.store') }}" x-data="campaignForm()" class="space-y-8">
    @csrf
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Informații de Bază</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Denumirea și descrierea campaniei</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nume campanie *
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               required
                               placeholder="ex. Newsletter Săptămânal - Ianuarie 2025"
                               class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Subiect email *
                        </label>
                        <input type="text" 
                               id="subject" 
                               name="subject" 
                               value="{{ old('subject') }}"
                               required
                               placeholder="Subiectul care va apărea în inbox"
                               class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @error('subject')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nume expeditor
                        </label>
                        <input type="text" 
                               id="from_name" 
                               name="from_name" 
                               value="{{ old('from_name', Auth::user()->name) }}"
                               placeholder="Numele care va apărea ca expeditor"
                               class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @error('from_name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Template Selection -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Template Email</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Selectează un template sau creează conținut personalizat</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="email_template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Template salvat
                        </label>
                        <select id="email_template_id" 
                                name="email_template_id" 
                                x-model="selectedTemplate"
                                @change="loadTemplate()"
                                class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Selectează un template existent</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('email_template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Sau creează un template nou în 
                            <a href="{{ route('email.templates.create') }}" 
                               target="_blank"
                               class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">secțiunea templates</a>
                        </p>
                    </div>
                    
                    <div x-show="!selectedTemplate">
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Conținut HTML *
                        </label>
                        <textarea id="content" 
                                  name="content" 
                                  rows="12"
                                  x-model="content"
                                  required
                                  placeholder="Introdu conținutul HTML al email-ului..."
                                  class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white font-mono text-sm">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        <div class="mt-3 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Variabile disponibile: {{'{{'}}name{{'}}'}}, {{'{{'}}email{{'}}'}}, {{'{{'}}company{{'}}'}}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipients -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Destinatari</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Selectează segmentele de contacte pentru campanie</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Segmente de contacte *
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-48 overflow-y-auto p-1">
                            @foreach($segments as $segment)
                                <label class="relative flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <input type="checkbox" 
                                           name="segments[]" 
                                           value="{{ $segment->id }}"
                                           {{ in_array($segment->id, old('segments', [])) ? 'checked' : '' }}
                                           x-model="selectedSegments"
                                           @change="calculateRecipients()"
                                           class="sr-only">
                                    <div class="flex items-center justify-between w-full">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $segment->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ number_format($segment->contacts_count) }} contacte
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 border-2 border-gray-300 dark:border-gray-600 rounded transition-colors"
                                                 :class="selectedSegments.includes({{ $segment->id }}) ? 'bg-indigo-600 border-indigo-600' : ''">
                                                <svg x-show="selectedSegments.includes({{ $segment->id }})" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('segments')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('segments.*')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div x-show="estimatedRecipients > 0" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong x-text="estimatedRecipients"></strong> destinatari estimați pentru această campanie
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Schedule -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Programare</h3>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Când să se trimită?
                        </label>
                        <div class="space-y-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" 
                                       name="send_type" 
                                       value="now" 
                                       {{ old('send_type', 'now') === 'now' ? 'checked' : '' }}
                                       x-model="sendType"
                                       class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 focus:ring-indigo-500">
                                <span class="ml-3 text-sm text-gray-900 dark:text-white">Trimite acum</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" 
                                       name="send_type" 
                                       value="scheduled" 
                                       {{ old('send_type') === 'scheduled' ? 'checked' : '' }}
                                       x-model="sendType"
                                       class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 focus:ring-indigo-500">
                                <span class="ml-3 text-sm text-gray-900 dark:text-white">Programează pentru mai târziu</span>
                            </label>
                        </div>
                    </div>
                    
                    <div x-show="sendType === 'scheduled'" x-transition>
                        <label for="scheduled_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Data și ora
                        </label>
                        <input type="datetime-local" 
                               id="scheduled_at" 
                               name="scheduled_at" 
                               value="{{ old('scheduled_at') }}"
                               :required="sendType === 'scheduled'"
                               class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        @error('scheduled_at')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- SMTP Configuration -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">SMTP Server</h3>
                </div>
                
                <div>
                    <label for="smtp_config_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Configurație SMTP
                    </label>
                    <select id="smtp_config_id" 
                            name="smtp_config_id" 
                            class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Configurație automată</option>
                        @foreach($smtpConfigs as $config)
                            <option value="{{ $config->id }}" 
                                    {{ old('smtp_config_id') == $config->id ? 'selected' : '' }}>
                                {{ $config->name }} 
                                @if($config->daily_limit)
                                    ({{ $config->emails_sent_today }}/{{ $config->daily_limit }} astăzi)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Configurația va fi selectată automat dacă nu specifici una
                    </p>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="space-y-3">
                    <button type="submit" 
                            name="action" 
                            value="save_draft"
                            class="w-full flex justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Salvează ca Draft
                    </button>
                    
                    <button type="submit" 
                            name="action" 
                            value="send"
                            x-bind:disabled="selectedSegments.length === 0"
                            class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span x-show="sendType === 'now'">Trimite Campania</span>
                        <span x-show="sendType === 'scheduled'">Programează Campania</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function campaignForm() {
    return {
        selectedTemplate: '{{ old('email_template_id') }}',
        selectedSegments: {!! json_encode(old('segments', [])) !!},
        sendType: '{{ old('send_type', 'now') }}',
        content: '{{ old('content') }}',
        estimatedRecipients: 0,
        templates: @json($templates),
        
        init() {
            this.calculateRecipients();
        },
        
        loadTemplate() {
            if (this.selectedTemplate) {
                const template = this.templates.find(t => t.id == this.selectedTemplate);
                if (template) {
                    this.content = template.content;
                }
            } else {
                this.content = '';
            }
        },
        
        calculateRecipients() {
            // Aici ar trebui să faci un request AJAX pentru a calcula exact numărul de destinatari
            // Pentru moment, estimăm pe baza segmentelor selectate
            let total = 0;
            const segments = @json($segments);
            
            this.selectedSegments.forEach(segmentId => {
                const segment = segments.find(s => s.id == segmentId);
                if (segment) {
                    total += segment.contacts_count;
                }
            });
            
            this.estimatedRecipients = total;
        }
    }
}
</script>
@endpush
@endsection
