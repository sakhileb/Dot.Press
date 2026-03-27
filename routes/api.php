<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\CollaborationController;
use App\Http\Controllers\Api\DeckController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SlideController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('decks', DeckController::class);
    Route::apiResource('slides', SlideController::class);
    Route::apiResource('assets', AssetController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::get('assets/{asset}/download', [AssetController::class, 'download'])
        ->middleware('signed')
        ->name('assets.download');

    Route::prefix('ai')->middleware('throttle:ai')->group(function (): void {
        Route::get('usage', [AiController::class, 'usage']);
        Route::post('decks/{deck}/generate-slide', [AiController::class, 'generateSlide']);
        Route::post('slides/{slide}/rewrite-text', [AiController::class, 'rewriteText']);
    });

    Route::prefix('collab')->middleware('throttle:collab')->group(function (): void {
        Route::post('slides/{slide}/heartbeat', [CollaborationController::class, 'heartbeat']);
        Route::get('slides/{slide}/participants', [CollaborationController::class, 'participants']);
    });
});
