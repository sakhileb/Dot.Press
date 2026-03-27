<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
});
</script>

<template>
    <Head title="Dot.Press — Beautiful Presentations, Powered by AI" />

    <div class="relative min-h-screen overflow-hidden bg-[#0c0f1a] text-white">

        <!-- ── Ambient background ── -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <!-- deep radial glow top-left -->
            <div class="absolute -top-40 -left-40 h-[700px] w-[700px] rounded-full bg-amber-400/10 blur-[120px]"></div>
            <!-- deep radial glow bottom-right -->
            <div class="absolute -bottom-32 -right-32 h-[600px] w-[600px] rounded-full bg-orange-500/10 blur-[120px]"></div>
            <!-- subtle center glow -->
            <div class="absolute top-1/2 left-1/2 h-[400px] w-[900px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-amber-300/5 blur-[100px]"></div>

            <!-- grid overlay -->
            <div
                class="absolute inset-0 opacity-[0.04]"
                style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 60px 60px;"
            ></div>

            <!-- floating slide cards — decorative -->
            <div class="absolute top-28 right-[-60px] w-64 rotate-6 rounded-2xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur-sm lg:right-24 xl:right-40">
                <div class="mb-3 h-2 w-24 rounded-full bg-amber-400/60"></div>
                <div class="mb-2 h-2 w-40 rounded-full bg-white/20"></div>
                <div class="mb-2 h-2 w-32 rounded-full bg-white/10"></div>
                <div class="mt-4 h-20 rounded-lg bg-gradient-to-br from-amber-400/20 to-orange-500/20"></div>
            </div>
            <div class="absolute top-56 right-8 w-52 -rotate-3 rounded-2xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur-sm lg:right-12 xl:right-28">
                <div class="mb-3 h-2 w-16 rounded-full bg-orange-400/60"></div>
                <div class="mt-4 h-16 rounded-lg bg-gradient-to-br from-orange-500/20 to-amber-300/20"></div>
                <div class="mt-3 h-2 w-28 rounded-full bg-white/20"></div>
            </div>
            <div class="absolute bottom-24 left-[-40px] w-56 -rotate-6 rounded-2xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur-sm lg:left-20 xl:left-36">
                <div class="mb-3 h-2 w-20 rounded-full bg-amber-300/60"></div>
                <div class="mb-2 h-2 w-36 rounded-full bg-white/10"></div>
                <div class="mt-4 h-14 rounded-lg bg-gradient-to-br from-amber-400/15 to-orange-400/15"></div>
            </div>
        </div>

        <!-- ── Navigation ── -->
        <nav class="relative z-10 flex items-center justify-between px-6 py-6 sm:px-12 lg:px-20">
            <div class="flex items-center gap-3">
                <img src="/dot_pres.png" alt="Dot.Press logo" class="h-10 w-auto drop-shadow-lg" />
            </div>

            <div v-if="canLogin" class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-xl bg-white/10 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"
                >
                    Dashboard →
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="rounded-xl px-5 py-2.5 text-sm font-medium text-white/80 transition hover:text-white"
                    >
                        Sign in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/30 transition hover:brightness-110"
                    >
                        Get started free
                    </Link>
                </template>
            </div>
        </nav>

        <!-- ── Hero ── -->
        <main class="relative z-10 flex flex-col items-center px-6 pb-24 pt-16 text-center sm:px-12 lg:pt-28">

            <!-- Badge -->
            <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-4 py-1.5 text-xs font-medium text-amber-300 backdrop-blur-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                AI-Powered Presentation Builder
            </div>

            <!-- Headline -->
            <h1 class="mx-auto max-w-4xl text-5xl font-extrabold leading-tight tracking-tight sm:text-6xl lg:text-7xl">
                Slides that
                <span class="bg-gradient-to-r from-amber-300 via-orange-400 to-orange-500 bg-clip-text text-transparent">
                    tell your story
                </span>
                <br />beautifully.
            </h1>

            <p class="mx-auto mt-8 max-w-2xl text-lg leading-relaxed text-white/60">
                Dot.Press is a canvas-first presentation platform with built-in AI generation, real-time collaboration, and one-click PDF&nbsp;&amp;&nbsp;PPTX export. Go from idea to deck in minutes.
            </p>

            <!-- CTAs -->
            <div class="mt-12 flex flex-wrap items-center justify-center gap-4">
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="group relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 px-8 py-4 text-base font-bold text-white shadow-xl shadow-amber-500/30 transition hover:brightness-110 active:scale-95"
                >
                    <span class="relative z-10">Start building for free</span>
                </Link>
                <Link
                    v-if="canLogin && !$page.props.auth.user"
                    :href="route('login')"
                    class="rounded-2xl border border-white/20 bg-white/5 px-8 py-4 text-base font-semibold text-white/90 backdrop-blur-sm transition hover:border-white/40 hover:bg-white/10 active:scale-95"
                >
                    Sign in to your account
                </Link>
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="group relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 px-8 py-4 text-base font-bold text-white shadow-xl shadow-amber-500/30 transition hover:brightness-110 active:scale-95"
                >
                    Go to Dashboard →
                </Link>
            </div>

            <!-- Social proof -->
            <p class="mt-8 text-sm text-white/30">No credit card required &bull; Unlimited decks on free plan</p>

            <!-- ── Feature grid ── -->
            <div class="mx-auto mt-28 grid max-w-5xl gap-5 text-left sm:grid-cols-2 lg:grid-cols-3">

                <div class="group rounded-2xl border border-white/10 bg-white/5 p-7 backdrop-blur-sm transition hover:border-amber-400/30 hover:bg-white/8">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400/30 to-orange-500/20">
                        <!-- canvas icon -->
                        <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </div>
                    <h3 class="mb-2 text-base font-semibold text-white">Canvas Editor</h3>
                    <p class="text-sm leading-relaxed text-white/50">Drag, resize, rotate elements on a pixel-perfect Konva.js stage with snapping, z-index control, and undo/redo.</p>
                </div>

                <div class="group rounded-2xl border border-white/10 bg-white/5 p-7 backdrop-blur-sm transition hover:border-amber-400/30 hover:bg-white/8">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400/30 to-orange-500/20">
                        <!-- AI icon -->
                        <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 8v4l3 3"/><path stroke-linecap="round" d="M18 2v4M20 4h-4"/></svg>
                    </div>
                    <h3 class="mb-2 text-base font-semibold text-white">AI Generation</h3>
                    <p class="text-sm leading-relaxed text-white/50">Describe your slide in plain English. Our Claude-powered engine writes the copy, lays out elements, and styles them instantly.</p>
                </div>

                <div class="group rounded-2xl border border-white/10 bg-white/5 p-7 backdrop-blur-sm transition hover:border-amber-400/30 hover:bg-white/8">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400/30 to-orange-500/20">
                        <!-- collab icon -->
                        <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="9" cy="7" r="3"/><path d="M3 21v-2a4 4 0 0 1 4-4h4"/><circle cx="17" cy="11" r="3"/><path d="M21 21v-2a4 4 0 0 0-4-4h-0"/></svg>
                    </div>
                    <h3 class="mb-2 text-base font-semibold text-white">Real-time Collaboration</h3>
                    <p class="text-sm leading-relaxed text-white/50">See teammates' live cursors, presence indicators, and resolve edit conflicts automatically with optimistic concurrency.</p>
                </div>

                <div class="group rounded-2xl border border-white/10 bg-white/5 p-7 backdrop-blur-sm transition hover:border-amber-400/30 hover:bg-white/8">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400/30 to-orange-500/20">
                        <!-- export icon -->
                        <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M3 17v2a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-2"/></svg>
                    </div>
                    <h3 class="mb-2 text-base font-semibold text-white">One-click Export</h3>
                    <p class="text-sm leading-relaxed text-white/50">Download your deck as a pixel-perfect PDF or a native PowerPoint PPTX file — shareable anywhere, any device.</p>
                </div>

                <div class="group rounded-2xl border border-white/10 bg-white/5 p-7 backdrop-blur-sm transition hover:border-amber-400/30 hover:bg-white/8">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400/30 to-orange-500/20">
                        <!-- rich text icon -->
                        <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 6h16M4 12h10M4 18h12"/></svg>
                    </div>
                    <h3 class="mb-2 text-base font-semibold text-white">Rich Text Engine</h3>
                    <p class="text-sm leading-relaxed text-white/50">Tiptap-powered inline editing with font controls, colors, alignment, lists, and spacing — all without leaving the canvas.</p>
                </div>

                <div class="group rounded-2xl border border-white/10 bg-white/5 p-7 backdrop-blur-sm transition hover:border-amber-400/30 hover:bg-white/8">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400/30 to-orange-500/20">
                        <!-- present icon -->
                        <svg class="h-6 w-6 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 10l4.553-2.07A1 1 0 0 1 21 8.85v6.3a1 1 0 0 1-1.447.92L15 14M3 8a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8Z"/></svg>
                    </div>
                    <h3 class="mb-2 text-base font-semibold text-white">Presentation Mode</h3>
                    <p class="text-sm leading-relaxed text-white/50">Full-screen presenter view with smooth keyboard navigation, Home/End shortcuts, and a single-key fullscreen toggle.</p>
                </div>

            </div>

            <!-- ── Bottom CTA strip ── -->
            <div class="mx-auto mt-24 w-full max-w-3xl rounded-3xl border border-amber-400/20 bg-gradient-to-br from-amber-400/10 via-orange-500/5 to-transparent p-12 text-center backdrop-blur-sm">
                <img src="/dot_pres.png" alt="Dot.Press" class="mx-auto mb-6 h-16 w-auto drop-shadow-xl" />
                <h2 class="text-3xl font-bold text-white">Ready to impress?</h2>
                <p class="mt-3 text-white/50">Join thousands of creators building stunning decks with Dot.Press.</p>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="mt-8 inline-block rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 px-10 py-4 text-base font-bold text-white shadow-xl shadow-amber-500/25 transition hover:brightness-110 active:scale-95"
                >
                    Create your first deck →
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="mt-8 inline-block rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 px-10 py-4 text-base font-bold text-white shadow-xl shadow-amber-500/25 transition hover:brightness-110 active:scale-95"
                >
                    Sign in to get started →
                </Link>
            </div>
        </main>

        <!-- ── Footer ── -->
        <footer class="relative z-10 border-t border-white/5 px-6 py-8 text-center text-sm text-white/25">
            &copy; {{ new Date().getFullYear() }} Dot.Press. All rights reserved.
        </footer>
    </div>
</template>

