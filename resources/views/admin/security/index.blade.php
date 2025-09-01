@extends('layouts.app')

@section('title', 'Security Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                        <div>
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                                    <i class="fas fa-shield-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Security Dashboard</h1>
                                    <p class="text-gray-600 dark:text-gray-400">Monitor login attempts, blocked IPs, and security threats</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.security.login-attempts') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-list mr-2"></i>
                                View All Attempts
                            </a>
                            <button type="button" onclick="clearOldAttempts()"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-trash mr-2"></i>
                                Clear Old
                            </button>
                            <a href="{{ route('admin.security.export') }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                                <i class="fas fa-download mr-2"></i>
                                Export Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Attempts Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide">Total Attempts</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white" id="total-attempts">
                                {{ number_format($stats['total_attempts']) }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-full">
                            <i class="fas fa-sign-in-alt text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 h-1"></div>
            </div>

            <!-- Failed Attempts Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-600 dark:text-red-400 uppercase tracking-wide">Failed Attempts</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white" id="failed-attempts">
                                {{ number_format($stats['failed_attempts']) }}
                            </p>
                        </div>
                        <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-full">
                            <i class="fas fa-times-circle text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 h-1"></div>
            </div>

            <!-- Blocked Attempts Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-wide">Blocked Attempts</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white" id="blocked-attempts">
                                {{ number_format($stats['blocked_attempts']) }}
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-full">
                            <i class="fas fa-ban text-yellow-600 dark:text-yellow-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 h-1"></div>
            </div>

            <!-- Currently Blocked Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400 uppercase tracking-wide">Currently Blocked</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white" id="currently-blocked">
                                {{ number_format($stats['currently_blocked']) }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full">
                            <i class="fas fa-shield-alt text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 h-1"></div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Security Activity Chart -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Security Activity</h3>
                            <div class="inline-flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
                                <button type="button" onclick="loadChartData('day')"
                                        class="px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md bg-white dark:bg-gray-600 shadow-sm transition-colors duration-200">
                                    24h
                                </button>
                                <button type="button" onclick="loadChartData('week')"
                                        class="px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md hover:bg-white dark:hover:bg-gray-600 transition-colors duration-200">
                                    7d
                                </button>
                                <button type="button" onclick="loadChartData('month')"
                                        class="px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md hover:bg-white dark:hover:bg-gray-600 transition-colors duration-200">
                                    30d
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="h-80">
                            <canvas id="securityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Statistics -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Today's Activity</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Attempts</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['today']['total']) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed Attempts</p>
                            <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['today']['failed']) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Blocked Attempts</p>
                            <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($stats['today']['blocked']) }}</p>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">This Week</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['this_week']['total']) }} total</p>
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ number_format($stats['this_week']['failed']) }} failed</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">This Month</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($stats['this_month']['total']) }} total</p>
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ number_format($stats['this_month']['failed']) }} failed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Suspicious IPs -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Suspicious IP Addresses</h3>
                        <span class="px-3 py-1 text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200 rounded-full">
                            Last 24h
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    @if(count($suspiciousIps) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Failed Attempts</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Attempt</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach($suspiciousIps as $ip)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $ip['ip_address'] }}</span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">
                                                {{ $ip['failed_count'] }}
                                            </span>
                                            <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">/ {{ $ip['attempt_count'] }}</span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($ip['last_attempt'])->diffForHumans() }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <button onclick="blockIp('{{ $ip['ip_address'] }}')"
                                                    class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900/50 dark:hover:bg-yellow-900/70 text-yellow-800 dark:text-yellow-200 transition-colors duration-200">
                                                <i class="fas fa-ban mr-1"></i>
                                                Block
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-shield-check text-4xl text-green-500 dark:text-green-400 mb-4"></i>
                            <p class="text-gray-500 dark:text-gray-400">No suspicious activity detected</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Top Failed Emails -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Failed Emails</h3>
                        <span class="px-3 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-200 rounded-full">
                            Last 7 days
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    @if(count($topFailedEmails) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Attempts</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Attempt</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach($topFailedEmails as $email)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ Str::limit($email['email'], 25) }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">
                                                {{ $email['attempt_count'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($email['last_attempt'])->diffForHumans() }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <button onclick="blockUser('{{ $email['email'] }}')"
                                                    class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900/50 dark:hover:bg-yellow-900/70 text-yellow-800 dark:text-yellow-200 transition-colors duration-200">
                                                <i class="fas fa-ban mr-1"></i>
                                                Block
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-user-check text-4xl text-green-500 dark:text-green-400 mb-4"></i>
                            <p class="text-gray-500 dark:text-gray-400">No failed login patterns detected</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Currently Blocked Users -->
        @if(count($blockedUsers) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Currently Blocked Users & IPs</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email/IP</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Blocked Until</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($blockedUsers as $blocked)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $blocked['email'] }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $blocked['ip_address'] }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($blocked['blocked_until'])
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($blocked['blocked_until'])->format('M j, Y H:i') }}
                                        </div>
                                        <div class="text-xs text-red-600 dark:text-red-400">
                                            {{ \Carbon\Carbon::parse($blocked['blocked_until'])->diffForHumans() }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    @if(isset($blocked['metadata']['reason']))
                                        {{ $blocked['metadata']['reason'] }}
                                    @else
                                        Automatic block
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button onclick="unblockIp('{{ $blocked['ip_address'] }}')"
                                                class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-green-100 hover:bg-green-200 dark:bg-green-900/50 dark:hover:bg-green-900/70 text-green-800 dark:text-green-200 transition-colors duration-200">
                                            <i class="fas fa-unlock mr-1"></i>
                                            Unblock IP
                                        </button>
                                        @if($blocked['email'] !== 'system_block')
                                        <button onclick="unblockUser('{{ $blocked['email'] }}')"
                                                class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/50 dark:hover:bg-blue-900/70 text-blue-800 dark:text-blue-200 transition-colors duration-200">
                                            <i class="fas fa-user-unlock mr-1"></i>
                                            Unblock User
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Block IP Modal -->
<div id="blockIpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Block IP Address</h3>
            <button type="button" onclick="closeModal('blockIpModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="blockIpForm" class="space-y-4">
            <input type="hidden" id="blockIpAddress" name="ip_address">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Address</label>
                <input type="text" id="displayIpAddress" readonly
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Block Duration (hours)</label>
                <select name="duration" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="1">1 hour</option>
                    <option value="6">6 hours</option>
                    <option value="24" selected>24 hours</option>
                    <option value="168">1 week</option>
                    <option value="720">1 month</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason (optional)</label>
                <textarea name="reason" rows="3" placeholder="Reason for blocking this IP..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('blockIpModal')"
                        class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors duration-200">
                    <i class="fas fa-ban mr-2"></i>Block IP
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Block User Modal -->
<div id="blockUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Block User Email</h3>
            <button type="button" onclick="closeModal('blockUserModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="blockUserForm" class="space-y-4">
            <input type="hidden" id="blockUserEmail" name="email">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                <input type="text" id="displayUserEmail" readonly
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Block Duration (hours)</label>
                <select name="duration" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="1">1 hour</option>
                    <option value="6">6 hours</option>
                    <option value="24" selected>24 hours</option>
                    <option value="168">1 week</option>
                    <option value="720">1 month</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason (optional)</label>
                <textarea name="reason" rows="3" placeholder="Reason for blocking this user..."
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('blockUserModal')"
                        class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors duration-200">
                    <i class="fas fa-ban mr-2"></i>Block User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Clear Old Attempts Modal -->
<div id="clearOldModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Clear Old Login Attempts</h3>
            <button type="button" onclick="closeModal('clearOldModal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="clearOldForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delete attempts older than (days)</label>
                <select name="days" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="7">7 days</option>
                    <option value="30" selected>30 days</option>
                    <option value="90">90 days</option>
                    <option value="365">1 year</option>
                </select>
            </div>
            
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 mt-1 mr-3"></i>
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        This action cannot be undone. Old login attempts will be permanently deleted.
                    </p>
                </div>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeModal('clearOldModal')"
                        class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-800 dark:text-white rounded-lg transition-colors duration-200">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                    <i class="fas fa-trash mr-2"></i>Clear Old Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
let securityChart;
let currentPeriod = 'day';

$(document).ready(function() {
    loadChartData('day');
    setupEventHandlers();
    
    // Auto refresh every 30 seconds
    setInterval(refreshStats, 30000);
});

function setupEventHandlers() {
    // Block IP form
    $('#blockIpForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.post('{{ route("admin.security.block-ip") }}', Object.fromEntries(formData))
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    closeModal('blockIpModal');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Error blocking IP: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    });
    
    // Block User form
    $('#blockUserForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.post('{{ route("admin.security.block-user") }}', Object.fromEntries(formData))
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    closeModal('blockUserModal');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Error blocking user: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    });
    
    // Clear Old form
    $('#clearOldForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.post('{{ route("admin.security.clear-old") }}', Object.fromEntries(formData))
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    closeModal('clearOldModal');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Error clearing data: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    });
}

