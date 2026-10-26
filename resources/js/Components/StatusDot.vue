<script setup>
const props = defineProps({
    online: { type: Boolean, default: false },
    pulse: { type: Boolean, default: false },
});
</script>

<template>
    <span class="relative inline-flex h-2 w-2 shrink-0" :title="online ? 'Online' : 'Offline'">
        <span
            v-if="pulse && online"
            class="ping-soft absolute inline-flex h-full w-full rounded-full opacity-60"
            :class="online ? 'bg-accent' : 'bg-rose'"
        />
        <span class="relative inline-flex h-2 w-2 rounded-full" :class="online ? 'bg-accent' : 'bg-rose'" />
    </span>
</template>
