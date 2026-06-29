<?php

use App\Http\Controllers\Auth\EcosystemAuthController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SlideEditorController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/auth/ecosystem', [EcosystemAuthController::class, 'handle'])
    ->name('ecosystem.auth');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $userId = auth()->id();
        $totalProjects = \App\Models\Project::where('user_id', $userId)->count();
        $totalDecks = \App\Models\Deck::whereHas('project', fn ($q) => $q->where('user_id', $userId))->count();
        $templateDecks = \App\Models\Deck::where('is_template', true)
            ->whereHas('project', fn ($q) => $q->where('user_id', $userId))->count();
        $totalAssets = \App\Models\Asset::whereHas('project', fn ($q) => $q->where('user_id', $userId))->count();
        $recentProjects = \App\Models\Project::where('user_id', $userId)
            ->withCount('decks')->latest()->limit(8)->get();
        return view('dashboard', compact('totalProjects', 'totalDecks', 'templateDecks', 'totalAssets', 'recentProjects'));
    })->name('dashboard');

    Route::get('/editor', [SlideEditorController::class, 'start'])->name('editor.start');
    Route::get('/editor/decks/{deck}/slides/{slide}', [SlideEditorController::class, 'show'])
        ->name('editor.slides.show');
    Route::get('/present/decks/{deck}/slides/{slide}', [SlideEditorController::class, 'present'])
        ->name('presentation.slides.show');
    Route::get('/export/decks/{deck}/pdf', [ExportController::class, 'pdf'])
        ->middleware('throttle:export')
        ->name('export.decks.pdf');
    Route::get('/export/decks/{deck}/pptx', [ExportController::class, 'pptx'])
        ->middleware('throttle:export')
        ->name('export.decks.pptx');
});
