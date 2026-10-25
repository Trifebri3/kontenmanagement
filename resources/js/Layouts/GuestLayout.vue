<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center bg-slate-50 px-4 py-8 sm:py-12 selection:bg-emerald-500 selection:text-white">
        <!-- Brand Header -->
        <div class="mb-6 text-center">
            <Link :href="route('dashboard')" class="inline-flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white font-black text-2xl shadow-md group-hover:scale-105 transition-transform">
                    C
                </div>
                <div class="mt-3">
                    <span class="font-black text-slate-900 text-xl tracking-tight block">COMP</span>
                    <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">Content Operations Platform</span>
                </div>
            </Link>
        </div>

        <!-- Form Card Container -->
        <div class="w-full max-w-md bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm">
            <slot />
        </div>

        <!-- Back to Home Link -->
        <div class="mt-6 text-center">
            <Link :href="'/'" class="text-xs font-semibold text-slate-500 hover:text-emerald-600 transition inline-flex items-center space-x-1">
                <span>&larr; Kembali ke Beranda</span>
            </Link>
        </div>
    </div>
</template>
