<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Fund;
use Illuminate\Auth\Access\HandlesAuthorization;

class FundPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_fund');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Fund $fund): bool
    {
        return $user->can('view_fund');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_fund');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Fund $fund): bool
    {
        return $user->can('update_fund');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Fund $fund): bool
    {
        return $user->can('delete_fund');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_fund');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Fund $fund): bool
    {
        return $user->can('force_delete_fund');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_fund');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Fund $fund): bool
    {
        return $user->can('restore_fund');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_fund');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Fund $fund): bool
    {
        return $user->can('replicate_fund');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_fund');
    }
}
