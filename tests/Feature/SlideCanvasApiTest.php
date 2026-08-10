<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Project;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlideCanvasApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_slide_canvas_state(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'team_id' => $user->currentTeam->id,
            'name' => 'Canvas Project',
            'slug' => 'canvas-project',
        ]);

        $deck = Deck::create([
            'project_id' => $project->id,
            'title' => 'Deck 1',
            'sort_order' => 0,
        ]);

        $slide = Slide::create([
            'deck_id' => $deck->id,
            'title' => 'Slide 1',
            'sort_order' => 0,
            'canvas_state' => ['elements' => []],
        ]);

        $payload = [
            'canvas_state' => [
                'elements' => [
                    [
                        'id' => 'el-1',
                        'type' => 'rect',
                        'x' => 100,
                        'y' => 120,
                        'width' => 200,
                        'height' => 100,
                    ],
                ],
                'meta' => ['version' => 1],
            ],
        ];

        $this->actingAs($user)
            ->putJson('/api/slides/'.$slide->id, $payload)
            ->assertOk()
            ->assertJsonPath('canvas_state.elements.0.id', 'el-1');

        $this->assertEquals('el-1', $slide->fresh()->canvas_state['elements'][0]['id']);
    }

    public function test_non_owner_cannot_update_slide_canvas_state(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $intruder = User::factory()->withPersonalTeam()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'team_id' => $owner->currentTeam->id,
            'name' => 'Owner Project',
            'slug' => 'owner-project',
        ]);

        $deck = Deck::create([
            'project_id' => $project->id,
            'title' => 'Deck 1',
            'sort_order' => 0,
        ]);

        $slide = Slide::create([
            'deck_id' => $deck->id,
            'title' => 'Slide 1',
            'sort_order' => 0,
            'canvas_state' => ['elements' => []],
        ]);

        // Slide route-model binding now resolves through the
        // HasUserScopeThroughOwner global scope, so an intruder's query
        // finds no row at all -- Laravel throws a 404 before
        // SlideController::update's explicit authorize() call ever runs.
        // This is a real improvement over the old 403 (the route no
        // longer confirms the resource exists to non-owners).
        $this->actingAs($intruder)
            ->putJson('/api/slides/'.$slide->id, [
                'canvas_state' => ['elements' => []],
            ])
            ->assertNotFound();
    }

    public function test_canvas_update_detects_revision_conflict(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'team_id' => $user->currentTeam->id,
            'name' => 'Conflict Project',
            'slug' => 'conflict-project',
        ]);

        $deck = Deck::create([
            'project_id' => $project->id,
            'title' => 'Deck 1',
            'sort_order' => 0,
        ]);

        $slide = Slide::create([
            'deck_id' => $deck->id,
            'title' => 'Slide 1',
            'sort_order' => 0,
            'revision' => 3,
            'canvas_state' => ['elements' => []],
        ]);

        $this->actingAs($user)
            ->putJson('/api/slides/'.$slide->id, [
                'canvas_state' => ['elements' => []],
                'expected_revision' => 2,
            ])
            ->assertStatus(409)
            ->assertJsonPath('conflict', true)
            ->assertJsonPath('server_revision', 3);
    }
}
