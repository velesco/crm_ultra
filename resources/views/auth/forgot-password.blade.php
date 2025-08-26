@extends('layouts.guest')

@section('title', 'Resetare Parolă')

@section('content')
<div class="text-center mb-8">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Ai uitat parola?</h2>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Nu-ți face griji! Introdu adresa de email și îți vom trimite un link pentru resetarea parolei.
    </p>
</div>

<!-- Session Status -->
<x-auth-session-status class="mb-6" :status="session('status')" />

<!-- Reset Form -->
<form method="POST" action="{{ route('password.email') }}" class="space-y-6">
    @csrf

    <!-- Email Address -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Adresa de email
        </label>
        <div class="relative">
            <input id="email" 
                   name="email" 
                   type="email" 
                   value="{{ old('email') }}"
                   required 
                   autofocus
                   placeholder="nume@companie.ro"
                   class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-300 text-sm">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                </svg>
            </div>
        </div>
        @error('email')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <div>
        <button type="submit" 
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-[1.02]">
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-indigo-300 group-hover:text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </span>
            Trimite link-ul de resetare
        </button>
    </div>
</form>

<!-- Back to Login -->
<div class="mt-6 text-center">
    <a href="{{ route('login') }}" 
       class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 transition-colors">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Înapoi la autentificare
    </a>
</div>
@endsection
