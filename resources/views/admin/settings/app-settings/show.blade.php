@extends('layouts.app')

@section('title', 'View Application Setting')
@section('page-title', 'View Application Setting')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.setting-detail-card {
    transition: all 0.2s ease-in-out;
}
.detail-row {
    @apply flex justify-between items-start py-3 border-b border-gray-200 dark:border-gray-700;
}
.detail-label {
    @apply font-medium text-gray-600 dark:text-gray-300 w-1/3;
}
.detail-value {
    @apply text-gray-900 dark:text-white w-2/3 break-words;
}
.badge {
    @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
}
.badge-success {
    @apply bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100;
}
.badge-danger {
    @apply bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100;
}
.badge-warning {
    @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100;
}
.badge-info {
    @apply bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100;
}
.code-block {
    @apply bg-gray-100 dark:bg-gray-700 rounded-md px-3 py-2 font-mono text-sm border;
}
</style>
@endpush

@section('header-actions')
<div class="flex items-center space-x-3">
    <a href="{{ route('admin.app-settings.index', ['category' => $appSetting->category]) }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Settings
    </a>
    
    <a href="{{ route('admin.app-settings.edit', $appSetting) }}" 
       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-edit mr-2"></i>
        Edit Setting
    </a>
    
    <button onclick="testConnection()" 
            id="testConnectionBtn"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-plug mr-2"></i>
        Test Connection
    </button>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Setting Details -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg setting-detail-card">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-cog mr-3 text-indigo-500"></i>
                    {{ $appSetting->label }}
                    @if($appSetting->is_encrypted)
                        <span class="badge badge-warning ml-3">
                            <i class="fas fa-lock mr-1"></i>
                            Encrypted
                        </span>
                    @endif
                    @if($appSetting->is_env_synced)
                        <span class="badge badge-info ml-2">
                            <i class="fas fa-sync mr-1"></i>
                            Synced to .env
                        </span>
                    @endif
                </h3>
                <div class="flex items-center space-x-2">
                    <span class="badge {{ $appSetting->is_active ? 'badge-success' : 'badge-danger' }}">
                        <i class="fas fa-{{ $appSetting->is_active ? 'check-circle' : 'times-circle' }} mr-1"></i>
                        {{ $appSetting->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Basic Information -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Basic Information
                    </h4>
                    
                    <div class="space-y-0">
                        <div class="detail-row">
                            <div class="detail-label">Setting Key:</div>
                            <div class="detail-value">
                                <code class="code-block">{{ $appSetting->key }}</code>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Category:</div>
                            <div class="detail-value">
                                <span class="badge badge-info">
                                    <i class="fas fa-{{ getCategoryIcon($appSetting->category) }} mr-1"></i>
                                    {{ ucfirst($appSetting->category) }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Data Type:</div>
                            <div class="detail-value">
                                <span class="badge badge-info">
                                    <i class="fas fa-{{ getTypeIcon($appSetting->type) }} mr-1"></i>
                                    {{ ucfirst($appSetting->type) }}
                                </span>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Sort Order:</div>
                            <div class="detail-value">{{ $appSetting->sort_order ?? 0 }}</div>
                        </div>

                        @if($appSetting->description)
                            <div class="detail-row">
                                <div class="detail-label">Description:</div>
                                <div class="detail-value">{{ $appSetting->description }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Value & Configuration -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <i class="fas fa-sliders-h mr-2 text-green-500"></i>
                        Value & Configuration
                    </h4>
                    
                    <div class="space-y-0">
                        <div class="detail-row">
                            <div class="detail-label">Current Value:</div>
                            <div class="detail-value">
                                @if($appSetting->type === 'encrypted')
                                    <span class="text-yellow-600 dark:text-yellow-400">
                                        <i class="fas fa-lock mr-1"></i>
                                        [Encrypted - Hidden for security]
                                    </span>
                                @elseif($appSetting->type === 'boolean')
                                    <span class="badge {{ $appSetting->value ? 'badge-success' : 'badge-danger' }}">
                                        <i class="fas fa-{{ $appSetting->value ? 'check' : 'times' }} mr-1"></i>
                                        {{ $appSetting->value ? 'Enabled' : 'Disabled' }}
                                    </span>
                                @elseif($appSetting->type === 'json' && is_array($appSetting->value))
                                    <pre class="code-block overflow-x-auto">{{ json_encode($appSetting->value, JSON_PRETTY_PRINT) }}</pre>
                                @elseif(empty($appSetting->value))
                                    <span class="text-gray-400 italic">Not set</span>
                                @else
                                    <code class="code-block">{{ $appSetting->value }}</code>
                                @endif
                            </div>
                        </div>

                        @if($appSetting->is_env_synced && $appSetting->env_key)
                            <div class="detail-row">
                                <div class="detail-label">Environment Key:</div>
                                <div class="detail-value">
                                    <code class="code-block">{{ $appSetting->env_key }}</code>
                                    <div class="mt-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            This setting syncs with .env file
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($appSetting->validation_rules && count($appSetting->validation_rules) > 0)
                            <div class="detail-row">
                                <div class="detail-label">Validation Rules:</div>
                                <div class="detail-value">
                                    @foreach($appSetting->validation_rules as $rule)
                                        <span class="badge badge-info mr-1 mb-1">{{ $rule }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($appSetting->options && count($appSetting->options) > 0)
                            <div class="detail-row">
                                <div class="detail-label">Available Options:</div>
                                <div class="detail-value">
                                    <div class="space-y-1">
                                        @foreach($appSetting->options as $value => $label)
                                            <div class="flex items-center">
                                                <code class="code-block mr-2">{{ $value }}</code>
                                                <span class="text-sm">→ {{ $label }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metadata & Timestamps -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg setting-detail-card">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <i class="fas fa-clock mr-2 text-purple-500"></i>
                Metadata & History
            </h3>
        </div>

        <div class="px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-0">
                    <div class="detail-row">
                        <div class="detail-label">Created At:</div>
                        <div class="detail-value">
                            <div class="flex flex-col">
                                <span>{{ $appSetting->created_at->format('Y-m-d H:i:s') }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $appSetting->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Last Updated:</div>
                        <div class="detail-value">
                            <div class="flex flex-col">
                                <span>{{ $appSetting->updated_at->format('Y-m-d H:i:s') }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $appSetting->updated_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-0">
                    <div class="detail-row">
                        <div class="detail-label">Setting ID:</div>
                        <div class="detail-value">
                            <code class="code-block">#{{ $appSetting->id }}</code>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-label">Auto-Generated:</div>
                        <div class="detail-value">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <i class="fas fa-robot mr-1"></i>
                                {{ $appSetting->env_key ? 'Has .env integration' : 'Database only' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg setting-detail-card">
        <div class="px-6 py-4">
            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                <i class="fas fa-tools mr-2 text-orange-500"></i>
                Available Actions
            </h4>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.app-settings.edit', $appSetting) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Setting
                </a>
                
                @if($appSetting->is_env_synced)
                    <button onclick="syncToEnv()" 
                            id="syncToEnvBtn"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync mr-2"></i>
                        Force Sync to .env
                    </button>
                @endif
                
                @if(in_array($appSetting->category, ['google', 'sms', 'whatsapp', 'email']))
                    <button onclick="testConnection()" 
                            id="testConnectionBtn"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-plug mr-2"></i>
                        Test Connection
                    </button>
                @endif

                <button onclick="exportSetting()" 
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export Setting
                </button>

                <button onclick="deleteSetting()" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-2"></i>
                    Delete Setting
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-2">Delete Setting</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Are you sure you want to delete the setting <strong>"{{ $appSetting->label }}"</strong>? 
                    This action cannot be undone and will also remove the setting from your .env file.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDelete"
                        class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Delete
                </button>
                <button onclick="closeDeleteModal()"
                        class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Test connection functionality
function testConnection() {
    const button = document.getElementById('testConnectionBtn');
    if (!button) return;
    
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
    
    fetch(`{{ route('admin.app-settings.test-connection', ':provider') }}`.replace(':provider', '{{ $appSetting->category }}'), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            key: '{{ $appSetting->key }}',
            value: '{{ $appSetting->type === 'encrypted' ? '' : $appSetting->value }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Connection test successful!', 'success');
        } else {
            showToast('Connection test failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Connection test error.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Sync to .env functionality
function syncToEnv() {
    const button = document.getElementById('syncToEnvBtn');
    if (!button) return;
    
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Syncing...';
    
    fetch('{{ route("admin.app-settings.sync-to-env") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            setting_id: {{ $appSetting->id }}
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Successfully synced to .env file!', 'success');
        } else {
            showToast('Sync failed: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Sync error occurred.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Export setting functionality
function exportSetting() {
    window.location.href = '{{ route("admin.app-settings.export", $appSetting->category) }}?setting_id={{ $appSetting->id }}';
}

// Delete setting functionality
function deleteSetting() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Confirm delete
document.getElementById('confirmDelete').addEventListener('click', function() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.app-settings.destroy", $appSetting) }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
    
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    
    form.appendChild(csrfToken);
    form.appendChild(methodField);
    document.body.appendChild(form);
    form.submit();
});

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
    toast.className = `fixed top-4 right-4 z-50 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after delay
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush

@php
function getCategoryIcon($category) {
    $icons = [
        'google' => 'google',
        'sms' => 'sms',
        'whatsapp' => 'whatsapp',
        'email' => 'envelope',
        'general' => 'cog',
        'database' => 'database'
    ];
    return $icons[$category] ?? 'cog';
}

function getTypeIcon($type) {
    $icons = [
        'string' => 'font',
        'boolean' => 'toggle-on',
        'integer' => 'hashtag',
        'float' => 'calculator',
        'json' => 'code',
        'encrypted' => 'lock'
    ];
    return $icons[$type] ?? 'question';
}
@endphp
