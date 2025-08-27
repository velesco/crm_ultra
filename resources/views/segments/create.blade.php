@extends('layouts.app')

@section('title', 'Create New Segment')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Segment</h1>
            <p class="text-gray-600 dark:text-gray-400">Define a new segment to organize your contacts</p>
        </div>
        <a href="{{ route('segments.index') }}" class="btn-secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Segments
        </a>
    </div>

    <form method="POST" action="{{ route('segments.store') }}" class="space-y-6" x-data="segmentForm()">
        @csrf
        
        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Basic Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Segment Name *
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
                           required
                           placeholder="e.g., Active Customers"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Segment Type *
                    </label>
                    <select name="type" 
                            id="type" 
                            required
                            x-model="segmentType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('type') border-red-500 @enderror">
                        <option value="">Select Type</option>
                        <option value="static" {{ old('type') === 'static' ? 'selected' : '' }}>Static</option>
                        <option value="dynamic" {{ old('type') === 'dynamic' ? 'selected' : '' }}>Dynamic</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <p x-show="segmentType === 'static'" x-transition>
                            <strong>Static:</strong> Manually add/remove contacts. Fixed membership.
                        </p>
                        <p x-show="segmentType === 'dynamic'" x-transition>
                            <strong>Dynamic:</strong> Automatically updates based on conditions you define.
                        </p>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              placeholder="Describe the purpose and criteria for this segment..."
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Dynamic Segment Conditions -->
        <div x-show="segmentType === 'dynamic'" 
             x-transition
             class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Segment Conditions</h2>
            
            <div class="space-y-4">
                <!-- Condition Logic -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Condition Logic
                    </label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="condition_logic" value="AND" class="text-blue-600" {{ old('condition_logic', 'AND') === 'AND' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">AND (all conditions must match)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="condition_logic" value="OR" class="text-blue-600" {{ old('condition_logic') === 'OR' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">OR (any condition can match)</span>
                        </label>
                    </div>
                </div>

                <!-- Conditions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Conditions
                    </label>
                    <div x-data="conditionsBuilder()" class="space-y-3">
                        <template x-for="(condition, index) in conditions" :key="index">
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <!-- Field -->
                                <select x-model="condition.field" :name="`conditions[${index}][field]`" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                                    <option value="">Select Field</option>
                                    <option value="first_name">First Name</option>
                                    <option value="last_name">Last Name</option>
                                    <option value="email">Email</option>
                                    <option value="phone">Phone</option>
                                    <option value="status">Status</option>
                                    <option value="source">Source</option>
                                    <option value="created_at">Created Date</option>
                                    <option value="updated_at">Updated Date</option>
                                    <option value="last_activity_at">Last Activity</option>
                                </select>

                                <!-- Operator -->
                                <select x-model="condition.operator" :name="`conditions[${index}][operator]`" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                                    <option value="">Select Operator</option>
                                    <option value="equals">Equals</option>
                                    <option value="not_equals">Not Equals</option>
                                    <option value="contains">Contains</option>
                                    <option value="not_contains">Not Contains</option>
                                    <option value="starts_with">Starts With</option>
                                    <option value="ends_with">Ends With</option>
                                    <option value="greater_than">Greater Than</option>
                                    <option value="less_than">Less Than</option>
                                    <option value="is_empty">Is Empty</option>
                                    <option value="is_not_empty">Is Not Empty</option>
                                </select>

                                <!-- Value -->
                                <input type="text" 
                                       x-model="condition.value" 
                                       :name="`conditions[${index}][value]`"
                                       placeholder="Value"
                                       x-show="!['is_empty', 'is_not_empty'].includes(condition.operator)"
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">

                                <!-- Remove Button -->
                                <button type="button" 
                                        @click="removeCondition(index)"
                                        x-show="conditions.length > 1"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Add Condition Button -->
                        <button type="button" 
                                @click="addCondition()"
                                class="inline-flex items-center px-3 py-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Add Condition
                        </button>
                    </div>
                </div>

                <!-- Preview -->
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Preview Matching Contacts</h4>
                    <button type="button" 
                            @click="previewConditions()"
                            class="btn-secondary text-sm">
                        Preview Results
                    </button>
                    <div x-show="previewResults" x-transition class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                        <span x-text="previewResults"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Static Segment Contacts -->
        <div x-show="segmentType === 'static'" 
             x-transition
             class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Add Contacts</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select Contacts
                    </label>
                    <select name="contacts[]" 
                            multiple
                            size="8"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">
                                {{ $contact->first_name }} {{ $contact->last_name }} ({{ $contact->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Hold Ctrl/Cmd to select multiple contacts
                    </p>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Settings</h2>
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active" 
                           value="1"
                           {{ old('is_active', '1') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Activate this segment immediately
                    </label>
                </div>
                
                <div x-show="segmentType === 'dynamic'" x-transition class="flex items-center">
                    <input type="hidden" name="auto_refresh" value="0">
                    <input type="checkbox" 
                           name="auto_refresh" 
                           id="auto_refresh" 
                           value="1"
                           {{ old('auto_refresh', '1') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <label for="auto_refresh" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Automatically refresh segment daily
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('segments.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Create Segment
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function segmentForm() {
    return {
        segmentType: '{{ old('type') }}',
        previewResults: null
    }
}

function conditionsBuilder() {
    return {
        conditions: [
            { field: '', operator: '', value: '' }
        ],
        
        addCondition() {
            this.conditions.push({ field: '', operator: '', value: '' });
        },
        
        removeCondition(index) {
            this.conditions.splice(index, 1);
        },
        
        previewConditions() {
            // In a real implementation, this would make an AJAX call
            // to preview the number of contacts matching the conditions
            this.previewResults = "Loading preview...";
            
            // Simulate API call
            setTimeout(() => {
                this.previewResults = "This would show approximately 247 matching contacts";
            }, 1000);
        }
    }
}
</script>
@endpush
@endsection