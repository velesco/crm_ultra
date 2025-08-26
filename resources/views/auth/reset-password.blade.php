@extends('layouts.guest')

@section('title', 'Setare Parolă Nouă')

<div class="text-center mb-8">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Setează o parolă nouă</h2>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Introdu o parolă nouă și sigură pentru contul tău
    </p>
</div>

<!-- Reset Form -->
<form method="POST" action="{{ route('password.store') }}" class="space-y-6">
    @csrf

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <!-- Email Address -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Adresa de email
        </label>
        <div class="relative">
            <input id="email" 
                   name="email" 
                   type="email" 
                   value="{{ old('email', $request->email) }}"
                   required 
                   autofocus
                   autocomplete="username"
                   readonly
                   class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-700 dark:text-gray-300 text-sm cursor-not-allowed">
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

    <!-- Password -->
    <div x-data="{ showPassword: false, password: '', strength: 0 }" x-init="$watch('password', value => { 
        let score = 0;
        if (value.length >= 8) score++;
        if (/[a-z]/.test(value)) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[^a-zA-Z0-9]/.test(value)) score++;
        strength = score;
    })">
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Parola nouă
        </label>
        <div class="relative">
            <input id="password" 
                   name="password" 
                   x-model="password"
                   :type="showPassword ? 'text' : 'password'"
                   required 
                   autocomplete="new-password"
                   placeholder="Creează o parolă sigură"
                   class="block w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-300 text-sm">
            <button type="button" 
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg x-show="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                </svg>
            </button>
        </div>
        
        <!-- Password Strength Indicator -->
        <div x-show="password.length > 0" class="mt-2">
            <div class="flex space-x-1">
                <div :class="strength >= 1 ? 'bg-red-500' : 'bg-gray-200 dark:bg-gray-600'" class="h-1 w-1/5 rounded-full"></div>
                <div :class="strength >= 2 ? 'bg-orange-500' : 'bg-gray-200 dark:bg-gray-600'" class="h-1 w-1/5 rounded-full"></div>
                <div :class="strength >= 3 ? 'bg-yellow-500' : 'bg-gray-200 dark:bg-gray-600'" class="h-1 w-1/5 rounded-full"></div>
                <div :class="strength >= 4 ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-600'" class="h-1 w-1/5 rounded-full"></div>
                <div :class="strength >= 5 ? 'bg-green-600' : 'bg-gray-200 dark:bg-gray-600'" class="h-1 w-1/5 rounded-full"></div>
            </div>
            <p class="text-xs mt-1" 
               :class="strength < 3 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                <span x-show="strength < 2">Parolă slabă</span>
                <span x-show="strength === 2">Parolă acceptabilă</span>
                <span x-show="strength === 3">Parolă bună</span>
                <span x-show="strength === 4">Parolă foarte bună</span>
                <span x-show="strength === 5">Parolă excelentă</span>
            </p>
        </div>
        
        @error('password')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div x-data="{ showConfirmPassword: false }">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Confirmă parola nouă
        </label>
        <div class="relative">
            <input id="password_confirmation" 
                   name="password_confirmation" 
                   :type="showConfirmPassword ? 'text' : 'password'"
                   required 
                   autocomplete="new-password"
                   placeholder="Confirmă parola nouă"
                   class="block w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-300 text-sm">
            <button type="button" 
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg x-show="!showConfirmPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="showConfirmPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                </svg>
            </button>
        </div>
        @error('password_confirmation')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Submit Button -->
    <div>
        <button type="submit" 
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-[1.02]">
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-indigo-300 group-hover:text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </span>
            Resetează parola
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
