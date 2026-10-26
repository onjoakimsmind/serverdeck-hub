<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    Activity,
    AlertTriangle,
    Box,
    CheckCircle2,
    Copy,
    HardDrive,
    Plus,
    Radio,
    Server,
    Trash2,
    Wifi,
    X,
} from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusDot from '@/Components/StatusDot.vue';
import { formatUptime, percentTone, timeAgo } from '@/lib/format';

defineOptions({ layout: AppLayout });

const props = defineProps({
    hosts: { type: Array, required: true },
});

const page = usePage();
const showCreate = ref(false);
const copied = ref(false);
const hubOrigin = window.location.origin;

const form = useForm({ name: '' });

const newToken = computed(() => page.props.flash?.new_token ?? null);

function createHost() {
    form.post('/hosts', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showCreate.value = false;
        },
    });
}

function removeHost(host) {
    if (! confirm(`Remove ${host.name} from the Hub? It will need to be re-enrolled to report again.`)) {
        return;
    }

    form.delete(`/hosts/${host.id}`, { preserveScroll: true });
}

async function copyToken() {
    if (! newToken.value) return;

    await navigator.clipboard.writeText(newToken.value);
    copied.value = true;
    window.setTimeout(() => (copied.value = false), 2000);
}

function metricsFor(host) {
    return host.payload?.metrics ?? null;
}

function workloadsFor(host) {
    return host.payload?.workloads ?? null;
}

function healthFor(host) {
    return host.payload?.health ?? null;
}

function updatesFor(host) {
    return host.payload?.updates ?? null;
}

function networkFor(host) {
    return host.payload?.network ?? null;
}
</script>

