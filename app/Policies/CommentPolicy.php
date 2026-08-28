<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isStaff();
    }

    public function view(User $user, Comment $comment): bool
    {
        return $user->role->isStaff();
    }

    /**
     * Any authenticated, verified user may comment, regardless of role.
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->role->isStaff();
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->role->isStaff();
    }
}
