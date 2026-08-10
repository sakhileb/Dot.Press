<?php

namespace App\Services;

use App\Models\Element;
use App\Models\Slide;
use Illuminate\Support\Str;

/**
 * Persists a canvas save's flat element array into the normalized Element
 * table, instead of the old behaviour of only ever writing the whole array
 * as JSON on Slide.canvas_state.
 */
class CanvasElementSyncService
{
    /**
     * @param  list<array<string, mixed>>  $canvasElements
     */
    public function sync(Slide $slide, array $canvasElements): void
    {
        $keptClientIds = [];

        foreach (array_values($canvasElements) as $index => $canvasElement) {
            $attributes = Element::attributesFromCanvasElement($canvasElement);

            // The frontend is expected to assign an id before saving, but
            // guard against a missing one rather than silently dropping
            // the element or letting two elements collide on a null
            // client_id (the unique index allows multiple nulls, which
            // would make them impossible to individually update later).
            $clientId = $attributes['client_id'] ?? (string) Str::uuid();
            $keptClientIds[] = $clientId;

            $slide->elements()->updateOrCreate(
                ['client_id' => $clientId],
                [
                    'type' => $attributes['type'],
                    'name' => $attributes['name'],
                    'locked' => $attributes['locked'],
                    'transform' => $attributes['transform'],
                    'style' => $attributes['style'],
                    'content' => $attributes['content'],
                    'sort_order' => $index,
                ],
            );
        }

        // Anything not present in this save was removed on the canvas.
        $slide->elements()
            ->whereNotIn('client_id', $keptClientIds)
            ->delete();
    }
}
