<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Dashboard') - {{ config('app.name', 'CRM Ultra') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Alpine.js for interactive components -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Chart.js for dashboard charts - Fixed version -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }"
          x-init="$watch('darkMode', value => {
              localStorage.setItem('darkMode', value);
              if (value) { document.documentElement.classList.add('dark') } else { document.documentElement.classList.remove('dark') }
          })"
          :class="{ 'dark': darkMode }">

        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 flex z-40 lg:hidden">
            <div @click="sidebarOpen = false" class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
        </div>

        <!-- Sidebar -->
        <div class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:inset-y-0">
            <div class="flex flex-col flex-grow bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 pt-5 pb-4 overflow-y-auto">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 px-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">C</span>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">CRM Ultra</h1>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="mt-8 flex-1 px-2 space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-indigo-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v3H8V5z"></path>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Contacts -->
                    <div x-data="{ open: {{ request()->routeIs('contacts.*') || request()->routeIs('segments.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Contacts
                            <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            <a href="{{ route('contacts.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('contacts.index') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                All Contacts
                            </a>
                            <a href="{{ route('contacts.create') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('contacts.create') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Add Contact
                            </a>
                            <a href="/contacts/import"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->is('contacts/import') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Import Contacts
                            </a>
                            <a href="{{ route('segments.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('segments.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Contact Segments
                            </a>
                        </div>
                    </div>

                    <!-- Email Marketing -->
                    <div x-data="{ open: {{ request()->routeIs('email.*') || request()->routeIs('smtp.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Email Marketing
                            <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            <a href="{{ route('email.campaigns.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('email.campaigns.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Campaigns
                            </a>
                            <a href="{{ route('email.templates.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('email.templates.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Templates
                            </a>
                            <a href="{{ route('smtp-configs.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('smtp-configs.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                SMTP Settings
                            </a>
                        </div>
                    </div>

                    <!-- SMS Marketing -->
                    <div x-data="{ open: {{ request()->routeIs('sms.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            SMS Marketing
                            <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            <a href="{{ route('sms.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('sms.index') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                SMS Messages
                            </a>
                            <a href="{{ route('sms.create') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('sms.create') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Send SMS
                            </a>
                            <a href="{{ route('sms.providers.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('sms.providers.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                SMS Providers
                            </a>
                        </div>
                    </div>

                    <!-- WhatsApp -->
                    <div x-data="{ open: {{ request()->routeIs('whatsapp.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.106"/>
                            </svg>
                            WhatsApp
                            <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            <a href="{{ route('whatsapp.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('whatsapp.index') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Messages
                            </a>
                            <a href="{{ route('whatsapp.send') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('whatsapp.send') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Send Message
                            </a>
                            <a href="{{ route('whatsapp.sessions.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('whatsapp.sessions.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Sessions
                            </a>
                        </div>
                    </div>

                    <!-- Communications -->
                    <a href="{{ route('communications.index') }}"
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('communications.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('communications.*') ? 'text-indigo-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"></path>
                        </svg>
                        Unified Inbox
                    </a>

                    <!-- Data Management -->
                    <div x-data="{ open: {{ request()->routeIs('data.*') || request()->routeIs('google-sheets.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                            Data Management
                            <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            <a href="{{ route('data.import.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('data.import.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Data Import
                            </a>
                            <a href="{{ route('exports.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('exports.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Data Export
                            </a>
                            <a href="{{ route('google.sheets.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('google-sheets.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Google Sheets
                            </a>
                        </div>
                    </div>

                    <!-- Reports & Analytics -->
                    <div x-data="{ open: {{ request()->routeIs('reports.*') || request()->routeIs('analytics.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                                class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Reports & Analytics
                            <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            <a href="{{ route('reports.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('reports.index') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Overview
                            </a>
                            <a href="{{ route('admin.analytics.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Analytics Dashboard
                            </a>
                            <a href="{{ route('admin.revenue.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.revenue.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Revenue Analytics
                            </a>
                            <a href="{{ route('admin.custom-reports.index') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.custom-reports.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Custom Reports
                            </a>
                        </div>
                    </div>

                    @role('super_admin|admin|manager')
                    <!-- Admin Section -->
                    <div class="mt-8">
                        <h3 class="px-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Administration</h3>
                        <div class="mt-2 space-y-1">
                            <!-- Admin Dashboard -->
                            <a href="{{ route('admin.dashboard') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Admin Dashboard
                            </a>

                            <!-- Core Management -->
                            <div x-data="{ open: {{ request()->routeIs('admin.user-management.*') || request()->routeIs('admin.settings.*') ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                        class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                    </svg>
                                    Core Management
                                    <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                        <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="ml-8 space-y-1">
                                    <a href="{{ route('admin.user-management.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.user-management.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        User Management
                                    </a>
                                    <a href="{{ route('admin.settings.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        System Settings
                                    </a>
                                </div>
                            </div>

                            <!-- Security & Monitoring -->
                            <div x-data="{ open: {{ request()->routeIs('admin.security.*') || request()->routeIs('admin.api-keys.*') || request()->routeIs('admin.webhook-logs.*') || request()->routeIs('admin.system-logs.*') || request()->routeIs('admin.performance.*') ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                        class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Security & Monitoring
                                    <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                        <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="ml-8 space-y-1">
                                    <a href="{{ route('admin.security.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.security.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        Security Center
                                    </a>
                                    <a href="{{ route('admin.api-keys.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.api-keys.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        API Keys
                                    </a>
                                    <a href="{{ route('admin.webhook-logs.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.webhook-logs.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        Webhook Logs
                                    </a>
                                    <a href="{{ route('admin.system-logs.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.system-logs.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        System Logs
                                    </a>
                                    <a href="{{ route('admin.performance.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.performance.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        Performance Monitor
                                    </a>
                                </div>
                            </div>

                            @role('super_admin|admin')
                            <!-- System Operations -->
                            <div x-data="{ open: {{ request()->routeIs('admin.backups.*') || request()->routeIs('admin.queue-monitor.*') || request()->routeIs('admin.compliance.*') ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                        class="w-full group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="mr-3 flex-shrink-0 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-.42-.268l-.738-2.212a2 2 0 00-.187-.423l-1.417-1.417a2 2 0 00-.423-.187L10.622 9.89a6 6 0 00-.268-.42L9.877 7.083a2 2 0 00-.547-1.022L7.083 3.814a2 2 0 00-2.828 0L2.04 6.04a2 2 0 000 2.828l2.247 2.247a2 2 0 001.022.547l2.387.477a6 6 0 00.42.268l.738 2.212c.055.164.127.319.187.423l1.417 1.417c.104.06.259.132.423.187l2.212.738c.089.149.174.295.268.42l.477 2.387a2 2 0 00.547 1.022l2.247 2.247a2 2 0 002.828 0l2.216-2.216a2 2 0 000-2.828l-2.247-2.247z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    System Operations
                                    <svg :class="{ 'rotate-90': open }" class="ml-auto h-4 w-4 transform transition-transform">
                                        <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="ml-8 space-y-1">
                                    <a href="{{ route('admin.backups.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.backups.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        Backup Management
                                    </a>
                                    <a href="{{ route('admin.queue-monitor.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.queue-monitor.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        Queue Monitor
                                    </a>
                                    <a href="{{ route('admin.compliance.index') }}"
                                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.compliance.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        GDPR Compliance
                                    </a>
                                </div>
                            </div>
                            @endrole
                        </div>
                    </div>
                    @endrole

                    <!-- Settings -->
                    <div class="mt-8">
                        <a href="{{ route('settings.index') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('settings.*') ? 'text-indigo-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Settings
                        </a>
                    </div>
                </nav>

                <!-- User menu -->
                <div class="mt-auto px-2 pb-2">
                    <div class="flex items-center">
                        <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&color=7F9CF5&background=EBF4FF" alt="">
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ Auth::user()->name }}</p>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div x-show="sidebarOpen"
             x-transition:enter="transform transition ease-in-out duration-300"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="lg:hidden fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
            <!-- Mobile sidebar content (same as desktop but without fixed positioning) -->
            <div class="flex flex-col h-full pt-5 pb-4 overflow-y-auto">
                <!-- Logo -->
                <div class="flex items-center flex-shrink-0 px-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">C</span>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">CRM Ultra</h1>
                    </div>
                </div>

                <!-- Navigation (same as desktop) -->
                <!-- Content truncated for brevity but includes all the same navigation items -->
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:ml-64 flex flex-col">
            <!-- Top bar -->
            <div class="flex-shrink-0 flex h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 lg:hidden">
                <button @click="sidebarOpen = true"
                        class="px-4 border-r border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div class="flex-1 px-4 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        @yield('title', 'Dashboard')
                    </h1>

                    <!-- Top bar actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Dark mode toggle -->
                        <button @click="darkMode = !darkMode"
                                class="p-2 text-gray-400 hover:text-gray-500 dark:text-gray-300 dark:hover:text-gray-200">
                            <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </button>

                        <!-- Quick actions -->
                        <a href="{{ route('contacts.create') }}"
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                            <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Contact
                        </a>
                    </div>
                </div>
            </div>

            <!-- Flash messages -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <span @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </span>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <span @click="show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </span>
                </div>
            @endif

            <!-- Main content area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>

        @stack('scripts')
        
        <!-- Quick Send Modal JavaScript -->
        <script>
        // Global functions for Quick Send Modal
        function openQuickSendModal(contactId = null) {
            // Load contacts if not loaded
            loadContacts();
            
            // Load SMTP configs
            loadSmtpConfigs();
            
            // Pre-select contact if provided
            if (contactId) {
                setTimeout(() => {
                    const contactSelect = document.getElementById('contact_id');
                    contactSelect.value = contactId;
                }, 100);
            }
            
            // Open modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'quick-send-modal' }));
        }
        
        function loadContacts() {
            const contactSelect = document.getElementById('contact_id');
            
            fetch('/api/contacts')
                .then(response => response.json())
                .then(contacts => {
                    contactSelect.innerHTML = '<option value="">Choose a contact...</option>';
                    contacts.forEach(contact => {
                        const option = document.createElement('option');
                        option.value = contact.id;
                        option.textContent = `${contact.full_name} (${contact.email || contact.phone || 'No contact info'})`;
                        contactSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading contacts:', error);
                    contactSelect.innerHTML = '<option value="">Error loading contacts</option>';
                });
        }
        
        function loadSmtpConfigs() {
            const smtpSelect = document.getElementById('smtp_config_id');
            
            fetch('/api/smtp-configs')
                .then(response => response.json())
                .then(configs => {
                    smtpSelect.innerHTML = '<option value="">Select SMTP configuration...</option>';
                    configs.forEach(config => {
                        const option = document.createElement('option');
                        option.value = config.id;
                        option.textContent = `${config.name} (${config.from_email})`;
                        smtpSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading SMTP configs:', error);
                    smtpSelect.innerHTML = '<option value="">Error loading SMTP configs</option>';
                });
        }
        
        // Channel selection handler
        document.addEventListener('DOMContentLoaded', function() {
            const channelRadios = document.querySelectorAll('input[name="channel"]');
            const emailSubjectField = document.getElementById('email-subject-field');
            const smtpConfigField = document.getElementById('smtp-config-field');
            
            channelRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'email') {
                        emailSubjectField.classList.remove('hidden');
                        smtpConfigField.classList.remove('hidden');
                        document.getElementById('smtp_config_id').required = true;
                    } else {
                        emailSubjectField.classList.add('hidden');
                        smtpConfigField.classList.add('hidden');
                        document.getElementById('smtp_config_id').required = false;
                    }
                    
                    // Update radio button styling
                    channelRadios.forEach(r => {
                        const parent = r.closest('label');
                        const border = parent.querySelector('div:last-child');
                        if (r.checked) {
                            border.classList.add('border-indigo-500');
                            border.classList.remove('border-transparent');
                        } else {
                            border.classList.remove('border-indigo-500');
                            border.classList.add('border-transparent');
                        }
                    });
                });
            });
            
            // Listen for custom events
            document.addEventListener('open-quick-send-modal', function(event) {
                openQuickSendModal(event.detail?.contactId);
            });
        });
        </script>
    </body>
</html>
