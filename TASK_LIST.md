# Dot.Press Build Task List

Source: Retrieved from the shared Claude roadmap summary (6 phases, 32 tasks, recommended order 1 -> 2 -> 3 -> 4 -> 5 -> 6).

## Phase 01 - Foundation (6 tasks)
- [x] Initialize Laravel app with Jetstream (Vue stack) and Tailwind CSS.
- [x] Configure app environments (.env), app key, queue/cache drivers, and secrets for local and production.
- [x] Design and run migrations for core schema: users, projects, decks, slides, elements, assets.
- [x] Set up Jetstream auth flows and teams/profile features as needed for Dot.Press.
- [x] Implement project and deck CRUD with Laravel policies and ownership authorization.
- [x] Configure file storage for uploaded assets (local/S3) and signed access where needed.

## Phase 02 - Canvas Engine (8 tasks)
- [x] Choose and integrate canvas library (Fabric.js or Konva.js).
- [x] Implement slide viewport, stage, and coordinate system.
- [x] Add element creation for text, shape, image, and group containers.
- [x] Implement drag, resize, rotate, and snapping/alignment guides.
- [x] Add selection model: single, multi-select, marquee select, group select.
- [x] Build z-index controls (bring forward, send backward, layer ordering).
- [x] Implement undo/redo history with command-based state updates.
- [x] Persist and hydrate canvas state per slide from the database.

## Phase 03 - Rich Text (5 tasks)
- [x] Integrate Tiptap editor for text elements inside canvas boxes.
- [x] Add inline formatting toolbar (font, size, color, bold, italic, underline).
- [x] Support lists, alignment, indentation, and line spacing controls.
- [x] Implement text box edit mode with clean enter/escape interactions.
- [x] Persist rich text JSON and render accurately in edit and presentation modes.

## Phase 04 - Media and Shapes (5 tasks)
- [x] Add image upload, crop/fit options, and replace-image workflow.
- [x] Add shape library (rectangles, circles, lines, arrows, polygons).
- [x] Integrate icon picker/library and style controls.
- [x] Support video embeds with preview, sizing, and playback settings.
- [x] Add reusable style presets for fills, borders, shadows, and opacity.

## Phase 05 - AI Features (4 tasks)
- [x] Build AI slide generator flow using Claude API from a prompt. (Ask AI)
- [x] Implement AI text rewrite actions (shorten, expand, rephrase, tone). (Ask AI)
- [x] Add AI content safety, prompt logging, and error handling.
- [x] Add AI usage metering, rate limits, and user-facing loading states.

## Phase 06 - Collaboration and Export (4 tasks)
- [x] Integrate real-time collaboration (for example Liveblocks presence/cursors).
- [x] Add conflict-safe multiplayer editing for slide and element updates.
- [x] Implement export pipeline for PDF and PPTX outputs.
- [x] Build presentation mode with keyboard navigation and fullscreen controls.

## Recommended Build Order
1. Complete Phase 01.
2. Complete Phase 02.
3. Complete Phase 03.
4. Complete Phase 04.
5. Complete Phase 05 (can start after Phase 03 in parallel).
6. Complete Phase 06.

## Milestone Checkpoints
- [x] M1: Foundation complete (auth + CRUD + storage)
- [x] M2: Canvas core usable (edit + transform + persist)
- [x] M3: Rich text polished
- [x] M4: Media and shapes complete
- [x] M5: AI v1 shipped
- [x] M6: Collaboration and export shipped
