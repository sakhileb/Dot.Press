<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * The presentation domain (Project and everything under it) moved to
 * team-scoping -- see HasTeamScope on Project and the team_id check in
 * HasUserScopeThroughOwner. This trait is genuinely single-user territory
 * now: AiUsageLog is a personal usage/audit trail, not a shared content
 * model, and stays scoped to the authenticated user's own rows the same
 * way it always was.
 *
 * mass-assignment still sets user_id explicitly at create time; this
 * scope only governs reads.
 */
trait HasUserScope
{
    protected static function bootHasUserScope(): void
    {
        static::addGlobalScope('user', function (Builder $builder): void {
            if (Auth::check()) {
                $builder->where($builder->getModel()->getTable().'.user_id', Auth::id());
            }
        });
    }
}
