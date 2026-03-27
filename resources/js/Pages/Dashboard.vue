<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                    <h3 class="text-2xl font-semibold text-gray-900">Dot.Press Canvas</h3>
                    <p class="mt-3 text-gray-600 max-w-2xl">
                        Open the editor to begin building your slide deck with drag-and-drop elements,
                        multi-select, resize/rotate transforms, and persistent canvas state.
                    </p>
                    <div class="mt-6">
                        <Link
                            :href="route('editor.start')"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-black"
                        >
                            Launch Editor
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
