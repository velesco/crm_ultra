<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContactPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view contacts') || $user->hasRole(['admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Contact $contact): bool
    {
        // Admins and managers can view all contacts
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Agents can view contacts they created or are assigned to
        if ($user->hasRole('agent')) {
            return $contact->created_by === $user->id || $contact->assigned_to === $user->id;
        }

        // Check specific permission
        return $user->hasPermissionTo('view contacts');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create contacts') || $user->hasRole(['admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Contact $contact): bool
    {
        // Admins and managers can update all contacts
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Agents can update contacts they created or are assigned to
        if ($user->hasRole('agent')) {
            return $contact->created_by === $user->id || $contact->assigned_to === $user->id;
        }

        // Check specific permission
        return $user->hasPermissionTo('edit contacts');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Contact $contact): bool
    {
        // Only admins and managers can delete contacts
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Agents can only delete contacts they created (not assigned ones)
        if ($user->hasRole('agent') && $contact->created_by === $user->id) {
            return true;
        }

        return $user->hasPermissionTo('delete contacts');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Contact $contact): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('restore contacts');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Contact $contact): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('force-delete contacts');
    }

    /**
     * Determine whether the user can export contacts.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo('export contacts') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can import contacts.
     */
    public function import(User $user): bool
    {
        return $user->hasPermissionTo('import contacts') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can send emails to contact.
     */
    public function sendEmail(User $user, Contact $contact): bool
    {
        // Must be able to view the contact and have email permissions
        if (!$this->view($user, $contact)) {
            return false;
        }

        return $user->hasPermissionTo('send emails') || $user->hasRole(['admin', 'manager', 'agent']);
    }

    /**
     * Determine whether the user can send SMS to contact.
     */
    public function sendSms(User $user, Contact $contact): bool
    {
        // Must be able to view the contact and have SMS permissions
        if (!$this->view($user, $contact)) {
            return false;
        }

        return $user->hasPermissionTo('send sms') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can send WhatsApp to contact.
     */
    public function sendWhatsApp(User $user, Contact $contact): bool
    {
        // Must be able to view the contact and have WhatsApp permissions
        if (!$this->view($user, $contact)) {
            return false;
        }

        return $user->hasPermissionTo('send whatsapp') || $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can assign contact to other users.
     */
    public function assign(User $user, Contact $contact): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('assign contacts');
    }

    /**
     * Determine whether the user can bulk update contacts.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('bulk-update contacts');
    }

    /**
     * Determine whether the user can bulk delete contacts.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->hasPermissionTo('bulk-delete contacts');
    }

    /**
     * Determine whether the user can view contact activity/communications.
     */
    public function viewActivity(User $user, Contact $contact): bool
    {
        return $this->view($user, $contact);
    }

    /**
     * Determine whether the user can edit contact segments.
     */
    public function manageSegments(User $user, Contact $contact): bool
    {
        if (!$this->update($user, $contact)) {
            return false;
        }

        return $user->hasPermissionTo('manage segments') || $user->hasRole(['admin', 'manager']);
    }
}
