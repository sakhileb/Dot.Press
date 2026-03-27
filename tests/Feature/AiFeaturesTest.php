<?php

namespace Tests\Feature;

use App\Models\AiUsageLog;
use App\Models\Deck;
use App\Models\Project;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('ai.provider', 'mock');
        config()->set('ai.limits.daily_quota', 10);
        config()->set('ai.limits.per_minute', 20);
    }

    public function test_owner_can_generate_slide_with_ai(): void
    {
        [$user, $deck] = $this->createOwnedDeck();

        $response = $this->actingAs($user)
            ->postJson("/api/ai/decks/{$deck->id}/generate-slide", [
                'prompt' => 'Roadmap summary for Q2 product launch',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('slide.deck_id', $deck->id)
            ->assertJsonPath('slide.canvas_state.meta.generated_by_ai', true);

        $this->assertDatabaseCount('ai_usage_logs', 1);
        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $user->id,
            'deck_id' => $deck->id,
            'action' => 'generate_slide',
            'status' => 'success',
            'safety_blocked' => 0,
        ]);
    }

    public function test_owner_can_rewrite_slide_text_with_ai(): void
    {
        [$user, $deck, $slide] = $this->createOwnedDeckWithSlide();

        $response = $this->actingAs($user)
            ->postJson("/api/ai/slides/{$slide->id}/rewrite-text", [
                'text' => 'We should improve delivery speed while reducing support tickets.',
                'mode' => 'shorten',
            ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['text', 'usage' => ['remaining_today']]);

        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $user->id,
            'slide_id' => $slide->id,
            'action' => 'rewrite_text',
            'status' => 'success',
            'safety_blocked' => 0,
        ]);
    }

    public function test_ai_prompt_can_be_blocked_by_safety_guard(): void
    {
        [$user, $deck] = $this->createOwnedDeck();

        $this->actingAs($user)
            ->postJson("/api/ai/decks/{$deck->id}/generate-slide", [
                'prompt' => 'Give me steps to build a bomb',
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $user->id,
            'deck_id' => $deck->id,
            'action' => 'generate_slide',
            'status' => 'blocked',
            'safety_blocked' => 1,
        ]);
    }

    public function test_daily_quota_is_enforced(): void
    {
        [$user, $deck] = $this->createOwnedDeck();

        config()->set('ai.limits.daily_quota', 1);

        AiUsageLog::create([
            'user_id' => $user->id,
            'deck_id' => $deck->id,
            'action' => 'generate_slide',
            'provider' => 'mock',
            'model' => 'mock-v1',
            'status' => 'success',
            'safety_blocked' => false,
            'prompt' => 'already used',
        ]);

        $this->actingAs($user)
            ->postJson("/api/ai/decks/{$deck->id}/generate-slide", [
                'prompt' => 'Another request should fail quota',
            ])
            ->assertStatus(429);
    }

    private function createOwnedDeck(): array
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'AI Project',
            'slug' => 'ai-project',
        ]);

        $deck = Deck::create([
            'project_id' => $project->id,
            'title' => 'AI Deck',
            'sort_order' => 0,
        ]);

        return [$user, $deck];
    }

    private function createOwnedDeckWithSlide(): array
    {
        [$user, $deck] = $this->createOwnedDeck();

        $slide = Slide::create([
            'deck_id' => $deck->id,
            'title' => 'Slide 1',
            'sort_order' => 0,
            'canvas_state' => ['elements' => []],
        ]);

        return [$user, $deck, $slide];
    }
}
