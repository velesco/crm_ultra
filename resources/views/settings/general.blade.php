@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">General Settings</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Configure system-wide settings and preferences
                    </p>
                </div>
                <div>
                    <a href="{{ route('settings.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('settings.general.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Application Settings --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Application Settings</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Basic application configuration
                </p>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="app_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Application Name
                        </label>
                        <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('app_name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="app_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Application URL
                        </label>
                        <input type="url" id="app_url" name="app_url" value="{{ old('app_url', $settings['app_url']) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('app_url')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="app_timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Timezone
                        </label>
                        <select id="app_timezone" name="app_timezone"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="UTC" {{ $settings['app_timezone'] === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ $settings['app_timezone'] === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                            <option value="America/Chicago" {{ $settings['app_timezone'] === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                            <option value="America/Denver" {{ $settings['app_timezone'] === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                            <option value="America/Los_Angeles" {{ $settings['app_timezone'] === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                            <option value="Europe/London" {{ $settings['app_timezone'] === 'Europe/London' ? 'selected' : '' }}>London</option>
                            <option value="Europe/Paris" {{ $settings['app_timezone'] === 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                            <option value="Europe/Berlin" {{ $settings['app_timezone'] === 'Europe/Berlin' ? 'selected' : '' }}>Berlin</option>
                            <option value="Europe/Bucharest" {{ $settings['app_timezone'] === 'Europe/Bucharest' ? 'selected' : '' }}>Bucharest</option>
                            <option value="Asia/Tokyo" {{ $settings['app_timezone'] === 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                            <option value="Asia/Shanghai" {{ $settings['app_timezone'] === 'Asia/Shanghai' ? 'selected' : '' }}>Shanghai</option>
                            <option value="Australia/Sydney" {{ $settings['app_timezone'] === 'Australia/Sydney' ? 'selected' : '' }}>Sydney</option>
                        </select>
                        @error('app_timezone')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="app_locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Default Language
                        </label>
                        <select id="app_locale" name="app_locale"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="en" {{ $settings['app_locale'] === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ro" {{ $settings['app_locale'] === 'ro' ? 'selected' : '' }}>Romanian</option>
                            <option value="fr" {{ $settings['app_locale'] === 'fr' ? 'selected' : '' }}>French</option>
                            <option value="de" {{ $settings['app_locale'] === 'de' ? 'selected' : '' }}>German</option>
                            <option value="es" {{ $settings['app_locale'] === 'es' ? 'selected' : '' }}>Spanish</option>
                            <option value="it" {{ $settings['app_locale'] === 'it' ? 'selected' : '' }}>Italian</option>
                        </select>
                        @error('app_locale')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Email Configuration --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Email Configuration</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Default email settings for system notifications
                </p>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="mail_from_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            From Email Address
                        </label>
                        <input type="email" id="mail_from_address" name="mail_from_address" 
                               value="{{ old('mail_from_address', $settings['mail_from_address']) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('mail_from_address')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="mail_from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            From Name
                        </label>
                        <input type="text" id="mail_from_name" name="mail_from_name" 
                               value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('mail_from_name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Display & Formatting --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Display & Formatting</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Configure how data is displayed throughout the application
                </p>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="pagination_per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Items per Page
                        </label>
                        <select id="pagination_per_page" name="pagination_per_page"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="10" {{ $settings['pagination_per_page'] == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $settings['pagination_per_page'] == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $settings['pagination_per_page'] == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $settings['pagination_per_page'] == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        @error('pagination_per_page')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="default_currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Default Currency
                        </label>
                        <select id="default_currency" name="default_currency"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="USD" {{ $settings['default_currency'] === 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ $settings['default_currency'] === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            <option value="GBP" {{ $settings['default_currency'] === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                            <option value="RON" {{ $settings['default_currency'] === 'RON' ? 'selected' : '' }}>RON (lei)</option>
                            <option value="JPY" {{ $settings['default_currency'] === 'JPY' ? 'selected' : '' }}>JPY (¥)</option>
                        </select>
                        @error('default_currency')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Date Format
                        </label>
                        <select id="date_format" name="date_format"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Y-m-d" {{ $settings['date_format'] === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                            <option value="m/d/Y" {{ $settings['date_format'] === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            <option value="d/m/Y" {{ $settings['date_format'] === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            <option value="d.m.Y" {{ $settings['date_format'] === 'd.m.Y' ? 'selected' : '' }}>DD.MM.YYYY</option>
                            <option value="M j, Y" {{ $settings['date_format'] === 'M j, Y' ? 'selected' : '' }}>Mon DD, YYYY</option>
                        </select>
                        @error('date_format')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Security & Features --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Security & Features</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Control access and enable/disable features
                </p>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Feature toggles --}}
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" id="allow_registration" name="allow_registration" value="1"
                                   {{ $settings['allow_registration'] ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="allow_registration" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Allow user registration
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="require_email_verification" name="require_email_verification" value="1"
                                   {{ $settings['require_email_verification'] ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="require_email_verification" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Require email verification
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="enable_google_login" name="enable_google_login" value="1"
                                   {{ $settings['enable_google_login'] ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="enable_google_login" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Enable Google OAuth login
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="enable_two_factor" name="enable_two_factor" value="1"
                                   {{ $settings['enable_two_factor'] ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="enable_two_factor" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                Enable two-factor authentication
                            </label>
                        </div>
                    </div>

                    {{-- Limits --}}
                    <div class="space-y-4">
                        <div>
                            <label for="max_file_upload_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Max File Upload Size (MB)
                            </label>
                            <input type="number" id="max_file_upload_size" name="max_file_upload_size" min="1" max="100"
                                   value="{{ old('max_file_upload_size', $settings['max_file_upload_size']) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('max_file_upload_size')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_bulk_operations" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Max Bulk Operations
                            </label>
                            <input type="number" id="max_bulk_operations" name="max_bulk_operations" min="100" max="10000"
                                   value="{{ old('max_bulk_operations', $settings['max_bulk_operations']) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('max_bulk_operations')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="session_timeout" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Session Timeout (minutes)
                            </label>
                            <select id="session_timeout" name="session_timeout"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="30" {{ $settings['session_timeout'] == 30 ? 'selected' : '' }}>30 minutes</option>
                                <option value="60" {{ $settings['session_timeout'] == 60 ? 'selected' : '' }}>1 hour</option>
                                <option value="120" {{ $settings['session_timeout'] == 120 ? 'selected' : '' }}>2 hours</option>
                                <option value="240" {{ $settings['session_timeout'] == 240 ? 'selected' : '' }}>4 hours</option>
                                <option value="480" {{ $settings['session_timeout'] == 480 ? 'selected' : '' }}>8 hours</option>
                                <option value="1440" {{ $settings['session_timeout'] == 1440 ? 'selected' : '' }}>24 hours</option>
                            </select>
                            @error('session_timeout')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end space-x-3">
            <a href="{{ route('settings.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection