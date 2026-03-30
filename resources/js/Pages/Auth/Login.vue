<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign in — Dot.Press" />

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
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight mb-4">
                    Welcome back to
                    <span class="bg-gradient-to-r from-amber-300 via-orange-400 to-orange-500 bg-clip-text text-transparent">
                        Dot.Press
                    </span>
                </h1>
                <p class="text-white/60 text-lg leading-relaxed max-w-sm mb-12">
                    Sign in and pick up right where you left off. Your presentations are waiting.
                </p>
                <!-- Decorative slide cards -->
                <div class="relative h-52">
                    <div class="absolute top-0 left-0 w-64 rotate-3 rounded-2xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur-sm">
                        <div class="mb-3 h-2 w-24 rounded-full bg-amber-400/60"></div>
                        <div class="mb-2 h-2 w-40 rounded-full bg-white/20"></div>
                        <div class="mb-2 h-2 w-32 rounded-full bg-white/10"></div>
                        <div class="mt-4 h-16 rounded-lg bg-gradient-to-br from-amber-400/20 to-orange-500/20"></div>
                    </div>
                    <div class="absolute top-6 left-20 w-52 -rotate-2 rounded-2xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur-sm">
                        <div class="mb-3 h-2 w-16 rounded-full bg-orange-400/60"></div>
                        <div class="mt-4 h-14 rounded-lg bg-gradient-to-br from-orange-500/20 to-amber-300/20"></div>
                        <div class="mt-3 h-2 w-28 rounded-full bg-white/20"></div>
                    </div>
                </div>
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
                    <h2 class="text-2xl font-bold mb-1">Sign in</h2>
                    <p class="text-white/50 text-sm mb-8">
                        Don't have an account?
                        <Link :href="route('register')" class="text-amber-400 hover:text-amber-300 transition">Create one free</Link>
                    </p>

                    <div v-if="status" class="mb-6 rounded-xl bg-green-500/10 border border-green-500/30 px-4 py-3 text-sm text-green-400">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-white/70 mb-1.5">Email address</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="username"
                                class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/30 outline-none transition focus:border-amber-400/60 focus:ring-2 focus:ring-amber-400/20"
                                placeholder="you@example.com"
                            />
                            <InputError class="mt-1.5" :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-sm font-medium text-white/70">Password</label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs text-amber-400/80 hover:text-amber-300 transition"
                                >
                                    Forgot password?
                                </Link>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                autocomplete="current-password"
                                class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/30 outline-none transition focus:border-amber-400/60 focus:ring-2 focus:ring-amber-400/20"
                                placeholder="••••••••"
                            />
                            <InputError class="mt-1.5" :message="form.errors.password" />
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2">
                            <Checkbox v-model:checked="form.remember" name="remember" id="remember" />
                            <label for="remember" class="text-sm text-white/60 cursor-pointer">Remember me</label>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-xl bg-gradient-to-r from-amber-400 to-orange-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-amber-500/30 transition hover:brightness-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed mt-2"
                        >
                            <span v-if="form.processing">Signing in…</span>
                            <span v-else>Sign in →</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
