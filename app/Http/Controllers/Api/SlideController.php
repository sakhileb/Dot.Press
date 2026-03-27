<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use App\Models\Slide;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Slide::class);

        $validated = $request->validate([
            'deck_id' => ['nullable', 'integer', 'exists:decks,id'],
        ]);

        if (isset($validated['deck_id'])) {
            $deck = Deck::with('project')->findOrFail($validated['deck_id']);
            $this->authorize('view', $deck);

            return $deck->slides()->orderBy('sort_order')->get();
        }

        return Slide::query()
            ->whereHas('deck.project', function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id);
            })
            ->with('deck')
            ->orderByDesc('id')
            ->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Slide::class);

        $payload = $request->validate([
            'deck_id' => ['required', 'integer', 'exists:decks,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'layout' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'canvas_state' => ['nullable', 'array'],
        ]);

        $deck = Deck::with('project')->findOrFail($payload['deck_id']);
        $this->authorize('view', $deck);

        $slide = $deck->slides()->create([
            'title' => $payload['title'] ?? 'Untitled Slide',
            'notes' => $payload['notes'] ?? null,
            'layout' => $payload['layout'] ?? 'blank',
            'sort_order' => $payload['sort_order'] ?? ((int) $deck->slides()->max('sort_order') + 1),
            'canvas_state' => $payload['canvas_state'] ?? ['elements' => []],
        ]);

        return response()->json($slide, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Slide $slide)
    {
        $this->authorize('view', $slide);

        return $slide->load('deck');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Slide $slide)
    {
        $this->authorize('update', $slide);

        $payload = $request->validate([
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'layout' => ['sometimes', 'nullable', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'canvas_state' => ['sometimes', 'nullable', 'array'],
            'expected_revision' => ['sometimes', 'integer', 'min:0'],
        ]);

        if (array_key_exists('canvas_state', $payload) && array_key_exists('expected_revision', $payload)) {
            $expected = (int) $payload['expected_revision'];

            if ($slide->revision !== $expected) {
                return response()->json([
                    'message' => 'Slide changed in another session. Please reload latest state.',
                    'conflict' => true,
                    'server_revision' => $slide->revision,
                    'server_canvas_state' => $slide->canvas_state,
                ], 409);
            }

            $nextRevision = $slide->revision + 1;
            $canvasState = $payload['canvas_state'] ?? [];
            $meta = is_array($canvasState['meta'] ?? null) ? $canvasState['meta'] : [];
            $meta['revision'] = $nextRevision;
            $canvasState['meta'] = $meta;

            $payload['canvas_state'] = $canvasState;
            $payload['revision'] = $nextRevision;
        }

        unset($payload['expected_revision']);

        $slide->update($payload);

        return $slide->fresh();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slide $slide)
    {
        $this->authorize('delete', $slide);

        $slide->delete();

        return response()->noContent();
    }
}
