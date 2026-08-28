<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== Role::Reader;
    }

    public function view(User $user, Post $post): bool
    {
        return $user->role->isStaff() || $post->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role !== Role::Reader;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->role->isStaff() || $post->user_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->role->isStaff() || $post->user_id === $user->id;
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->role === Role::Admin;
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->role === Role::Admin;
    }
}
