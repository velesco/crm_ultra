@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8">
            <div class="mb-4 lg:mb-0">
                <nav class="flex text-sm text-gray-500 mb-3" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700 transition-colors duration-200">Admin</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('admin.webhook-logs.index') }}" class="hover:text-gray-700 transition-colors duration-200">Webhook Logs</a>
                    <span class="mx-2">/</span>
                    <span class="text-gray-900">Webhook #{{ $webhookLog->id }}</span>
                </nav>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    Webhook Details
                </h1>
                <p class="text-gray-600 mt-2">{{ ucfirst($webhookLog->webhook_type) }} webhook from {{ ucfirst($webhookLog->provider) }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($webhookLog->canRetry())
                    <button type="button" 
                            onclick="retryWebhook()" 
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Retry Webhook
                    </button>
                @endif
                <a href="{{ route('admin.webhook-logs.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Status Overview --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Status Overview</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
                        <div class="text-center">
                            <div class="mb-3">
                                @if($webhookLog->status === 'pending')
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @elseif($webhookLog->status === 'processing')
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        Processing
                                    </span>
                                @elseif($webhookLog->status === 'completed')
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @elseif($webhookLog->status === 'failed')
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @elseif($webhookLog->status === 'retrying')
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                        Retrying
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                        {{ ucfirst($webhookLog->status) }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">Current Status</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl font-semibold text-gray-900 mb-1">{{ $webhookLog->attempts }}</div>
                            <div class="text-xs text-gray-500">Attempts</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl font-semibold text-gray-900 mb-1">
                                @if($webhookLog->processing_time)
                                    {{ number_format($webhookLog->processing_time, 2) }}ms
                                @else
                                    -
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">Processing Time</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl font-semibold mb-1">
                                @if($webhookLog->response_code)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $webhookLog->response_code < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $webhookLog->response_code }}
                                    </span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">Response Code</div>
                        </div>
                    </div>
                    
                    @if($webhookLog->status === 'failed' && $webhookLog->next_retry_at)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-yellow-800">
                                        Next retry scheduled for: <strong>{{ $webhookLog->next_retry_at->format('M j, Y H:i:s') }}</strong>
                                        ({{ $webhookLog->next_retry_at->diffForHumans() }})
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($webhookLog->error_message)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-red-800 font-semibold mb-2">Error Message</h4>
                                    <p class="text-red-700">{{ $webhookLog->error_message }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Webhook Information --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Webhook Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-2">Type</div>
                            <div class="flex items-center">
                                @if($webhookLog->webhook_type === 'email')
                                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                @elseif($webhookLog->webhook_type === 'sms')
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                @elseif($webhookLog->webhook_type === 'api')
                                    <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                @endif
                                <span class="text-gray-900">{{ ucfirst($webhookLog->webhook_type) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-2">Provider</div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                                <span class="text-gray-900">{{ ucfirst($webhookLog->provider) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-2">Event Type</div>
                            <div class="flex items-center">
                                @if($webhookLog->event_type === 'delivered')
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @elseif($webhookLog->event_type === 'failed')
                                    <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                                <span class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $webhookLog->event_type)) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-2">Method</div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $webhookLog->method }}
                            </span>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-sm font-medium text-gray-500 mb-2">URL</div>
                            <code class="block bg-gray-100 px-3 py-2 rounded-lg text-sm text-gray-900 break-all">{{ $webhookLog->url }}</code>
                        </div>
                        @if($webhookLog->reference_id && $webhookLog->reference_type)
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-2">Reference</div>
                            <div class="text-gray-900">{{ $webhookLog->reference_type }}: {{ $webhookLog->reference_id }}</div>
                        </div>
                        @endif
                        @if($webhookLog->webhook_id)
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-2">Webhook ID</div>
                            <code class="text-sm text-gray-900">{{ $webhookLog->webhook_id }}</code>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Timeline</h3>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    <div class="relative flex space-x-3">
                                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Webhook Received</p>
                                                <p class="text-sm text-gray-500">{{ $webhookLog->webhook_received_at->format('M j, Y H:i:s') }}</p>
                                                <p class="text-xs text-gray-400">{{ $webhookLog->webhook_received_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            
                            @if($webhookLog->processed_at)
                            <li>
                                <div class="relative pb-8">
                                    @if($webhookLog->next_retry_at)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div class="h-8 w-8 rounded-full {{ $webhookLog->status === 'completed' ? 'bg-green-500' : 'bg-red-500' }} flex items-center justify-center ring-8 ring-white">
                                            @if($webhookLog->status === 'completed')
                                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Processing {{ $webhookLog->status === 'completed' ? 'Completed' : 'Failed' }}</p>
                                                <p class="text-sm text-gray-500">{{ $webhookLog->processed_at->format('M j, Y H:i:s') }}</p>
                                                <p class="text-xs text-gray-400">{{ $webhookLog->processed_at->diffForHumans() }}</p>
                                                @if($webhookLog->processing_time)
                                                    <p class="text-xs text-blue-600">Took {{ number_format($webhookLog->processing_time, 2) }}ms</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                            
                            @if($webhookLog->next_retry_at)
                            <li>
                                <div class="relative">
                                    <div class="relative flex space-x-3">
                                        <div class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Next Retry Scheduled</p>
                                                <p class="text-sm text-gray-500">{{ $webhookLog->next_retry_at->format('M j, Y H:i:s') }}</p>
                                                <p class="text-xs text-gray-400">{{ $webhookLog->next_retry_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                {{-- Headers --}}
                @if($webhookLog->headers)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Request Headers</h3>
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-200">
                                @foreach($webhookLog->headers as $key => $value)
                                <tr>
                                    <td class="py-3 pr-6 text-sm font-medium text-gray-900 text-right" style="width: 200px;">{{ $key }}:</td>
                                    <td class="py-3 text-sm">
                                        <code class="text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ is_array($value) ? implode(', ', $value) : $value }}</code>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Payload --}}
                <div x-data="{ format: 'raw' }" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Raw Payload</h3>
                        <div class="flex items-center bg-gray-100 rounded-lg p-1">
                            <button @click="format = 'raw'" 
                                    :class="format === 'raw' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                                    class="px-3 py-1 text-sm font-medium rounded-md cursor-pointer hover:bg-white hover:shadow-sm transition-all duration-150">
                                Raw
                            </button>
                            <button @click="format = 'formatted'" 
                                    :class="format === 'formatted' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600'"
                                    class="px-3 py-1 text-sm font-medium rounded-md cursor-pointer hover:bg-white hover:shadow-sm transition-all duration-150">
                                Formatted
                            </button>
                        </div>
                    </div>
                    <pre id="payloadContent" class="bg-gray-100 p-4 rounded-lg text-sm text-gray-800 overflow-auto max-h-96"><code>{{ $webhookLog->payload }}</code></pre>
                </div>

                {{-- Processed Data --}}
                @if($webhookLog->processed_data)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Processed Data</h3>
                    <pre class="bg-gray-100 p-4 rounded-lg text-sm text-gray-800 overflow-auto max-h-72"><code>{{ json_encode($webhookLog->processed_data, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
                @endif

                {{-- Response --}}
                @if($webhookLog->response)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Response</h3>
                    <pre class="bg-gray-100 p-4 rounded-lg text-sm text-gray-800"><code>{{ $webhookLog->response }}</code></pre>
                </div>
                @endif

                {{-- Error Context --}}
                @if($webhookLog->error_context)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-red-600 mb-6">Error Context</h3>
                    <pre class="bg-red-50 p-4 rounded-lg text-sm text-red-800"><code>{{ json_encode($webhookLog->error_context, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Actions --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
                    <div class="space-y-3">
                        @if($webhookLog->canRetry())
                            <button type="button" 
                                    onclick="retryWebhook()" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Retry This Webhook
                            </button>
                        @endif
                        <button type="button" 
                                onclick="copyToClipboard('payload')"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-white border border-blue-300 rounded-lg shadow-sm text-sm font-medium text-blue-700 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                            </svg>
                            Copy Payload
                        </button>
                        @if($webhookLog->processed_data)
                            <button type="button" 
                                    onclick="copyToClipboard('processed')"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-white border border-cyan-300 rounded-lg shadow-sm text-sm font-medium text-cyan-700 hover:bg-cyan-50 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                </svg>
                                Copy Processed Data
                            </button>
                        @endif
                        <a href="{{ route('admin.webhook-logs.export') }}?webhook_ids[]={{ $webhookLog->id }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-white border border-green-300 rounded-lg shadow-sm text-sm font-medium text-green-700 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export This Log
                        </a>
                    </div>
                </div>

                {{-- Metadata --}}
                @if($webhookLog->metadata)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Metadata</h3>
                    <pre class="bg-gray-100 p-4 rounded-lg text-sm text-gray-800"><code>{{ json_encode($webhookLog->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
                @endif

                {{-- System Information --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">System Information</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">IP Address</div>
                            <div class="text-sm text-gray-900">{{ $webhookLog->ip_address ?? 'Unknown' }}</div>
                        </div>
                        @if($webhookLog->user_agent)
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">User Agent</div>
                            <div class="text-xs text-gray-600 break-all">{{ $webhookLog->user_agent }}</div>
                        </div>
                        @endif
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Created</div>
                            <div class="text-sm text-gray-900">{{ $webhookLog->created_at->format('M j, Y H:i:s') }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Updated</div>
                            <div class="text-sm text-gray-900">{{ $webhookLog->updated_at->format('M j, Y H:i:s') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Related Webhooks --}}
                @if($relatedLogs->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Related Webhooks</h3>
                    <div class="space-y-3">
                        @foreach($relatedLogs as $related)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900">{{ ucfirst($related->event_type) }}</div>
                                <div class="text-xs text-gray-500">{{ $related->webhook_received_at->format('M j, H:i') }}</div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($related->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @elseif($related->status === 'failed')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @elseif($related->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($related->status) }}
                                    </span>
                                @endif
                                <a href="{{ route('admin.webhook-logs.show', $related) }}" 
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function retryWebhook() {
    if (!confirm('Are you sure you want to retry this webhook?')) return;
    
    fetch(`{{ route('admin.webhook-logs.retry', $webhookLog) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Webhook queued for retry');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showToast('error', data.message || 'Failed to retry webhook');
        }
    })
    .catch(error => {
        console.error('Error retrying webhook:', error);
        showToast('error', 'Failed to retry webhook');
    });
}

function copyToClipboard(type) {
    let text = '';
    
    if (type === 'payload') {
        text = document.getElementById('payloadContent').textContent;
    } else if (type === 'processed') {
        text = JSON.stringify(@json($webhookLog->processed_data), null, 2);
    }
    
    navigator.clipboard.writeText(text).then(() => {
        showToast('success', 'Copied to clipboard');
    }).catch(err => {
        console.error('Error copying to clipboard:', err);
        showToast('error', 'Failed to copy to clipboard');
    });
}

function showToast(type, message) {
    // Implement your toast notification system here
    console.log(`${type}: ${message}`);
}
</script>
@endpush
