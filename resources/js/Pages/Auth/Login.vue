<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LockKeyhole, Mail } from '@lucide/vue';
import AppLogo from '@/Components/AppLogo.vue';

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const showPassword = ref(false);
const canSubmit = computed(() => form.email.length > 0 && form.password.length > 0);

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <Head title="Sign in" />

    <div class="grid min-h-screen place-items-center px-4">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex justify-center">
                <AppLogo />
            </div>

            <form class="panel space-y-4 p-6" @submit.prevent="submit">
                <div>
                    <label for="email" class="label">Email</label>
                    <div class="relative">
                        <Mail class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-dim" />
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="input pl-9"
                            autocomplete="email"
                            autofocus
                        />
                    </div>
                    <p v-if="form.errors.email" class="mt-1 text-[11px] text-rose">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="label">Password</label>
                    <div class="relative">
                        <LockKeyhole class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-dim" />
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            class="input pl-9 pr-9"
                            autocomplete="current-password"
                        />
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-dim hover:text-muted"
                            @click="showPassword = !showPassword"
                        >
                            <component :is="showPassword ? EyeOff : Eye" class="h-4 w-4" />
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-1 text-[11px] text-rose">{{ form.errors.password }}</p>
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-[11px] text-muted">
                    <input v-model="form.remember" type="checkbox" class="h-3.5 w-3.5 rounded border-line bg-panel-2" />
                    Keep me signed in
                </label>

                <button type="submit" class="btn btn-primary w-full" :disabled="!canSubmit || form.processing">
                    <span v-if="form.processing" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-t-transparent" />
                    Sign in
                </button>
            </form>

            <p class="mt-5 text-center text-[11px] text-dim">
                There is no public registration. Ask whoever runs this Hub for an account.
            </p>
        </div>
    </div>
</template>
