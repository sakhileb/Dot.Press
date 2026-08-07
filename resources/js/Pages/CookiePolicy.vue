<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';

defineProps({
    cookies: String,
});
</script>

<template>
    <Head title="Cookie Policy — Dot.Press">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700&family=Roboto+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    </Head>

    <div class="press-terms font-sans antialiased">
        <div class="min-h-screen flex flex-col items-center px-5 py-12">
            <div class="mb-8">
                <AuthenticationCardLogo />
            </div>

            <div class="press-terms-card w-full sm:max-w-2xl p-6 sm:p-8 rounded-2xl shadow-xl prose" v-html="cookies" />
        </div>
    </div>
</template>

<style>
/* Same brand palette as Components/AuthenticationCard.vue's .press-auth block — duplicated here since this
   page doesn't render AuthenticationCard itself. Only ever used by this guest-facing cookie policy page. */
.press-terms {
    --ink: #180f08;
    --ink-soft: #201409;
    --gold: #f0c33a;
    --gold-soft: #f5d573;
    --paper: #f5efe4;
    --mist: #b8a692;
    --line: rgba(245, 239, 228, 0.14);
    --font-body: 'Public Sans', system-ui, sans-serif;
    background: var(--ink);
    color: var(--paper);
    font-family: var(--font-body);
}
.press-terms-card { background: var(--ink-soft); border: 1px solid var(--line); color: var(--paper); }
.press-terms-card h1,
.press-terms-card h2,
.press-terms-card h3 { color: var(--paper); font-family: 'Fraunces', Georgia, serif; }
.press-terms-card p,
.press-terms-card li { color: var(--mist); }
.press-terms-card a { color: var(--gold); }
.press-terms-card a:hover { color: var(--gold-soft); }
.press-terms-card strong { color: var(--paper); }
/* @tailwindcss/typography's default inline-code color (near-black) is unreadable against this
   dark card — override to the same paper/gold pairing used everywhere else on this page. */
.press-terms-card code { color: var(--gold-soft); background: var(--ink); border-radius: 0.25rem; padding: 0.15em 0.4em; }
.press-terms-card code::before,
.press-terms-card code::after { content: none; }
</style>
