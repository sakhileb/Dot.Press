<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\Project;
use App\Models\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SlideEditorController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();

        $project = Project::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Dot.Press Workspace',
            ],
            [
                'slug' => 'dot-press-workspace',
                'settings' => ['theme' => 'default'],
            ],
        );

        $deck = $project->decks()->firstOrCreate(
            ['title' => 'Main Deck'],
            [
                'sort_order' => 0,
                'theme' => ['background' => '#ffffff'],
            ],
        );

        $slide = $deck->slides()->orderBy('sort_order')->first();

        if ($slide === null) {
            $slide = $deck->slides()->create([
                'title' => 'Slide 1',
                'layout' => 'blank',
                'sort_order' => 0,
                'canvas_state' => [
                    'elements' => [],
                    'meta' => ['version' => 1],
                ],
            ]);
        }

        return redirect()->route('editor.slides.show', [$deck, $slide]);
    }

    public function show(Deck $deck, Slide $slide): Response
    {
        $this->authorize('view', $deck);
        $this->authorize('view', $slide);

        abort_unless($slide->deck_id === $deck->id, 404);

        $deck->load([
            'project',
            'slides' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        return Inertia::render('Canvas/Editor', [
            'deck' => [
                'id' => $deck->id,
                'title' => $deck->title,
                'project' => [
                    'id' => $deck->project->id,
                    'name' => $deck->project->name,
                ],
            ],
            'slides' => $deck->slides->map(fn (Slide $slideItem) => [
                'id' => $slideItem->id,
                'title' => $slideItem->title,
                'sort_order' => $slideItem->sort_order,
            ]),
            'slide' => [
                'id' => $slide->id,
                'title' => $slide->title,
                'layout' => $slide->layout,
                'sort_order' => $slide->sort_order,
                'revision' => $slide->revision,
                'canvas_state' => $slide->canvas_state,
            ],
        ]);
    }

    public function present(Deck $deck, Slide $slide): Response
    {
        $this->authorize('view', $deck);
        $this->authorize('view', $slide);

        abort_unless($slide->deck_id === $deck->id, 404);

        $deck->load([
            'project',
            'slides' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        return Inertia::render('Canvas/Presentation', [
            'deck' => [
                'id' => $deck->id,
                'title' => $deck->title,
                'project' => [
                    'id' => $deck->project->id,
                    'name' => $deck->project->name,
                ],
            ],
            'slides' => $deck->slides->map(fn (Slide $slideItem) => [
                'id' => $slideItem->id,
                'title' => $slideItem->title,
                'sort_order' => $slideItem->sort_order,
            ]),
            'slide' => [
                'id' => $slide->id,
                'title' => $slide->title,
                'layout' => $slide->layout,
                'sort_order' => $slide->sort_order,
                'revision' => $slide->revision,
                'canvas_state' => $slide->canvas_state,
            ],
        ]);
    }
}
