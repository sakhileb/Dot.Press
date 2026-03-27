<?php

namespace App\Policies;

use App\Models\Slide;
use App\Models\User;

class SlidePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Slide $slide): bool
    {
        return $slide->deck->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->exists;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Slide $slide): bool
    {
        return $slide->deck->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Slide $slide): bool
    {
        return $slide->deck->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Slide $slide): bool
    {
        return $slide->deck->project->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Slide $slide): bool
    {
        return $slide->deck->project->user_id === $user->id;
    }
}
