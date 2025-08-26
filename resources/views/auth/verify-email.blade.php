@extends('layouts.guest')

@section('title', 'Verifică Email')

@section('content')
<div class="text-center mb-8">
    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/50 dark:to-purple-900/50 rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Verifică adresa de email</h2>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Îți mulțumim că te-ai înregistrat! Înainte de a începe, te rugăm să verifici adresa de email făcând clic pe linkul pe care ți l-am trimis prin email.
    </p>
</div>

@if (session('status') == 'verification-link-sent')
    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                Un nou link de verificare a fost trimis pe adresa de email furnizată la înregistrare.
            </p>
        </div>
    </div>
@endif

<div class="space-y-4">
    <!-- Resend Verification Email -->
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" 
                class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-[1.02]">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Retrimite email-ul de verificare
        </button>
    </form>

    <!-- Log Out -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" 
                class="w-full flex justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Delogare
        </button>
    </form>
</div>

<!-- Help Text -->
<div class="mt-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
        Nu ai primit email-ul? Verifică folderul de spam sau retrimite linkul de verificare.
    </p>
</div>
@endsection
