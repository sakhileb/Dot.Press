<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Create account — Dot.Press" />

    <div class="relative min-h-screen overflow-hidden bg-[#0c0f1a] text-white flex items-center justify-center">

        <!-- Ambient background -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -left-40 h-[700px] w-[700px] rounded-full bg-amber-400/10 blur-[120px]"></div>
            <div class="absolute -bottom-32 -right-32 h-[600px] w-[600px] rounded-full bg-orange-500/10 blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 h-[400px] w-[900px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-amber-300/5 blur-[100px]"></div>
            <div
                class="absolute inset-0 opacity-[0.04]"
                style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 60px 60px;"
            ></div>
        </div>

        <!-- Main layout -->
        <div class="relative z-10 w-full max-w-6xl mx-auto px-6 py-12 lg:px-12 grid lg:grid-cols-2 gap-16 items-center">

            <!-- Left: Branding -->
            <div class="hidden lg:flex flex-col">
                <Link href="/">
                    <img src="/dot_pres.png" alt="Dot.Press" class="h-12 w-auto mb-10 drop-shadow-lg" />
                </Link>
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-4 py-1.5 text-xs font-medium text-amber-300 backdrop-blur-sm w-fit">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    AI-Powered Presentation Builder
                </div>
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight mb-4">
                    Build slides that
                    <span class="bg-gradient-to-r from-amber-300 via-orange-400 to-orange-500 bg-clip-text text-transparent">
                        tell your story
                    </span>
                </h1>
                <p class="text-white/60 text-lg leading-relaxed max-w-sm mb-10">
                    Join thousands of creators using Dot.Press to craft stunning presentations in minutes.
                </p>
                <ul class="space-y-3 text-sm text-white/60">
                    <li class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></span>
                        AI-powered slide generation
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></span>
                        One-click PDF &amp; PPTX export
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></span>
                        Real-time collaboration
                    </li>
                </ul>
            </div>

            <!-- Right: Form -->
            <div class="w-full max-w-md mx-auto lg:mx-0">
                <!-- Mobile logo -->
                <div class="flex justify-center mb-8 lg:hidden">
                    <Link href="/">
                        <img src="/dot_pres.png" alt="Dot.Press" class="h-10 w-auto drop-shadow-lg" />
                    </Link>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-sm">
                    <h2 class="text-2xl font-bold mb-1">Create your account</h2>
                    <p class="text-white/50 text-sm mb-8">
                        Already have an account?
                        <Link :href="route('login')" class="text-amber-400 hover:text-amber-300 transition">Sign in</Link>
                    </p>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-white/70 mb-1.5">Full name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autofocus
                                autocomplete="name"
                                class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/30 outline-none transition focus:border-amber-400/60 focus:ring-2 focus:ring-amber-400/20"
                                placeholder="Jane Smith"
                            />
                            <InputError class="mt-1.5" :message="form.errors.name" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-white/70 mb-1.5">Email address</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="username"
                                class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/30 outline-none transition focus:border-amber-400/60 focus:ring-2 focus:ring-amber-400/20"
                                placeholder="you@example.com"
                            />
                            <InputError class="mt-1.5" :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-white/70 mb-1.5">Password</label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/30 outline-none transition focus:border-amber-400/60 focus:ring-2 focus:ring-amber-400/20"
                                placeholder="Minimum 8 characters"
                            />
                            <InputError class="mt-1.5" :message="form.errors.password" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-white/70 mb-1.5">Confirm password</label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/30 outline-none transition focus:border-amber-400/60 focus:ring-2 focus:ring-amber-400/20"
                                placeholder="••••••••"
                            />
                            <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                        </div>

                        <!-- Terms -->
                        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <Checkbox id="terms" v-model:checked="form.terms" name="terms" required class="mt-0.5" />
                                <span class="text-sm text-white/60">
                                    I agree to the
                                    <a target="_blank" :href="route('terms.show')" class="text-amber-400 hover:text-amber-300 transition">Terms of Service</a>
                                    and
                                    <a target="_blank" :href="route('policy.show')" class="text-amber-400 hover:text-amber-300 transition">Privacy Policy</a>
                                </span>
                            </label>
                            <InputError class="mt-1.5" :message="form.errors.terms" />
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-amber-500/30 transition hover:brightness-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed mt-2"
                        >
                            <span v-if="form.processing">Creating account…</span>
                            <span v-else>Create account →</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
