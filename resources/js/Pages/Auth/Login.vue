<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: '',
    },
});

const page = usePage();

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke Akun - COMP" />

        <div class="mb-5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                Selamat Datang Kembali
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Masuk untuk mengakses workspace operasional konten Anda.
            </p>
        </div>

        <!-- Flash Status or Error Notification -->
        <div v-if="status" class="mb-4 text-xs font-semibold text-emerald-700 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200">
            {{ status }}
        </div>
        <div v-if="page.props.flash?.error" class="mb-4 text-xs font-semibold text-rose-700 bg-rose-50 p-2.5 rounded-xl border border-rose-200">
            {{ page.props.flash.error }}
        </div>

        <!-- Google OAuth Login Button -->
        <div class="mb-4">
            <a
                :href="route('auth.google.redirect')"
                class="w-full flex items-center justify-center space-x-2.5 py-2.5 px-4 rounded-xl border border-slate-300 hover:border-slate-400 bg-white hover:bg-slate-50 active:scale-98 text-slate-700 text-xs font-bold shadow-2xs transition"
            >
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Masuk dengan Google</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-[11px]">
                <span class="bg-white px-3 text-slate-400 font-medium">atau masuk dengan email</span>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Alamat Email" class="text-xs font-bold text-slate-700" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full text-sm rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600"
                    v-model="form.email"
                    placeholder="nama@perusahaan.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Kata Sandi" class="text-xs font-bold text-slate-700" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-900"
                    >
                        Lupa sandi?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full text-sm rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600"
                    v-model="form.password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-emerald-600 focus:ring-emerald-600" />
                    <span class="text-xs text-slate-600 font-medium">Ingat saya</span>
                </label>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-98 text-white rounded-xl text-sm font-bold shadow-sm transition flex items-center justify-center space-x-2"
                    :class="{ 'opacity-60 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Memproses...' : 'Masuk ke Aplikasi' }}</span>
                </button>
            </div>

            <div class="pt-3 text-center text-xs text-slate-500">
                Belum memiliki akun?
                <Link :href="route('register')" class="font-bold text-emerald-700 hover:text-emerald-900 ms-1">
                    Daftar Sekarang
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
