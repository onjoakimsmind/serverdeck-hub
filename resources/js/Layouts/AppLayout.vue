<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut } from '@lucide/vue';
import AppLogo from '@/Components/AppLogo.vue';
import ToastHost from '@/Components/ToastHost.vue';

const page = usePage();

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen">
        <header class="border-b border-line/70 bg-panel/60 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-3">
                <AppLogo />

                <div v-if="page.props.auth" class="flex items-center gap-3">
                    <span class="hidden text-xs text-dim sm:inline">{{ page.props.auth.email }}</span>
                    <button type="button" class="btn btn-sm" @click="logout">
                        <LogOut class="h-3.5 w-3.5" />
                        Sign out
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-5 py-8">
            <slot />
        </main>

        <ToastHost />
    </div>
</template>
