<?php

namespace App\Broadcasting;

use App\Models\Deck;
use App\Models\Project;
use App\Models\Slide;
use App\Models\User;

/**
 * Authorization for the 'slide.{slideId}' private channel, factored out of
 * routes/channels.php so it can be unit tested directly -- the "null" and
 * "log" broadcast drivers (what tests and local dev use by default) never
 * actually invoke a channel's callback via the /broadcasting/auth HTTP
 * endpoint, so that endpoint can't be used to verify this logic.
 *
 * Every lookup here is deliberately unscoped and walked manually
 * (Slide -> Deck -> Project) rather than via ->with('deck.project'):
 * eager-loading through the relations would apply Deck's and Project's
 * own team-scoped global scopes using the *current* authenticated user,
 * which would silently return null for anyone outside the team instead
 * of the row this check actually needs to test membership against.
 */
class SlideChannelAuthorizer
{
    public static function authorize(User $user, int $slideId): bool
    {
        $slide = Slide::withoutGlobalScopes()->find($slideId);

        if (! $slide) {
            return false;
        }

        $deck = Deck::withoutGlobalScopes()->find($slide->deck_id);

        if (! $deck) {
            return false;
        }

        $project = Project::withoutGlobalScopes()->find($deck->project_id);

        if (! $project) {
            return false;
        }

        return $user->belongsToTeam($project->team);
    }
}
