<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CollaborationController extends Controller
{
    public function heartbeat(Request $request, Slide $slide)
    {
        $slide->loadMissing('deck.project');
        $this->authorize('view', $slide);

        $validated = $request->validate([
            'cursor' => ['nullable', 'array'],
            'cursor.x' => ['nullable', 'numeric', 'min:0', 'max:1280'],
            'cursor.y' => ['nullable', 'numeric', 'min:0', 'max:720'],
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['string', 'max:120'],
        ]);

        $user = $request->user();
        $ttl = now()->addSeconds(20);

        Cache::put($this->presenceKey($slide->id, $user->id), [
            'user_id' => $user->id,
            'name' => $user->name,
            'cursor' => $validated['cursor'] ?? null,
            'selected_ids' => $validated['selected_ids'] ?? [],
            'updated_at' => now()->toIso8601String(),
        ], $ttl);

        return response()->json([
            'participants' => $this->collectParticipants($slide->id, $user->id),
        ]);
    }

    public function participants(Request $request, Slide $slide)
    {
        $slide->loadMissing('deck.project');
        $this->authorize('view', $slide);

        return response()->json([
            'participants' => $this->collectParticipants($slide->id, $request->user()->id),
        ]);
    }

    private function collectParticipants(int $slideId, int $viewerId): array
    {
        $keys = Cache::get($this->indexKey($slideId), []);

        $entries = [];
        $activeKeys = [];

        foreach ((array) $keys as $key) {
            $payload = Cache::get($key);

            if (! is_array($payload)) {
                continue;
            }

            $activeKeys[] = $key;

            if ((int) ($payload['user_id'] ?? 0) === $viewerId) {
                continue;
            }

            $entries[] = [
                'user_id' => (int) ($payload['user_id'] ?? 0),
                'name' => (string) ($payload['name'] ?? 'Collaborator'),
                'cursor' => $payload['cursor'] ?? null,
                'selected_ids' => $payload['selected_ids'] ?? [],
                'color' => $this->cursorColor((int) ($payload['user_id'] ?? 0)),
                'updated_at' => $payload['updated_at'] ?? null,
            ];
        }

        Cache::put($this->indexKey($slideId), $activeKeys, now()->addSeconds(25));

        return $entries;
    }

    private function presenceKey(int $slideId, int $userId): string
    {
        $key = "collab:slide:{$slideId}:user:{$userId}";
        $index = Cache::get($this->indexKey($slideId), []);

        if (! in_array($key, $index, true)) {
            $index[] = $key;
            Cache::put($this->indexKey($slideId), $index, now()->addSeconds(25));
        }

        return $key;
    }

    private function indexKey(int $slideId): string
    {
        return "collab:slide:{$slideId}:index";
    }

    private function cursorColor(int $userId): string
    {
        $palette = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899'];

        return $palette[$userId % count($palette)];
    }
}
