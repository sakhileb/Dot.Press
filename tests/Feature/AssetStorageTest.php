<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_and_fetch_signed_delivery_url(): void
    {
        config()->set('filesystems.asset_upload_disk', 'local');
        Storage::fake('local');

        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'DotPress',
            'slug' => 'dotpress',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/assets', [
                'project_id' => $project->id,
                'file' => UploadedFile::fake()->create('slide.png', 128, 'image/png'),
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'asset' => ['id', 'disk', 'path', 'project_id'],
                'delivery_url',
                'expires_in_seconds',
            ]);

        $assetPath = $response->json('asset.path');
        Storage::disk('local')->assertExists($assetPath);

        $showResponse = $this->actingAs($user)
            ->getJson('/api/assets/'.$response->json('asset.id'));

        $showResponse->assertOk()->assertJsonStructure([
            'asset',
            'delivery_url',
            'expires_in_seconds',
        ]);

        $deliveryUrl = $showResponse->json('delivery_url');
        $path = parse_url($deliveryUrl, PHP_URL_PATH) ?? '';
        $query = parse_url($deliveryUrl, PHP_URL_QUERY);
        $relativeUrl = $path.($query ? '?'.$query : '');

        $downloadResponse = $this->actingAs($user)->get($relativeUrl);

        $downloadResponse->assertOk();
    }

    public function test_user_cannot_access_asset_from_another_users_project(): void
    {
        config()->set('filesystems.asset_upload_disk', 'local');
        Storage::fake('local');

        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'name' => 'Owner Project',
            'slug' => 'owner-project',
        ]);

        $upload = $this->actingAs($owner)
            ->postJson('/api/assets', [
                'project_id' => $project->id,
                'file' => UploadedFile::fake()->create('deck.pdf', 100, 'application/pdf'),
            ]);

        $assetId = $upload->json('asset.id');

        $this->actingAs($intruder)
            ->getJson('/api/assets/'.$assetId)
            ->assertForbidden();
    }
}
