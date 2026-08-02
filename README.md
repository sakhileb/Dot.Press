<div align="center">

<img src="docs/logo.svg" alt="Dot.Press" width="320" />

<br /><br />

**Create stunning presentations with AI generation and real-time collaboration.**

<br />

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white) ![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white) ![Inertia](https://img.shields.io/badge/Inertia.js%20%2B%20Vue%203-6E4AFF?style=flat-square) ![PostgreSQL](https://img.shields.io/badge/PostgreSQL-336791?style=flat-square&logo=postgresql&logoColor=white)

<br /><br />

**Part of the [InfoDot Ecosystem](https://github.com/sakhileb/InfoDot)** &nbsp;·&nbsp; `press.infodot.app`

</div>

---

## What is Dot.Press?

Dot.Press is a presentation / slide-deck design tool in the InfoDot ecosystem — think Canva's or Google Slides' deck editor, not a newsroom or CMS (despite the ecosystem registry's `newspaper` icon — see `wiki.md` §1 for that naming note). A canvas-first editor (Konva.js) lets a user build slide decks with drag/resize/rotate elements and Tiptap-powered rich text; an AI layer (Anthropic Claude, with an honest mock fallback when no API key is configured) can generate a slide from a prompt or rewrite selected text; lightweight cache-based presence shows who else has a slide open; and decks export to PDF or PPTX.

For a full, code-verified account of what's actually implemented versus aspirational — including a functional bug found and fixed in the dashboard route, and a naming/scope correction on "real-time collaboration" — see [`wiki.md`](wiki.md).

## Core Features

- Canvas editor (Konva.js) — drag, resize, rotate text/shape/image elements with an optimistic-concurrency revision check on save
- AI slide generation and text rewrite (shorten/expand/rephrase/tone), rate-limited and safety-checked, with a working mock provider when no live API key is set
- Lightweight collaboration presence — see who else is viewing a slide and their cursor/selection (cache-backed heartbeat, not a websocket/live-cursor broadcast)
- One-click export to PDF (dompdf) and PPTX (PhpPresentation)
- Ecosystem SSO from the InfoDot hub via `EcosystemAuthController`

## Domain Models

Source of truth: `app/Models/` and `database/migrations/`.

- **Project** — top-level container, owned by a single user
- **Deck** — a presentation within a project (title, theme, template flag)
- **Slide** — one canvas within a deck; canvas content (elements + viewport) is stored as JSON on the slide, with a `revision` counter for conflict detection
- **Element** — a normalized per-shape table exists and is wired to `Slide`, but the canvas API currently stores all slide content as JSON on `Slide.canvas_state` rather than reading/writing through `Element` rows — see `wiki.md` §3
- **Asset** — an uploaded file (image/video/audio/PDF), scoped to a project, delivered via signed URL
- **AiUsageLog** — every AI call, with prompt/response (truncated), tokens, latency, and safety-block status

There is no team-based sharing on the presentation domain yet — `Project`/`Deck`/`Slide`/`Asset` are all scoped by a single `user_id`, not by Jetstream `Team`. Jetstream Teams exist for auth but nothing in the presentation domain is team-shared today.

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.3+ |
| Frontend | **Inertia.js + Vue 3** (not Livewire — `config/jetstream.php` sets the Inertia stack, and `livewire/livewire` is not a dependency of this app) |
| Canvas | Konva.js / vue-konva |
| Rich text | Tiptap 3 |
| Database | PostgreSQL (shared across the ecosystem, `DB_DATABASE=infodot`) |
| Collaboration presence | Laravel's cache driver (heartbeat + TTL), not a websocket/broadcast service |
| Auth | Laravel Sanctum + Jetstream (InfoDot SSO via `/auth/ecosystem`) |
| AI | Anthropic Claude, direct integration with an `AI_PROVIDER=mock` fallback (`.env.example` default) |
| Export | `barryvdh/laravel-dompdf` (PDF), `phpoffice/phppresentation` (PPTX) |
| Storage | Local disk by default (`ASSET_UPLOAD_DISK`), S3-compatible config present but unused in this repo |

## Quick Start

```bash
git clone https://github.com/sakhileb/Dot.Press.git
cd Dot.Press
cp .env.example .env
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate
php artisan serve
```

> **Ecosystem SSO:** Set `DB_*` env vars to the shared InfoDot PostgreSQL instance and `APP_URL=https://press.infodot.app`. Users authenticated through InfoDot gain access automatically via Sanctum handoff tokens.

## Ecosystem

**Dot.Press** is one of **21 platforms** in the InfoDot ecosystem, connected via shared PostgreSQL and Sanctum SSO. Visit [InfoDot](https://github.com/sakhileb/InfoDot) to explore the full platform map.

## License

MIT © [SK Digital / BluPin Incorporated](https://github.com/sakhileb)
