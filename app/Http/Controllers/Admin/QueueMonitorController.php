<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\MetricsRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\Contracts\WorkloadRepository;
use Carbon\Carbon;

class QueueMonitorController extends Controller
{
    protected $jobRepository;
    protected $supervisorRepository;
    protected $masterSupervisorRepository;
    protected $metricsRepository;
    protected $workloadRepository;

    public function __construct(
        JobRepository $jobRepository,
        SupervisorRepository $supervisorRepository,
        MasterSupervisorRepository $masterSupervisorRepository,
        MetricsRepository $metricsRepository,
        WorkloadRepository $workloadRepository
    ) {
        $this->middleware(['auth', 'role:super_admin|admin']);
        $this->jobRepository = $jobRepository;
        $this->supervisorRepository = $supervisorRepository;
        $this->masterSupervisorRepository = $masterSupervisorRepository;
        $this->metricsRepository = $metricsRepository;
        $this->workloadRepository = $workloadRepository;
    }

    /**
     * Display queue monitoring dashboard
     */
    public function index(Request $request)
    {
        $stats = $this->getQueueStats();
        $failedJobs = $this->getFailedJobs(10);
        $recentJobs = $this->getRecentJobs(20);
        $supervisors = $this->getSupervisors();
        $workload = $this->getWorkload();

        // Get chart data for the last 24 hours
        $chartData = $this->getChartData();

        if ($request->ajax()) {
            return response()->json([
                'stats' => $stats,
                'failedJobs' => $failedJobs,
                'recentJobs' => $recentJobs,
                'supervisors' => $supervisors,
                'workload' => $workload,
                'chartData' => $chartData
            ]);
        }

        return view('admin.queue-monitor.index', compact(
            'stats', 'failedJobs', 'recentJobs', 'supervisors', 'workload', 'chartData'
        ));
    }

    /**
     * Get detailed job information
     */
    public function show(Request $request, $id)
    {
        try {
            // Try to get from recent jobs first
            $job = $this->jobRepository->getRecent()->firstWhere('id', $id);
            
            if (!$job) {
                // Try failed jobs
                $job = $this->jobRepository->getFailed()->firstWhere('id', $id);
            }

            if (!$job) {
                return response()->json(['error' => 'Job not found'], 404);
            }

            $jobDetails = [
                'id' => $job->id,
                'queue' => $job->queue,
                'name' => $job->name,
                'status' => $job->status,
                'payload' => $job->payload,
                'exception' => $job->exception ?? null,
                'failed_at' => $job->failed_at,
                'started_at' => $job->started_at,
                'finished_at' => $job->finished_at,
                'runtime' => $job->runtime,
                'attempts' => $job->attempts
            ];

            if ($request->ajax()) {
                return response()->json($jobDetails);
            }

            return view('admin.queue-monitor.show', compact('jobDetails'));
        } catch (\Exception $e) {
            SystemLog::error('queue', 'job_details_error', 'Failed to get job details: ' . $e->getMessage(), [
                'job_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get job details'], 500);
        }
    }

    /**
     * Retry failed jobs
     */
    public function retryJob(Request $request, $id)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            
            SystemLog::info('queue', 'job_retried', 'Job retried successfully', [
                'job_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job retried successfully'
            ]);
        } catch (\Exception $e) {
            SystemLog::error('queue', 'job_retry_error', 'Failed to retry job: ' . $e->getMessage(), [
                'job_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retry job'
            ], 500);
        }
    }

    /**
     * Retry all failed jobs
     */
    public function retryAllFailed()
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            
            SystemLog::info('queue', 'all_jobs_retried', 'All failed jobs retried successfully', [
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All failed jobs retried successfully'
            ]);
        } catch (\Exception $e) {
            SystemLog::error('queue', 'retry_all_error', 'Failed to retry all jobs: ' . $e->getMessage(), [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retry all jobs'
            ], 500);
        }
    }

    /**
     * Delete failed job
     */
    public function deleteJob(Request $request, $id)
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);
            
            SystemLog::info('queue', 'job_deleted', 'Failed job deleted successfully', [
                'job_id' => $id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Job deleted successfully'
            ]);
        } catch (\Exception $e) {
            SystemLog::error('queue', 'job_delete_error', 'Failed to delete job: ' . $e->getMessage(), [
                'job_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete job'
            ], 500);
        }
    }

    /**
     * Clear all failed jobs
     */
    public function clearAllFailed()
    {
        try {
            Artisan::call('queue:flush');
            
            SystemLog::info('queue', 'all_failed_cleared', 'All failed jobs cleared successfully', [
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All failed jobs cleared successfully'
            ]);
        } catch (\Exception $e) {
            SystemLog::error('queue', 'clear_all_error', 'Failed to clear all failed jobs: ' . $e->getMessage(), [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear all failed jobs'
            ], 500);
        }
    }

    /**
     * Pause queue processing
     */
    public function pauseQueue(Request $request)
    {
        $queue = $request->input('queue', 'default');
        
        try {
            Artisan::call('queue:pause', ['queue' => $queue]);
            
            SystemLog::info('queue', 'queue_paused', 'Queue paused successfully', [
                'queue' => $queue,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Queue '{$queue}' paused successfully"
            ]);
        } catch (\Exception $e) {
            SystemLog::error('queue', 'queue_pause_error', 'Failed to pause queue: ' . $e->getMessage(), [
                'queue' => $queue,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to pause queue'
            ], 500);
        }
    }

    /**
     * Resume queue processing
     */
    public function resumeQueue(Request $request)
    {
        $queue = $request->input('queue', 'default');
        
        try {
            Artisan::call('queue:continue', ['queue' => $queue]);
            
            SystemLog::info('queue', 'queue_resumed', 'Queue resumed successfully', [
                'queue' => $queue,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Queue '{$queue}' resumed successfully"
            ]);
        } catch (\Exception $e) {
            SystemLog::error('queue', 'queue_resume_error', 'Failed to resume queue: ' . $e->getMessage(), [
                'queue' => $queue,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resume queue'
            ], 500);
        }
    }

    /**
     * Get queue statistics
     */
    protected function getQueueStats()
    {
        try {
            $stats = Cache::remember('queue_stats', 30, function () {
                // Get basic stats
                $failedJobsCount = DB::table('failed_jobs')->count();
                $jobsPerHour = $this->metricsRepository->jobsPerHour();
                $recentlyFailed = $this->metricsRepository->recentlyFailed();
                $recentJobs = $this->jobRepository->getRecent();
                
                // Calculate processing stats
                $totalProcessed = $recentJobs->count();
                $totalFailed = collect($recentlyFailed)->sum();
                $successRate = $totalProcessed > 0 ? (($totalProcessed - $totalFailed) / $totalProcessed) * 100 : 0;

                // Get queue sizes
                $queueSizes = [];
                $queues = ['default', 'emails', 'sms', 'whatsapp', 'import', 'sync'];
                
                foreach ($queues as $queue) {
                    try {
                        $queueSizes[$queue] = Redis::connection()->llen("queues:{$queue}");
                    } catch (\Exception $e) {
                        $queueSizes[$queue] = 0;
                    }
                }

                return [
                    'total_jobs' => $totalProcessed,
                    'failed_jobs' => $failedJobsCount,
                    'jobs_per_hour' => collect($jobsPerHour)->sum(),
                    'success_rate' => round($successRate, 2),
                    'queue_sizes' => $queueSizes,
                    'total_queued' => array_sum($queueSizes)
                ];
            });

            return $stats;
        } catch (\Exception $e) {
            SystemLog::error('queue', 'stats_error', 'Failed to get queue stats: ' . $e->getMessage());
            
            return [
                'total_jobs' => 0,
                'failed_jobs' => 0,
                'jobs_per_hour' => 0,
                'success_rate' => 0,
                'queue_sizes' => [],
                'total_queued' => 0
            ];
        }
    }

    /**
     * Get failed jobs
     */
    protected function getFailedJobs($limit = 10)
    {
        try {
            return $this->jobRepository->getFailed($limit);
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Get recent jobs
     */
    protected function getRecentJobs($limit = 20)
    {
        try {
            return $this->jobRepository->getRecent($limit);
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Get supervisors
     */
    protected function getSupervisors()
    {
        try {
            $masters = $this->masterSupervisorRepository->all();
            $supervisors = [];

            foreach ($masters as $master) {
                $supervisors = array_merge($supervisors, $this->supervisorRepository->all());
            }

            return collect($supervisors);
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Get workload
     */
    protected function getWorkload()
    {
        try {
            return collect($this->workloadRepository->get());
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Get chart data for the last 24 hours
     */
    protected function getChartData()
    {
        try {
            $hours = [];
            $processed = [];
            $failed = [];

            for ($i = 23; $i >= 0; $i--) {
                $hour = Carbon::now()->subHours($i);
                $hours[] = $hour->format('H:00');
                
                // Get metrics for this hour
                $hourlyProcessed = $this->metricsRepository->jobsPerHour()[$hour->format('Y-m-d H:00')] ?? 0;
                $hourlyFailed = $this->metricsRepository->recentlyFailed()[$hour->format('Y-m-d H:00')] ?? 0;
                
                $processed[] = $hourlyProcessed;
                $failed[] = $hourlyFailed;
            }

            return [
                'labels' => $hours,
                'processed' => $processed,
                'failed' => $failed
            ];
        } catch (\Exception $e) {
            // Return empty data if metrics not available
            $hours = [];
            for ($i = 23; $i >= 0; $i--) {
                $hours[] = Carbon::now()->subHours($i)->format('H:00');
            }

            return [
                'labels' => $hours,
                'processed' => array_fill(0, 24, 0),
                'failed' => array_fill(0, 24, 0)
            ];
        }
    }

    /**
     * Export queue data
     */
    public function export(Request $request)
    {
        try {
            $stats = $this->getQueueStats();
            $failedJobs = $this->getFailedJobs(100);
            $recentJobs = $this->getRecentJobs(100);
            
            $data = [
                'stats' => $stats,
                'failed_jobs' => $failedJobs->toArray(),
                'recent_jobs' => $recentJobs->toArray(),
                'exported_at' => now()->toDateTimeString()
            ];

            $filename = 'queue_monitor_' . now()->format('Y_m_d_H_i_s') . '.json';
            $content = json_encode($data, JSON_PRETTY_PRINT);

            SystemLog::info('queue', 'data_exported', 'Queue data exported successfully', [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return response($content, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            SystemLog::error('queue', 'export_error', 'Failed to export queue data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data'
            ], 500);
        }
    }

    /**
     * Get queue health status
     */
    public function health()
    {
        try {
            $stats = $this->getQueueStats();
            $supervisors = $this->getSupervisors();
            
            $health = [
                'status' => 'healthy',
                'issues' => [],
                'recommendations' => []
            ];

            // Check for issues
            if ($stats['failed_jobs'] > 50) {
                $health['status'] = 'warning';
                $health['issues'][] = 'High number of failed jobs (' . $stats['failed_jobs'] . ')';
                $health['recommendations'][] = 'Review and retry failed jobs';
            }

            if ($stats['success_rate'] < 90) {
                $health['status'] = 'warning';
                $health['issues'][] = 'Low success rate (' . $stats['success_rate'] . '%)';
                $health['recommendations'][] = 'Investigate job failures';
            }

            if ($supervisors->isEmpty()) {
                $health['status'] = 'critical';
                $health['issues'][] = 'No active supervisors found';
                $health['recommendations'][] = 'Start Laravel Horizon';
            }

            if ($stats['total_queued'] > 1000) {
                $health['status'] = 'warning';
                $health['issues'][] = 'High queue backlog (' . $stats['total_queued'] . ' jobs)';
                $health['recommendations'][] = 'Scale up workers or optimize job processing';
            }

            return response()->json($health);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'issues' => ['Unable to determine queue health'],
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