<template>
    <Head title="Hosts" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-bright">Hosts</h1>
                <p class="mt-0.5 text-[12px] text-dim">
                    Read-only status from each enrolled ServerDeck install. One-way only — nothing here can act on a host.
                </p>
            </div>
            <button type="button" class="btn btn-primary btn-sm" @click="showCreate = true">
                <Plus class="h-3.5 w-3.5" />
                Enroll a host
            </button>
        </div>

        <div v-if="newToken" class="panel space-y-3 border-accent/30 p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-bright">Enrollment token — shown once</p>
                    <p class="mt-1 text-[12px] leading-5 text-dim">
                        Copy this now. It will not be shown again. In the enrolled host's ServerDeck, go to
                        <span class="font-mono text-muted">Settings → Hub reporting</span> and set:
                    </p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <p class="label">Hub URL</p>
                    <code class="block rounded-lg border border-line bg-base/70 px-3 py-2 font-mono text-xs text-muted">{{ hubOrigin }}</code>
                </div>
                <div>
                    <p class="label">Enrollment token</p>
                    <div class="flex items-center gap-2">
                        <code class="block flex-1 truncate rounded-lg border border-line bg-base/70 px-3 py-2 font-mono text-xs text-bright">{{ newToken }}</code>
                        <button type="button" class="btn btn-sm shrink-0" @click="copyToken">
                            <component :is="copied ? CheckCircle2 : Copy" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showCreate" class="panel space-y-4 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-bright">Enroll a host</p>
                <button type="button" class="btn btn-sm" @click="showCreate = false">
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>
            <form class="flex flex-wrap items-end gap-3" @submit.prevent="createHost">
                <div class="min-w-48 flex-1">
                    <label for="host-name" class="label">Name</label>
                    <input id="host-name" v-model="form.name" class="input" placeholder="web-03" autofocus />
                    <p v-if="form.errors.name" class="mt-1 text-[11px] text-rose">{{ form.errors.name }}</p>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                    Create
                </button>
            </form>
        </div>

        <div v-if="hosts.length === 0" class="panel p-10 text-center">
            <Server class="mx-auto h-8 w-8 text-dim" />
            <p class="mt-3 text-sm font-medium text-bright">No hosts enrolled yet</p>
            <p class="mx-auto mt-1 max-w-sm text-[12px] text-dim">
                Enroll a host above, then paste the Hub URL and token into that host's ServerDeck under
                Settings → Hub reporting.
            </p>
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="host in hosts" :key="host.id" class="panel overflow-hidden">
                <div class="panel-header">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <StatusDot :online="host.online" pulse />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-bright">{{ host.name }}</p>
                            <p class="text-[11px] text-dim">
                                {{ host.online ? 'Online' : 'Offline' }} · last seen {{ timeAgo(host.last_seen_at) }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="grid h-7 w-7 shrink-0 place-items-center rounded-lg text-dim transition hover:bg-rose/10 hover:text-rose"
                        aria-label="Remove host"
                        @click="removeHost(host)"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                    </button>
                </div>

                <div v-if="host.payload" class="space-y-4 p-4">
                    <div>
                        <p class="truncate text-[11px] text-dim">{{ host.payload.host?.hostname }} · {{ host.payload.host?.os }}</p>
                        <p class="mt-0.5 text-[11px] text-dim">
                            up {{ formatUptime(host.payload.host?.uptime_seconds) }} · ServerDeck {{ host.payload.host?.serverdeck_version }}
                        </p>
                    </div>

                    <div v-if="metricsFor(host)" class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg border border-line bg-panel-2/60 py-2">
                            <p class="text-[10px] uppercase tracking-wide text-dim">CPU</p>
                            <p class="text-sm font-semibold" :class="percentTone(metricsFor(host).cpu_percent)">
                                {{ metricsFor(host).cpu_percent?.toFixed(0) ?? '—' }}%
                            </p>
                        </div>
                        <div class="rounded-lg border border-line bg-panel-2/60 py-2">
                            <p class="text-[10px] uppercase tracking-wide text-dim">Memory</p>
                            <p class="text-sm font-semibold" :class="percentTone(metricsFor(host).memory_percent)">
                                {{ metricsFor(host).memory_percent?.toFixed(0) ?? '—' }}%
                            </p>
                        </div>
                        <div class="rounded-lg border border-line bg-panel-2/60 py-2">
                            <p class="text-[10px] uppercase tracking-wide text-dim">Disk</p>
                            <p class="text-sm font-semibold" :class="percentTone(metricsFor(host).root_disk_percent)">
                                {{ metricsFor(host).root_disk_percent?.toFixed(0) ?? '—' }}%
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <span v-if="workloadsFor(host)" class="badge border-line bg-panel-2 text-muted">
                            <Box class="h-3 w-3" />
                            Docker {{ workloadsFor(host).docker_running }}/{{ workloadsFor(host).docker_total }}
                        </span>
                        <span v-if="workloadsFor(host)" class="badge border-line bg-panel-2 text-muted">
                            <Activity class="h-3 w-3" />
                            Incus {{ workloadsFor(host).incus_running }}/{{ workloadsFor(host).incus_total }}
                        </span>
                        <span
                            v-if="healthFor(host)"
                            class="badge"
                            :class="healthFor(host).checks_critical > 0 ? 'border-rose/25 bg-rose/10 text-rose' : healthFor(host).checks_warning > 0 ? 'border-amber/25 bg-amber/10 text-amber' : 'border-accent/25 bg-accent/10 text-accent'"
                        >
                            <AlertTriangle class="h-3 w-3" />
                            {{ healthFor(host).checks_clean }}/{{ healthFor(host).checks_total }} checks clean
                        </span>
                        <span v-if="updatesFor(host)?.pending" class="badge border-line bg-panel-2 text-muted">
                            <HardDrive class="h-3 w-3" />
                            {{ updatesFor(host).pending }} updates
                            <template v-if="updatesFor(host).security">· {{ updatesFor(host).security }} security</template>
                        </span>
                        <span v-if="networkFor(host)?.tailscale" class="badge border-cyan/25 bg-cyan/10 text-cyan">
                            <Wifi class="h-3 w-3" />
                            {{ networkFor(host).tailscale.hostname ?? 'Tailscale' }}
                        </span>
                        <span v-if="networkFor(host)?.twingate" class="badge" :class="networkFor(host).twingate.connected ? 'border-cyan/25 bg-cyan/10 text-cyan' : 'border-line bg-panel-2 text-dim'">
                            <Radio class="h-3 w-3" />
                            Twingate {{ networkFor(host).twingate.connected ? 'connected' : 'disconnected' }}
                        </span>
                    </div>
                </div>

                <div v-else class="p-4 text-[12px] text-dim">
                    Waiting for the first report from this host.
                </div>
            </div>
        </div>
    </div>
</template>
