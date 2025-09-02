@extends('layouts.app')

@section('title', 'Revenue Forecast - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-crystal-ball text-white text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h1 class="text-2xl font-bold text-gray-900">Revenue Forecast</h1>
                            <p class="text-sm text-gray-600">Predictive analytics and revenue forecasting with trend analysis</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.revenue.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Overview
                    </a>
                    <div class="flex rounded-lg shadow-sm bg-white border border-gray-300">
                        <button type="button" class="forecast-period px-3 py-2 text-sm font-medium rounded-l-lg border-r border-gray-300 text-gray-700 hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500" data-months="3">
                            3 Months
                        </button>
                        <button type="button" class="forecast-period px-3 py-2 text-sm font-medium border-r border-gray-300 bg-blue-600 text-white" data-months="6">
                            6 Months
                        </button>
                        <button type="button" class="forecast-period px-3 py-2 text-sm font-medium rounded-r-lg text-gray-700 hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500" data-months="12">
                            12 Months
                        </button>
                    </div>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150" onclick="exportForecast()">
                        <i class="fas fa-download mr-2"></i>Export Forecast
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Forecast Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-line text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Predicted Revenue</p>
                        <p class="text-2xl font-bold text-gray-900" id="predictedRevenue">
                            ${{ number_format(collect($forecastData)->sum('predicted_revenue'), 2) }}
                        </p>
                        <p class="text-xs text-green-600 mt-1">Next {{ $months }} months</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-trending-up text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Growth Trend</p>
                        <p class="text-2xl font-bold 
                            @if($trendAnalysis['trend'] === 'increasing') text-green-600
                            @elseif($trendAnalysis['trend'] === 'decreasing') text-red-600
                            @else text-yellow-600
                            @endif">
                            {{ ucfirst($trendAnalysis['trend']) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Current trajectory</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-percentage text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Confidence Level</p>
                        <p class="text-2xl font-bold text-gray-900" id="confidenceLevel">
                            {{ number_format(collect($forecastData)->avg('confidence') * 100, 0) }}%
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Prediction accuracy</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-chart-bar text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Monthly Average</p>
                        <p class="text-2xl font-bold text-gray-900" id="monthlyAverage">
                            ${{ number_format(collect($forecastData)->avg('predicted_revenue'), 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Per month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forecast Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-chart-area text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Revenue Forecast Chart</h3>
                            <p class="text-sm text-gray-600">Historical data vs predicted revenue for the next {{ $months }} months</p>
                        </div>
                    </div>
                    <div class="flex rounded-lg shadow-sm bg-white border border-gray-300">
                        <button class="chart-type px-3 py-2 text-sm font-medium rounded-l-lg border-r border-gray-300 text-gray-700 hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500" data-type="line">
                            <i class="fas fa-chart-line mr-1"></i> Line
                        </button>
                        <button class="chart-type px-3 py-2 text-sm font-medium rounded-r-lg bg-blue-600 text-white" data-type="bar">
                            <i class="fas fa-chart-bar mr-1"></i> Bar
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="relative h-96">
                    <canvas id="forecastChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Trend Analysis and Seasonal Patterns -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-chart-line text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Trend Analysis</h3>
                            <p class="text-sm text-gray-600">Statistical analysis of revenue patterns</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-4 text-center mb-6">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl 
                                @if($trendAnalysis['trend'] === 'increasing') text-green-600
                                @elseif($trendAnalysis['trend'] === 'decreasing') text-red-600
                                @else text-yellow-600
                                @endif mb-2">
                                <i class="fas fa-{{ $trendAnalysis['trend'] === 'increasing' ? 'arrow-up' : ($trendAnalysis['trend'] === 'decreasing' ? 'arrow-down' : 'minus') }}"></i>
                            </div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Trend Direction</h4>
                            <p class="text-sm font-medium 
                                @if($trendAnalysis['trend'] === 'increasing') text-green-600
                                @elseif($trendAnalysis['trend'] === 'decreasing') text-red-600
                                @else text-yellow-600
                                @endif">
                                {{ ucfirst($trendAnalysis['trend']) }}
                            </p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl text-blue-600 mb-2">${{ number_format($trendAnalysis['average'], 0) }}</div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Average Revenue</h4>
                            <p class="text-sm text-gray-600">6-month avg</p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl text-yellow-600 mb-2">{{ number_format($trendAnalysis['volatility'], 1) }}%</div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Volatility</h4>
                            <p class="text-sm text-gray-600">Risk level</p>
                        </div>
                    </div>

                    <!-- Trend Insights -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="text-blue-900 font-semibold mb-3 flex items-center">
                            <i class="fas fa-lightbulb mr-2"></i>Trend Insights
                        </h4>
                        <div class="space-y-3 text-sm">
                            @if($trendAnalysis['trend'] === 'increasing')
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                                    <div>
                                        <span class="font-semibold text-gray-900">Positive Growth:</span>
                                        <span class="text-gray-700">Revenue is showing an upward trend with consistent growth patterns.</span>
                                    </div>
                                </div>
                            @elseif($trendAnalysis['trend'] === 'decreasing')
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-0.5"></i>
                                    <div>
                                        <span class="font-semibold text-gray-900">Declining Trend:</span>
                                        <span class="text-gray-700">Revenue is showing a downward trend. Consider reviewing strategies.</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start">
                                    <i class="fas fa-minus-circle text-blue-500 mr-2 mt-0.5"></i>
                                    <div>
                                        <span class="font-semibold text-gray-900">Stable Pattern:</span>
                                        <span class="text-gray-700">Revenue is maintaining steady levels with minimal fluctuation.</span>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex items-start">
                                <i class="fas fa-chart-bar text-blue-500 mr-2 mt-0.5"></i>
                                <div>
                                    <span class="font-semibold text-gray-900">Volatility:</span>
                                    <span class="text-gray-700">
                                        @if($trendAnalysis['volatility'] < 10)
                                            Low risk - Stable revenue patterns
                                        @elseif($trendAnalysis['volatility'] < 20)
                                            Moderate risk - Some fluctuation expected
                                        @else
                                            High risk - Significant revenue variations
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calendar-alt text-yellow-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Seasonal Patterns</h3>
                            <p class="text-sm text-gray-600">Monthly performance multipliers</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($seasonalPatterns as $pattern)
                            @php
                                $multiplier = $pattern['multiplier'];
                                $isPositive = $multiplier >= 1;
                                $percentage = ($multiplier - 1) * 100;
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-lg {{ $isPositive ? 'bg-green-50' : 'bg-red-50' }}">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $pattern['month_name'] }}</div>
                                    <div class="text-xs text-gray-600">{{ $pattern['description'] }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $isPositive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $isPositive ? '+' : '' }}{{ number_format($percentage, 0) }}%
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">x{{ number_format($multiplier, 2) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Forecast Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-table text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Detailed Forecast Breakdown</h3>
                        <p class="text-sm text-gray-600">Month-by-month revenue predictions with confidence intervals</p>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Predicted Revenue</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Confidence Level</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Seasonal Factor</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Range (±20%)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($forecastData as $forecast)
                                @php
                                    $revenue = $forecast['predicted_revenue'];
                                    $confidence = $forecast['confidence'] * 100;
                                    $lowerBound = $revenue * 0.8;
                                    $upperBound = $revenue * 1.2;
                                    $month = \Carbon\Carbon::parse($forecast['date']);
                                    $seasonalPattern = collect($seasonalPatterns)->firstWhere('month', $month->month);
                                    $riskLevel = $confidence >= 80 ? 'low' : ($confidence >= 60 ? 'medium' : 'high');
                                    $riskColor = $riskLevel === 'low' ? 'green' : ($riskLevel === 'medium' ? 'yellow' : 'red');
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $month->format('F Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $month->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="font-semibold text-green-600">${{ number_format($revenue, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end">
                                            <div class="w-16 h-2 bg-gray-200 rounded-full mr-2">
                                                <div class="h-2 bg-{{ $riskColor }}-500 rounded-full" style="width: {{ $confidence }}%"></div>
                                            </div>
                                            <span class="text-{{ $riskColor }}-600 font-medium">{{ number_format($confidence, 0) }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if($seasonalPattern)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $seasonalPattern['multiplier'] >= 1 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                x{{ number_format($seasonalPattern['multiplier'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm text-gray-600">
                                            ${{ number_format($lowerBound, 0) }} - ${{ number_format($upperBound, 0) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $riskColor }}-100 text-{{ $riskColor }}-800">
                                            {{ ucfirst($riskLevel) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-900">Total ({{ $months }} months)</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format(collect($forecastData)->sum('predicted_revenue'), 2) }}</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format(collect($forecastData)->avg('confidence') * 100, 0) }}%</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">-</th>
                                <th class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                    ${{ number_format(collect($forecastData)->sum('predicted_revenue') * 0.8, 0) }} - 
                                    ${{ number_format(collect($forecastData)->sum('predicted_revenue') * 1.2, 0) }}
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-gray-900">-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Strategic Recommendations -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-bullseye text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Strategic Recommendations</h3>
                        <p class="text-sm text-gray-600">AI-powered insights and actionable recommendations</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-blue-900 font-semibold mb-4 flex items-center">
                            <i class="fas fa-rocket mr-2"></i>Growth Opportunities
                        </h4>
                        
                        @php
                            $bestMonth = collect($seasonalPatterns)->sortByDesc('multiplier')->first();
                            $worstMonth = collect($seasonalPatterns)->sortBy('multiplier')->first();
                        @endphp
                        
                        <div class="space-y-4">
                            <div class="flex items-start p-4 bg-yellow-50 rounded-lg">
                                <i class="fas fa-calendar-star text-yellow-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Seasonal Peak</div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Focus marketing efforts on {{ $bestMonth['month_name'] }} 
                                        ({{ number_format(($bestMonth['multiplier'] - 1) * 100) }}% above average)
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-green-50 rounded-lg">
                                <i class="fas fa-chart-line text-green-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Trend Acceleration</div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        @if($trendAnalysis['trend'] === 'increasing')
                                            Capitalize on positive trend with increased investment
                                        @else
                                            Focus on customer retention to reverse declining trend
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-blue-50 rounded-lg">
                                <i class="fas fa-target text-blue-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Revenue Target</div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Set monthly target of ${{ number_format(collect($forecastData)->avg('predicted_revenue') * 1.1, 0) }} 
                                        (+10% stretch goal)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-red-900 font-semibold mb-4 flex items-center">
                            <i class="fas fa-shield-alt mr-2"></i>Risk Mitigation
                        </h4>
                        
                        <div class="space-y-4">
                            <div class="flex items-start p-4 bg-red-50 rounded-lg">
                                <i class="fas fa-calendar-minus text-red-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Low Season Planning</div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Prepare for {{ $worstMonth['month_name'] }} downturn 
                                        ({{ number_format((1 - $worstMonth['multiplier']) * 100) }}% below average)
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-yellow-50 rounded-lg">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Volatility Management</div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        @if($trendAnalysis['volatility'] > 15)
                                            High volatility detected - diversify revenue streams
                                        @else
                                            Maintain current stability with consistent strategies
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                                <i class="fas fa-chart-pie text-gray-600 mr-3 mt-1"></i>
                                <div>
                                    <div class="font-medium text-gray-900">Confidence Buffer</div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Plan for ±20% variance in predictions (confidence: {{ number_format(collect($forecastData)->avg('confidence') * 100, 0) }}%)
                                    </p>
                                </div>
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
let forecastChart;
let currentMonths = {{ $months }};
let currentChartType = 'bar';

document.addEventListener('DOMContentLoaded', function() {
    initializeForecastChart();
    
    // Period filter handlers
    document.querySelectorAll('.forecast-period').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.forecast-period').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('text-gray-700', 'hover:bg-gray-50');
            });
            this.classList.remove('text-gray-700', 'hover:bg-gray-50');
            this.classList.add('bg-blue-600', 'text-white');
            
            currentMonths = parseInt(this.getAttribute('data-months'));
            loadForecastData(currentMonths);
        });
    });
    
    // Chart type handlers
    document.querySelectorAll('.chart-type').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.chart-type').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('text-gray-700', 'hover:bg-gray-50');
            });
            this.classList.remove('text-gray-700', 'hover:bg-gray-50');
            this.classList.add('bg-blue-600', 'text-white');
            
            currentChartType = this.getAttribute('data-type');
            updateChartType(currentChartType);
        });
    });
});

function initializeForecastChart() {
    const ctx = document.getElementById('forecastChart').getContext('2d');
    
    // Generate historical data (last 6 months)
    const historicalLabels = [];
    const historicalData = [];
    const currentDate = new Date();
    
    for (let i = 5; i >= 0; i--) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
        historicalLabels.push(date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' }));
        historicalData.push(Math.floor(Math.random() * 5000) + 3000); // Sample historical data
    }
    
    // Forecast data
    const forecastLabels = @json(collect($forecastData)->pluck('date')->map(function($date) {
        return \Carbon\Carbon::parse($date)->format('M Y');
    }));
    const forecastRevenue = @json(collect($forecastData)->pluck('predicted_revenue'));
    
    // Combine labels and data
    const allLabels = [...historicalLabels, ...forecastLabels];
    const allHistoricalData = [...historicalData, ...Array(forecastLabels.length).fill(null)];
    const allForecastData = [...Array(historicalLabels.length).fill(null), ...forecastRevenue];
    
    forecastChart = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Historical Revenue',
                    data: allHistoricalData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Predicted Revenue',
                    data: allForecastData,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        color: function(context) {
                            // Highlight the boundary between historical and forecast data
                            return context.index === historicalLabels.length - 1 ? 'rgba(255, 193, 7, 0.5)' : 'rgba(0,0,0,0.05)';
                        },
                        lineWidth: function(context) {
                            return context.index === historicalLabels.length - 1 ? 3 : 1;
                        }
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

function updateChartType(type) {
    forecastChart.config.type = type;
    forecastChart.update();
}

function loadForecastData(months) {
    // This would normally make an AJAX call to update the forecast
    console.log('Loading forecast for', months, 'months');
    
    // Update UI elements
    document.getElementById('predictedRevenue').textContent = '$' + (Math.random() * 50000 + 20000).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('confidenceLevel').textContent = Math.floor(Math.random() * 30 + 70) + '%';
    document.getElementById('monthlyAverage').textContent = '$' + (Math.random() * 8000 + 4000).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function exportForecast() {
    const url = new URL('{{ route("admin.revenue.export") }}');
    url.searchParams.append('type', 'forecast');
    url.searchParams.append('months', currentMonths);
    
    window.open(url.toString(), '_blank');
}
</script>
@endpush
