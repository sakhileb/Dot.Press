<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Project used to be single-user (see the superseded note that lived on
 * HasUserScope) -- it now carries a team_id and is shared by every member
 * of the owning team, the same way Dot.Mines scopes machines/reports/etc.
 * by team. A query against Project is scoped to every team the
 * authenticated user belongs to (not just their current team) so
 * switching teams in the UI doesn't hide a project mid-session; policies
 * still gate individual actions.
 */
trait HasTeamScope
{
    protected static function bootHasTeamScope(): void
    {
        static::addGlobalScope('team', function (Builder $builder): void {
            if (Auth::check()) {
                $teamIds = Auth::user()->allTeams()->pluck('id');

                $builder->whereIn($builder->getModel()->getTable().'.team_id', $teamIds);
            }
        });
    }
}
