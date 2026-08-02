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
        $projects = \App\Models\Project::where('user_id', auth()->id())
            ->with(['decks' => fn ($query) => $query->withCount('slides')->orderByDesc('updated_at')])
            ->latest('id')
            ->get();

        return Inertia::render('Dashboard', [
            'projects' => $projects,
        ]);
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
