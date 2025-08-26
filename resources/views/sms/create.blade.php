@extends('layouts.app')

@section('title', 'Compune SMS')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Compune SMS</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Creează și trimite un nou mesaj SMS către contactele tale
            </p>
        </div>
        <a href="{{ route('sms.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Înapoi la SMS
        </a>
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('sms.store') }}" x-data="smsForm()" class="space-y-8">
    @csrf
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Recipients Selection -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Destinatari</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Selectează cum dorești să trimiți mesajul</p>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <!-- Recipient Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Tip destinatar
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label class="relative flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                   :class="recipientType === 'individual' ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-600' : ''">
                                <input type="radio" 
                                       name="recipient_type" 
                                       value="individual" 
                                       x-model="recipientType"
                                       class="sr-only">
                                <div class="flex flex-col items-center text-center">
                                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Individual</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Un contact specific</span>
                                </div>
                            </label>
                            
                            <label class="relative flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                   :class="recipientType === 'contacts' ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-600' : ''">
                                <input type="radio" 
                                       name="recipient_type" 
                                       value="contacts" 
                                       x-model="recipientType"
                                       class="sr-only">
                                <div class="flex flex-col items-center text-center">
                                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Contacte</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Selectează contacte</span>
                                </div>
                            </label>
                            
                            <label class="relative flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                   :class="recipientType === 'segments' ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-600' : ''">
                                <input type="radio" 
                                       name="recipient_type" 
                                       value="segments" 
                                       x-model="recipientType"
                                       class="sr-only">
                                <div class="flex flex-col items-center text-center">
                                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Segmente</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Grupe de contacte</span>
                                </div>
                            </label>
                        </div>
                        @error('recipient_type')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Individual Contact -->
                    <div x-show="recipientType === 'individual'" x-transition>
                        <label for="contact_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Selectează contactul
                        </label>
                        <select id="contact_id" 
                                name="contact_id" 
                                x-model="selectedContact"
                                @change="updateRecipientCount()"
                                class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Selectează un contact</option>
                            @foreach($contacts as $contact)
                                @if($contact->phone)
                                    <option value="{{ $contact->id }}">
                                        {{ $contact->name }} - {{ $contact->phone }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('contact_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        <!-- Manual Phone Number -->
                        <div class="mt-4">
                            <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Sau introdu numărul de telefon manual
                            </label>
                            <input type="text" 
                                   id="phone_number" 
                                   name="phone_number" 
                                   value="{{ old('phone_number') }}"
                                   placeholder="ex. +40712345678"
                                   class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Dacă completezi acest câmp, va fi folosit în locul contactului selectat
                            </p>
                            @error('phone_number')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Multiple Contacts Selection -->
                    <div x-show="recipientType === 'contacts'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Selectează contactele
                        </label>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 max-h-64 overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($contacts as $contact)
                                    @if($contact->phone)
                                        <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded cursor-pointer">
                                            <input type="checkbox" 
                                                   name="contacts[]" 
                                                   value="{{ $contact->id }}"
                                                   x-model="selectedContacts"
                                                   @change="updateRecipientCount()"
                                                   class="w-4 h-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                                            <div class="ml-2 flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $contact->name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $contact->phone }}
                                                </p>
                                            </div>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @error('contacts')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Segments Selection -->
                    <div x-show="recipientType === 'segments'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Selectează segmentele
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($segments as $segment)
                                <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <input type="checkbox" 
                                           name="segments[]" 
                                           value="{{ $segment->id }}"
                                           x-model="selectedSegments"
                                           @change="updateRecipientCount()"
                                           class="sr-only">
                                    <div class="flex items-center justify-between w-full">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $segment->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ number_format($segment->contacts_count) }} contacte cu telefon
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
                    </div>
                    
                    <!-- Recipient Count -->
                    <div x-show="estimatedRecipients > 0" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                <strong x-text="estimatedRecipients"></strong> destinatari estimați
                                <span x-show="estimatedCost > 0"> • Cost estimativ: <strong x-text="estimatedCost.toFixed(4)"></strong> RON</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Message Content -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Conținutul Mesajului</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Scrie mesajul SMS care va fi trimis</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mesajul SMS *
                        </label>
                        <div class="relative">
                            <textarea id="message" 
                                      name="message" 
                                      rows="4" 
                                      maxlength="320"
                                      required
                                      x-model="message"
                                      @input="updateCharacterCount(); calculateCost();"
                                      placeholder="Introdu mesajul tău aici..."
                                      class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white resize-none">{{ old('message') }}</textarea>
                        </div>
                        
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <div class="flex items-center space-x-4">
                                <span class="text-gray-500 dark:text-gray-400">
                                    <span x-text="characterCount"></span>/320 caractere
                                </span>
                                <span class="text-gray-500 dark:text-gray-400">
                                    <span x-text="smsCount"></span> SMS
                                    <span x-show="smsCount > 1" class="text-yellow-600 dark:text-yellow-400">(mesaj lung)</span>
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" 
                                        @click="insertVariable('{{name}}')"
                                        class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 rounded">
                                    Nume
                                </button>
                                <button type="button" 
                                        @click="insertVariable('{{company}}')"
                                        class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 rounded">
                                    Companie
                                </button>
                            </div>
                        </div>
                        @error('message')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Preview -->
                    <div x-show="message.length > 0" class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Previzualizare:</h4>
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-3 max-w-sm">
                            <p class="text-sm text-gray-900 dark:text-white" x-text="message.replace(/\{\{name\}\}/g, 'John Doe').replace(/\{\{company\}\}/g, 'ACME Corp')"></p>
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
            
            <!-- Provider Selection -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Provider SMS</h3>
                </div>
                
                <div>
                    <label for="provider_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Alege provider
                    </label>
                    <select id="provider_id" 
                            name="provider_id" 
                            x-model="selectedProvider"
                            @change="calculateCost()"
                            class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Provider automat</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" 
                                    data-cost="{{ $provider->cost_per_sms }}"
                                    {{ old('provider_id') == $provider->id ? 'selected' : '' }}>
                                {{ $provider->name }} 
                                @if($provider->cost_per_sms)
                                    ({{ number_format($provider->cost_per_sms, 4) }} RON/SMS)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Providerul va fi selectat automat dacă nu specifici unul
                    </p>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="space-y-3">
                    <button type="submit" 
                            x-bind:disabled="estimatedRecipients === 0 || message.length === 0"
                            class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span x-show="sendType === 'now'">Trimite SMS</span>
                        <span x-show="sendType === 'scheduled'">Programează SMS</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function smsForm() {
    return {
        recipientType: '{{ old('recipient_type', 'individual') }}',
        selectedContact: '{{ old('contact_id') }}',
        selectedContacts: @json(old('contacts', [])),
        selectedSegments: @json(old('segments', [])),
        selectedProvider: '{{ old('provider_id') }}',
        sendType: '{{ old('send_type', 'now') }}',
        message: '{{ old('message') }}',
        characterCount: 0,
        smsCount: 1,
        estimatedRecipients: 0,
        estimatedCost: 0,
        segments: @json($segments),
        providers: @json($providers),
        
        init() {
            this.updateCharacterCount();
            this.updateRecipientCount();
            this.calculateCost();
        },
        
        updateCharacterCount() {
            this.characterCount = this.message.length;
            this.smsCount = Math.ceil(this.characterCount / 160);
        },
        
        updateRecipientCount() {
            let count = 0;
            
            if (this.recipientType === 'individual') {
                count = this.selectedContact ? 1 : 0;
            } else if (this.recipientType === 'contacts') {
                count = this.selectedContacts.length;
            } else if (this.recipientType === 'segments') {
                this.selectedSegments.forEach(segmentId => {
                    const segment = this.segments.find(s => s.id == segmentId);
                    if (segment) {
                        count += segment.contacts_count;
                    }
                });
            }
            
            this.estimatedRecipients = count;
            this.calculateCost();
        },
        
        calculateCost() {
            let costPerSms = 0.05; // default cost
            
            if (this.selectedProvider) {
                const provider = this.providers.find(p => p.id == this.selectedProvider);
                if (provider && provider.cost_per_sms) {
                    costPerSms = parseFloat(provider.cost_per_sms);
                }
            }
            
            this.estimatedCost = this.estimatedRecipients * this.smsCount * costPerSms;
        },
        
        insertVariable(variable) {
            const textarea = document.getElementById('message');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            
            this.message = text.substring(0, start) + variable + text.substring(end);
            
            // Move cursor after inserted variable
            this.$nextTick(() => {
                textarea.selectionStart = textarea.selectionEnd = start + variable.length;
                textarea.focus();
                this.updateCharacterCount();
                this.calculateCost();
            });
        }
    }
}
</script>
@endpush
@endsection
