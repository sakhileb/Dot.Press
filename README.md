<div align="center">

<img src="public/dot_pres.png" alt="Dot.Press" width="200" />

<h1>Dot.Press</h1>

<p>Canvas-first presentation builder with AI generation, real-time collaboration, and one-click export.</p>

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.x-42B883?style=flat-square&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat-square&logo=postgresql&logoColor=white)](https://postgresql.org)
[![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)](LICENSE)

</div>

---

## Overview

Dot.Press is a fully-featured canvas presentation builder in the Dot ecosystem. Built on a Konva.js canvas engine with TipTap rich-text editing, it enables pixel-perfect slide design, AI-generated content and layouts, real-time collaborative editing, and PDF/PPTX export — all in the browser.

> **Architecture note:** Dot.Press uses Vue 3 + Inertia.js rather than Livewire 3. The Konva.js canvas engine requires direct DOM manipulation and frame-by-frame rendering that is incompatible with Livewire's server-rendered model.

---

## Features

- **Canvas editor** — Konva.js pixel canvas with shapes, images, text, and custom elements
- **Rich text** — TipTap editor for slide text with full formatting support
- **AI generation** — generate slide layouts, copy, and images from a prompt (Anthropic Claude)
- **Real-time collaboration** — live multi-user editing with cursor presence
- **Export** — PDF and PPTX one-click export
- **Templates** — starter template library with customisable themes
- **Ecosystem SSO** — authenticate from InfoDot with a single click

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 + PHP 8.4 |
| Frontend | Vue 3 + Inertia.js + Vite |
| Canvas | Konva.js |
| Rich text | TipTap |
| Auth | Jetstream 5 + Sanctum (ecosystem SSO) |
| Database | PostgreSQL 16 (shared infodot instance) |
| WebSockets | Laravel Reverb |
| AI | Anthropic Claude API |

---

## Quick Start

```bash
git clone https://github.com/sakhileb/Dot.Press.git && cd Dot.Press
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate && npm run dev & php artisan serve
```

```bash
bash bin/test.sh   # Run tests
```

---

## Part of the Dot Ecosystem

Dot.Press connects to [InfoDot](https://github.com/sakhileb/InfoDot) — the central hub. Log in to InfoDot once and navigate here without re-authenticating via `/auth/ecosystem`.

---

MIT — © SK Digital / BluPin Incorporated
