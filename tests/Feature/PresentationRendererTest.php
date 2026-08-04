<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Project;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresentationRendererTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_owner_can_open_presentation_route(): void
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Presentation Project',
            'slug' => 'presentation-project',
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
            'canvas_state' => [
                'elements' => [
                    [
                        'id' => 'text-1',
                        'type' => 'text',
                        'x' => 100,
                        'y' => 100,
                        'width' => 400,
                        'height' => 120,
                        'richContent' => [
                            'type' => 'doc',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => 'Hello rich presentation',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('presentation.slides.show', [$deck->id, $slide->id]))
            ->assertOk();
    }

    public function test_non_owner_cannot_open_presentation_route(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Owner Project',
            'slug' => 'owner-project',
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

        // Deck/Slide route-model binding now resolves through the
        // HasUserScopeThroughOwner global scope, so an intruder's query
        // finds no row at all -- Laravel throws a 404 before
        // SlideEditorController::present's explicit authorize() calls
        // ever run. This is a real improvement over the old 403 (the
        // route no longer confirms the resource exists to non-owners).
        $this->actingAs($intruder)
            ->get(route('presentation.slides.show', [$deck->id, $slide->id]))
            ->assertNotFound();
    }
}
