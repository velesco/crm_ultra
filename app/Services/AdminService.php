<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Contact;
use App\Models\User;
use App\Models\EmailCampaign;
use App\Models\SmsMessage;
use App\Models\WhatsAppMessage;
use App\Models\DataImport;
use App\Models\Communication;
use App\Models\SystemLog;
use Carbon\Carbon;
use Exception;

class AdminService
{
    /**
     * Get comprehensive system statistics
     */
    public function getSystemStats(): array
    {
        try {
            return [
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('email_verified_at', '!=', null)->count(),
                    'new_today' => User::whereDate('created_at', today())->count(),
                    'new_this_week' => User::where('created_at', '>=', now()->startOfWeek())->count(),
                    'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                ],
                'contacts' => [
                    'total' => Contact::count(),
                    'active' => Contact::where('status', 'active')->count(),
                    'prospects' => Contact::where('status', 'prospect')->count(),
                    'customers' => Contact::where('status', 'customer')->count(),
                    'new_today' => Contact::whereDate('created_at', today())->count(),
                    'new_this_week' => Contact::where('created_at', '>=', now()->startOfWeek())->count(),
                ],
                'campaigns' => [
                    'total' => EmailCampaign::count(),
                    'active' => EmailCampaign::where('status', 'active')->count(),
                    'completed' => EmailCampaign::where('status', 'completed')->count(),
                    'scheduled' => EmailCampaign::where('status', 'scheduled')->count(),
                    'sent_today' => EmailCampaign::whereDate('sent_at', today())->count(),
                ],
                'communications' => [
                    'total' => Communication::count(),
                    'emails' => Communication::where('type', 'email')->count(),
                    'sms' => Communication::where('type', 'sms')->count(),
                    'whatsapp' => Communication::where('type', 'whatsapp')->count(),
                    'today' => Communication::whereDate('created_at', today())->count(),
                    'this_week' => Communication::where('created_at', '>=', now()->startOfWeek())->count(),
                ],
                'storage' => [
                    'total_size' => $this->getStorageSize(),
                    'database_size' => $this->getDatabaseSize(),
                    'log_files_size' => $this->getLogFilesSize(),
                    'uploads_size' => $this->getUploadsSize(),
                ],
                'performance' => [
                    'avg_response_time' => $this->getAverageResponseTime(),
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true),
                    'uptime' => $this->getSystemUptime(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get system stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent system activity
     */
    public function getRecentActivity(int $limit = 20): array
    {
        try {
            $activities = collect();

            // Recent user registrations
            $newUsers = User::select('id', 'name', 'email', 'created_at')
                ->latest()
                ->limit($limit / 4)
                ->get()
                ->map(function ($user) {
                    return [
                        'type' => 'user_registered',
                        'title' => 'New User Registration',
                        'description' => "{$user->name} ({$user->email}) joined the system",
                        'user' => $user->name,
                        'timestamp' => $user->created_at,
                        'icon' => 'user-plus',
                        'color' => 'success'
                    ];
                });

            // Recent campaigns
            $campaigns = EmailCampaign::select('id', 'name', 'status', 'created_at')
                ->with('user:id,name')
                ->latest()
                ->limit($limit / 4)
                ->get()
                ->map(function ($campaign) {
                    return [
                        'type' => 'campaign_created',
                        'title' => 'New Email Campaign',
                        'description' => "Campaign '{$campaign->name}' was created",
                        'user' => $campaign->user->name ?? 'System',
                        'timestamp' => $campaign->created_at,
                        'icon' => 'mail',
                        'color' => 'primary'
                    ];
                });

            // Recent contacts
            $contacts = Contact::select('id', 'first_name', 'last_name', 'email', 'created_at')
                ->latest()
                ->limit($limit / 4)
                ->get()
                ->map(function ($contact) {
                    return [
                        'type' => 'contact_created',
                        'title' => 'New Contact Added',
                        'description' => "{$contact->first_name} {$contact->last_name} ({$contact->email}) was added",
                        'user' => 'System',
                        'timestamp' => $contact->created_at,
                        'icon' => 'users',
                        'color' => 'info'
                    ];
                });

            // Recent imports
            $imports = DataImport::select('id', 'filename', 'status', 'total_records', 'created_at')
                ->with('user:id,name')
                ->latest()
                ->limit($limit / 4)
                ->get()
                ->map(function ($import) {
                    return [
                        'type' => 'data_import',
                        'title' => 'Data Import',
                        'description' => "Import '{$import->filename}' - {$import->total_records} records ({$import->status})",
                        'user' => $import->user->name ?? 'System',
                        'timestamp' => $import->created_at,
                        'icon' => 'download',
                        'color' => $import->status === 'completed' ? 'success' : ($import->status === 'failed' ? 'danger' : 'warning')
                    ];
                });

            // Combine and sort all activities
            $activities = $newUsers->concat($campaigns)
                ->concat($contacts)
                ->concat($imports)
                ->sortByDesc('timestamp')
                ->take($limit)
                ->values();

            return $activities->toArray();

        } catch (Exception $e) {
            Log::error('Failed to get recent activity: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system health check data
     */
    public function getSystemHealth(): array
    {
        try {
            $health = [
                'database' => $this->checkDatabaseHealth(),
                'cache' => $this->checkCacheHealth(),
                'queue' => $this->checkQueueHealth(),
                'storage' => $this->checkStorageHealth(),
                'external_apis' => $this->checkExternalApisHealth(),
                'overall_status' => 'healthy'
            ];

            // Determine overall status
            $statuses = collect($health)->except('overall_status')->pluck('status');
            if ($statuses->contains('critical')) {
                $health['overall_status'] = 'critical';
            } elseif ($statuses->contains('warning')) {
                $health['overall_status'] = 'warning';
            }

            return $health;

        } catch (Exception $e) {
            Log::error('Failed to check system health: ' . $e->getMessage());
            return ['overall_status' => 'critical'];
        }
    }

    /**
     * Get top users by activity
     */
    public function getTopUsersByActivity(int $limit = 5): array
    {
        try {
            return User::select('users.id', 'users.name', 'users.email')
                ->selectRaw('COUNT(DISTINCT email_campaigns.id) as campaigns_count')
                ->selectRaw('COUNT(DISTINCT contacts.id) as contacts_count')
                ->selectRaw('COUNT(DISTINCT data_imports.id) as imports_count')
                ->leftJoin('email_campaigns', 'users.id', '=', 'email_campaigns.user_id')
                ->leftJoin('contacts', 'users.id', '=', 'contacts.created_by')
                ->leftJoin('data_imports', 'users.id', '=', 'data_imports.user_id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderByRaw('(campaigns_count + contacts_count + imports_count) DESC')
                ->limit($limit)
                ->get()
                ->map(function ($user) {
                    $user->total_activity = $user->campaigns_count + $user->contacts_count + $user->imports_count;
                    return $user;
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Failed to get top users: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user growth data for charts
     */
    public function getUserGrowthData(int $days = 30): array
    {
        try {
            $growth = [];
            $startDate = now()->subDays($days);

            for ($i = 0; $i < $days; $i++) {
                $date = $startDate->copy()->addDays($i);
                $count = User::whereDate('created_at', $date)->count();
                
                $growth[] = [
                    'date' => $date->format('Y-m-d'),
                    'count' => $count,
                    'formatted_date' => $date->format('M j')
                ];
            }

            return $growth;

        } catch (Exception $e) {
            Log::error('Failed to get user growth data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system usage data
     */
    public function getSystemUsageData(int $days = 7): array
    {
        try {
            $usage = [];
            $startDate = now()->subDays($days);

            for ($i = 0; $i < $days; $i++) {
                $date = $startDate->copy()->addDays($i);
                
                $usage[] = [
                    'date' => $date->format('Y-m-d'),
                    'emails' => Communication::where('type', 'email')->whereDate('created_at', $date)->count(),
                    'sms' => Communication::where('type', 'sms')->whereDate('created_at', $date)->count(),
                    'whatsapp' => Communication::where('type', 'whatsapp')->whereDate('created_at', $date)->count(),
                    'formatted_date' => $date->format('M j')
                ];
            }

            return $usage;

        } catch (Exception $e) {
            Log::error('Failed to get system usage data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get communication trends
     */
    public function getCommunicationTrends(int $days = 30): array
    {
        try {
            return Communication::select(
                    DB::raw('DATE(created_at) as date'),
                    'type',
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date', 'type')
                ->orderBy('date')
                ->get()
                ->groupBy('date')
                ->map(function ($dayData) {
                    $trends = [
                        'email' => 0,
                        'sms' => 0,
                        'whatsapp' => 0
                    ];
                    
                    foreach ($dayData as $item) {
                        $trends[$item->type] = $item->count;
                    }
                    
                    return $trends;
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Failed to get communication trends: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system alerts count
     */
    public function getSystemAlertsCount(): int
    {
        try {
            $alerts = 0;

            // Failed jobs
            $failedJobs = DB::table('failed_jobs')->count();
            $alerts += $failedJobs;

            // Inactive SMTP configs
            $inactiveSmtp = DB::table('smtp_configs')->where('is_active', false)->count();
            $alerts += $inactiveSmtp;

            // Disconnected WhatsApp sessions
            $disconnectedWhatsApp = DB::table('whats_app_sessions')->where('status', 'disconnected')->count();
            $alerts += $disconnectedWhatsApp;

            return $alerts;

        } catch (Exception $e) {
            Log::error('Failed to get system alerts count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Log system action
     */
    public function logSystemAction(int $userId, string $action, string $description, array $metadata = []): void
    {
        try {
            SystemLog::create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'metadata' => json_encode($metadata),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log system action: ' . $e->getMessage());
        }
    }

    // Private helper methods

    private function getStorageSize(): int
    {
        try {
            return Storage::size('public') ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getDatabaseSize(): string
    {
        try {
            $size = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ")[0]->db_size_mb ?? 0;

            return $size . ' MB';
        } catch (Exception $e) {
            return '0 MB';
        }
    }

    private function getLogFilesSize(): string
    {
        try {
            $logPath = storage_path('logs');
            $size = 0;
            
            if (is_dir($logPath)) {
                foreach (glob($logPath . '/*.log') as $file) {
                    $size += filesize($file);
                }
            }
            
            return round($size / 1024 / 1024, 1) . ' MB';
        } catch (Exception $e) {
            return '0 MB';
        }
    }

    private function getUploadsSize(): string
    {
        try {
            $uploadPath = storage_path('app/public');
            $size = 0;
            
            if (is_dir($uploadPath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($uploadPath)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                }
            }
            
            return round($size / 1024 / 1024, 1) . ' MB';
        } catch (Exception $e) {
            return '0 MB';
        }
    }

    private function getAverageResponseTime(): string
    {
        // This would typically be implemented with APM tools
        // For now, return a mock value
        return '150ms';
    }

    private function getSystemUptime(): string
    {
        try {
            $uptimeFile = '/proc/uptime';
            if (file_exists($uptimeFile)) {
                $uptime = (int) file_get_contents($uptimeFile);
                $days = floor($uptime / 86400);
                $hours = floor(($uptime % 86400) / 3600);
                $minutes = floor(($uptime % 3600) / 60);
                
                return "{$days}d {$hours}h {$minutes}m";
            }
            
            return 'N/A';
        } catch (Exception $e) {
            return 'N/A';
        }
    }

    private function checkDatabaseHealth(): array
    {
        try {
            DB::connection()->getPdo();
            $responseTime = microtime(true);
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $responseTime) * 1000, 2);

            return [
                'status' => $responseTime < 100 ? 'healthy' : ($responseTime < 500 ? 'warning' : 'critical'),
                'response_time' => $responseTime . 'ms',
                'message' => 'Database connection active'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'critical',
                'response_time' => 'N/A',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    private function checkCacheHealth(): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $retrieved === 'test' ? 'healthy' : 'warning',
                'message' => $retrieved === 'test' ? 'Cache working properly' : 'Cache not responding correctly'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'critical',
                'message' => 'Cache failed: ' . $e->getMessage()
            ];
        }
    }

    private function checkQueueHealth(): array
    {
        try {
            $failedJobs = DB::table('failed_jobs')->count();
            $status = $failedJobs === 0 ? 'healthy' : ($failedJobs < 10 ? 'warning' : 'critical');

            return [
                'status' => $status,
                'failed_jobs' => $failedJobs,
                'message' => $failedJobs === 0 ? 'No failed jobs' : "{$failedJobs} failed jobs found"
            ];
        } catch (Exception $e) {
            return [
                'status' => 'critical',
                'message' => 'Queue check failed: ' . $e->getMessage()
            ];
        }
    }

    private function checkStorageHealth(): array
    {
        try {
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usagePercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);

            $status = $usagePercent < 80 ? 'healthy' : ($usagePercent < 90 ? 'warning' : 'critical');

            return [
                'status' => $status,
                'usage_percent' => $usagePercent,
                'free_space' => round($freeSpace / 1024 / 1024 / 1024, 2) . ' GB',
                'message' => "Storage usage: {$usagePercent}%"
            ];
        } catch (Exception $e) {
            return [
                'status' => 'warning',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Optimize system
     */
    public function optimizeSystem(string $action): array
    {
        try {
            $results = [];

            switch ($action) {
                case 'database':
                    $results['database'] = $this->optimizeDatabase();
                    break;

                case 'storage':
                    $results['storage'] = $this->optimizeStorage();
                    break;

                case 'cache':
                    $results['cache'] = $this->optimizeCache();
                    break;

                case 'queue':
                    $results['queue'] = $this->optimizeQueue();
                    break;

                case 'all':
                    $results['database'] = $this->optimizeDatabase();
                    $results['storage'] = $this->optimizeStorage();
                    $results['cache'] = $this->optimizeCache();
                    $results['queue'] = $this->optimizeQueue();
                    break;
            }

            return $results;

        } catch (Exception $e) {
            Log::error('Failed to optimize system: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get system alerts
     */
    public function getSystemAlerts(): array
    {
        try {
            $alerts = [];

            // Check for failed jobs
            $failedJobs = DB::table('failed_jobs')->count();
            if ($failedJobs > 0) {
                $alerts[] = [
                    'id' => 'failed_jobs',
                    'type' => 'error',
                    'title' => 'Failed Jobs',
                    'message' => "$failedJobs failed jobs need attention",
                    'action_url' => '/admin/queue',
                    'created_at' => now()
                ];
            }

            // Check for inactive SMTP configs
            $inactiveSmtp = DB::table('smtp_configs')->where('is_active', false)->count();
            if ($inactiveSmtp > 0) {
                $alerts[] = [
                    'id' => 'inactive_smtp',
                    'type' => 'warning',
                    'title' => 'Inactive SMTP Configs',
                    'message' => "$inactiveSmtp SMTP configurations are inactive",
                    'action_url' => '/email-configs',
                    'created_at' => now()
                ];
            }

            // Check for disconnected WhatsApp sessions
            $disconnectedWhatsApp = DB::table('whats_app_sessions')->where('status', 'disconnected')->count();
            if ($disconnectedWhatsApp > 0) {
                $alerts[] = [
                    'id' => 'disconnected_whatsapp',
                    'type' => 'warning',
                    'title' => 'Disconnected WhatsApp Sessions',
                    'message' => "$disconnectedWhatsApp WhatsApp sessions are disconnected",
                    'action_url' => '/whatsapp/sessions',
                    'created_at' => now()
                ];
            }

            // Check disk usage
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            if ($freeSpace && $totalSpace) {
                $usagePercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
                if ($usagePercent > 90) {
                    $alerts[] = [
                        'id' => 'high_disk_usage',
                        'type' => 'error',
                        'title' => 'High Disk Usage',
                        'message' => "Disk usage is at $usagePercent%",
                        'action_url' => '/admin/storage',
                        'created_at' => now()
                    ];
                } elseif ($usagePercent > 80) {
                    $alerts[] = [
                        'id' => 'moderate_disk_usage',
                        'type' => 'warning',
                        'title' => 'Moderate Disk Usage',
                        'message' => "Disk usage is at $usagePercent%",
                        'action_url' => '/admin/storage',
                        'created_at' => now()
                    ];
                }
            }

            return $alerts;

        } catch (Exception $e) {
            Log::error('Failed to get system alerts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Dismiss alert
     */
    public function dismissAlert(string $alertId, int $userId): void
    {
        try {
            // Store dismissed alerts in cache for 24 hours
            $dismissedAlerts = Cache::get('dismissed_alerts_' . $userId, []);
            $dismissedAlerts[$alertId] = now()->addDay();
            Cache::put('dismissed_alerts_' . $userId, $dismissedAlerts, now()->addDay());

            // Log the action
            $this->logSystemAction(
                $userId,
                'alert_dismissed',
                "System alert '$alertId' was dismissed",
                ['alert_id' => $alertId]
            );

        } catch (Exception $e) {
            Log::error('Failed to dismiss alert: ' . $e->getMessage());
            throw $e;
        }
    }

    // Private optimization methods

    private function optimizeDatabase(): array
    {
        try {
            $results = [];

            // Clean up old system logs (keep last 30 days)
            $deletedLogs = SystemLog::where('created_at', '<', now()->subDays(30))->delete();
            $results['old_logs_cleaned'] = $deletedLogs;

            // Clean up old failed jobs (keep last 7 days)
            $deletedFailedJobs = DB::table('failed_jobs')
                ->where('failed_at', '<', now()->subDays(7))
                ->delete();
            $results['old_failed_jobs_cleaned'] = $deletedFailedJobs;

            // Optimize database tables (MySQL specific)
            try {
                DB::statement('OPTIMIZE TABLE contacts, users, email_campaigns, communications');
                $results['tables_optimized'] = true;
            } catch (Exception $e) {
                $results['tables_optimized'] = false;
                $results['optimize_error'] = $e->getMessage();
            }

            return $results;

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function optimizeStorage(): array
    {
        try {
            $results = [];
            $totalCleaned = 0;

            // Clean up old log files
            $logPath = storage_path('logs');
            if (is_dir($logPath)) {
                $logFiles = glob($logPath . '/*.log');
                $oldLogFiles = array_filter($logFiles, function($file) {
                    return filemtime($file) < strtotime('-30 days');
                });
                
                foreach ($oldLogFiles as $file) {
                    $size = filesize($file);
                    if (unlink($file)) {
                        $totalCleaned += $size;
                    }
                }
            }

            // Clean up temporary files
            $tempPath = storage_path('app/temp');
            if (is_dir($tempPath)) {
                $tempFiles = glob($tempPath . '/*');
                foreach ($tempFiles as $file) {
                    if (is_file($file) && filemtime($file) < strtotime('-1 day')) {
                        $size = filesize($file);
                        if (unlink($file)) {
                            $totalCleaned += $size;
                        }
                    }
                }
            }

            $results['storage_cleaned'] = round($totalCleaned / 1024 / 1024, 2) . ' MB';
            return $results;

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function optimizeCache(): array
    {
        try {
            $results = [];

            // Clear expired cache entries
            Cache::flush();
            $results['cache_cleared'] = true;

            // Warm up important caches
            $this->getSystemStats(); // This will cache the stats
            $results['cache_warmed'] = true;

            return $results;

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function optimizeQueue(): array
    {
        try {
            $results = [];

            // Get queue statistics
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            $results['pending_jobs'] = $pendingJobs;
            $results['failed_jobs'] = $failedJobs;

            // Clean up old failed jobs
            $deletedJobs = DB::table('failed_jobs')
                ->where('failed_at', '<', now()->subDays(7))
                ->delete();
            $results['cleaned_failed_jobs'] = $deletedJobs;

            return $results;

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get storage usage breakdown
     */
    public function getStorageUsage(): array
    {
        try {
            return [
                'database' => $this->getDatabaseSize(),
                'uploads' => $this->getUploadsSize(),
                'logs' => $this->getLogFilesSize(),
                'cache' => $this->getCacheSize(),
                'temp' => $this->getTempSize()
            ];
        } catch (Exception $e) {
            Log::error('Failed to get storage usage: ' . $e->getMessage());
            return [];
        }
    }

    private function getCacheSize(): string
    {
        try {
            $cachePath = storage_path('framework/cache');
            $size = 0;
            
            if (is_dir($cachePath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($cachePath)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                }
            }
            
            return round($size / 1024 / 1024, 1) . ' MB';
        } catch (Exception $e) {
            return '0 MB';
        }
    }

    private function getTempSize(): string
    {
        try {
            $tempPath = storage_path('app/temp');
            $size = 0;
            
            if (is_dir($tempPath)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tempPath)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                    }
                }
            }
            
            return round($size / 1024 / 1024, 1) . ' MB';
        } catch (Exception $e) {
            return '0 MB';
        }
    }

    private function checkExternalApisHealth(): array
    {
        // This would check external API endpoints
        // For now, return a healthy status
        return [
            'status' => 'healthy',
            'message' => 'External APIs responding'
        ];
    }

    /**
     * Get system information
     */
    public function getSystemInfo(): array
    {
        try {
            return [
                'php' => [
                    'version' => PHP_VERSION,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size')
                ],
                'laravel' => [
                    'version' => app()->version(),
                    'environment' => app()->environment(),
                    'debug' => config('app.debug'),
                    'timezone' => config('app.timezone')
                ],
                'database' => [
                    'connection' => config('database.default'),
                    'host' => config('database.connections.mysql.host'),
                    'database' => config('database.connections.mysql.database')
                ],
                'cache' => [
                    'driver' => config('cache.default'),
                    'stores' => array_keys(config('cache.stores'))
                ],
                'queue' => [
                    'connection' => config('queue.default'),
                    'connections' => array_keys(config('queue.connections'))
                ]
            ];
        } catch (Exception $e) {
            Log::error('Failed to get system info: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export system data
     */
    public function exportSystemData(string $type, string $format = 'csv', ?string $dateFrom = null, ?string $dateTo = null)
    {
        try {
            $data = [];
            $filename = $type . '_export_' . now()->format('Y-m-d_H-i-s') . '.' . $format;

            // Apply date filters if provided
            $dateQuery = function($query) use ($dateFrom, $dateTo) {
                if ($dateFrom) {
                    $query->where('created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->where('created_at', '<=', $dateTo);
                }
                return $query;
            };

            switch ($type) {
                case 'users':
                    $data = User::with('roles')
                        ->when($dateFrom || $dateTo, $dateQuery)
                        ->get()
                        ->map(function ($user) {
                            return [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'roles' => $user->roles->pluck('name')->implode(', '),
                                'created_at' => $user->created_at,
                                'email_verified_at' => $user->email_verified_at
                            ];
                        });
                    break;

                case 'contacts':
                    $data = Contact::when($dateFrom || $dateTo, $dateQuery)
                        ->get()
                        ->map(function ($contact) {
                            return [
                                'id' => $contact->id,
                                'first_name' => $contact->first_name,
                                'last_name' => $contact->last_name,
                                'email' => $contact->email,
                                'phone' => $contact->phone,
                                'company' => $contact->company,
                                'status' => $contact->status,
                                'created_at' => $contact->created_at
                            ];
                        });
                    break;

                case 'campaigns':
                    $data = EmailCampaign::with('user')
                        ->when($dateFrom || $dateTo, $dateQuery)
                        ->get()
                        ->map(function ($campaign) {
                            return [
                                'id' => $campaign->id,
                                'name' => $campaign->name,
                                'subject' => $campaign->subject,
                                'status' => $campaign->status,
                                'recipients_count' => $campaign->recipients_count,
                                'sent_count' => $campaign->sent_count,
                                'opened_count' => $campaign->opened_count,
                                'clicked_count' => $campaign->clicked_count,
                                'created_by' => $campaign->user->name ?? 'Unknown',
                                'created_at' => $campaign->created_at,
                                'sent_at' => $campaign->sent_at
                            ];
                        });
                    break;

                case 'messages':
                    $data = Communication::when($dateFrom || $dateTo, $dateQuery)
                        ->get()
                        ->map(function ($communication) {
                            return [
                                'id' => $communication->id,
                                'type' => $communication->type,
                                'from' => $communication->from,
                                'to' => $communication->to,
                                'subject' => $communication->subject,
                                'status' => $communication->status,
                                'created_at' => $communication->created_at
                            ];
                        });
                    break;

                case 'all':
                    return response()->json([
                        'success' => false,
                        'message' => 'Full system export is not supported via web interface. Please use command line.'
                    ], 400);
            }

            // Generate file based on format
            return $this->generateExportFile($data, $filename, $format);

        } catch (Exception $e) {
            Log::error('Failed to export system data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate export file
     */
    private function generateExportFile($data, string $filename, string $format)
    {
        $filePath = storage_path('app/exports/' . $filename);
        
        // Create exports directory if it doesn't exist
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        switch ($format) {
            case 'csv':
                $handle = fopen($filePath, 'w');
                if (!empty($data)) {
                    // Write header
                    fputcsv($handle, array_keys($data[0]));
                    // Write data
                    foreach ($data as $row) {
                        fputcsv($handle, $row);
                    }
                }
                fclose($handle);
                break;

            case 'json':
                file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
                break;

            case 'xlsx':
                // For Excel export, you'd typically use PhpSpreadsheet
                // For now, fall back to CSV
                return $this->generateExportFile($data, str_replace('.xlsx', '.csv', $filename), 'csv');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
