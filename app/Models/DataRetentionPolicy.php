<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataRetentionPolicy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'data_type',
        'retention_period_days',
        'legal_basis',
        'auto_delete',
        'is_active',
        'criteria',
        'exceptions',
        'last_executed_at',
        'created_by',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'criteria' => 'array',
        'exceptions' => 'array',
        'auto_delete' => 'boolean',
        'is_active' => 'boolean',
        'last_executed_at' => 'datetime',
    ];

    // Data types
    const DATA_TYPE_CONTACTS = 'contacts';

    const DATA_TYPE_EMAIL_LOGS = 'email_logs';

    const DATA_TYPE_SMS_LOGS = 'sms_logs';

    const DATA_TYPE_WHATSAPP_LOGS = 'whatsapp_logs';

    const DATA_TYPE_SYSTEM_LOGS = 'system_logs';

    const DATA_TYPE_LOGIN_ATTEMPTS = 'login_attempts';

    const DATA_TYPE_CONSENT_LOGS = 'consent_logs';

    const DATA_TYPE_DATA_REQUESTS = 'data_requests';

    const DATA_TYPE_BACKUP_FILES = 'backup_files';

    const DATA_TYPE_EXPORT_FILES = 'export_files';

    /**
     * Get the creator of the policy
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater of the policy
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for active policies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for auto-delete policies
     */
    public function scopeAutoDelete($query)
    {
        return $query->where('auto_delete', true);
    }

    /**
     * Scope for policies by data type
     */
    public function scopeForDataType($query, $dataType)
    {
        return $query->where('data_type', $dataType);
    }

    /**
     * Scope for policies with overdue executions
     */
    public function scopeOverdueExecution($query)
    {
        return $query->where('is_active', true)
            ->where('auto_delete', true)
            ->where(function ($q) {
                $q->whereNull('last_executed_at')
                    ->orWhere('last_executed_at', '<', now()->subWeek());
            });
    }

    /**
     * Get all data types
     */
    public static function getDataTypes()
    {
        return [
            self::DATA_TYPE_CONTACTS => 'Contacts',
            self::DATA_TYPE_EMAIL_LOGS => 'Email Logs',
            self::DATA_TYPE_SMS_LOGS => 'SMS Logs',
            self::DATA_TYPE_WHATSAPP_LOGS => 'WhatsApp Logs',
            self::DATA_TYPE_SYSTEM_LOGS => 'System Logs',
            self::DATA_TYPE_LOGIN_ATTEMPTS => 'Login Attempts',
            self::DATA_TYPE_CONSENT_LOGS => 'Consent Logs',
            self::DATA_TYPE_DATA_REQUESTS => 'Data Requests',
            self::DATA_TYPE_BACKUP_FILES => 'Backup Files',
            self::DATA_TYPE_EXPORT_FILES => 'Export Files',
        ];
    }

    /**
     * Get data type icon
     */
    public function getDataTypeIcon()
    {
        return match ($this->data_type) {
            self::DATA_TYPE_CONTACTS => 'users',
            self::DATA_TYPE_EMAIL_LOGS => 'mail',
            self::DATA_TYPE_SMS_LOGS => 'message-square',
            self::DATA_TYPE_WHATSAPP_LOGS => 'message-circle',
            self::DATA_TYPE_SYSTEM_LOGS => 'activity',
            self::DATA_TYPE_LOGIN_ATTEMPTS => 'log-in',
            self::DATA_TYPE_CONSENT_LOGS => 'shield-check',
            self::DATA_TYPE_DATA_REQUESTS => 'file-text',
            self::DATA_TYPE_BACKUP_FILES => 'archive',
            self::DATA_TYPE_EXPORT_FILES => 'download',
            default => 'database'
        };
    }

    /**
     * Check if policy should execute
     */
    public function shouldExecute()
    {
        if (! $this->is_active || ! $this->auto_delete) {
            return false;
        }

        // Check if enough time has passed since last execution (at least 1 day)
        if ($this->last_executed_at && $this->last_executed_at->diffInDays(now()) < 1) {
            return false;
        }

        return true;
    }

    /**
     * Get retention period in human readable format
     */
    public function getRetentionPeriodHuman()
    {
        $days = $this->retention_period_days;

        if ($days >= 365) {
            $years = floor($days / 365);

            return $years.' year'.($years > 1 ? 's' : '');
        }

        if ($days >= 30) {
            $months = floor($days / 30);

            return $months.' month'.($months > 1 ? 's' : '');
        }

        return $days.' day'.($days > 1 ? 's' : '');
    }

    /**
     * Get next execution time
     */
    public function getNextExecutionTime()
    {
        if (! $this->is_active || ! $this->auto_delete) {
            return null;
        }

        if (! $this->last_executed_at) {
            return now(); // Ready to execute
        }

        return $this->last_executed_at->addDay();
    }

    /**
     * Execute the retention policy
     */
    public function execute()
    {
        if (! $this->shouldExecute()) {
            return false;
        }

        $cutoffDate = now()->subDays($this->retention_period_days);
        $deletedCount = 0;

        switch ($this->data_type) {
            case self::DATA_TYPE_CONTACTS:
                $deletedCount = $this->executeContactsPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_EMAIL_LOGS:
                $deletedCount = $this->executeEmailLogsPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_SMS_LOGS:
                $deletedCount = $this->executeSmsLogsPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_WHATSAPP_LOGS:
                $deletedCount = $this->executeWhatsAppLogsPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_SYSTEM_LOGS:
                $deletedCount = $this->executeSystemLogsPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_LOGIN_ATTEMPTS:
                $deletedCount = $this->executeLoginAttemptsPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_CONSENT_LOGS:
                $deletedCount = $this->executeConsentLogsPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_DATA_REQUESTS:
                $deletedCount = $this->executeDataRequestsPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_BACKUP_FILES:
                $deletedCount = $this->executeBackupFilesPolicy($cutoffDate);
                break;

            case self::DATA_TYPE_EXPORT_FILES:
                $deletedCount = $this->executeExportFilesPolicy($cutoffDate);
                break;
        }

        // Update last execution time
        $this->last_executed_at = now();
        $this->save();

        return $deletedCount;
    }

    /**
     * Execute contacts retention policy
     */
    private function executeContactsPolicy($cutoffDate)
    {
        $query = Contact::where('created_at', '<', $cutoffDate);

        // Apply criteria if specified
        if (! empty($this->criteria)) {
            foreach ($this->criteria as $criterion) {
                $query->where($criterion['field'], $criterion['operator'], $criterion['value']);
            }
        }

        return $query->delete();
    }

    /**
     * Execute email logs retention policy
     */
    private function executeEmailLogsPolicy($cutoffDate)
    {
        return EmailLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Execute SMS logs retention policy
     */
    private function executeSmsLogsPolicy($cutoffDate)
    {
        return SmsMessage::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Execute WhatsApp logs retention policy
     */
    private function executeWhatsAppLogsPolicy($cutoffDate)
    {
        return WhatsAppMessage::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Execute system logs retention policy
     */
    private function executeSystemLogsPolicy($cutoffDate)
    {
        return SystemLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Execute login attempts retention policy
     */
    private function executeLoginAttemptsPolicy($cutoffDate)
    {
        return LoginAttempt::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Execute consent logs retention policy
     */
    private function executeConsentLogsPolicy($cutoffDate)
    {
        return ConsentLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Execute data requests retention policy
     */
    private function executeDataRequestsPolicy($cutoffDate)
    {
        return DataRequest::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Execute backup files retention policy
     */
    private function executeBackupFilesPolicy($cutoffDate)
    {
        $backups = SystemBackup::where('created_at', '<', $cutoffDate)->get();
        $deletedCount = 0;

        foreach ($backups as $backup) {
            // Delete physical file
            if ($backup->file_path && file_exists(storage_path('app/'.$backup->file_path))) {
                unlink(storage_path('app/'.$backup->file_path));
            }

            $backup->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    /**
     * Execute export files retention policy
     */
    private function executeExportFilesPolicy($cutoffDate)
    {
        $exports = ExportRequest::where('created_at', '<', $cutoffDate)->get();
        $deletedCount = 0;

        foreach ($exports as $export) {
            // Delete physical file
            if ($export->file_path && file_exists(storage_path('app/'.$export->file_path))) {
                unlink(storage_path('app/'.$export->file_path));
            }

            $export->delete();
            $deletedCount++;
        }

        return $deletedCount;
    }

    /**
     * Get affected records count (preview without deleting)
     */
    public function getAffectedRecordsCount()
    {
        if (! $this->is_active) {
            return 0;
        }

        $cutoffDate = now()->subDays($this->retention_period_days);

        switch ($this->data_type) {
            case self::DATA_TYPE_CONTACTS:
                $query = Contact::where('created_at', '<', $cutoffDate);
                if (! empty($this->criteria)) {
                    foreach ($this->criteria as $criterion) {
                        $query->where($criterion['field'], $criterion['operator'], $criterion['value']);
                    }
                }

                return $query->count();

            case self::DATA_TYPE_EMAIL_LOGS:
                return EmailLog::where('created_at', '<', $cutoffDate)->count();

            case self::DATA_TYPE_SMS_LOGS:
                return SmsMessage::where('created_at', '<', $cutoffDate)->count();

            case self::DATA_TYPE_WHATSAPP_LOGS:
                return WhatsAppMessage::where('created_at', '<', $cutoffDate)->count();

            case self::DATA_TYPE_SYSTEM_LOGS:
                return SystemLog::where('created_at', '<', $cutoffDate)->count();

            case self::DATA_TYPE_LOGIN_ATTEMPTS:
                return LoginAttempt::where('created_at', '<', $cutoffDate)->count();

            case self::DATA_TYPE_CONSENT_LOGS:
                return ConsentLog::where('created_at', '<', $cutoffDate)->count();

            case self::DATA_TYPE_DATA_REQUESTS:
                return DataRequest::where('created_at', '<', $cutoffDate)->count();

            case self::DATA_TYPE_BACKUP_FILES:
                return SystemBackup::where('created_at', '<', $cutoffDate)->count();

            case self::DATA_TYPE_EXPORT_FILES:
                return ExportRequest::where('created_at', '<', $cutoffDate)->count();

            default:
                return 0;
        }
    }
}
