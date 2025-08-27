<?php

namespace App\Policies;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmailCampaignPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view email-campaigns') || $user->hasRole(['admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmailCampaign $emailCampaign): bool
    {
        // Admins and managers can view all campaigns
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Agents can only view campaigns they created
        if ($user->hasRole('agent')) {
            return $emailCampaign->created_by === $user->id;
        }

        return $user->hasPermissionTo('view email-campaigns');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create email-campaigns') || $user->hasRole(['admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmailCampaign $emailCampaign): bool
    {
        // Cannot update campaigns that are already sent
        if (in_array($emailCampaign->status, ['sent', 'sending'])) {
            return false;
        }

        // Admins and managers can update all campaigns
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Agents can only update campaigns they created
        if ($user->hasRole('agent')) {
            return $emailCampaign->created_by === $user->id;
        }

        return $user->hasPermissionTo('edit email-campaigns');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmailCampaign $emailCampaign): bool
    {
        // Cannot delete campaigns that are sent or sending
        if (in_array($emailCampaign->status, ['sent', 'sending'])) {
            return false;
        }

        // Admins can delete any campaign
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can delete campaigns, but not from other managers unless they created them
        if ($user->hasRole('manager')) {
            return $emailCampaign->created_by === $user->id || 
                   !$emailCampaign->creator->hasRole('manager');
        }

        // Agents can only delete campaigns they created
        if ($user->hasRole('agent')) {
            return $emailCampaign->created_by === $user->id;
        }

        return $user->hasPermissionTo('delete email-campaigns');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmailCampaign $emailCampaign): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('restore email-campaigns');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmailCampaign $emailCampaign): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('force-delete email-campaigns');
    }

    /**
     * Determine whether the user can send the campaign.
     */
    public function send(User $user, EmailCampaign $emailCampaign): bool
    {
        // Campaign must be in draft or scheduled status
        if (!in_array($emailCampaign->status, ['draft', 'scheduled'])) {
            return false;
        }

        // Must be able to view the campaign
        if (!$this->view($user, $emailCampaign)) {
            return false;
        }

        return $user->hasPermissionTo('send email-campaigns') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can pause the campaign.
     */
    public function pause(User $user, EmailCampaign $emailCampaign): bool
    {
        // Campaign must be sending
        if ($emailCampaign->status !== 'sending') {
            return false;
        }

        return $this->send($user, $emailCampaign);
    }

    /**
     * Determine whether the user can resume the campaign.
     */
    public function resume(User $user, EmailCampaign $emailCampaign): bool
    {
        // Campaign must be paused
        if ($emailCampaign->status !== 'paused') {
            return false;
        }

        return $this->send($user, $emailCampaign);
    }

    /**
     * Determine whether the user can schedule the campaign.
     */
    public function schedule(User $user, EmailCampaign $emailCampaign): bool
    {
        // Campaign must be in draft status
        if ($emailCampaign->status !== 'draft') {
            return false;
        }

        return $this->send($user, $emailCampaign);
    }

    /**
     * Determine whether the user can duplicate the campaign.
     */
    public function duplicate(User $user, EmailCampaign $emailCampaign): bool
    {
        // Must be able to view the campaign and create new ones
        return $this->view($user, $emailCampaign) && $this->create($user);
    }

    /**
     * Determine whether the user can send test emails.
     */
    public function sendTest(User $user, EmailCampaign $emailCampaign): bool
    {
        return $this->view($user, $emailCampaign) && 
               ($user->hasPermissionTo('send test emails') || $user->hasRole(['admin', 'manager', 'agent']));
    }

    /**
     * Determine whether the user can manage campaign contacts.
     */
    public function manageContacts(User $user, EmailCampaign $emailCampaign): bool
    {
        // Campaign must not be sent
        if (in_array($emailCampaign->status, ['sent', 'sending'])) {
            return false;
        }

        return $this->update($user, $emailCampaign);
    }

    /**
     * Determine whether the user can view campaign statistics.
     */
    public function viewStats(User $user, EmailCampaign $emailCampaign): bool
    {
        return $this->view($user, $emailCampaign);
    }

    /**
     * Determine whether the user can view detailed campaign reports.
     */
    public function viewReports(User $user, EmailCampaign $emailCampaign): bool
    {
        // Must be able to view campaign and have reports permission
        if (!$this->view($user, $emailCampaign)) {
            return false;
        }

        return $user->hasPermissionTo('view reports') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can export campaign reports.
     */
    public function exportReports(User $user, EmailCampaign $emailCampaign): bool
    {
        return $this->viewReports($user, $emailCampaign) && 
               ($user->hasPermissionTo('export reports') || $user->hasRole(['admin', 'manager']));
    }

    /**
     * Determine whether the user can cancel the campaign.
     */
    public function cancel(User $user, EmailCampaign $emailCampaign): bool
    {
        // Can only cancel scheduled or paused campaigns
        if (!in_array($emailCampaign->status, ['scheduled', 'paused'])) {
            return false;
        }

        return $this->send($user, $emailCampaign);
    }

    /**
     * Determine whether the user can preview the campaign.
     */
    public function preview(User $user, EmailCampaign $emailCampaign): bool
    {
        return $this->view($user, $emailCampaign);
    }
}
