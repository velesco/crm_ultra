@extends('layouts.app')

@section('title', 'Application Settings')
@section('page-title', 'Application Settings')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.setting-card {
    transition: all 0.2s ease-in-out;
}
.setting-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.setting-input {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white;
}
.setting-label {
    @apply block text-sm font-medium text-gray-700 dark:text-gray-300;
}
.category-icon {
    width: 24px;
    height: 24px;
}
</style>
@endpush

@section('header-actions')
<div class="flex items-center space-x-3">
    <div class="flex items-center space-x-2">
        <label for="categoryFilter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Category:</label>
        <select id="categoryFilter" onchange="filterByCategory()" 
                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
            <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>
                    {{ ucfirst($cat) }}
                </option>
            @endforeach
        </select>
    </div>
    
    <a href="{{ route('admin.app-settings.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-plus mr-2"></i>
        Add Setting
    </a>
    
    <button onclick="initializeDefaults()" 
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
        <i class="fas fa-magic mr-2"></i>
        Initialize Defaults
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    
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

    @if($settings->isEmpty())
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8 text-center">
            <i class="fas fa-cog text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Settings Found</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">
                Get started by creating your first application setting or initialize with defaults.
            </p>
            <div class="space-x-4">
                <a href="{{ route('admin.app-settings.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    <i class="fas fa-plus mr-2"></i>
                    Create Setting
                </a>
                <button onclick="initializeDefaults()" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <i class="fas fa-magic mr-2"></i>
                    Initialize Defaults
                </button>
            </div>
        </div>
    @else
        @foreach($settings as $categoryName => $categorySettings)
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg setting-card">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                            <i class="category-icon fas fa-{{ $this->getCategoryIcon($categoryName) }} mr-3 text-indigo-500"></i>
                            {{ ucfirst($categoryName) }} Settings
                        </h3>
                        <button onclick="bulkUpdate('{{ $categoryName }}')" 
                                class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                            <i class="fas fa-save mr-1"></i>
                            Save All
                        </button>
                    </div>
                </div>

                <form id="bulk-form-{{ $categoryName }}" class="px-6 py-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-6">
                        @foreach($categorySettings as $setting)
                            <div class="setting-item">
                                <div class="flex items-center justify-between mb-2">
                                    <label for="setting-{{ $setting->id }}" class="setting-label">
                                        {{ $setting->label }}
                                        @if($setting->is_encrypted)
                                            <i class="fas fa-lock ml-1 text-yellow-500" title="Encrypted"></i>
                                        @endif
                                        @if($setting->is_env_synced)
                                            <i class="fas fa-sync ml-1 text-blue-500" title="Synced to .env"></i>
                                        @endif
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.app-settings.edit', $setting) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" onclick="deleteSetting({{ $setting->id }})" 
                                                class="text-red-600 hover:text-red-900 text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                @if($setting->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $setting->description }}</p>
                                @endif

                                @if($setting->type === 'boolean')
                                    <div class="flex items-center">
                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                        <input type="checkbox" 
                                               id="setting-{{ $setting->id }}" 
                                               name="settings[{{ $setting->key }}]" 
                                               value="1"
                                               {{ $setting->value ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="setting-{{ $setting->id }}" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                            Enable {{ $setting->label }}
                                        </label>
                                    </div>
                                @elseif($setting->options && is_array($setting->options))
                                    <select id="setting-{{ $setting->id }}" 
                                            name="settings[{{ $setting->key }}]" 
                                            class="setting-input">
                                        @foreach($setting->options as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" {{ $setting->value == $optionValue ? 'selected' : '' }}>
                                                {{ $optionLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                @elseif($setting->type === 'json')
                                    <textarea id="setting-{{ $setting->id }}" 
                                              name="settings[{{ $setting->key }}]" 
                                              rows="4"
                                              class="setting-input font-mono text-sm"
                                              placeholder="Enter valid JSON">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                                @else
                                    <input type="{{ $setting->type === 'encrypted' ? 'password' : ($setting->type === 'integer' ? 'number' : 'text') }}" 
                                           id="setting-{{ $setting->id }}" 
                                           name="settings[{{ $setting->key }}]" 
                                           value="{{ $setting->type === 'encrypted' ? '' : $setting->value }}"
                                           placeholder="{{ $setting->type === 'encrypted' ? 'Enter new value to change' : 'Enter ' . $setting->label }}"
                                           class="setting-input">
                                @endif

                                @if($setting->env_key)
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="fas fa-code mr-1"></i>
                                        .env key: {{ $setting->env_key }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        @endforeach
    @endif
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
                    Are you sure you want to delete this setting? This action cannot be undone.
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
let deleteSettingId = null;

function filterByCategory() {
    const category = document.getElementById('categoryFilter').value;
    const url = new URL(window.location);
    url.searchParams.set('category', category);
    window.location.href = url.toString();
}

function bulkUpdate(category) {
    const form = document.getElementById(`bulk-form-${category}`);
    const formData = new FormData(form);
    
    // Add category to form data
    formData.append('category', category);
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    
    fetch('{{ route("admin.app-settings.bulk-update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Settings saved successfully!', 'success');
            // Reload to show updated values
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving settings.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function deleteSetting(settingId) {
    deleteSettingId = settingId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteSettingId = null;
}

function initializeDefaults() {
    if (!confirm('This will create default settings for all categories. Continue?')) {
        return;
    }
    
    fetch('{{ route("admin.app-settings.initialize-defaults") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Default settings initialized successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while initializing defaults.', 'error');
    });
}

// Confirm delete
document.getElementById('confirmDelete').addEventListener('click', function() {
    if (!deleteSettingId) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/app-settings/${deleteSettingId}`;
    
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

// Helper function for category icons
function getCategoryIcon(category) {
    const icons = {
        'google': 'google',
        'sms': 'sms',
        'whatsapp': 'whatsapp',
        'email': 'envelope',
        'general': 'cog',
        'database': 'database'
    };
    return icons[category] || 'cog';
}
</script>
@endpush
