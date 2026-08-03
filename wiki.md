---
title: Dot.Press — Platform Wiki
version: 0.3.1
status: draft
owners: [Press Platform Lead]
platform-id: dot-press
last-review: 2026-08-03
---

# Dot.Press

Purpose: this is Dot.Press's own knowledge home — owned and maintained by the Dot.Press team. It describes what this platform actually is, as implemented, and how it connects to the wider Dot Ecosystem. Dot.Brain never edits this file; it only reads what we choose to publish.

> **Related:** [Dot.Brain's ingested view of this platform](https://github.com/sakhilebhayi/Dot.Brain/blob/main/platforms/dot-press.md)

---

## 1. What Dot.Press Is

Dot.Press is a **presentation/slide-deck design tool** — the closest analogue is Canva's or Google Slides' presentation editor, not a newsroom or CMS. Teams create projects, build decks of canvas-based slides (text, shapes, images), optionally generate a slide from an AI prompt, collaborate on a slide in near-real-time via presence heartbeats, and export a deck to PDF or PPTX.

This is a naming correction worth stating plainly: `InfoDot/config/ecosystem.php` registers Dot.Press with the icon `newspaper`, which reads as "press" in the news/publishing sense. The actual codebase — models, migrations, routes, the Konva-based canvas editor, the `Deck`/`Slide`/`Element` domain — is unambiguously a slide-authoring tool ("press" as in "PowerPoint-adjacent", not "the press"). There is no article, story, byline, or publication concept anywhere in the schema. Anyone integrating against Dot.Press from the ecosystem side should expect a presentations product, not editorial/CMS functionality.

**Status: further along than a skeleton, but with one broken core route found and fixed this pass, one stale README, and a dead/incompatible layout file removed.** Real models, migrations, five Policies, a full CRUD API surface (projects/decks/slides/assets), an AI generation service with an honest mock fallback, a canvas editor (Vue + Konva + Tiptap), lightweight cache-based collaboration presence, and PDF/PPTX export all exist and are wired end-to-end with authorization checks. A real, fairly extensive `tests/Feature` suite exists (24 feature test files) covering auth, teams, AI, assets, export, and the canvas API — though per this environment's constraints (see §9), none of it has been executed here. The `/dashboard` route, however, was broken as of the most recent commits before this pass (see §4) — this was Dot.Press's actual "home page" for a logged-in user, and it would have thrown a fatal "view not found" error in production.

## 2. Architecture

| Layer | Technology | Notes |
|---|---|---|
| Framework | Laravel 13, PHP 8.3+ | Standard app skeleton (Jetstream + Fortify for auth/teams) |
| UI | **Inertia.js + Vue 3** — not Livewire | `config/jetstream.php` sets `'stack' => 'inertia'`; `livewire/livewire` is absent from `composer.json` and `composer.lock`. This is a real, deliberate difference from sibling platforms that use the Livewire stack — see §4 for a bug this caused. |
| Canvas | Konva.js (`konva`, `vue-konva`) | Slide viewport, elements, drag/resize/transform |
| Rich text | Tiptap 3 (`@tiptap/vue-3`, starter-kit, text-align, underline, color) | In-canvas text editing |
| Database | PostgreSQL (assumed 16, matching ecosystem convention) | Shared instance across the ecosystem — `DB_DATABASE=infodot` confirmed in `.env.example` |
| Auth | Laravel Sanctum + `App\Http\Controllers\Auth\EcosystemAuthController` | SSO handoff from the InfoDot hub (`/auth/ecosystem`) — matches the ecosystem contract exactly (see §5) |
| AI | Anthropic Claude via `App\Services\Ai\ContentGenerator`, gated by `App\Services\Ai\SafetyGuard` | `AI_PROVIDER` defaults to `mock` in `.env.example` — an honest, working mock path exists for slide generation and text rewrite when no live key is present, matching the ecosystem convention of not presenting fake AI output as real |
| Export | `barryvdh/laravel-dompdf` (PDF), `phpoffice/phppresentation` (PPTX) | Both wired to real controller actions, both authorization-checked, both rate-limited (`throttle:export`, 10/min) |
| Realtime collaboration | **Not Reverb/Liveblocks** — a cache-based presence heartbeat | `CollaborationController` stores per-user cursor/selection state in the cache with a 20s TTL and polls it back; this is "who's viewing this slide right now," not live multiplayer canvas sync. `TASK_LIST.md`'s claim of "real-time collaboration (Liveblocks presence/cursors)" and "conflict-safe multiplayer editing" overstates what exists — see §4. |

There is no team-based data scoping in the actual domain: `Project`, `Deck`, `Slide`, `Asset`, and their Policies all scope by `user_id` (`$project->user_id === $user->id`), not by Jetstream `Team`. Jetstream teams exist (auth/teams scaffolding is untouched, standard) but the presentation domain itself is single-owner, not team-shared. Note this if planning team-collaboration features — the schema would need a `team_id` or a sharing/membership table added, neither of which exists today.

## 3. Domain Entities (as implemented)

Source: `database/migrations/2026_03_27_181854_create_projects_table.php` through `..._create_slides_table.php`, and `app/Models/`.

| Model | Table | Purpose |
|---|---|---|
| `Project` | `projects` | Top-level container owned by a `user_id`; holds `settings` (JSON) |
| `Deck` | `decks` | A presentation within a project — `title`, `theme` (JSON), `sort_order`, `is_template` |
| `Slide` | `slides` | One canvas within a deck — `layout`, `sort_order`, `canvas_state` (JSON: elements + viewport + meta), `revision` (optimistic-concurrency counter, see §4) |
| `Element` | `elements` | A positioned canvas object — `type`, `content`/`style`/`transform` (all JSON), `sort_order`, `locked`. Note: in practice, slide content lives inline inside `Slide.canvas_state.elements` (a JSON blob), not as normalized `Element` rows — the `Element` model and its migration exist and are wired via `Slide::elements()`, but no controller in this codebase reads or writes through it. This looks like an intended normalization that the canvas API bypassed in favor of storing the whole canvas as one JSON document per slide. |
| `Asset` | `assets` | An uploaded file (image/video/audio/PDF) scoped to a project, with signed-URL delivery |
| `AiUsageLog` | `ai_usage_logs` | Every AI call (slide-generate or text-rewrite) — prompt/response (truncated), token counts, latency, safety-block status |

All five domain models (`Project`, `Deck`, `Slide`, `Asset`, plus `Team` from Jetstream) have a matching Policy in `app/Policies/`, and every controller action in `app/Http/Controllers/Api/` calls `$this->authorize(...)` before touching a record — this was verified read-through, not assumed (§7).

## 4. What Was Actually Broken, and What Was Fixed This Pass

Found by reading the code, not by running it (see §9 on environment constraints):

- **`/dashboard` route was fatal.** `routes/web.php` called `return view('dashboard', ...)` with a hand-computed set of stats (`totalProjects`, `totalDecks`, etc.), but no `resources/views/dashboard.blade.php` exists anywhere in the repo — only an Inertia page at `resources/js/Pages/Dashboard.vue`, which expects a `projects` prop shaped as `[{ id, name, description, decks: [{ id, title, slides_count, is_template, updated_at }] }]`. Hitting `/dashboard` as any authenticated user would have thrown Laravel's "View [dashboard] not found" error. No existing test caught this — the auth/registration tests only assert a redirect *to* `route('dashboard')`, none of them actually GET the page. **Fixed:** the route now does `Inertia::render('Dashboard', ['projects' => ...])` with the correct eager-loaded shape (`decks` with `withCount('slides')`), matching what `Dashboard.vue` already expects. This is the single largest functional fix in this pass — it restores the app's actual home page for a logged-in user.
- **`resources/views/layouts/app.blade.php` was dead, incompatible code — removed.** Introduced in the most recent commit ("feat: add Dot OS app layout"), this file used `@livewireStyles`, `@livewireScripts`, `@livewire('navigation-menu')`, and `<x-banner />` — all part of Jetstream's *Livewire* stack. This app runs the *Inertia* stack (`config/jetstream.php`: `'stack' => 'inertia'`) and has no `livewire/livewire` dependency at all, so none of those directives/components exist here. The file was not referenced by any route, controller, or `@extends` anywhere in the codebase (grepped and confirmed), so nothing else in `app/` or `resources/` was touched removing it. It read like a template copy-pasted from a Livewire-stack sibling platform without adapting it to Dot.Press's actual stack. The real, working Blade layer for this app is `resources/views/app.blade.php` (the Inertia root view) plus `resources/js/Layouts/AppLayout.vue` (the Vue nav/shell) — both already correctly reference `/dot_pres.png` as the logo.
- **`TASK_LIST.md` overstates Phase 06 (Collaboration).** It marks "Integrate real-time collaboration (for example Liveblocks presence/cursors)" and "conflict-safe multiplayer editing for slide and element updates" both `[x]` complete. What exists is `CollaborationController`: a cache-backed heartbeat (`POST /api/collab/slides/{slide}/heartbeat`) that stores a user's cursor position and selection for 20 seconds, plus a `GET .../participants` poll — genuinely useful "who else is here" presence, but not Liveblocks, not a websocket, and not live cursor broadcast. Conflict safety on slide edits is real but is optimistic-locking, not multiplayer merge: `SlideController::update` checks a client-supplied `expected_revision` against `Slide.revision` and returns `409` with the server's current state on mismatch — solid last-write-wins-with-conflict-detection, not simultaneous multi-user editing. `TASK_LIST.md` should be read as a rough completion checklist against a *different, more ambitious* original spec, not as a precise description of shipped behavior.
- **`jsconfig.json`** is a real, working Vue path-alias config (`@/* → resources/js/*`) for editor tooling — nothing unusual or misleading here, unlike `TASK_LIST.md`.

## 5. SSO Contract Verification

`app/Http/Controllers/Auth/EcosystemAuthController.php` matches the ecosystem-wide pattern exactly:

```php
$accessToken = PersonalAccessToken::findToken($request->query('token'));
abort_if(! $accessToken || ! $accessToken->can('ecosystem:read') || ($accessToken->expires_at && $accessToken->expires_at->isPast()), 403);
$user = $accessToken->tokenable;
$accessToken->delete();
Auth::login($user);
return redirect()->route('dashboard');
```

Token lookup, scope check (`ecosystem:read`), expiry check, one-time consumption (`delete()` before login), then redirect to `dashboard` — consistent with the contract used elsewhere in the ecosystem. Route is registered at `GET /auth/ecosystem` → `ecosystem.auth`, outside the authenticated middleware group, as expected for an SSO landing endpoint.

`config/database.php` defaults `DB_DATABASE` to `env('DB_DATABASE', 'laravel')` (the Laravel skeleton default — unremarkable, every connection array in this file has that fallback), but `.env.example` explicitly sets `DB_DATABASE=infodot`, matching the shared ecosystem database convention. `DB_CONNECTION=pgsql` is also set correctly in `.env.example`. This is consistent with how `Dot.Billing` and other siblings are configured.

The git log entry "fix: update routes/web.php imports and dashboard query" (commit `96c1e02`) is misleading in isolation — it did fix the imports, but the "dashboard query" it introduced still passed data to a nonexistent Blade view, so the route remained broken until this pass. The commit message describes intent, not verified outcome; see §9 on why that gap exists in this environment.

## 6. Events Emitted

**None.** There are no Laravel event/listener classes and no outbound webhook/message-bus code anywhere in `app/`. Nothing here should be read as implying Dot.Press currently publishes Knowledge Packs or ecosystem events — it doesn't. If/when event emission is added, natural candidates based on the existing domain are: deck created, deck exported (PDF/PPTX), AI slide generated, AI quota exhausted.

## 7. Security Scan (this pass)

Scope, per the Engineering Loop: one focused pass, authorization gaps and obvious tech debt only.

**Result: no IDOR findings.** Every controller in `app/Http/Controllers/Api/` (`ProjectController`, `DeckController`, `SlideController`, `AssetController`, `AiController`, `CollaborationController`) and the two web controllers that take a by-ID model (`SlideEditorController`, `ExportController`) call `$this->authorize(...)` against a Policy before reading or mutating the record — including on nested lookups (e.g. `DeckController::store` validates `project_id` then explicitly re-authorizes `view` on the resolved `Project`, rather than trusting the ID alone). All five Policies (`ProjectPolicy`, `DeckPolicy`, `SlidePolicy`, `AssetPolicy`, plus Jetstream's `TeamPolicy`) consistently scope by the real ownership chain (`$model->project->user_id === $user->id` or equivalent), not by trusting a route-bound model in isolation. This was read through file-by-file, not sampled. The asset-download route additionally requires a Laravel `signed` URL on top of the Policy check. Rate limiters exist and are applied for `ai`, `export`, and `collab` endpoints (`app/Providers/AppServiceProvider.php`).

The one functional bug found (§4, the dead-view dashboard route) is not a security issue — it's an availability bug — but is reported here since it was the actual finding of this pass's read-through.

**Given the domain is genuinely single-owner (`user_id`-scoped, no sharing/team model on decks yet), there was no cross-tenant surface to find beyond what's checked above** — there's no "another team member" or "another user with edit rights" concept in this schema at all, so the usual team-membership-boundary bugs found in sibling platforms don't have an equivalent here yet. Worth flagging as a design gap, not a vulnerability: if Dot.Press later adds deck sharing/collaboration beyond presence (a `deck_collaborators` table, say), the Policies above will need to grow beyond a single `user_id` check, and that would be the moment for a dedicated authorization pass.

## 8. Branding

`dot_pres.png` (2362×2362 PNG, repo root and `public/dot_pres.png`) was already wired into the working Inertia frontend before this pass: `resources/views/app.blade.php` used it as `<link rel="icon">`, and `resources/js/Layouts/AppLayout.vue` used it as the nav-bar logo (`<img src="/dot_pres.png" ...>`). This pass added the sized favicon variants the ecosystem convention expects — `public/apple-touch-icon.png` (180×180), `public/favicon-32x32.png`, `public/favicon-16x16.png`, generated via `sips` from `dot_pres.png` — wired into `app.blade.php`'s `<head>`, plus `public/images/logo.png` as the canonical ecosystem-convention copy. `public/favicon.ico` was and remains a 0-byte empty file; it is superseded by the PNG favicon links, not fixed directly (regenerating a real multi-resolution `.ico` from a PNG is outside what `sips` does cleanly).

The dead `resources/views/layouts/app.blade.php` removed in §4 also referenced a `brand-icon` with a `newspaper` Material Symbol and the tagline "Publishing Platform" — reinforcing the naming-mismatch note in §1. Its removal means that specific mislabeling is gone from the codebase, not just from this document.

## 9. Environment Constraints on This Pass

No PHP, Composer, PostgreSQL, or Docker were available in the environment this pass ran in. Every finding above was reached by reading source files directly — models, migrations, controllers, routes, Policies, config, `composer.json`/`composer.lock`, and the Vue components those routes render into. Nothing was run, migrated, or tested. The dashboard-route fix and the dead-layout removal are believed correct based on that read-through (the fix's prop shape was checked line-by-line against what `Dashboard.vue` destructures), but neither has been executed. A genuine PHP + PostgreSQL + `php artisan serve` environment (or CI) is a mandatory gate before this is production-verified — see [Dot.Brain 02-Engineering-Loop.md](../Dot.Brain/os/02-Engineering-Loop.md) §2.

## 10. Connecting to Dot.Brain

Dot.Press is registered in the InfoDot ecosystem map (`InfoDot/config/ecosystem.php`) as `press` → `Dot.Press`, icon `newspaper` (see §1 for why that icon undersells/mislabels what this platform does). Dot.Brain's ingested view of Dot.Press is maintained at [`platforms/dot-press.md`](https://github.com/sakhilebhayi/Dot.Brain/blob/main/platforms/dot-press.md). As of this pass, Dot.Press has no event emission and no Knowledge Pack publishing code (§6) — any ingested view of this platform that describes live event flow or aggregated presentation metrics is describing a target state, not current behavior, the same gap documented in Dot.Billing's wiki.md §7.

## 11. Roadmap / Open Questions

- [ ] Fix `public/favicon.ico` properly (currently 0 bytes) — either regenerate a real multi-resolution `.ico` with a tool beyond `sips`, or drop the `.ico` reference entirely now that PNG favicon links are wired in `app.blade.php`.
- [ ] Decide whether `Element` (the normalized per-shape table) should actually be used by the canvas API, or whether it should be removed/documented as superseded by the JSON-blob-per-slide approach that `SlideController`/`AiController` actually use today.
- [ ] Rewrite `TASK_LIST.md`'s Phase 06 checkmarks to reflect what's actually implemented (cache-presence + optimistic-lock conflict detection), not the original "Liveblocks/multiplayer" spec — or explicitly annotate the gap inline rather than leaving it to be discovered by reading code.
- [ ] No sharing/team model exists on the presentation domain (`Project`/`Deck`/`Slide` are single-`user_id`-owned) — if team-based collaboration is a real product goal (Jetstream Teams are already present for auth), this needs a deliberate schema + Policy design pass, not an incremental patch.
- [ ] No event emission or Knowledge Pack publishing exists yet — prerequisite for any real Dot.Brain integration beyond static registration.
- [ ] Verify in a real environment: the `Inertia::render('Dashboard', ...)` fix in this pass, and that `Dashboard.vue`'s prop destructuring matches exactly (see §9 — read-verified only, not executed).
- [ ] `public/favicon.ico` and the new PNG favicons were generated via `sips` outside any build step — confirm this matches how sibling platforms handle static asset generation (manual, not npm/vite-driven).

## Change Log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.3.1 | 2026-08-03 | Sakhile Bhayi | Fixed the README's logo: it referenced `docs/logo.svg`, an auto-generated generic placeholder badge (dark card + platform name text), not this platform's real designed logo. Swapped to the real PNG logo already used elsewhere in this repo, and removed the now-dead `docs/logo.svg` placeholder file entirely rather than leaving it alongside the real asset. |
| 0.3.0 | 2026-08-03 | Sakhile Bhayi | Real-execution verification pass — this platform (Vue/Inertia frontend, plain Laravel backend) was actually installed and run against real PHP/PostgreSQL for the first time (composer install ran clean under the default PHP 8.5, no downgrade needed). `php artisan migrate` failed on the first real run with a genuine ordering bug: `create_assets_table`, `create_decks_table`, `create_elements_table`, and `create_slides_table` all shared an identical timestamp, and alphabetical tie-breaking ran `create_elements_table` (which foreign-keys to `slides`) before the `slides` table existed. Fixed by retimestamping `create_decks_table`/`create_slides_table`/`create_elements_table` to run in true dependency order (decks, then slides, then elements — decks and assets are independent of each other and of slides). After the fix, `php artisan migrate` ran clean and `php artisan test` passed 52/56 (4 skipped, gated by config, not real failures). Applied the Dot.Brain adr/ADR-0013 idempotent guard to this platform's six shared Jetstream-core migrations (users/password_reset_tokens/sessions, two-factor columns, personal_access_tokens, teams, team_user, team_invitations), each now wrapped in `Schema::hasTable`/`hasColumn` checks so this platform's copies are safe to co-execute with any other Dot platform against the same shared `infodot` database. Re-verified against a fresh, empty database after guarding: migrate clean, same 52 passed / 4 skipped / 0 failed test result, confirming the guard changes no observable behavior. |
| 0.2.0 | 2026-08-03 | Sakhile Bhayi | Redesigned the marketing surface's background in `resources/js/Pages/Welcome.vue` (this app is Inertia + Vue 3, not Blade, so there is no `welcome.blade.php` — the landing page lives here). The real `dot_pres.png` logo was already wired into the nav and bottom CTA strip from a prior pass, but the hero relied entirely on abstract radial-gradient glow blobs and decorative floating-card mockups with no photographic content. Added a real, licensed Unsplash photo of a creative workspace (MacBook Pro + iMac desk setup) by Domenico Loia (@domenicoloia), unsplash.com/photos/macbook-pro-on-table-beside-white-imac-and-magic-mouse-hGV2TfOh0ns — fitting Dot.Press's actual domain, a canvas-first AI slide-deck design tool (confirmed against wiki.md, not the newspaper/publishing framing corrected in 0.1.0). Hotlinked via Unsplash's CDN, credited inline as an HTML comment, placed under the existing ambient-glow layer at low opacity with a dark gradient overlay so the amber/orange brand gradient and all foreground text keep their existing contrast. Verified the CDN URL resolves (`curl -sI` returned `HTTP/2 200`) before committing. |
| 0.1.0 | 2026-08-02 | Press Platform Lead | Initial platform-owned wiki. Ecosystem integration pass: verified SSO contract (matches convention) and `DB_DATABASE=infodot`; found and fixed a fatal broken `/dashboard` route (referenced a nonexistent Blade view — restored as a correct `Inertia::render('Dashboard', ...)` call matching the existing `Dashboard.vue` page); removed a dead, Livewire-stack-incompatible `resources/views/layouts/app.blade.php` introduced in the prior commit (this app runs Inertia + Vue, has no `livewire/livewire` dependency); wired sized favicons (`apple-touch-icon.png`, `favicon-32x32.png`, `favicon-16x16.png`) generated via `sips` from the existing `dot_pres.png`, plus `public/images/logo.png`; ran a full security read-through of every by-ID controller action and Policy — found no IDOR gaps, all five domain Policies consistently scope by real ownership; corrected the ecosystem-registry "newspaper"/publishing framing against the actual presentation/slide-deck domain; flagged `TASK_LIST.md`'s Phase 06 collaboration checkmarks as overstating what's actually implemented (cache-based presence + optimistic-lock conflict detection, not live multiplayer). |

## Open Questions
- Is the `newspaper` icon / "press" naming in `InfoDot/config/ecosystem.php` intentional (e.g. a future pivot toward publishing/newsroom features is planned) or simply a holdover from the platform's name before its actual product direction (presentations) was decided? This wiki assumes the latter based on the code, but only the platform owner can confirm intent.
- Should `Element` be wired into the canvas API as originally likely intended, or formally deprecated in favor of the JSON-blob-per-slide model that's actually in use?
