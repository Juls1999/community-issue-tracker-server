<?php

namespace App\Policies;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IssuePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Issue $issue): bool
    {
        $role = $user->role?->name;

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'resident') {
            return $issue->user_id === $user->id;
        }

        if ($role === 'staff') {
            return $issue->assigned_to === $user->id || ($issue->assigned_to === null
                && $user->barangay_id !== null
                && $issue->barangay_id === $user->barangay_id);
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array(
            $user->role?->name,
            ['resident', 'admin'],
            true
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Issue $issue): bool
    {
        $role = $user->role?->name;

        // Admins can update any issue.
        if ($role === 'admin') {
            return true;
        }

        // Residents can edit their own issues only while pending.
        if ($role === 'resident') {
            return $issue->user_id === $user->id
                && $issue->status === 'pending';
        }

        // Staff can update issues they are responsible for.
        return $role === 'staff'
            && $issue->assigned_to === $user->id;
    }

    /** Determine whether the user can assign staff */
    public function assign(User $user, Issue $issue): bool
    {
        // Assignment is an administrative operation.
        return $user->role?->name === 'admin';
    }

    /**
     * Summary of updateStatus
     * @param User $user
     * @param Issue $issue
     * @return bool
     */
    public function updateStatus(User $user, Issue $issue): bool
    {
        $role = $user->role?->name;

        if ($role === 'admin') {
            return true;
        }

        return $role === 'staff'
            && $issue->assigned_to === $user->id;
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Issue $issue): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Issue $issue): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Issue $issue): bool
    {
        return false;
    }
}
