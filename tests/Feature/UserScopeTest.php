<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Direct-scope case: Project.team_id, guarded by HasTeamScope.
     */
    public function test_scope_alone_blocks_cross_user_access_even_without_an_explicit_where(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $outsider = User::factory()->withPersonalTeam()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'team_id' => $owner->currentTeam->id,
            'name' => 'Owner Project',
            'slug' => 'owner-project',
        ]);

        $this->actingAs($outsider);

        $this->assertNull(Project::find($project->id));
        $this->assertSame(0, Project::query()->count());

        $this->actingAs($owner);

        $this->assertNotNull(Project::find($project->id));
        $this->assertSame(1, Project::query()->count());
    }

    /**
     * Transitive-scope case: Deck has no team_id of its own -- ownership
     * flows through Deck.project_id -> Project.team_id, guarded by
     * HasUserScopeThroughOwner via whereHas('project', ...).
     */
    public function test_transitive_scope_blocks_cross_user_access_to_decks_without_an_explicit_where(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $outsider = User::factory()->withPersonalTeam()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'team_id' => $owner->currentTeam->id,
            'name' => 'Owner Project',
            'slug' => 'owner-project-decks',
        ]);

        $deck = Deck::create([
            'project_id' => $project->id,
            'title' => 'Owner Deck',
            'sort_order' => 0,
        ]);

        $this->actingAs($outsider);

        $this->assertNull(Deck::find($deck->id));
        $this->assertSame(0, Deck::query()->count());

        $this->actingAs($owner);

        $this->assertNotNull(Deck::find($deck->id));
        $this->assertSame(1, Deck::query()->count());
    }

    /**
     * A second member of the same team (not just the creator) can also
     * see the project -- this is the actual point of team-scoping.
     */
    public function test_a_teammate_who_did_not_create_the_project_can_still_see_it(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $teammate = User::factory()->create();
        $owner->currentTeam->users()->attach($teammate, ['role' => 'editor']);
        $teammate->switchTeam($owner->currentTeam);

        $project = Project::create([
            'user_id' => $owner->id,
            'team_id' => $owner->currentTeam->id,
            'name' => 'Shared Project',
            'slug' => 'shared-project',
        ]);

        $this->actingAs($teammate);

        $this->assertNotNull(Project::find($project->id));
    }
}
