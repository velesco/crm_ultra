@extends('layouts.app')

@section('title', 'Test SMTP Form')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Test SMTP Form</h1>

        <!-- Display Errors and Success Messages -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <div class="font-medium">Errors:</div>
                <ul class="mt-1 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('smtp-configs.store') }}" onsubmit="console.log('Form submitted!'); return true;">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="name">Configuration Name</label>
                        <input type="text" name="name" id="name" value="Test Config" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="host">SMTP Host</label>
                        <input type="text" name="host" id="host" value="smtp.example.com" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="port">Port</label>
                        <input type="number" name="port" id="port" value="587" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="encryption">Encryption</label>
                        <select name="encryption" id="encryption" required class="w-full px-3 py-2 border rounded">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="test@example.com" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" value="testpass123" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="from_email">From Email</label>
                        <input type="email" name="from_email" id="from_email" value="test@example.com" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="from_name">From Name</label>
                        <input type="text" name="from_name" id="from_name" value="Test Name" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="daily_limit">Daily Limit</label>
                        <input type="number" name="daily_limit" id="daily_limit" value="500" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="hourly_limit">Hourly Limit</label>
                        <input type="number" name="hourly_limit" id="hourly_limit" value="50" required class="w-full px-3 py-2 border rounded">
                    </div>
                    
                    <div>
                        <label for="priority">Priority</label>
                        <input type="number" name="priority" id="priority" value="10" min="1" max="100" required class="w-full px-3 py-2 border rounded">
                    </div>

                    <input type="hidden" name="provider" value="custom">
                    
                    <div>
                        <input type="checkbox" name="is_active" id="is_active" checked>
                        <label for="is_active">Active</label>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Test form loaded');
    
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        console.log('Form is being submitted...');
        console.log('Action:', form.action);
        console.log('Method:', form.method);
        
        // Log all form data
        const formData = new FormData(form);
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }
    });
});
</script>
@endsection
