<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Project;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollaborationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_send_collaboration_heartbeat(): void
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Collab Project',
            'slug' => 'collab-project',
        ]);

        $deck = Deck::create([
            'project_id' => $project->id,
            'title' => 'Deck',
            'sort_order' => 0,
        ]);

        $slide = Slide::create([
            'deck_id' => $deck->id,
            'title' => 'Slide 1',
            'sort_order' => 0,
            'canvas_state' => ['elements' => []],
        ]);

        $this->actingAs($user)
            ->postJson('/api/collab/slides/'.$slide->id.'/heartbeat', [
                'cursor' => ['x' => 120, 'y' => 220],
                'selected_ids' => ['el-1'],
            ])
            ->assertOk()
            ->assertJsonStructure(['participants']);

        $this->actingAs($user)
            ->getJson('/api/collab/slides/'.$slide->id.'/participants')
            ->assertOk()
            ->assertJsonStructure(['participants']);
    }
}
