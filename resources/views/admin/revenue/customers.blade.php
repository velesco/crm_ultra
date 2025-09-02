@extends('layouts.app')

@section('title', 'Customer Revenue Analysis - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-cyan-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Customer Revenue Analysis
                    </h1>
                    <p class="text-gray-600 mt-2">Deep dive into customer value, segmentation, and lifetime analytics</p>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-wrap gap-3">
                    <a href="{{ route('admin.revenue.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Overview
                    </a>
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button type="button" class="period-filter px-3 py-1 text-sm font-medium text-gray-600 rounded-md cursor-pointer hover:bg-white hover:shadow-sm transition-all duration-150" data-period="30_days">
                            30 Days
                        </button>
                        <button type="button" class="period-filter px-3 py-1 text-sm font-medium bg-white shadow-sm text-blue-600 rounded-md cursor-pointer transition-all duration-150" data-period="90_days">
                            90 Days
                        </button>
                        <button type="button" class="period-filter px-3 py-1 text-sm font-medium text-gray-600 rounded-md cursor-pointer hover:bg-white hover:shadow-sm transition-all duration-150" data-period="this_year">
                            This Year
                        </button>
                    </div>
                    <button type="button" 
                            onclick="exportCustomerData()"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>

        {{-- Customer Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">New Customers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($customerStats['new_customers']) }}</p>
                        <p class="text-sm text-green-600 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                            {{ number_format($customerStats['customer_growth'], 1) }}% growth
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Active Customers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($customerStats['active_customers']) }}</p>
                        <p class="text-sm text-gray-500">Currently active</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($customerStats['total_customers']) }}</p>
                        <p class="text-sm text-gray-500">All time</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 mr-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Avg. Lifetime Value</p>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($lifetimeValue, 2) }}</p>
                        <p class="text-sm text-gray-500">Per customer</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer Segments and Charts Row --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Customer Segments
                    </h3>
                    <p class="text-sm text-gray-600">Distribution by customer type</p>
                </div>
                <div class="h-72">
                    <canvas id="customerSegmentsChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Value Distribution
                    </h3>
                    <p class="text-sm text-gray-600">Customer value by segment</p>
                </div>

                <div class="space-y-4">
                    @foreach($customerSegments as $segment => $count)
                        @php
                            $percentage = $customerStats['total_customers'] > 0 ? ($count / $customerStats['total_customers']) * 100 : 0;
                            $colors = [
                                'vip' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'text-yellow-600', 'progress' => 'bg-yellow-500'],
                                'enterprise' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'text-blue-600', 'progress' => 'bg-blue-500'],
                                'smb' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'text-green-600', 'progress' => 'bg-green-500'],
                                'individual' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'icon' => 'text-cyan-600', 'progress' => 'bg-cyan-500']
                            ];
                            $colorSet = $colors[$segment] ?? $colors['individual'];
                        @endphp
                        <div class="flex items-center justify-between p-3 {{ $colorSet['bg'] }} rounded-lg">
                            <div class="flex items-center">
                                <div class="mr-3">
                                    <svg class="w-5 h-5 {{ $colorSet['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold {{ $colorSet['text'] }}">{{ ucfirst($segment) }} Customers</div>
                                    <div class="text-xs text-gray-600">{{ $count }} customers ({{ number_format($percentage, 1) }}%)</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-24 bg-gray-200 rounded-full h-2 mb-1">
                                    <div class="h-2 {{ $colorSet['progress'] }} rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <div class="text-xs {{ $colorSet['text'] }} font-medium">{{ number_format($percentage, 1) }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Customer Acquisition Timeline --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Customer Acquisition Timeline
                </h3>
                <p class="text-sm text-gray-600">New customers acquired over time</p>
            </div>
            <div class="h-80">
                <canvas id="acquisitionTimelineChart"></canvas>
            </div>
        </div>

        {{-- Customer Insights --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    Customer Insights & Recommendations
                </h3>
                <p class="text-sm text-gray-600">Data-driven insights for customer strategy</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h4 class="text-blue-600 font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Key Findings
                    </h4>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                            <div>
                                <div class="font-semibold text-gray-900">Customer Growth</div>
                                <p class="text-sm text-gray-600">{{ number_format($customerStats['customer_growth'], 1) }}% increase in new customers this period</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="font-semibold text-gray-900">Average Lifetime Value</div>
                                <p class="text-sm text-gray-600">${{ number_format($lifetimeValue, 2) }} per customer across all segments</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-cyan-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <div>
                                <div class="font-semibold text-gray-900">Segment Distribution</div>
                                <p class="text-sm text-gray-600">{{ count($customerSegments) }} distinct customer segments identified</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-green-600 font-semibold mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Strategic Recommendations
                    </h4>
                    <div class="space-y-4">
                        @if($customerStats['customer_growth'] > 10)
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <div>
                                <div class="font-semibold text-gray-900">Scale Acquisition</div>
                                <p class="text-sm text-gray-600">High growth rate - consider scaling successful acquisition channels</p>
                            </div>
                        </div>
                        @endif
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 16L3 21l5.25-1.67L12 21l3.75-1.67L21 21l-2-5H5zm3-5c0 1.1.9 2 2 2s2-.9 2-2V6c0-1.1-.9-2-2-2s-2 .9-2 2v5zm6-5c0-1.1.9-2 2-2s2 .9 2 2v5c0 1.1-.9 2-2 2s-2-.9-2-2V6z"/>
                            </svg>
                            <div>
                                <div class="font-semibold text-gray-900">VIP Program</div>
                                <p class="text-sm text-gray-600">Develop VIP customer retention program for high-value segments</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-cyan-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <div>
                                <div class="font-semibold text-gray-900">Lifecycle Marketing</div>
                                <p class="text-sm text-gray-600">Implement automated lifecycle campaigns to increase customer value</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                            <div>
                                <div class="font-semibold text-gray-900">Segment Optimization</div>
                                <p class="text-sm text-gray-600">Focus marketing efforts on highest-converting customer segments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let customerSegmentsChart;
let acquisitionTimelineChart;
let currentPeriod = '90_days';

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    
    // Period filter handlers
    document.querySelectorAll('.period-filter').forEach(button => {
        button.addEventListener('click', function() {
            // Update button states
            document.querySelectorAll('.period-filter').forEach(btn => {
                btn.classList.remove('bg-white', 'shadow-sm', 'text-blue-600');
                btn.classList.add('text-gray-600');
            });
            this.classList.remove('text-gray-600');
            this.classList.add('bg-white', 'shadow-sm', 'text-blue-600');
            
            currentPeriod = this.getAttribute('data-period');
            loadCustomerData(currentPeriod);
        });
    });
});

