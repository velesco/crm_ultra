@extends('layouts.guest')

@section('title', 'Înregistrare')

@section('content')
<div class="text-center mb-8">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Creează un cont nou</h2>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Începe să folosești CRM Ultra astăzi</p>
</div>

<!-- Google OAuth Register -->
<div class="mb-6">
    <a href="{{ route('auth.google') }}" 
       class="w-full flex justify-center items-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200 group">
        <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        <span class="group-hover:text-gray-900 dark:group-hover:text-white">Înregistrează-te cu Google</span>
    </a>
</div>

<!-- Separator -->
<div class="relative mb-6">
    <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
    </div>
    <div class="relative flex justify-center text-sm">
        <span class="bg-white dark:bg-gray-800 px-4 text-gray-500 dark:text-gray-400">sau cu email</span>
    </div>
</div>

<!-- Register Form -->
<form method="POST" action="{{ route('register') }}" class="space-y-6">
    @csrf

    <!-- Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Nume complet
        </label>
        <div class="relative">
            <input id="name" 
                   name="name" 
                   type="text" 
                   value="{{ old('name') }}"
                   required 
                   autofocus 
                   autocomplete="name"
                   placeholder="Nume Prenume"
                   class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-300 text-sm">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
        @error('name')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Email -->
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
                   autocomplete="username"
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
            Parola
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"/>
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
            Confirmă parola
        </label>
        <div class="relative">
            <input id="password_confirmation" 
                   name="password_confirmation" 
                   :type="showConfirmPassword ? 'text' : 'password'"
                   required 
                   autocomplete="new-password"
                   placeholder="Confirmă parola"
                   class="block w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-300 text-sm">
            <button type="button" 
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg x-show="!showConfirmPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"/>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </span>
            Creează contul
        </button>
    </div>
</form>

<!-- Login Link -->
<div class="mt-6 text-center">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Ai deja un cont? 
        <a href="{{ route('login') }}" 
           class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
            Autentifică-te aici
        </a>
    </p>
</div>
@endsection
