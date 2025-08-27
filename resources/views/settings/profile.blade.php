@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Profile Settings</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage your personal information and preferences
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- User Stats Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                {{-- Avatar Section --}}
                <div class="text-center">
                    <div class="mb-4">
                        @if($user->avatar)
                            <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-24 h-24 rounded-full mx-auto object-cover">
                        @else
                            <div class="w-24 h-24 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center mx-auto">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <form id="avatar-form" action="{{ route('settings.profile.avatar') }}" method="POST" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" name="avatar" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
                    </form>
                    
                    <div class="flex justify-center space-x-2">
                        <button onclick="document.querySelector('input[name=avatar]').click()" 
                                class="text-xs px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Upload
                        </button>
                        @if($user->avatar)
                            <form action="{{ route('settings.profile.avatar.delete') }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-xs px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700"
                                        onclick="return confirm('Are you sure you want to delete your avatar?')">
                                    Remove
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- User Stats --}}
                <div class="mt-8 space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Activity Overview</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Campaigns Created</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($userStats['campaigns_created']) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Contacts Created</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($userStats['contacts_created']) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-400">SMS Sent</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($userStats['sms_sent']) }}</span>
                        </div>

                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                <div>Member since: {{ $userStats['account_created']->format('M d, Y') }}</div>
                                @if($userStats['last_login'])
                                    <div>Last login: {{ $userStats['last_login']->diffForHumans() }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Profile Form --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('settings.profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Personal Information --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Personal Information</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Update your personal details and contact information
                        </p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    First Name
                                </label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="{{ old('first_name', $user->first_name) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('first_name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Last Name
                                </label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="{{ old('last_name', $user->last_name) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('last_name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Phone Number
                                </label>
                                <input type="tel" id="phone" name="phone" 
                                       value="{{ old('phone', $user->phone) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('phone')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="company" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Company
                                </label>
                                <input type="text" id="company" name="company" 
                                       value="{{ old('company', $user->company) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('company')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="job_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Job Title
                                </label>
                                <input type="text" id="job_title" name="job_title" 
                                       value="{{ old('job_title', $user->job_title) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('job_title')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Bio
                            </label>
                            <textarea id="bio" name="bio" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                      placeholder="Tell us a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Preferences --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Preferences</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Customize your CRM experience
                        </p>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Theme
                                </label>
                                <select id="theme" name="theme"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="light" {{ $preferences['theme'] === 'light' ? 'selected' : '' }}>Light</option>
                                    <option value="dark" {{ $preferences['theme'] === 'dark' ? 'selected' : '' }}>Dark</option>
                                    <option value="auto" {{ $preferences['theme'] === 'auto' ? 'selected' : '' }}>Auto</option>
                                </select>
                                @error('theme')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Language
                                </label>
                                <select id="language" name="language"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="en" {{ $preferences['language'] === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="ro" {{ $preferences['language'] === 'ro' ? 'selected' : '' }}>Romanian</option>
                                    <option value="fr" {{ $preferences['language'] === 'fr' ? 'selected' : '' }}>French</option>
                                    <option value="de" {{ $preferences['language'] === 'de' ? 'selected' : '' }}>German</option>
                                    <option value="es" {{ $preferences['language'] === 'es' ? 'selected' : '' }}>Spanish</option>
                                </select>
                                @error('language')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Timezone
                                </label>
                                <select id="timezone" name="timezone"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="UTC" {{ $preferences['timezone'] === 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ $preferences['timezone'] === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="Europe/London" {{ $preferences['timezone'] === 'Europe/London' ? 'selected' : '' }}>London</option>
                                    <option value="Europe/Bucharest" {{ $preferences['timezone'] === 'Europe/Bucharest' ? 'selected' : '' }}>Bucharest</option>
                                    <option value="Asia/Tokyo" {{ $preferences['timezone'] === 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                                </select>
                                @error('timezone')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="dashboard_layout" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Dashboard Layout
                                </label>
                                <select id="dashboard_layout" name="dashboard_layout"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="grid" {{ $preferences['dashboard_layout'] === 'grid' ? 'selected' : '' }}>Grid</option>
                                    <option value="list" {{ $preferences['dashboard_layout'] === 'list' ? 'selected' : '' }}>List</option>
                                    <option value="cards" {{ $preferences['dashboard_layout'] === 'cards' ? 'selected' : '' }}>Cards</option>
                                </select>
                                @error('dashboard_layout')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Notification Preferences --}}
                        <div class="space-y-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white">Notifications</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="notifications_email" name="notifications_email" value="1"
                                           {{ $preferences['notifications_email'] ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                                    <label for="notifications_email" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                        Email notifications
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" id="notifications_browser" name="notifications_browser" value="1"
                                           {{ $preferences['notifications_browser'] ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                                    <label for="notifications_browser" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                        Browser notifications
                                    </label>
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
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection