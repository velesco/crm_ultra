<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemBackup;
use App\Services\BackupService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
        $this->backupService = $backupService;
    }

    /**
     * Display backup management dashboard
     */
    public function index(Request $request): Factory|Application|View|JsonResponse|\Illuminate\Contracts\Foundation\Application
    {
        $query = SystemBackup::with('creator');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%")
                    ->orWhereHas('creator', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%$search%");
                    });
            });
        }

        $backups = $query->latest()->paginate(20);
        $stats = $this->backupService->getBackupStats();

        // Get recent activity
        $recentActivity = SystemBackup::with('creator')
            ->latest()
            ->limit(5)
            ->get();

        // Calculate additional stats
        $additionalStats = [
            'success_rate' => $stats['total_backups'] > 0
                ? round(($stats['successful_backups'] / $stats['total_backups']) * 100, 2)
                : 0,
            'avg_size' => $stats['successful_backups'] > 0
                ? $stats['total_size'] / $stats['successful_backups']
                : 0,
            'oldest_backup_age' => $stats['last_backup'] ? $stats['last_backup']->age_in_days : 0,
        ];

        if ($request->ajax()) {
            return response()->json([
                'backups' => view('admin.backups.table', compact('backups'))->render(),
                'stats' => array_merge($stats, $additionalStats),
                'recent_activity' => $recentActivity,
            ]);
        }

        return view('admin.backups.index', compact(
            'backups',
            'stats',
            'additionalStats',
            'recentActivity'
        ));
    }

    /**
     * Show backup creation form
     */
    public function create()
    {
        return view('admin.backups.create');
    }

    /**
     * Create a new backup
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:system_backups,name',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:full,database,files',
        ]);

        try {
            $backup = null;

            switch ($request->type) {
                case 'full':
                    $backup = $this->backupService->createFullBackup(
                        $request->name,
                        $request->description
                    );
                    break;

                case 'database':
                    $backup = SystemBackup::query()->create([
                        'name' => $request->name,
                        'description' => $request->description,
                        'type' => 'database',
                        'status' => 'in_progress',
                        'created_by' => auth()->id(),
                        'started_at' => now(),
                    ]);

                    // Create database backup
                    $dbPath = $this->backupService->createDatabaseBackup($request->name);
                    $backup->update([
                        'file_path' => str_replace(storage_path('app/'), '', $dbPath),
                        'file_size' => filesize($dbPath),
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    break;

                case 'files':
                    $backup = SystemBackup::query()->create([
                        'name' => $request->name,
                        'description' => $request->description,
                        'type' => 'files',
                        'status' => 'in_progress',
                        'created_by' => auth()->id(),
                        'started_at' => now(),
                    ]);

                    // Create files backup
                    $filesPath = $this->backupService->createFilesBackup($request->name);
                    $backup->update([
                        'file_path' => str_replace(storage_path('app/'), '', $filesPath),
                        'file_size' => filesize($filesPath),
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    break;
            }

            Log::info('Manual backup created', [
                'backup_id' => $backup->id,
                'type' => $request->type,
                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.backups.index')
                ->with('success', 'Backup created successfully!');

        } catch (Exception $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'type' => $request->type,
                'user' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Backup creation failed: '.$e->getMessage());
        }
    }

    /**
     * Display backup details
     */
    public function show(SystemBackup $backup)
    {
        $backup->load('creator');

        // Validate backup integrity
        $validation = $this->backupService->validateBackup($backup);

        // Get related backups (same type, recent)
        $relatedBackups = SystemBackup::query()->where('type', $backup->type)
            ->where('id', '!=', $backup->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.backups.show', compact('backup', 'validation', 'relatedBackups'));
    }

    /**
     * Download backup file
     */
    public function download(SystemBackup $backup)
    {
        if ($backup->status !== 'completed' || ! $backup->file_path) {
            return back()->with('error', 'Backup file is not available for download.');
        }

        $filePath = storage_path('app/'.$backup->file_path);

        if (! file_exists($filePath)) {
            return back()->with('error', 'Backup file not found on disk.');
        }

        Log::info('Backup downloaded', [
            'backup_id' => $backup->id,
            'downloaded_by' => auth()->id(),
        ]);

        return Response::download($filePath, $backup->name.'_backup.zip');
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request, SystemBackup $backup)
    {
        $request->validate([
            'restore_database' => 'boolean',
            'restore_files' => 'boolean',
            'confirmation' => 'required|accepted',
        ]);

        if ($backup->status !== 'completed') {
            return back()->with('error', 'Cannot restore from incomplete backup.');
        }

        try {
            $restoreDatabase = $request->boolean('restore_database');
            $restoreFiles = $request->boolean('restore_files');

            if (! $restoreDatabase && ! $restoreFiles) {
                return back()->with('error', 'Please select at least one restore option.');
            }

            $this->backupService->restoreBackup($backup, $restoreDatabase, $restoreFiles);

            Log::info('Backup restored', [
                'backup_id' => $backup->id,
                'restored_by' => auth()->id(),
                'restore_database' => $restoreDatabase,
                'restore_files' => $restoreFiles,
            ]);

            return redirect()
                ->route('admin.backups.index')
                ->with('success', 'System restored successfully from backup!');

        } catch (Exception $e) {
            Log::error('Backup restoration failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
                'user' => auth()->id(),
            ]);

            return back()->with('error', 'Restore failed: '.$e->getMessage());
        }
    }

    /**
     * Delete backup
     */
    public function destroy(SystemBackup $backup): \Illuminate\Http\RedirectResponse
    {
        if (! $backup->canBeDeleted()) {
            return back()->with('error', 'This backup cannot be deleted while in progress.');
        }

        try {
            $this->backupService->deleteBackup($backup);

            Log::info('Backup deleted', [
                'backup_id' => $backup->id,
                'deleted_by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.backups.index')
                ->with('success', 'Backup deleted successfully!');

        } catch (Exception $e) {
            Log::error('Backup deletion failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete backup: '.$e->getMessage());
        }
    }

    /**
     * Get backup statistics API
     */
    public function stats()
    {
        $stats = $this->backupService->getBackupStats();

        // Add chart data
        $chartData = [
            'daily_backups' => $this->getDailyBackupStats(),
            'backup_types' => $this->getBackupTypeStats(),
            'success_rate' => $this->getSuccessRateStats(),
        ];

        return response()->json(array_merge($stats, ['charts' => $chartData]));
    }

    /**
     * Validate backup integrity
     */
    public function validateBackup(SystemBackup $backup)
    {
        $validation = $this->backupService->validateBackup($backup);

        return response()->json($validation);
    }


    /**
     * Bulk actions on backups
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,validate',
            'backup_ids' => 'required|array',
            'backup_ids.*' => 'exists:system_backups,id',
        ]);

        $backups = SystemBackup::whereIn('id', $request->backup_ids)->get();
        $results = [];

        foreach ($backups as $backup) {
            try {
                switch ($request->action) {
                    case 'delete':
                        if ($backup->canBeDeleted()) {
                            $this->backupService->deleteBackup($backup);
                            $results[] = ['id' => $backup->id, 'success' => true, 'message' => 'Deleted'];
                        } else {
                            $results[] = ['id' => $backup->id, 'success' => false, 'message' => 'Cannot delete'];
                        }
                        break;

                    case 'validate':
                        $validation = $this->backupService->validateBackup($backup);
                        $results[] = [
                            'id' => $backup->id,
                            'success' => $validation['valid'],
                            'message' => $validation['valid'] ? 'Valid' : $validation['error'],
                        ];
                        break;
                }
            } catch (Exception $e) {
                $results[] = ['id' => $backup->id, 'success' => false, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Display scheduled backups
     */
    public function scheduled(Request $request)
    {
        // Get scheduled backups (future backups)
        $query = SystemBackup::with('creator')
            ->where('status', 'scheduled')
            ->orWhere('created_at', '>', now());

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $scheduledBackups = $query->latest()->paginate(20);

        // Statistics for scheduled backups
        $stats = [
            'total_scheduled' => SystemBackup::where('status', 'scheduled')->count(),
            'next_backup' => SystemBackup::where('status', 'scheduled')
                ->where('created_at', '>', now())
                ->orderBy('created_at')
                ->first(),
            'daily_scheduled' => SystemBackup::where('status', 'scheduled')
                ->whereRaw('JSON_EXTRACT(metadata, "$.frequency") = "daily"')
                ->count(),
            'weekly_scheduled' => SystemBackup::where('status', 'scheduled')
                ->whereRaw('JSON_EXTRACT(metadata, "$.frequency") = "weekly"')
                ->count(),
            'monthly_scheduled' => SystemBackup::where('status', 'scheduled')
                ->whereRaw('JSON_EXTRACT(metadata, "$.frequency") = "monthly"')
                ->count(),
        ];

        return view('admin.backups.scheduled', compact('scheduledBackups', 'stats'));
    }

    /**
     * Get daily backup statistics for charts
     */
    private function getDailyBackupStats()
    {
        return SystemBackup::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->map->count;
    }

    /**
     * Get backup type statistics
     */
    private function getBackupTypeStats()
    {
        return SystemBackup::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->keyBy('type')
            ->map->count;
    }

    /**
     * Get success rate statistics
     */
    private function getSuccessRateStats()
    {
        $total = SystemBackup::count();
        $successful = SystemBackup::where('status', 'completed')->count();
        $failed = SystemBackup::where('status', 'failed')->count();

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Cleanup old backups
     */
    public function cleanup(Request $request)
    {
        $validated = $request->validate([
            'days_to_keep' => 'required|integer|min:1|max:365',
        ]);

        try {
            $cutoffDate = now()->subDays($validated['days_to_keep']);

            // Find old backups
            $oldBackups = SystemBackup::where('created_at', '<', $cutoffDate)
                ->where('status', '!=', 'in_progress')
                ->get();

            $deletedCount = 0;
            $totalSizeFreed = 0;
            $errors = [];

            foreach ($oldBackups as $backup) {
                try {
                    // Delete backup file if it exists
                    if ($backup->file_path && Storage::disk('backups')->exists($backup->file_path)) {
                        $fileSize = Storage::disk('backups')->size($backup->file_path);
                        Storage::disk('backups')->delete($backup->file_path);
                        $totalSizeFreed += $fileSize;
                    }

                    // Delete database record
                    $backup->delete();
                    $deletedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to delete backup {$backup->name}: " . $e->getMessage();
                }
            }

            $message = "Successfully cleaned up {$deletedCount} old backup(s)";
            if ($totalSizeFreed > 0) {
                $message .= " and freed " . $this->formatBytes($totalSizeFreed) . " of disk space";
            }

            if (!empty($errors)) {
                $message .= ". Some errors occurred: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'size_freed' => $totalSizeFreed,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup old backups: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
