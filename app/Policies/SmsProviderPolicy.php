<?php

namespace App\Policies;

use App\Models\SmsProvider;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SmsProviderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view sms-providers') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SmsProvider $smsProvider): bool
    {
        // Admins can view all providers
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can view all providers
        if ($user->hasRole('manager')) {
            return true;
        }

        // Regular users can only view active providers they have permission to use
        return $smsProvider->is_active && 
               ($user->hasPermissionTo('view sms-providers') || $user->hasRole('agent'));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create sms-providers') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SmsProvider $smsProvider): bool
    {
        // Only admins and managers can update providers
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Users can only update providers they created (with restrictions)
        if ($smsProvider->created_by === $user->id) {
            // Can only update certain fields, not critical ones like API keys
            return $user->hasPermissionTo('edit sms-providers');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SmsProvider $smsProvider): bool
    {
        // Cannot delete providers that are in use
        if ($smsProvider->smsMessages()->exists()) {
            return false;
        }

        // Only admins can delete providers
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can delete providers they created
        if ($user->hasRole('manager') && $smsProvider->created_by === $user->id) {
            return true;
        }

        return $user->hasPermissionTo('delete sms-providers');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SmsProvider $smsProvider): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('restore sms-providers');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SmsProvider $smsProvider): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('force-delete sms-providers');
    }

    /**
     * Determine whether the user can test the provider.
     */
    public function test(User $user, SmsProvider $smsProvider): bool
    {
        // Must be able to view the provider
        if (!$this->view($user, $smsProvider)) {
            return false;
        }

        return $user->hasPermissionTo('test sms-providers') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can toggle provider active status.
     */
    public function toggleActive(User $user, SmsProvider $smsProvider): bool
    {
        // Only admins and managers can toggle status
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('manage sms-providers');
    }

    /**
     * Determine whether the user can use provider to send SMS.
     */
    public function sendSms(User $user, SmsProvider $smsProvider): bool
    {
        // Provider must be active
        if (!$smsProvider->is_active) {
            return false;
        }

        // Must be able to view the provider
        if (!$this->view($user, $smsProvider)) {
            return false;
        }

        // Check if provider has reached limits
        if (!$smsProvider->canSend()) {
            return false;
        }

        return $user->hasPermissionTo('send sms') || $user->hasRole(['admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can view provider statistics.
     */
    public function viewStats(User $user, SmsProvider $smsProvider): bool
    {
        return $this->view($user, $smsProvider);
    }

    /**
     * Determine whether the user can view detailed provider reports.
     */
    public function viewReports(User $user, SmsProvider $smsProvider): bool
    {
        // Must be able to view provider and have reports permission
        if (!$this->view($user, $smsProvider)) {
            return false;
        }

        return $user->hasPermissionTo('view reports') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can manage provider API keys and settings.
     */
    public function manageCredentials(User $user, SmsProvider $smsProvider): bool
    {
        // Only admins and managers can manage credentials
        return $user->hasRole(['admin', 'manager']) && $this->update($user, $smsProvider);
    }

    /**
     * Determine whether the user can reset provider counters.
     */
    public function resetCounters(User $user, SmsProvider $smsProvider): bool
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('manager') && $this->update($user, $smsProvider));
    }

    /**
     * Determine whether the user can view provider logs.
     */
    public function viewLogs(User $user, SmsProvider $smsProvider): bool
    {
        // Must be able to view provider and have logs permission
        if (!$this->view($user, $smsProvider)) {
            return false;
        }

        return $user->hasPermissionTo('view logs') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can export provider data.
     */
    public function exportData(User $user, SmsProvider $smsProvider): bool
    {
        return $this->view($user, $smsProvider) && 
               ($user->hasPermissionTo('export data') || $user->hasRole(['admin', 'manager']));
    }

    /**
     * Determine whether the user can configure webhooks.
     */
    public function manageWebhooks(User $user, SmsProvider $smsProvider): bool
    {
        return $this->manageCredentials($user, $smsProvider);
    }

    /**
     * Determine whether the user can duplicate the provider.
     */
    public function duplicate(User $user, SmsProvider $smsProvider): bool
    {
        // Must be able to view provider and create new ones
        return $this->view($user, $smsProvider) && $this->create($user);
    }

    /**
     * Determine whether the user can set provider priority.
     */
    public function setPriority(User $user, SmsProvider $smsProvider): bool
    {
        return $user->hasRole(['admin', 'manager']) && $this->update($user, $smsProvider);
    }
}
