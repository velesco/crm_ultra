<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WhatsAppSession;

class WhatsAppSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view whatsapp-sessions') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Admins can view all sessions
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can view all sessions
        if ($user->hasRole('manager')) {
            return true;
        }

        // Users can only view sessions they created
        return $whatsAppSession->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Check if user has reached session limit
        $userSessionCount = WhatsAppSession::where('created_by', $user->id)->count();
        $maxSessions = $user->hasRole('admin') ? 10 : ($user->hasRole('manager') ? 5 : 2);

        if ($userSessionCount >= $maxSessions) {
            return false;
        }

        return $user->hasPermissionTo('create whatsapp-sessions') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Cannot update active/connected sessions
        if ($whatsAppSession->status === 'connected') {
            return false;
        }

        // Admins can update all sessions
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can update all sessions
        if ($user->hasRole('manager')) {
            return true;
        }

        // Users can only update sessions they created
        return $whatsAppSession->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Cannot delete connected sessions
        if ($whatsAppSession->status === 'connected') {
            return false;
        }

        // Admins can delete any session
        if ($user->hasRole('admin')) {
            return true;
        }

        // Managers can delete sessions they created or from agents
        if ($user->hasRole('manager')) {
            return $whatsAppSession->created_by === $user->id ||
                   ! $whatsAppSession->creator->hasRole(['admin', 'manager']);
        }

        // Users can only delete sessions they created
        return $whatsAppSession->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WhatsAppSession $whatsAppSession): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('restore whatsapp-sessions');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WhatsAppSession $whatsAppSession): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('force-delete whatsapp-sessions');
    }

    /**
     * Determine whether the user can start the session.
     */
    public function start(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Session must be inactive
        if (in_array($whatsAppSession->status, ['connecting', 'connected'])) {
            return false;
        }

        // Must be able to view the session
        if (! $this->view($user, $whatsAppSession)) {
            return false;
        }

        // Check if user has WhatsApp permissions
        return $user->hasPermissionTo('manage whatsapp-sessions') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can stop the session.
     */
    public function stop(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Session must be active
        if (! in_array($whatsAppSession->status, ['connecting', 'connected'])) {
            return false;
        }

        return $this->start($user, $whatsAppSession);
    }

    /**
     * Determine whether the user can restart the session.
     */
    public function restart(User $user, WhatsAppSession $whatsAppSession): bool
    {
        return $this->start($user, $whatsAppSession);
    }

    /**
     * Determine whether the user can view QR code.
     */
    public function viewQR(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Session must be connecting and user must be able to view it
        if ($whatsAppSession->status !== 'connecting') {
            return false;
        }

        return $this->view($user, $whatsAppSession);
    }

    /**
     * Determine whether the user can send messages through this session.
     */
    public function sendMessages(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Session must be connected
        if ($whatsAppSession->status !== 'connected') {
            return false;
        }

        // Must be able to view the session
        if (! $this->view($user, $whatsAppSession)) {
            return false;
        }

        return $user->hasPermissionTo('send whatsapp') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can view session status.
     */
    public function viewStatus(User $user, WhatsAppSession $whatsAppSession): bool
    {
        return $this->view($user, $whatsAppSession);
    }

    /**
     * Determine whether the user can view session logs.
     */
    public function viewLogs(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Must be able to view session and have logs permission
        if (! $this->view($user, $whatsAppSession)) {
            return false;
        }

        return $user->hasPermissionTo('view logs') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can manage session webhooks.
     */
    public function manageWebhooks(User $user, WhatsAppSession $whatsAppSession): bool
    {
        return $this->update($user, $whatsAppSession) &&
               ($user->hasPermissionTo('manage webhooks') || $user->hasRole(['admin', 'manager']));
    }

    /**
     * Determine whether the user can export session data.
     */
    public function exportData(User $user, WhatsAppSession $whatsAppSession): bool
    {
        return $this->view($user, $whatsAppSession) &&
               ($user->hasPermissionTo('export data') || $user->hasRole(['admin', 'manager']));
    }

    /**
     * Determine whether the user can view session statistics.
     */
    public function viewStats(User $user, WhatsAppSession $whatsAppSession): bool
    {
        return $this->view($user, $whatsAppSession);
    }

    /**
     * Determine whether the user can reset session data.
     */
    public function reset(User $user, WhatsAppSession $whatsAppSession): bool
    {
        // Session must not be connected
        if ($whatsAppSession->status === 'connected') {
            return false;
        }

        return $this->update($user, $whatsAppSession);
    }
}
