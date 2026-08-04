<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Deck, Slide, Element, and Asset don't carry a user_id column of their
 * own -- ownership is transitive through project_id (Deck, Asset),
 * deck_id (Slide), or slide_id (Element), all the way up to
 * Project.user_id (see ProjectPolicy and its siblings, which all
 * resolve down to project->user_id the same way).
 *
 * This is the transitive-ownership counterpart to HasUserScope: instead
 * of a bare where('user_id', ...), it applies a whereHas(...) down the
 * relation chain declared by userOwnershipRelation(), so a query
 * against these models is scoped to the authenticated user's projects
 * by default. That is the same fail-closed guarantee HasUserScope gives
 * directly-owned models, and the same idea as Dot.Mines' HasTeamFilters
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
                $builder->whereHas(static::userOwnershipRelation(), function (Builder $query): void {
                    $query->where('user_id', Auth::id());
                });
            }
        });
    }

    /**
     * Dot-notation relation path from this model down to the Project
     * that carries the user_id column, e.g. 'project' for Deck/Asset,
     * 'deck.project' for Slide, 'slide.deck.project' for Element.
     */
    abstract protected static function userOwnershipRelation(): string;
}
