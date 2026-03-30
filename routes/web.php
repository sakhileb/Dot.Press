<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SlideEditorController;
use Inertia\Inertia;

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
        $projects = request()->user()
            ->projects()
            ->with(['decks' => fn ($q) => $q->withCount('slides')->orderBy('sort_order')])
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
