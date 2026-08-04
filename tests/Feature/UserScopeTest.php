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
     * Direct-scope case: Project.user_id, guarded by HasUserScope.
     */
    public function test_scope_alone_blocks_cross_user_access_even_without_an_explicit_where(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
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
     * Transitive-scope case: Deck has no user_id of its own -- ownership
     * flows through Deck.project_id -> Project.user_id, guarded by
     * HasUserScopeThroughOwner via whereHas('project', ...).
     */
    public function test_transitive_scope_blocks_cross_user_access_to_decks_without_an_explicit_where(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
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
}
