<?php

use App\Http\Controllers\Auth\EcosystemAuthController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SlideEditorController;
use App\Models\Project;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Jetstream\Jetstream;

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

// Cookie Policy — Jetstream's termsAndPrivacyPolicy feature covers terms.show/policy.show
// natively (registered at /terms-of-service and /privacy-policy, rendering the Inertia
// PrivacyPolicy/TermsOfService pages from resources/markdown/{terms,policy}.md). There's no
// Jetstream equivalent for a Cookie Policy, so this one is wired by hand, following the exact
// same Markdown-source + Inertia::render convention.
Route::get('/cookies', function () {
    return Inertia::render('CookiePolicy', [
        'cookies' => Str::markdown(file_get_contents(Jetstream::localizedMarkdownPath('cookies.md'))),
    ]);
})->name('cookies');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $projects = Project::where('user_id', auth()->id())
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
