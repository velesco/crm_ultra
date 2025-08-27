<?php

namespace App\Policies;

use App\Models\DataImport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DataImportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view data-imports') || $user->hasRole(['admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DataImport $dataImport): bool
    {
        // Admins and managers can view all imports
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Users can only view imports they created
        return $dataImport->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Check import limits per user role
        $userImportCount = DataImport::where('created_by', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $dailyLimit = match ($user->getRoleNames()->first()) {
            'admin' => 50,
            'manager' => 20,
            'agent' => 10,
            default => 5
        };

        if ($userImportCount >= $dailyLimit) {
            return false;
        }

        return $user->hasPermissionTo('create data-imports') || $user->hasRole(['admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DataImport $dataImport): bool
    {
        // Cannot update completed or processing imports
        if (in_array($dataImport->status, ['completed', 'processing', 'failed'])) {
            return false;
        }

        // Admins can update all imports
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can update imports they created
        if ($user->hasRole('manager') && $dataImport->created_by === $user->id) {
            return true;
        }

        // Users can only update imports they created
        return $dataImport->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DataImport $dataImport): bool
    {
        // Cannot delete processing imports
        if ($dataImport->status === 'processing') {
            return false;
        }

        // Admins can delete any import
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can delete imports they created or from agents
        if ($user->hasRole('manager')) {
            return $dataImport->created_by === $user->id || 
                   !$dataImport->creator->hasRole(['admin', 'manager']);
        }

        // Users can only delete imports they created
        return $dataImport->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DataImport $dataImport): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('restore data-imports');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DataImport $dataImport): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('force-delete data-imports');
    }

    /**
     * Determine whether the user can process the import.
     */
    public function process(User $user, DataImport $dataImport): bool
    {
        // Import must be pending
        if ($dataImport->status !== 'pending') {
            return false;
        }

        // Must be able to update the import
        if (!$this->update($user, $dataImport)) {
            return false;
        }

        // Check if user can import the specific data type
        return match ($dataImport->type) {
            'contacts' => $user->hasPermissionTo('import contacts') || $user->hasRole(['admin', 'manager', 'agent']),
            'email_campaigns' => $user->hasPermissionTo('import email-campaigns') || $user->hasRole(['admin', 'manager']),
            'sms_messages' => $user->hasPermissionTo('import sms') || $user->hasRole(['admin', 'manager']),
            default => $user->hasRole(['admin', 'manager'])
        };
    }

    /**
     * Determine whether the user can cancel the import.
     */
    public function cancel(User $user, DataImport $dataImport): bool
    {
        // Can only cancel pending or processing imports
        if (!in_array($dataImport->status, ['pending', 'processing'])) {
            return false;
        }

        return $this->update($user, $dataImport);
    }

    /**
     * Determine whether the user can retry the import.
     */
    public function retry(User $user, DataImport $dataImport): bool
    {
        // Can only retry failed imports
        if ($dataImport->status !== 'failed') {
            return false;
        }

        return $this->process($user, $dataImport);
    }

    /**
     * Determine whether the user can download import results.
     */
    public function downloadResults(User $user, DataImport $dataImport): bool
    {
        return $this->view($user, $dataImport);
    }

    /**
     * Determine whether the user can download error logs.
     */
    public function downloadErrors(User $user, DataImport $dataImport): bool
    {
        return $this->view($user, $dataImport) && 
               ($dataImport->status === 'completed' || $dataImport->status === 'failed');
    }

    /**
     * Determine whether the user can view import statistics.
     */
    public function viewStats(User $user, DataImport $dataImport): bool
    {
        return $this->view($user, $dataImport);
    }

    /**
     * Determine whether the user can view detailed import logs.
     */
    public function viewLogs(User $user, DataImport $dataImport): bool
    {
        // Must be able to view import and have logs permission
        if (!$this->view($user, $dataImport)) {
            return false;
        }

        return $user->hasPermissionTo('view logs') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can reprocess specific rows.
     */
    public function reprocessRows(User $user, DataImport $dataImport): bool
    {
        // Import must be completed with errors
        if ($dataImport->status !== 'completed' || $dataImport->errors_count == 0) {
            return false;
        }

        return $this->process($user, $dataImport);
    }

    /**
     * Determine whether the user can duplicate the import.
     */
    public function duplicate(User $user, DataImport $dataImport): bool
    {
        // Must be able to view import and create new ones
        return $this->view($user, $dataImport) && $this->create($user);
    }

    /**
     * Determine whether the user can export import data.
     */
    public function export(User $user, DataImport $dataImport): bool
    {
        return $this->view($user, $dataImport) && 
               ($user->hasPermissionTo('export data') || $user->hasRole(['admin', 'manager']));
    }

    /**
     * Determine whether the user can rollback the import.
     */
    public function rollback(User $user, DataImport $dataImport): bool
    {
        // Can only rollback completed imports within 24 hours
        if ($dataImport->status !== 'completed' || $dataImport->completed_at < now()->subDay()) {
            return false;
        }

        // Only admins and managers can rollback
        return $user->hasRole(['admin', 'manager']) && $this->view($user, $dataImport);
    }

    /**
     * Determine whether the user can manage import mappings.
     */
    public function manageMappings(User $user, DataImport $dataImport): bool
    {
        // Import must be pending
        if ($dataImport->status !== 'pending') {
            return false;
        }

        return $this->update($user, $dataImport);
    }

    /**
     * Determine whether the user can validate import data.
     */
    public function validate(User $user, DataImport $dataImport): bool
    {
        return $this->update($user, $dataImport);
    }
}
