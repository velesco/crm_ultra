<?php

namespace App\Services;

use App\Models\SystemBackup;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupService
{
    private $backupPath;

    private $maxBackups = 10;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        $this->ensureBackupDirectoryExists();
    }

    /**
     * Create a full system backup
     */
    public function createFullBackup($name = null, $description = null)
    {
        $name = $name ?? 'backup_'.Carbon::now()->format('Y_m_d_H_i_s');
        $description = $description ?? 'Full system backup created on '.Carbon::now()->format('Y-m-d H:i:s');

        try {
            // Create backup record
            $backup = SystemBackup::create([
                'name' => $name,
                'description' => $description,
                'type' => 'full',
                'status' => 'in_progress',
                'created_by' => auth()->id(),
                'started_at' => now(),
            ]);

            // Create database backup
            $dbBackupPath = $this->createDatabaseBackup($name);

            // Create files backup
            $filesBackupPath = $this->createFilesBackup($name);

            // Create combined zip
            $finalBackupPath = $this->createCombinedBackup($name, $dbBackupPath, $filesBackupPath);

            // Calculate file size
            $fileSize = File::size($finalBackupPath);

            // Update backup record
            $backup->update([
                'file_path' => str_replace(storage_path('app/'), '', $finalBackupPath),
                'file_size' => $fileSize,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Clean up temporary files
            $this->cleanupTempFiles([$dbBackupPath, $filesBackupPath]);

            // Cleanup old backups
            $this->cleanupOldBackups();

            Log::info('Full backup created successfully', ['backup_id' => $backup->id, 'name' => $name]);

            return $backup;

        } catch (\Exception $e) {
            Log::error('Full backup failed', ['error' => $e->getMessage(), 'name' => $name]);

            if (isset($backup)) {
                $backup->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Create database only backup
     */
    public function createDatabaseBackup($name = null)
    {
        $name = $name ?? 'db_backup_'.Carbon::now()->format('Y_m_d_H_i_s');
        $filename = $name.'_database.sql';
        $filePath = $this->backupPath.'/'.$filename;

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($filePath)
        );

        $returnCode = 0;
        $output = [];
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Database backup failed: '.implode("\n", $output));
        }

        return $filePath;
    }

    /**
     * Create files backup
     */
    public function createFilesBackup($name)
    {
        $filename = $name.'_files.zip';
        $filePath = $this->backupPath.'/'.$filename;

        $zip = new ZipArchive;
        if ($zip->open($filePath, ZipArchive::CREATE) !== true) {
            throw new \Exception('Could not create files backup zip');
        }

        // Add important directories
        $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/public');
        $this->addDirectoryToZip($zip, resource_path('views'), 'resources/views');
        $this->addDirectoryToZip($zip, config_path(), 'config');
        $this->addDirectoryToZip($zip, base_path('.env'), '.env');

        $zip->close();

        return $filePath;
    }

    /**
     * Create combined backup zip
     */
    private function createCombinedBackup($name, $dbBackupPath, $filesBackupPath)
    {
        $filename = $name.'_full_backup.zip';
        $filePath = $this->backupPath.'/'.$filename;

        $zip = new ZipArchive;
        if ($zip->open($filePath, ZipArchive::CREATE) !== true) {
            throw new \Exception('Could not create combined backup zip');
        }

        $zip->addFile($dbBackupPath, basename($dbBackupPath));
        $zip->addFile($filesBackupPath, basename($filesBackupPath));

        // Add backup info
        $backupInfo = [
            'name' => $name,
            'created_at' => Carbon::now()->toISOString(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database' => config('database.default'),
            'includes' => [
                'database' => basename($dbBackupPath),
                'files' => basename($filesBackupPath),
            ],
        ];

        $zip->addFromString('backup_info.json', json_encode($backupInfo, JSON_PRETTY_PRINT));
        $zip->close();

        return $filePath;
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(SystemBackup $backup, $restoreDatabase = true, $restoreFiles = true)
    {
        if ($backup->status !== 'completed') {
            throw new \Exception('Cannot restore from incomplete backup');
        }

        $backupFilePath = storage_path('app/'.$backup->file_path);

        if (! File::exists($backupFilePath)) {
            throw new \Exception('Backup file not found');
        }

        try {
            $backup->update(['status' => 'restoring']);

            // Extract backup
            $extractPath = $this->backupPath.'/restore_'.time();
            File::makeDirectory($extractPath, 0755, true);

            $zip = new ZipArchive;
            if ($zip->open($backupFilePath) === true) {
                $zip->extractTo($extractPath);
                $zip->close();
            } else {
                throw new \Exception('Could not extract backup file');
            }

            // Restore database
            if ($restoreDatabase) {
                $this->restoreDatabase($extractPath);
            }

            // Restore files
            if ($restoreFiles) {
                $this->restoreFiles($extractPath);
            }

            // Cleanup extraction directory
            File::deleteDirectory($extractPath);

            $backup->update(['status' => 'completed']);

            Log::info('Backup restored successfully', ['backup_id' => $backup->id]);

            return true;

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Backup restore failed', ['error' => $e->getMessage(), 'backup_id' => $backup->id]);
            throw $e;
        }
    }

    /**
     * Restore database from backup
     */
    private function restoreDatabase($extractPath)
    {
        $sqlFiles = File::glob($extractPath.'/*_database.sql');

        if (empty($sqlFiles)) {
            throw new \Exception('Database backup file not found');
        }

        $sqlFile = $sqlFiles[0];

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        $returnCode = 0;
        $output = [];
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Database restore failed: '.implode("\n", $output));
        }
    }

    /**
     * Restore files from backup
     */
    private function restoreFiles($extractPath)
    {
        $filesZip = File::glob($extractPath.'/*_files.zip');

        if (empty($filesZip)) {
            throw new \Exception('Files backup not found');
        }

        $zip = new ZipArchive;
        if ($zip->open($filesZip[0]) === true) {
            $zip->extractTo(base_path());
            $zip->close();
        } else {
            throw new \Exception('Could not extract files backup');
        }
    }

    /**
     * Delete backup
     */
    public function deleteBackup(SystemBackup $backup)
    {
        $filePath = storage_path('app/'.$backup->file_path);

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $backup->delete();

        Log::info('Backup deleted', ['backup_id' => $backup->id]);
    }

    /**
     * Get backup statistics
     */
    public function getBackupStats()
    {
        return [
            'total_backups' => SystemBackup::count(),
            'successful_backups' => SystemBackup::where('status', 'completed')->count(),
            'failed_backups' => SystemBackup::where('status', 'failed')->count(),
            'total_size' => SystemBackup::where('status', 'completed')->sum('file_size'),
            'last_backup' => SystemBackup::where('status', 'completed')->latest()->first(),
            'available_space' => disk_free_space($this->backupPath),
            'used_space' => $this->getDirectorySize($this->backupPath),
        ];
    }

    /**
     * Cleanup old backups
     */
    private function cleanupOldBackups()
    {
        $backups = SystemBackup::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->skip($this->maxBackups)
            ->get();

        foreach ($backups as $backup) {
            $this->deleteBackup($backup);
        }
    }

    /**
     * Cleanup temporary files
     */
    private function cleanupTempFiles(array $files)
    {
        foreach ($files as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }
    }

    /**
     * Add directory to zip recursively
     */
    private function addDirectoryToZip($zip, $dir, $zipPath = '')
    {
        if (is_file($dir)) {
            $zip->addFile($dir, $zipPath);

            return;
        }

        if (! is_dir($dir)) {
            return;
        }

        $files = File::allFiles($dir);
        foreach ($files as $file) {
            $relativePath = $zipPath ? $zipPath.'/'.$file->getRelativePathname() : $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $relativePath);
        }
    }

    /**
     * Get directory size
     */
    private function getDirectorySize($directory)
    {
        $size = 0;

        if (is_dir($directory)) {
            $files = File::allFiles($directory);
            foreach ($files as $file) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Ensure backup directory exists
     */
    private function ensureBackupDirectoryExists()
    {
        if (! File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }
    }

    /**
     * Schedule automatic backup
     */
    public function scheduleAutomaticBackup($frequency = 'daily')
    {
        // This would integrate with Laravel's task scheduler
        // For now, just create a backup
        return $this->createFullBackup(
            'scheduled_backup_'.Carbon::now()->format('Y_m_d_H_i_s'),
            "Scheduled {$frequency} backup"
        );
    }

    /**
     * Validate backup integrity
     */
    public function validateBackup(SystemBackup $backup)
    {
        $filePath = storage_path('app/'.$backup->file_path);

        if (! File::exists($filePath)) {
            return ['valid' => false, 'error' => 'Backup file not found'];
        }

        // Check if zip file is valid
        $zip = new ZipArchive;
        $result = $zip->open($filePath, ZipArchive::CHECKCONS);

        if ($result !== true) {
            return ['valid' => false, 'error' => 'Backup file is corrupted'];
        }

        // Check if required files exist in backup
        $requiredFiles = ['backup_info.json'];
        foreach ($requiredFiles as $file) {
            if ($zip->locateName($file) === false) {
                $zip->close();

                return ['valid' => false, 'error' => "Missing required file: {$file}"];
            }
        }

        $zip->close();

        return ['valid' => true, 'message' => 'Backup is valid'];
    }
}
