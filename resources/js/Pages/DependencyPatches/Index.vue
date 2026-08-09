<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pending: { type: Array, default: () => [] },
    reviewed: { type: Array, default: () => [] },
});

const rejectingId = ref(null);
const rejectForm = useForm({ reason: '' });

function approve(proposal) {
    useForm({}).post(`/operator/dependency-patches/${proposal.id}/approve`);
}

function promptReject(proposal) {
    rejectingId.value = proposal.id;
    rejectForm.reason = '';
}

function cancelReject() {
    rejectingId.value = null;
}

function confirmReject(proposal) {
    rejectForm.post(`/operator/dependency-patches/${proposal.id}/reject`, {
        onSuccess: () => { rejectingId.value = null; },
    });
}
</script>

<template>
    <AppLayout title="Dependency Patches — Dot.Press">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h1 class="text-2xl font-bold text-white mb-8">Dependency Patch Proposals</h1>

            <h2 class="text-lg font-semibold text-white mb-4">Pending ({{ pending.length }})</h2>
            <div v-for="proposal in pending" :key="proposal.id" class="rounded-2xl border border-white/10 bg-white/[0.02] p-5 mb-4">
                <p class="text-white font-semibold">{{ proposal.manager }} &mdash; {{ proposal.risk_summary }}</p>
                <p class="text-white/50 text-sm mt-1">Proposed: <code>{{ proposal.proposed_command }}</code></p>
                <ul class="text-white/40 text-xs mt-2 space-y-1">
                    <li v-for="(advisory, i) in proposal.advisories" :key="i">
                        {{ advisory.package }} ({{ advisory.severity }}) &mdash; {{ advisory.title }}
                    </li>
                </ul>

                <div v-if="rejectingId === proposal.id" class="flex items-center gap-2 mt-4">
                    <input v-model="rejectForm.reason" type="text" placeholder="Reason for rejecting"
                        class="flex-1 rounded-lg bg-white/5 border border-white/10 px-3 py-1.5 text-sm text-white" />
                    <button @click="confirmReject(proposal)" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm text-white">Confirm Reject</button>
                    <button @click="cancelReject" class="rounded-lg bg-white/10 px-3 py-1.5 text-sm text-white">Cancel</button>
                </div>
                <div v-else class="flex items-center gap-2 mt-4">
                    <button @click="approve(proposal)" class="rounded-lg bg-green-600 px-3 py-1.5 text-sm text-white">Approve</button>
                    <button @click="promptReject(proposal)" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm text-white">Reject</button>
                </div>
            </div>
            <p v-if="pending.length === 0" class="text-white/40 text-sm">No pending proposals.</p>

            <h2 class="text-lg font-semibold text-white mt-10 mb-4">Recently Reviewed</h2>
            <div v-for="proposal in reviewed" :key="proposal.id" class="rounded-2xl border border-white/10 bg-white/[0.02] p-4 mb-3 flex items-center justify-between">
                <span class="text-white text-sm">{{ proposal.manager }} &mdash; {{ proposal.risk_summary }}</span>
                <span class="text-xs font-semibold px-3 py-1 rounded-full"
                    :class="{
                        'bg-green-500/10 text-green-400': proposal.status === 'applied',
                        'bg-red-500/10 text-red-400': proposal.status === 'rejected' || proposal.status === 'failed',
                        'bg-amber-500/10 text-amber-400': proposal.status === 'approved',
                    }">
                    {{ proposal.status }}
                </span>
            </div>
            <p v-if="reviewed.length === 0" class="text-white/40 text-sm">No reviewed proposals yet.</p>
        </div>
    </AppLayout>
</template>
