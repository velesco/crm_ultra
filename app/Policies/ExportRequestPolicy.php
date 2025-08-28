<?php

namespace App\Policies;

use App\Models\ExportRequest;
use App\Models\User;

class ExportRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExportRequest $exportRequest): bool
    {
        // Super admin and admin can view all exports
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Managers can view all exports
        if ($user->hasRole('manager')) {
            return true;
        }

        // Users can view their own exports or public exports
        return $exportRequest->user_id === $user->id || $exportRequest->is_public;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExportRequest $exportRequest): bool
    {
        // Super admin and admin can update all exports
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Managers can update all exports
        if ($user->hasRole('manager')) {
            return true;
        }

        // Users can only update their own exports
        return $exportRequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExportRequest $exportRequest): bool
    {
        // Super admin and admin can delete all exports
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // Managers can delete all exports
        if ($user->hasRole('manager')) {
            return true;
        }

        // Users can only delete their own exports
        return $exportRequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExportRequest $exportRequest): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExportRequest $exportRequest): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can download the export file.
     */
    public function download(User $user, ExportRequest $exportRequest): bool
    {
        // Must be able to view the export and export must be completed
        return $this->view($user, $exportRequest) &&
               $exportRequest->status === 'completed' &&
               $exportRequest->file_path;
    }

    /**
     * Determine whether the user can start/process the export.
     */
    public function process(User $user, ExportRequest $exportRequest): bool
    {
        // Must be able to update and export must be pending
        return $this->update($user, $exportRequest) &&
               $exportRequest->status === 'pending';
    }

    /**
     * Determine whether the user can cancel the export.
     */
    public function cancel(User $user, ExportRequest $exportRequest): bool
    {
        // Must be able to update and export must be cancellable
        return $this->update($user, $exportRequest) &&
               in_array($exportRequest->status, ['pending', 'processing']);
    }

    /**
     * Determine whether the user can duplicate the export.
     */
    public function duplicate(User $user, ExportRequest $exportRequest): bool
    {
        // Must be able to view the export and create new exports
        return $this->view($user, $exportRequest) && $this->create($user);
    }

    /**
     * Determine whether the user can perform bulk actions.
     */
    public function bulkAction(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can create custom query exports.
     */
    public function customQuery(User $user): bool
    {
        // Only super admin and admin can create custom query exports for security
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can schedule exports.
     */
    public function schedule(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can create recurring exports.
     */
    public function recurring(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can make exports public.
     */
    public function makePublic(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can export sensitive data types.
     */
    public function exportSensitiveData(User $user, string $dataType): bool
    {
        $sensitiveDataTypes = ['revenue', 'system_logs', 'custom'];

        if (! in_array($dataType, $sensitiveDataTypes)) {
            return true;
        }

        // Only admin roles can export sensitive data
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can view export statistics.
     */
    public function viewStats(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can cleanup old exports.
     */
    public function cleanup(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }
}
