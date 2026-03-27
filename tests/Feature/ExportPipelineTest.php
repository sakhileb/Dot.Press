<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Project;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_download_pdf_and_pptx_exports(): void
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Export Project',
            'slug' => 'export-project',
        ]);

        $deck = Deck::create([
            'project_id' => $project->id,
            'title' => 'Export Deck',
            'sort_order' => 0,
        ]);

        Slide::create([
            'deck_id' => $deck->id,
            'title' => 'Slide 1',
            'sort_order' => 0,
            'canvas_state' => [
                'elements' => [
                    [
                        'type' => 'text',
                        'text' => 'Export me',
                        'x' => 100,
                        'y' => 100,
                        'width' => 300,
                        'height' => 120,
                    ],
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('export.decks.pdf', [$deck->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $pptxResponse = $this->actingAs($user)
            ->get(route('export.decks.pptx', [$deck->id]));

        if (class_exists('ZipArchive')) {
            $pptxResponse
                ->assertOk()
                ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        } else {
            $pptxResponse->assertStatus(503);
        }
    }
}
