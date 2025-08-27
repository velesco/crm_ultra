@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Security Settings</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage your account security and authentication methods
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
        {{-- Security Overview Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Security Status</h3>
                
                <div class="space-y-4">
                    {{-- Security Score --}}
                    <div class="text-center p-4 rounded-lg bg-green-50 dark:bg-green-900/20">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ $securityInfo['two_factor_enabled'] ? '85%' : '65%' }}
                        </div>
                        <div class="text-sm text-green-700 dark:text-green-300">Security Score</div>
                    </div>

                    {{-- Security Checklist --}}
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Password protected</span>
                        </div>

                        <div class="flex items-center">
                            @if($securityInfo['two_factor_enabled'])
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            @endif
                            <span class="text-sm text-gray-600 dark:text-gray-400">Two-factor authentication</span>
                        </div>

                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Email verified</span>
                        </div>
                    </div>

                    {{-- Last Activity --}}
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Recent Activity</h4>
                        <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                            @if($securityInfo['last_login_at'])
                                <div>Last login: {{ $securityInfo['last_login_at']->diffForHumans() }}</div>
                            @endif
                            @if($securityInfo['last_login_ip'])
                                <div>IP: {{ $securityInfo['last_login_ip'] }}</div>
                            @endif
                            <div>Password changed: {{ $securityInfo['password_changed_at']->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Security Settings --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Change Password --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Change Password</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Update your password to keep your account secure
                    </p>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('settings.security.password') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Current Password
                            </label>
                            <input type="password" id="current_password" name="current_password" 
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('current_password')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    New Password
                                </label>
                                <input type="password" id="password" name="password" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Confirm New Password
                                </label>
                                <input type="password" id="password_confirmation" name="password_confirmation" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Two-Factor Authentication --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Two-Factor Authentication</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Add an extra layer of security to your account
                    </p>
                </div>
                <div class="p-6">
                    @if($securityInfo['two_factor_enabled'])
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Two-factor authentication is enabled</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your account is protected with 2FA</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('settings.security.two-factor.disable') }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                        onclick="disableTwoFactor(this.form)"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    Disable 2FA
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <svg class="w-8 h-8 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Two-factor authentication is disabled</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Enable 2FA to add extra security to your account</p>
                                </div>
                            </div>
                            <button type="button" 
                                    onclick="enableTwoFactor()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Enable Two-Factor Authentication
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Active Sessions --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Active Sessions</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Monitor and manage your active login sessions
                    </p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($securityInfo['active_sessions'] as $session)
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $session['user_agent'] }}
                                            @if($session['current'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Current Session
                                                </span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $session['ip_address'] }} • Last active {{ $session['last_activity']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                @if(!$session['current'])
                                    <button type="button" 
                                            onclick="revokeSession('{{ $session['id'] }}')"
                                            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        Revoke
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <button type="button" 
                                onclick="revokeAllSessions()"
                                class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                            Revoke all other sessions
                        </button>
                    </div>
                </div>
            </div>

            {{-- Login Attempts --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Login Attempts</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Monitor login attempts to your account
                    </p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($securityInfo['login_attempts'] as $attempt)
                            <div class="flex items-center justify-between p-3 {{ $attempt['success'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded-lg">
                                <div class="flex items-center">
                                    @if($attempt['success'])
                                        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium {{ $attempt['success'] ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }}">
                                            {{ $attempt['success'] ? 'Successful login' : 'Failed login attempt' }}
                                        </p>
                                        <p class="text-xs {{ $attempt['success'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $attempt['ip'] }} • {{ $attempt['created_at']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Two-Factor Authentication Modal --}}
<div id="twoFactorModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                            Enable Two-Factor Authentication
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Scan the QR code with your authenticator app, then enter the verification code.
                            </p>
                            <div id="qr-code" class="mt-4 text-center hidden">
                                <!-- QR Code will be inserted here -->
                            </div>
                            <div id="recovery-codes" class="mt-4 hidden">
                                <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Recovery Codes:</p>
                                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded text-xs font-mono">
                                    <!-- Recovery codes will be inserted here -->
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Save these recovery codes in a safe place. You can use them to access your account if you lose your device.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" 
                        onclick="closeTwoFactorModal()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Done
                </button>
                <button type="button" 
                        onclick="closeTwoFactorModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function enableTwoFactor() {
    fetch('{{ route("settings.security.two-factor.enable") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.secret) {
            // Show QR code
            document.getElementById('qr-code').innerHTML = `
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(data.qr_code_url)}" alt="QR Code" class="mx-auto">
            `;
            document.getElementById('qr-code').classList.remove('hidden');
            
            // Show recovery codes
            const recoveryCodesDiv = document.getElementById('recovery-codes').querySelector('.bg-gray-100');
            recoveryCodesDiv.innerHTML = data.recovery_codes.join('<br>');
            document.getElementById('recovery-codes').classList.remove('hidden');
            
            // Show modal
            document.getElementById('twoFactorModal').classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to enable two-factor authentication');
    });
}

function disableTwoFactor(form) {
    const password = prompt('Please enter your password to disable two-factor authentication:');
    if (password) {
        const passwordInput = document.createElement('input');
        passwordInput.type = 'hidden';
        passwordInput.name = 'password';
        passwordInput.value = password;
        form.appendChild(passwordInput);
        form.submit();
    }
}

function closeTwoFactorModal() {
    document.getElementById('twoFactorModal').classList.add('hidden');
    location.reload(); // Reload to show updated 2FA status
}

function revokeSession(sessionId) {
    if (confirm('Are you sure you want to revoke this session?')) {
        fetch(`/settings/security/sessions/${sessionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(() => location.reload())
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to revoke session');
        });
    }
}

function revokeAllSessions() {
    if (confirm('Are you sure you want to revoke all other sessions? This will log you out from all other devices.')) {
        fetch('/settings/security/sessions/revoke-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(() => location.reload())
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to revoke sessions');
        });
    }
}
</script>
@endpush