@extends('layouts.app')

@section('title', 'Create Contact')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Contact</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Add a new contact to your CRM system
            </p>
        </div>
        <a href="{{ route('contacts.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Contacts
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
        <form method="POST" action="{{ route('contacts.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Contact Information</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Fill in the contact details below
                </p>
            </div>

            <div class="px-6 space-y-6">
                <!-- Avatar Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Picture</label>
                    <div class="flex items-center space-x-6">
                        <div class="shrink-0">
                            <img id="avatar-preview" class="h-16 w-16 object-cover rounded-full border-2 border-gray-300 dark:border-gray-600" 
                                 src="https://via.placeholder.com/64x64/9CA3AF/FFFFFF?text=?" alt="Avatar preview">
                        </div>
                        <div class="flex-1">
                            <input type="file" 
                                   name="avatar" 
                                   id="avatar" 
                                   accept="image/*"
                                   onchange="previewAvatar(this)"
                                   class="block w-full text-sm text-gray-500 dark:text-gray-400
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100
                                          dark:file:bg-indigo-900 dark:file:text-indigo-200
                                          dark:hover:file:bg-indigo-800">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF up to 2MB</p>
                        </div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="first_name" 
                               id="first_name" 
                               value="{{ old('first_name') }}"
                               required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('first_name') border-red-300 @enderror">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="last_name" 
                               id="last_name" 
                               value="{{ old('last_name') }}"
                               required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('last_name') border-red-300 @enderror">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Contact Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email <span class="text-red-500">*</span></label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}"
                               required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-300 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                        <input type="text" 
                               name="phone" 
                               id="phone" 
                               value="{{ old('phone') }}"
                               placeholder="+40 123 456 789"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('phone') border-red-300 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                        <input type="text" 
                               name="company" 
                               id="company" 
                               value="{{ old('company') }}"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('company') border-red-300 @enderror">
                        @error('company')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Title</label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="{{ old('title') }}"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('title') border-red-300 @enderror">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                    <textarea name="address" 
                              id="address" 
                              rows="3"
                              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('address') border-red-300 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="birthday" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Birthday</label>
                        <input type="date" 
                               name="birthday" 
                               id="birthday" 
                               value="{{ old('birthday') }}"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('birthday') border-red-300 @enderror">
                        @error('birthday')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="lead_source" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lead Source</label>
                        <select name="lead_source" 
                                id="lead_source"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('lead_source') border-red-300 @enderror">
                            <option value="">Select source...</option>
                            <option value="website" {{ old('lead_source') === 'website' ? 'selected' : '' }}>Website</option>
                            <option value="social_media" {{ old('lead_source') === 'social_media' ? 'selected' : '' }}>Social Media</option>
                            <option value="referral" {{ old('lead_source') === 'referral' ? 'selected' : '' }}>Referral</option>
                            <option value="email_campaign" {{ old('lead_source') === 'email_campaign' ? 'selected' : '' }}>Email Campaign</option>
                            <option value="phone_call" {{ old('lead_source') === 'phone_call' ? 'selected' : '' }}>Phone Call</option>
                            <option value="trade_show" {{ old('lead_source') === 'trade_show' ? 'selected' : '' }}>Trade Show</option>
                            <option value="other" {{ old('lead_source') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('lead_source')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" 
                                id="status"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('status') border-red-300 @enderror">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="unsubscribed" {{ old('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                            <option value="bounced" {{ old('status') === 'bounced' ? 'selected' : '' }}>Bounced</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Custom Fields -->
                <div>
                    <label for="custom_fields" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom Fields</label>
                    <div id="custom-fields-container" class="space-y-3">
                        <!-- Custom fields will be added here dynamically -->
                    </div>
                    <button type="button" 
                            onclick="addCustomField()" 
                            class="mt-2 inline-flex items-center px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Custom Field
                    </button>
                </div>

                <!-- Tags -->
                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tags</label>
                    <input type="text" 
                           name="tags" 
                           id="tags" 
                           value="{{ old('tags') }}"
                           placeholder="Enter tags separated by commas"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('tags') border-red-300 @enderror">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Separate multiple tags with commas (e.g., vip, customer, lead)
                    </p>
                    @error('tags')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="4"
                              placeholder="Add any additional notes about this contact..."
                              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Segments -->
                @if(isset($segments) && $segments->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign to Segments</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($segments as $segment)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" 
                                           name="segments[]" 
                                           value="{{ $segment->id }}"
                                           id="segment_{{ $segment->id }}"
                                           {{ in_array($segment->id, old('segments', [])) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="segment_{{ $segment->id }}" class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ $segment->name }}
                                    </label>
                                    @if($segment->description)
                                        <p class="text-gray-500 dark:text-gray-400">{{ $segment->description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
                <a href="{{ route('contacts.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 transition ease-in-out duration-150">
                    Cancel
                </a>
                
                <div class="flex space-x-3">
                    <button type="submit" 
                            name="action" 
                            value="save"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create Contact
                    </button>
                    
                    <button type="submit" 
                            name="action" 
                            value="save_and_new"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Save & Add Another
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let customFieldIndex = 0;

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function addCustomField() {
    const container = document.getElementById('custom-fields-container');
    const fieldHtml = `
        <div class="flex space-x-3 custom-field-row">
            <div class="flex-1">
                <input type="text" 
                       name="custom_fields[${customFieldIndex}][key]" 
                       placeholder="Field name"
                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="flex-1">
                <input type="text" 
                       name="custom_fields[${customFieldIndex}][value]" 
                       placeholder="Field value"
                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="flex-shrink-0">
                <button type="button" 
                        onclick="removeCustomField(this)"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition ease-in-out duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', fieldHtml);
    customFieldIndex++;
}

function removeCustomField(button) {
    button.closest('.custom-field-row').remove();
}

// Auto-format phone number
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('40')) {
        value = value.substring(2);
    }
    if (value.length > 0) {
        e.target.value = '+40 ' + value.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
    }
});

// Prevent form submission on Enter in tag field and convert to tag
document.getElementById('tags').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection