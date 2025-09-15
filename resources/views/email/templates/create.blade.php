@extends('layouts.app')

@section('title', 'Create Email Template')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="{{ route('email.templates.index') }}" class="text-gray-400 hover:text-gray-500">
                                <svg class="flex-shrink-0 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                <span class="sr-only">Back</span>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="{{ route('email.templates.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Templates</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-medium text-gray-500 dark:text-gray-400">Create</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                Create Email Template
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Create a new reusable email template for your campaigns
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <form action="{{ route('email.templates.store') }}" method="POST" x-data="templateEditor()" class="space-y-6">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Template Name -->
                                <div class="sm:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Name</label>
                                    <div class="mt-1">
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                               placeholder="Enter template name">
                                    </div>
                                    @error('name')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                    <div class="mt-1">
                                        <input list="categories" name="category" id="category" value="{{ old('category') }}" required
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                               placeholder="Enter or select category">
                                        <datalist id="categories">
                                            @foreach($predefinedCategories as $predefinedCategory)
                                                <option value="{{ $predefinedCategory }}">
                                            @endforeach
                                            @foreach($categories as $existingCategory)
                                                @if(!in_array($existingCategory, $predefinedCategories))
                                                    <option value="{{ $existingCategory }}">
                                                @endif
                                            @endforeach
                                        </datalist>
                                    </div>
                                    @error('category')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="flex items-center">
                                    <div class="flex items-center h-5">
                                        <input id="is_active" name="is_active" type="checkbox" 
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700">
                                    </div>
                                    <div class="ml-3">
                                        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Template</label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Active templates can be used in campaigns</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Line -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Subject Line</h3>
                            
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Subject</label>
                                <div class="mt-1">
                                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required x-model="subject"
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                           placeholder="Enter email subject line">
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Use variables like @{{name}} or @{{company}} to personalize the subject line.
                                </p>
                                @error('subject')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Subject Preview -->
                            <div class="mt-4" x-show="subject.length > 0">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preview</label>
                                <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                    <div class="text-sm text-gray-900 dark:text-white" x-text="subject || 'Enter subject line above'"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Content -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Email Content</h3>
                            
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Content</label>
                                <div class="mt-1">
                                    <textarea name="content" id="content" rows="12" required x-model="content"
                                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                              placeholder="Enter your email content here. You can use HTML and variables like @{{name}}, @{{email}}, etc.">{{ old('content') }}</textarea>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    HTML is supported. Use variables in double curly braces, e.g., @{{name}}, @{{email}}, @{{company}}.
                                </p>
                                @error('content')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Content Toolbar -->
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button type="button" @click="insertVariable('name')" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    @{{name}}
                                </button>
                                <button type="button" @click="insertVariable('email')" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    @{{email}}
                                </button>
                                <button type="button" @click="insertVariable('company')" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    @{{company}}
                                </button>
                                <button type="button" @click="insertVariable('phone')" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    @{{phone}}
                                </button>
                                <button type="button" @click="insertVariable('unsubscribe_link')" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    @{{unsubscribe_link}}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex justify-between">
                                <div class="flex space-x-3">
                                    <button type="button" @click="previewTemplate()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Preview
                                    </button>
                                </div>
                                <div class="flex space-x-3">
                                    <a href="{{ route('email.templates.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancel
                                    </a>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600">
                                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Create Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Preview -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6" x-show="showPreview">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Live Preview</h3>
                        <div class="border dark:border-gray-600 rounded-md">
                            <!-- Email Header -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Preview</div>
                                </div>
                            </div>
                            <!-- Email Content -->
                            <div class="p-4">
                                <div class="mb-4" x-show="subject.length > 0">
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Subject:</div>
                                    <div class="font-medium text-gray-900 dark:text-white" x-text="renderPreview(subject)"></div>
                                </div>
                                <div class="prose dark:prose-invert max-w-none text-sm" x-show="content.length > 0" x-html="renderPreview(content)"></div>
                                <div x-show="content.length === 0" class="text-gray-500 dark:text-gray-400 text-sm italic">
                                    Start typing your email content to see preview...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variable Helper -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Available Variables</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="insertVariable('name')">
                                <code class="text-sm text-blue-600 dark:text-blue-400">{{name}}</code>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Contact name</span>
                            </div>
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="insertVariable('email')">
                                <code class="text-sm text-blue-600 dark:text-blue-400">{{email}}</code>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Contact email</span>
                            </div>
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="insertVariable('company')">
                                <code class="text-sm text-blue-600 dark:text-blue-400">{{company}}</code>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Company name</span>
                            </div>
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="insertVariable('phone')">
                                <code class="text-sm text-blue-600 dark:text-blue-400">{{phone}}</code>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Phone number</span>
                            </div>
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="insertVariable('unsubscribe_link')">
                                <code class="text-sm text-blue-600 dark:text-blue-400">{{unsubscribe_link}}</code>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Unsubscribe link</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Click any variable to insert it at the cursor position. Variables will be replaced with actual contact data when emails are sent.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Tips</h3>
                        <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Use descriptive template names to easily identify them later</span>
                            </li>
                            <li class="flex">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Always include an unsubscribe link for compliance</span>
                            </li>
                            <li class="flex">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Test your templates with the preview feature before saving</span>
                            </li>
                            <li class="flex">
                                <svg class="flex-shrink-0 h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Categorize templates for better organization</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function templateEditor() {
    return {
        subject: '',
        content: '',
        showPreview: false,
        
        init() {
            this.showPreview = true;
        },
        
        insertVariable(variable) {
            const textarea = document.getElementById('content');
            const cursorPos = textarea.selectionStart;
            const textBefore = this.content.substring(0, cursorPos);
            const textAfter = this.content.substring(textarea.selectionEnd, this.content.length);
            this.content = textBefore + `{{${variable}}}` + textAfter;
            
            // Move cursor to after inserted variable
            this.$nextTick(() => {
                textarea.focus();
                textarea.setSelectionRange(cursorPos + variable.length + 4, cursorPos + variable.length + 4);
            });
        },
        
        previewTemplate() {
            if (!this.subject && !this.content) {
                alert('Please enter subject and content first.');
                return;
            }
            
            // You can implement a modal preview or open in new tab
            const previewWindow = window.open('', '_blank');
            const previewContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Template Preview</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                        .email-preview { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                        .subject { font-weight: bold; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
                    </style>
                </head>
                <body>
                    <div class="email-preview">
                        <div class="subject">Subject: ${this.renderPreview(this.subject)}</div>
                        <div class="content">${this.renderPreview(this.content)}</div>
                    </div>
                </body>
                </html>
            `;
            previewWindow.document.write(previewContent);
        },
        
        renderPreview(text) {
            return text
                .replace(/\{\{name\}\}/g, 'John Doe')
                .replace(/\{\{email\}\}/g, 'john.doe@example.com')
                .replace(/\{\{company\}\}/g, 'Acme Corp')
                .replace(/\{\{phone\}\}/g, '+1-555-0123')
                .replace(/\{\{unsubscribe_link\}\}/g, '<a href="#">Unsubscribe</a>')
                .replace(/\{\{([^}]+)\}\}/g, '<span style="color: #3b82f6; font-weight: 500;">[$1]</span>');
        }
    }
}
</script>
@endsection