function loadChartData(period) {
    currentPeriod = period;
    
    // Update button states
    document.querySelectorAll('.inline-flex.rounded-lg button').forEach(btn => {
        btn.classList.remove('bg-white', 'dark:bg-gray-600', 'shadow-sm');
        btn.classList.add('hover:bg-white', 'dark:hover:bg-gray-600');
    });
    event.target.classList.add('bg-white', 'dark:bg-gray-600', 'shadow-sm');
    event.target.classList.remove('hover:bg-white', 'dark:hover:bg-gray-600');
    
    $.get('{{ route("admin.security.chart-data") }}', { period: period })
        .done(function(data) {
            updateChart(data);
        })
        .fail(function() {
            showToast('error', 'Error loading chart data');
        });
}

function updateChart(data) {
    const ctx = document.getElementById('securityChart').getContext('2d');
    
    if (securityChart) {
        securityChart.destroy();
    }
    
    securityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.label),
            datasets: [{
                label: 'Failed Attempts',
                data: data.map(item => item.failed),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }, {
                label: 'Successful Logins',
                data: data.map(item => item.success),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4
            }, {
                label: 'Blocked Attempts',
                data: data.map(item => item.blocked),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

function blockIp(ipAddress) {
    document.getElementById('blockIpAddress').value = ipAddress;
    document.getElementById('displayIpAddress').value = ipAddress;
    showModal('blockIpModal');
}

function blockUser(email) {
    document.getElementById('blockUserEmail').value = email;
    document.getElementById('displayUserEmail').value = email;
    showModal('blockUserModal');
}

function unblockIp(ipAddress) {
    if (confirm(`Are you sure you want to unblock IP ${ipAddress}?`)) {
        $.post('{{ route("admin.security.unblock-ip") }}', { ip_address: ipAddress })
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Error unblocking IP: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    }
}

function unblockUser(email) {
    if (confirm(`Are you sure you want to unblock user ${email}?`)) {
        $.post('{{ route("admin.security.unblock-user") }}', { email: email })
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .fail(function(xhr) {
                showToast('error', 'Error unblocking user: ' + (xhr.responseJSON?.message || 'Unknown error'));
            });
    }
}

function clearOldAttempts() {
    showModal('clearOldModal');
}

function refreshStats() {
    $.get('{{ route("admin.security.index") }}?ajax=1')
        .done(function(data) {
            document.getElementById('total-attempts').textContent = data.stats.total_attempts.toLocaleString();
            document.getElementById('failed-attempts').textContent = data.stats.failed_attempts.toLocaleString();
            document.getElementById('blocked-attempts').textContent = data.stats.blocked_attempts.toLocaleString();
            document.getElementById('currently-blocked').textContent = data.stats.currently_blocked.toLocaleString();
        })
        .fail(function() {
            // Silently fail - don't show error for auto-refresh
        });
}

function showModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function showToast(type, message) {
    // Create toast notification
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-300 translate-x-full`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
</script>
@endpush
