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

        <!-- Chart.js for dashboard charts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                            <a href="{{ route('contacts.import') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('contacts.import') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                Import Contacts
                            </a>
                        </div>
                    </div>

                    <!-- Email -->
                    <div x-data="{ open: {{ request()->routeIs('email.*') ? 'true' : 'false' }} }">
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
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('smtp.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                SMTP Settings
                            </a>
                        </div>
                    </div>

                    <!-- SMS -->
                    <a href="{{ route('sms.index') }}"
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('sms.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('sms.*') ? 'text-indigo-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        SMS Marketing
                    </a>

                    <!-- WhatsApp -->
                    <a href="{{ route('whatsapp.index') }}"
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('whatsapp.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('whatsapp.*') ? 'text-indigo-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.106"/>
                        </svg>
                        WhatsApp
                    </a>

                    <!-- Communications -->
                    <a href="{{ route('communications.index') }}"
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('communications.*') ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('communications.*') ? 'text-indigo-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8z"></path>
                        </svg>
                        Unified Inbox
                    </a>
                </nav>

                <!-- User menu -->
                <div class="flex-shrink-0 px-4 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center" x-data="{ userMenuOpen: false }">
                        <div class="flex-shrink-0">
                            @auth
                                @if(Auth::user()->profile_photo_path ?? false)
                                    <img class="h-8 w-8 rounded-full" src="{{ Storage::url(Auth::user()->profile_photo_path) }}" alt="{{ Auth::user()->name }}">
                                @else
                                    <div class="h-8 w-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            @endauth
                        </div>
                        <div class="ml-3 flex-1">
                            @auth
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                            @endauth
                        </div>
                        <div class="ml-2 relative">
                            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>

                            <!-- User dropdown -->
                            <div x-show="userMenuOpen"
                                 @click.outside="userMenuOpen = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="origin-bottom-right absolute bottom-full right-0 mb-1 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 z-50">
                                <div class="py-1">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                        Profile Settings
                                    </a>
                                    <button @click="darkMode = !darkMode" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <span x-show="!darkMode">Enable Dark Mode</span>
                                        <span x-show="darkMode">Disable Dark Mode</span>
                                    </button>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:ml-64 flex flex-col flex-1">
            <!-- Top bar -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-600 focus:outline-none focus:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Page title -->
                    <div class="flex-1 lg:flex-initial">
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                            @yield('title', 'Dashboard')
                        </h1>
                    </div>

                    <!-- Top bar actions -->
                    <div class="flex items-center space-x-4">
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
    </body>
</html>
