<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === Role::Admin;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === Role::Admin;
    }

    /**
     * Admins may delete other users but never themselves — the panel would otherwise
     * let an admin lock themselves out.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->role === Role::Admin && $user->isNot($model);
    }
}
