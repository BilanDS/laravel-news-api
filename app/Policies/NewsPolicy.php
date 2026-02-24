<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NewsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    // Користувач може переглядати свою приховану новину
    public function view(User $user, News $news): bool
    {
        return $user->id === $news->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    // Користувач може редагувати тільки свою новину
    public function update(User $user, News $news): bool
    {
        return $user->id === $news->user_id;
    }

    // Користувач може видаляти тільки свою новину
    public function delete(User $user, News $news): bool
    {
        return $user->id === $news->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, News $news): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, News $news): bool
    {
        return false;
    }
}
