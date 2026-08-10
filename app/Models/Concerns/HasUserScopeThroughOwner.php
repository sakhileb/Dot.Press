<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Deck, Slide, Element, and Asset don't carry a team_id column of their
 * own -- ownership is transitive through project_id (Deck, Asset),
 * deck_id (Slide), or slide_id (Element), all the way up to
 * Project.team_id (see DeckPolicy and its siblings, which all resolve
 * down to team membership on project->team the same way).
 *
 * This is the transitive-ownership counterpart to HasTeamScope: instead
 * of a bare whereIn('team_id', ...), it applies a whereHas(...) down the
 * relation chain declared by userOwnershipRelation(), so a query against
 * these models is scoped to every team the authenticated user belongs to
 * by default. That is the same fail-closed guarantee HasTeamScope gives
 * Project directly, and the same idea as Dot.Mines' HasTeamFilters
 * applied to every tenant-owned model regardless of how deep it sits
 * below the team.
 *
 * mass-assignment still goes through the owning parent at create time
 * (e.g. $deck->slides()->create(...), $project->decks()->create(...));
 * this scope only governs reads.
 */
trait HasUserScopeThroughOwner
{
    protected static function bootHasUserScopeThroughOwner(): void
    {
        static::addGlobalScope('user', function (Builder $builder): void {
            if (Auth::check()) {
                $teamIds = Auth::user()->allTeams()->pluck('id');

                $builder->whereHas(static::userOwnershipRelation(), function (Builder $query) use ($teamIds): void {
                    $query->whereIn('team_id', $teamIds);
                });
            }
        });
    }

    /**
     * Dot-notation relation path from this model down to the Project
     * that carries the team_id column, e.g. 'project' for Deck/Asset,
     * 'deck.project' for Slide, 'slide.deck.project' for Element.
     */
    abstract protected static function userOwnershipRelation(): string;
}
