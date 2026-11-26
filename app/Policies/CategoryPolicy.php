<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, Category $category): Response|bool
    {
        if ($category->isSystem()) {
            return Response::deny('System categories cannot be modified.');
        }

        return (int) $category->user_id === (int) $user->id;
    }

    public function delete(User $user, Category $category): Response|bool
    {
        if ($category->isSystem()) {
            return Response::deny('System categories cannot be deleted.');
        }

        return (int) $category->user_id === (int) $user->id;
    }
}
