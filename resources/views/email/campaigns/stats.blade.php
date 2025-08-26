@extends('layouts.app')

@section('title', 'Statistici Campanie')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Statistici: {{ $campaign->name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Analiza detaliată a performanței campaniei de email
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('email.campaigns.show', $campaign) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Vezi campania
            </a>
            <a href="{{ route('email.campaigns.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Toate campaniile
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-8">
    
    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Recipients -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Destinatari</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['recipients_count']) }}</p>
                </div>
            </div>
        </div>
        
        <!-- Delivered -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Livrate</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['delivered_count']) }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400">
                        {{ $stats['recipients_count'] > 0 ? number_format(($stats['delivered_count'] / $stats['recipients_count']) * 100, 1) : 0 }}% din total
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Opened -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Deschise</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['opened_count']) }}</p>
                    <p class="text-xs text-purple-600 dark:text-purple-400">
                        {{ $stats['delivered_count'] > 0 ? number_format(($stats['opened_count'] / $stats['delivered_count']) * 100, 1) : 0 }}% rata de deschidere
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Clicked -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Click-uri</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['clicked_count']) }}</p>
                    <p class="text-xs text-orange-600 dark:text-orange-400">
                        {{ $stats['opened_count'] > 0 ? number_format(($stats['clicked_count'] / $stats['opened_count']) * 100, 1) : 0 }}% CTR
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Performance Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Performanță în Timp</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Evoluția deschiderilor și click-urilor</p>
                </div>
            </div>
            
            <div class="h-80">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>
        
        <!-- Engagement Breakdown -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Analiza Angajamentului</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Distribuția acțiunilor utilizatorilor</p>
                </div>
            </div>
            
            <div class="h-80 flex items-center justify-center">
                <canvas id="engagementChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Detailed Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Bounce and Unsubscribe Stats -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Probleme de Livrare</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Bounce-uri și dezabonări</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-red-900 dark:text-red-300">Bounce-uri Soft</p>
                        <p class="text-xs text-red-600 dark:text-red-400">Probleme temporare</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-900 dark:text-red-300">{{ number_format($stats['soft_bounced_count']) }}</p>
                        <p class="text-xs text-red-600 dark:text-red-400">
                            {{ $stats['recipients_count'] > 0 ? number_format(($stats['soft_bounced_count'] / $stats['recipients_count']) * 100, 2) : 0 }}%
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-red-900 dark:text-red-200">Bounce-uri Hard</p>
                        <p class="text-xs text-red-700 dark:text-red-400">Adrese invalide</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-900 dark:text-red-200">{{ number_format($stats['hard_bounced_count']) }}</p>
                        <p class="text-xs text-red-700 dark:text-red-400">
                            {{ $stats['recipients_count'] > 0 ? number_format(($stats['hard_bounced_count'] / $stats['recipients_count']) * 100, 2) : 0 }}%
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-orange-900 dark:text-orange-300">Dezabonări</p>
                        <p class="text-xs text-orange-600 dark:text-orange-400">Oprit din listă</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-orange-900 dark:text-orange-300">{{ number_format($stats['unsubscribed_count']) }}</p>
                        <p class="text-xs text-orange-600 dark:text-orange-400">
                            {{ $stats['recipients_count'] > 0 ? number_format(($stats['unsubscribed_count'] / $stats['recipients_count']) * 100, 2) : 0 }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Best Performing Links -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Link-uri Populare</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cele mai accesate link-uri</p>
                </div>
            </div>
            
            @if(count($topLinks) > 0)
                <div class="space-y-3">
                    @foreach($topLinks as $link)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $link->url }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Click-uri unice
                                </p>
                            </div>
                            <div class="ml-3 flex items-center">
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    {{ $link->unique_clicks }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nu există click-uri pe link-uri încă</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Export Stats -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Exportă Statistici</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Descarcă raportul detaliat al campaniei</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('email.campaigns.export', ['campaign' => $campaign, 'format' => 'csv']) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportă CSV
                </a>
                <a href="{{ route('email.campaigns.export', ['campaign' => $campaign, 'format' => 'excel']) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportă Excel
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Performance Chart
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
new Chart(performanceCtx, {
    type: 'line',
    data: {
        labels: @json($chartData['dates']),
        datasets: [{
            label: 'Deschideri',
            data: @json($chartData['opens']),
            borderColor: 'rgb(147, 51, 234)',
            backgroundColor: 'rgba(147, 51, 234, 0.1)',
            tension: 0.1
        }, {
            label: 'Click-uri',
            data: @json($chartData['clicks']),
            borderColor: 'rgb(249, 115, 22)',
            backgroundColor: 'rgba(249, 115, 22, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Engagement Chart
const engagementCtx = document.getElementById('engagementChart').getContext('2d');
new Chart(engagementCtx, {
    type: 'doughnut',
    data: {
        labels: ['Deschise', 'Click-uri', 'Bounce-uri', 'Dezabonări', 'Neinteracțiuni'],
        datasets: [{
            data: [
                {{ $stats['opened_count'] }},
                {{ $stats['clicked_count'] }},
                {{ $stats['soft_bounced_count'] + $stats['hard_bounced_count'] }},
                {{ $stats['unsubscribed_count'] }},
                {{ $stats['recipients_count'] - $stats['opened_count'] - $stats['clicked_count'] - $stats['soft_bounced_count'] - $stats['hard_bounced_count'] - $stats['unsubscribed_count'] }}
            ],
            backgroundColor: [
                'rgb(147, 51, 234)',
                'rgb(249, 115, 22)',
                'rgb(239, 68, 68)',
                'rgb(245, 158, 11)',
                'rgb(156, 163, 175)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection
