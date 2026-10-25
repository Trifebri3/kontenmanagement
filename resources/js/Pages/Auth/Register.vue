<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun Baru - COMP" />

        <div class="mb-5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                Buat Akun Baru
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Mulai kelola operasional dan jadwal konten tim Anda.
            </p>
        </div>

        <!-- Google OAuth Register Button -->
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
                <span>Daftar dengan Google</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-[11px]">
                <span class="bg-white px-3 text-slate-400 font-medium">atau daftar dengan email</span>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Nama Lengkap" class="text-xs font-bold text-slate-700" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full text-sm rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600"
                    v-model="form.name"
                    placeholder="Nama Lengkap"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Alamat Email" class="text-xs font-bold text-slate-700" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full text-sm rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600"
                    v-model="form.email"
                    placeholder="nama@perusahaan.com"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Kata Sandi (Password)" class="text-xs font-bold text-slate-700" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full text-sm rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600"
                    v-model="form.password"
                    placeholder="Minimal 8 karakter"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Konfirmasi Kata Sandi" class="text-xs font-bold text-slate-700" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full text-sm rounded-xl border-slate-200 focus:border-emerald-600 focus:ring-emerald-600"
                    v-model="form.password_confirmation"
                    placeholder="Ulangi kata sandi"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-emerald-700 hover:bg-emerald-800 active:scale-98 text-white rounded-xl text-sm font-bold shadow-sm transition flex items-center justify-center space-x-2"
                    :class="{ 'opacity-60 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Memproses...' : 'Daftar Akun Baru' }}</span>
                </button>
            </div>

            <div class="pt-3 text-center text-xs text-slate-500">
                Sudah memiliki akun?
                <Link :href="route('login')" class="font-bold text-emerald-700 hover:text-emerald-900 ms-1">
                    Masuk Sekarang
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
