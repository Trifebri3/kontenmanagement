<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import Modal from '@/Components/Modal.vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const isMobileWorkspaceSheetOpen = ref(false);

const switchWorkspace = (workspaceId) => {
    isMobileWorkspaceSheetOpen.value = false;
    router.post(route('workspaces.switch', workspaceId), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans pb-20 sm:pb-0">
        <!-- Top App Bar -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-15 sm:h-16 items-center justify-between">
                    
                    <!-- Left: Brand Logo & Desktop Switcher -->
                    <div class="flex items-center space-x-3 sm:space-x-6">
                        <!-- App Icon -->
                        <Link :href="route('dashboard')" class="flex items-center space-x-2.5 active:scale-95 transition-transform">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-black text-sm sm:text-base shadow-sm shadow-emerald-500/30">
                                C
                            </div>
                            <div class="flex flex-col">
                                <span class="font-black text-sm sm:text-base tracking-tight text-slate-900 leading-none">COMP</span>
                                <span class="text-[9px] sm:text-[10px] text-emerald-600 font-bold tracking-wider uppercase mt-0.5">Content Ops</span>
                            </div>
                        </Link>

                        <!-- Mobile Workspace Pill Button -->
                        <button
                            v-if="$page.props.auth.currentWorkspace"
                            @click="isMobileWorkspaceSheetOpen = true"
                            type="button"
                            class="sm:hidden flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-900 text-[11px] font-bold active:bg-emerald-100 transition"
                        >
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="max-w-[110px] truncate">{{ $page.props.auth.currentWorkspace.name }}</span>
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Desktop Workspace Switcher Dropdown -->
                        <div v-if="$page.props.auth.currentWorkspace" class="hidden sm:block">
                            <Dropdown align="left" width="64">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 hover:border-slate-300 text-xs font-semibold text-slate-700 transition"
                                    >
                                        <div class="w-5 h-5 rounded-md bg-emerald-600 text-white flex items-center justify-center font-bold text-[10px] uppercase">
                                            {{ $page.props.auth.currentWorkspace.name.substring(0, 1) }}
                                        </div>
                                        <div class="flex flex-col text-left">
                                            <span class="truncate max-w-[140px] font-bold text-slate-900 text-xs leading-none">
                                                {{ $page.props.auth.currentWorkspace.name }}
                                            </span>
                                            <span class="text-[10px] text-slate-500 font-medium">
                                                {{ $page.props.auth.currentWorkspace.role_label }}
                                            </span>
                                        </div>
                                        <svg class="h-3.5 w-3.5 text-slate-400 ms-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="px-3 py-2 border-b border-slate-100">
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pilih Perusahaan / Workspace</p>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto py-1">
                                        <button
                                            v-for="ws in $page.props.auth.workspaces"
                                            :key="ws.id"
                                            @click="switchWorkspace(ws.id)"
                                            type="button"
                                            class="w-full text-left px-3 py-2 flex items-center justify-between text-xs hover:bg-emerald-50 transition"
                                            :class="{'bg-emerald-50/80 font-bold text-emerald-900': ws.is_current, 'text-slate-700': !ws.is_current}"
                                        >
                                            <div class="flex items-center space-x-2 truncate">
                                                <div class="w-6 h-6 rounded bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                                    {{ ws.name.substring(0, 1) }}
                                                </div>
                                                <span class="truncate">{{ ws.name }}</span>
                                            </div>
                                            <span v-if="ws.is_current" class="text-emerald-600 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100">
                                                Aktif
                                            </span>
                                        </button>
                                    </div>
                                    <div class="border-t border-slate-100 p-1">
                                        <DropdownLink :href="route('workspaces.index')" class="text-xs text-emerald-600 font-bold hover:text-emerald-700 flex items-center space-x-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>Semua / Tambah Workspace</span>
                                        </DropdownLink>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>

                        <!-- Desktop Main Links -->
                        <nav class="hidden md:flex items-center space-x-1">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Dashboard
                            </NavLink>
                            <NavLink :href="route('workspaces.members.index')" :active="route().current('workspaces.members.*')">
                                Tim & Peran
                            </NavLink>
                            <NavLink
                                v-if="$page.props.auth.currentWorkspace?.can_manage_workspace"
                                :href="route('workspaces.settings')"
                                :active="route().current('workspaces.settings')"
                            >
                                Pengaturan
                            </NavLink>
                        </nav>
                    </div>

                    <!-- Right: User Avatar & Menu -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <!-- Desktop Role Badge -->
                        <span
                            v-if="$page.props.auth.currentWorkspace"
                            class="hidden sm:inline-flex text-[11px] font-bold px-2.5 py-1 rounded-full border shadow-2xs"
                            :class="$page.props.auth.currentWorkspace.role_badge"
                        >
                            {{ $page.props.auth.currentWorkspace.role_label }}
                        </span>

                        <!-- User Profile Dropdown -->
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center space-x-2 rounded-full sm:rounded-lg border border-transparent p-1 sm:px-2.5 sm:py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition focus:outline-none"
                                >
                                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                        {{ $page.props.auth.user.name.substring(0, 1) }}
                                    </div>
                                    <span class="hidden sm:inline max-w-[110px] truncate text-slate-800 font-bold">{{ $page.props.auth.user.name }}</span>
                                    <svg class="hidden sm:block h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-2.5 border-b border-slate-100">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ $page.props.auth.user.email }}</p>
                                </div>
                                <DropdownLink :href="route('workspaces.index')">
                                    🏢 Semua Workspace
                                </DropdownLink>
                                <DropdownLink :href="route('workspaces.members.index')">
                                    👥 Kelola Tim
                                </DropdownLink>
                                <DropdownLink :href="route('profile.edit')">
                                    👤 Profil Pengguna
                                </DropdownLink>
                                <div class="border-t border-slate-100"></div>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-rose-600 font-bold">
                                    Keluar (Logout)
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Notifications Banner -->
        <div v-if="$page.props.flash.success" class="bg-emerald-600 text-white text-xs font-semibold px-4 py-2.5 shadow-sm transition">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>
        </div>
        <div v-if="$page.props.flash.error" class="bg-rose-600 text-white text-xs font-semibold px-4 py-2.5 shadow-sm transition">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ $page.props.flash.error }}</span>
                </div>
            </div>
        </div>

        <!-- Page Sub-Header (Optional) -->
        <header v-if="$slots.header" class="bg-white border-b border-slate-200/80">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 py-4 sm:py-8">
            <slot />
        </main>

        <!-- Native-Like Mobile Bottom Navigation Bar -->
        <nav class="sm:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur border-t border-slate-200 z-50 shadow-lg px-2 py-1.5 flex items-center justify-around">
            <!-- 1. Home / Dashboard -->
            <Link
                :href="route('dashboard')"
                class="flex flex-col items-center justify-center flex-1 py-1 text-center transition"
                :class="route().current('dashboard') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium'"
            >
                <div class="p-1 rounded-full" :class="route().current('dashboard') ? 'bg-emerald-50' : ''">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span class="text-[10px] mt-0.5 leading-none">Beranda</span>
            </Link>

            <!-- 2. Tim -->
            <Link
                :href="route('workspaces.members.index')"
                class="flex flex-col items-center justify-center flex-1 py-1 text-center transition"
                :class="route().current('workspaces.members.*') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium'"
            >
                <div class="p-1 rounded-full" :class="route().current('workspaces.members.*') ? 'bg-emerald-50' : ''">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="text-[10px] mt-0.5 leading-none">Tim & Peran</span>
            </Link>

            <!-- 3. Workspaces (Perusahaan) -->
            <Link
                :href="route('workspaces.index')"
                class="flex flex-col items-center justify-center flex-1 py-1 text-center transition"
                :class="route().current('workspaces.index') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium'"
            >
                <div class="p-1 rounded-full" :class="route().current('workspaces.index') ? 'bg-emerald-50' : ''">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <span class="text-[10px] mt-0.5 leading-none">Perusahaan</span>
            </Link>

            <!-- 4. Pengaturan -->
            <Link
                :href="$page.props.auth.currentWorkspace?.can_manage_workspace ? route('workspaces.settings') : route('profile.edit')"
                class="flex flex-col items-center justify-center flex-1 py-1 text-center transition"
                :class="route().current('workspaces.settings') || route().current('profile.*') ? 'text-emerald-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium'"
            >
                <div class="p-1 rounded-full" :class="route().current('workspaces.settings') || route().current('profile.*') ? 'bg-emerald-50' : ''">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="text-[10px] mt-0.5 leading-none">Pengaturan</span>
            </Link>
        </nav>

        <!-- Slide-Up Mobile Workspace Sheet Modal -->
        <Modal :show="isMobileWorkspaceSheetOpen" @close="isMobileWorkspaceSheetOpen = false" max-width="sm">
            <div class="p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Pilih Perusahaan / Workspace</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Beralih data dan tugas operasional</p>
                    </div>
                    <button @click="isMobileWorkspaceSheetOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="py-3 space-y-2 max-h-72 overflow-y-auto">
                    <button
                        v-for="ws in $page.props.auth.workspaces"
                        :key="ws.id"
                        @click="switchWorkspace(ws.id)"
                        type="button"
                        class="w-full text-left p-3 rounded-xl border flex items-center justify-between transition"
                        :class="ws.is_current ? 'bg-emerald-50/90 border-emerald-300 ring-2 ring-emerald-500/20' : 'bg-white border-slate-200 active:bg-slate-50'"
                    >
                        <div class="flex items-center space-x-3 truncate">
                            <div class="w-9 h-9 rounded-lg bg-slate-800 text-white flex items-center justify-center font-black text-sm uppercase shrink-0">
                                {{ ws.name.substring(0, 1) }}
                            </div>
                            <div class="truncate">
                                <div class="font-extrabold text-xs text-slate-900 truncate">{{ ws.name }}</div>
                                <div class="text-[10px] text-slate-500 font-medium">Peran: {{ ws.role }}</div>
                            </div>
                        </div>
                        <span v-if="ws.is_current" class="text-emerald-700 font-bold text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 border border-emerald-200">
                            Aktif
                        </span>
                    </button>
                </div>

                <div class="pt-3 border-t border-slate-100 flex flex-col space-y-2">
                    <Link
                        :href="route('workspaces.index')"
                        @click="isMobileWorkspaceSheetOpen = false"
                        class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold text-center shadow-xs transition"
                    >
                        + Tambah Workspace Baru
                    </Link>
                </div>
            </div>
        </Modal>
    </div>
</template>
