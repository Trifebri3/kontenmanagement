<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <Head title="COMP - Content Operations Management Platform" />

    <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col justify-between selection:bg-emerald-500 selection:text-white antialiased">
        <!-- Top Navigation -->
        <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <!-- Brand Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white font-black text-lg shadow-sm">
                        C
                    </div>
                    <div>
                        <span class="font-extrabold text-slate-900 text-base tracking-tight block leading-none">COMP</span>
                        <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider">Content Operations</span>
                    </div>
                </div>

                <!-- Nav Action -->
                <div class="flex items-center space-x-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-97 text-white text-xs font-bold rounded-xl shadow-xs transition"
                    >
                        <span>Buka Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="inline-flex items-center space-x-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-97 text-white text-xs font-bold rounded-xl shadow-xs transition"
                    >
                        <span>Masuk Aplikasi</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content (Hero & Value Propositions) -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-16 flex-1 flex flex-col items-center">
            <!-- Badge -->
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Platform Manajemen Operasional Konten Terpadu</span>
            </div>

            <!-- Headline -->
            <h1 class="text-3xl sm:text-5xl md:text-6xl font-black text-slate-900 text-center tracking-tight max-w-3xl leading-[1.15]">
                Satu Sistem Rapi untuk <span class="text-emerald-600 underline decoration-emerald-200 decoration-wavy">Produksi Konten</span> Tim Anda
            </h1>

            <!-- Concise Description -->
            <p class="text-slate-600 text-sm sm:text-base text-center max-w-2xl mt-5 leading-relaxed">
                <strong>COMP</strong> (Content Operations Management Platform) memudahkan Anda dan tim merencanakan ide konten, menyusun brief & shot list rekaman, saling menugaskan pekerjaan tanpa birokrasi kaku, hingga menjadwalkan publikasi di media sosial secara terorganisir.
            </p>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="w-full sm:w-auto px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-97 text-white text-sm font-bold rounded-xl shadow-sm text-center transition flex items-center justify-center space-x-2"
                >
                    <span>Masuk ke Dashboard ({{ $page.props.auth.user.name }})</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="w-full sm:w-auto px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-97 text-white text-sm font-bold rounded-xl shadow-sm text-center transition flex items-center justify-center space-x-2"
                >
                    <span>Masuk ke Akun Anda &rarr;</span>
                </Link>
            </div>

            <!-- Key Feature Cards (Light & Clean Grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mt-12 w-full">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:border-emerald-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Multi-Perusahaan</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Kelola banyak brand atau perusahaan klien dalam 1 akun terpisah tanpa tercampur.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:border-emerald-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Admin & User Setara</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Tanpa hierarki kaku. Semua user bebas membuat brief dan saling menugaskan tugas.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:border-emerald-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xs mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-sm">PWA Nyaman di HP</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Checklist adegan rekaman langsung dari HP tim lapangan saat syuting konten.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs hover:border-emerald-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Kalender & Jadwal</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Pantau tanggal tayang, status review video, dan distribusi channel dalam satu kalender.
                    </p>
                </div>
            </div>

            <!-- Production Call to Action (Siap Deploy) -->
            <div class="mt-12 w-full bg-gradient-to-r from-emerald-800 to-teal-800 rounded-3xl p-6 sm:p-10 text-white shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
                <div>
                    <h3 class="text-xl sm:text-2xl font-black tracking-tight">
                        Siap Mengoptimalkan Operasional Konten Anda?
                    </h3>
                    <p class="text-xs sm:text-sm text-emerald-100 mt-1 max-w-xl leading-relaxed">
                        Daftar sekarang untuk mulai merencanakan brief, mengatur checklist footage lapangan di HP, hingga jadwal publikasi lintas perusahaan.
                    </p>
                </div>

                <div class="flex items-center space-x-3 shrink-0 w-full sm:w-auto">
                    <Link
                        :href="route('register')"
                        class="w-full sm:w-auto px-5 py-3 rounded-xl bg-white text-emerald-900 font-extrabold text-xs text-center shadow-xs hover:bg-emerald-50 transition"
                    >
                        Daftar Gratis &rarr;
                    </Link>
                    <Link
                        :href="route('login')"
                        class="w-full sm:w-auto px-5 py-3 rounded-xl bg-emerald-900/60 hover:bg-emerald-900 text-white font-extrabold text-xs text-center border border-white/20 transition"
                    >
                        Masuk Akun
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-4 text-center text-xs text-slate-500">
            <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2">
                <span>COMP &middot; Content Operations Management Platform</span>
                <span class="text-slate-400 text-[11px]">Clean White Edition &middot; Mobile Friendly</span>
            </div>
        </footer>
    </div>
</template>
