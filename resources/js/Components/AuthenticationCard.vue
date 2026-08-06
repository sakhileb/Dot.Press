<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700&family=Roboto+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    </Head>

    <div class="press-auth min-h-screen flex flex-col justify-center items-center px-5 py-12">
        <div class="mb-8">
            <slot name="logo" />
        </div>

        <div class="press-auth-card w-full sm:max-w-md px-6 py-8 sm:px-8 rounded-2xl shadow-xl">
            <slot />
        </div>
    </div>
</template>

<style>
/* Brand palette for guest/auth pages — the same CSS custom properties as Welcome.vue's .press-page block.
   This stylesheet is global (not `scoped`) so it can reach into the shared TextInput/PrimaryButton/InputLabel/
   Checkbox components rendered via <slot />, but every rule is prefixed with .press-auth, and this component
   is only ever used by guest pages (Login, Register, ForgotPassword, ResetPassword, VerifyEmail,
   ConfirmPassword, TwoFactorChallenge, TermsOfService, PrivacyPolicy — confirmed via grep), never the
   authenticated app, so nothing here leaks into the dashboard. No component prop/v-model/route() binding
   is touched by this file. */
.press-auth {
    --ink: #180f08;
    --ink-soft: #201409;
    --gold: #f0c33a;
    --gold-soft: #f5d573;
    --orange: #e8890f;
    --orange-soft: #f0a13f;
    --paper: #f5efe4;
    --mist: #b8a692;
    --line: rgba(245, 239, 228, 0.14);
    --font-display: 'Fraunces', Georgia, serif;
    --font-body: 'Public Sans', system-ui, sans-serif;
    --font-mono: 'Roboto Mono', ui-monospace, monospace;
    background: var(--ink);
    color: var(--paper);
    font-family: var(--font-body);
}
.press-auth .font-display { font-family: var(--font-display); font-optical-sizing: auto; }
.press-auth-card { background: var(--ink-soft); border: 1px solid var(--line); }

.press-auth h1, .press-auth h2 { font-family: var(--font-display); color: var(--paper); }

/* TextInput.vue / Checkbox.vue overrides — !important wins over their compiled Tailwind classes */
.press-auth input[type="email"],
.press-auth input[type="password"],
.press-auth input[type="text"] {
    background-color: var(--ink) !important;
    border-color: var(--line) !important;
    color: var(--paper) !important;
    border-radius: 0.5rem !important;
}
.press-auth input:focus {
    border-color: var(--gold) !important;
    box-shadow: 0 0 0 3px rgba(240, 195, 58, 0.28) !important;
    outline: none !important;
}
.press-auth input[type="checkbox"] {
    background-color: var(--ink) !important;
    border-color: rgba(245, 239, 228, 0.35) !important;
    color: var(--gold) !important;
}
.press-auth input[type="checkbox"]:focus { box-shadow: 0 0 0 3px rgba(240, 195, 58, 0.28) !important; }

/* InputLabel.vue */
.press-auth label.block.font-medium { color: var(--mist) !important; }

/* PrimaryButton.vue — gray-800 default becomes the brand gold */
.press-auth button.bg-gray-800,
.press-auth button.bg-gray-700 {
    background-color: var(--gold) !important;
    color: var(--ink) !important;
}
.press-auth button.bg-gray-800:hover,
.press-auth button.bg-gray-800:focus,
.press-auth button.bg-gray-800:active {
    background-color: var(--gold-soft) !important;
}

/* Informational copy + links (gray-600/gray-900/indigo defaults across the auth pages) */
.press-auth .text-gray-600 { color: var(--mist) !important; }
.press-auth .text-gray-600:hover, .press-auth .hover\:text-gray-900:hover { color: var(--paper) !important; }
.press-auth .text-indigo-600, .press-auth .focus\:ring-indigo-500:focus { color: var(--gold) !important; }
.press-auth .text-green-600 { color: #6ee7a0 !important; }
.press-auth .text-red-600 { color: #f6907f !important; }
.press-auth a { color: var(--gold); }
.press-auth a:hover { color: var(--gold-soft); }
</style>
