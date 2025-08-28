<?php

namespace App\Policies;

use App\Models\CustomReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only admins, managers, and super admins can view reports
        return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomReport $customReport): bool
    {
        // Super admins can view all reports
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins can view all reports
        if ($user->hasRole('admin')) {
            return true;
        }

        // Report creators can always view their own reports
        if ($customReport->created_by === $user->id) {
            return true;
        }

        // Public reports can be viewed by anyone with viewAny permission
        if ($customReport->visibility === 'public' && $this->viewAny($user)) {
            return true;
        }

        // Shared reports can be viewed by managers and above
        if ($customReport->visibility === 'shared' && $user->hasAnyRole(['manager', 'admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins, managers, and super admins can create reports
        return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomReport $customReport): bool
    {
        // Super admins can update any report
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins can update any report
        if ($user->hasRole('admin')) {
            return true;
        }

        // Report creators can update their own reports
        if ($customReport->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomReport $customReport): bool
    {
        // Super admins can delete any report
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admins can delete any report
        if ($user->hasRole('admin')) {
            return true;
        }

        // Report creators can delete their own reports
        if ($customReport->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can duplicate the model.
     */
    public function duplicate(User $user, CustomReport $customReport): bool
    {
        // Same permissions as view - if you can view it, you can duplicate it
        return $this->view($user, $customReport);
    }

    /**
     * Determine whether the user can export the model.
     */
    public function export(User $user, CustomReport $customReport): bool
    {
        // Same permissions as view - if you can view it, you can export it
        return $this->view($user, $customReport);
    }

    /**
     * Determine whether the user can execute the model.
     */
    public function execute(User $user, CustomReport $customReport): bool
    {
        // Same permissions as view - if you can view it, you can execute it
        return $this->view($user, $customReport);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CustomReport $customReport): bool
    {
        // Only super admins can restore deleted reports
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CustomReport $customReport): bool
    {
        // Only super admins can permanently delete reports
        return $user->hasRole('super_admin');
    }
}