function initializeCharts() {
    // Customer Segments Chart
    const segmentsCtx = document.getElementById('customerSegmentsChart').getContext('2d');
    const segmentsData = @json($customerSegments);
    
    customerSegmentsChart = new Chart(segmentsCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(segmentsData).map(segment => segment.charAt(0).toUpperCase() + segment.slice(1)),
            datasets: [{
                data: Object.values(segmentsData),
                backgroundColor: [
                    '#EAB308',  // VIP - Yellow
                    '#3B82F6',  // Enterprise - Blue
                    '#10B981',  // SMB - Green
                    '#06B6D4'   // Individual - Cyan
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            }
        }
    });

    // Acquisition Timeline Chart
    const timelineCtx = document.getElementById('acquisitionTimelineChart').getContext('2d');
    
    // Generate sample data for demonstration
    const timelineLabels = [];
    const timelineData = [];
    const currentDate = new Date();
    
    for (let i = 29; i >= 0; i--) {
        const date = new Date(currentDate);
        date.setDate(date.getDate() - i);
        timelineLabels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        timelineData.push(Math.floor(Math.random() * 10) + 1);
    }
    
    acquisitionTimelineChart = new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: timelineLabels,
            datasets: [{
                label: 'New Customers',
                data: timelineData,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

function loadCustomerData(period) {
    // Simulate loading customer data for different periods
    console.log('Loading customer data for period:', period);
    
    // In a real application, this would make an AJAX call
    // to fetch updated customer data
}

function exportCustomerData() {
    const url = new URL('{{ route("admin.revenue.export") }}');
    url.searchParams.append('type', 'customers');
    url.searchParams.append('period', currentPeriod);
    
    window.open(url.toString(), '_blank');
}
</script>
@endpush
@endsection
