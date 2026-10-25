<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    workspaces: {
        type: Array,
        required: true,
    },
});

const isCreateModalOpen = ref(false);

const form = useForm({
    name: '',
    timezone: 'Asia/Jakarta',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
    isCreateModalOpen.value = false;
};

const submitCreateWorkspace = () => {
    form.post(route('workspaces.store'), {
        onSuccess: () => {
            closeCreateModal();
        },
    });
};

const switchWorkspace = (workspaceId) => {
    router.post(route('workspaces.switch', workspaceId), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Kelola Perusahaan & Workspace" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">
                        Daftar Perusahaan & Workspace
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Kelola seluruh entitas bisnis dan brand dalam akun Anda.
                    </p>
                </div>
                <div>
                    <button
                        @click="openCreateModal"
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-97 text-white rounded-xl text-xs font-bold shadow-sm transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Workspace Baru</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="ws in workspaces"
                    :key="ws.id"
                    class="bg-white rounded-2xl border transition-all duration-150 overflow-hidden flex flex-col justify-between shadow-2xs"
                    :class="ws.is_current ? 'border-emerald-500 ring-2 ring-emerald-500/15' : 'border-slate-200/90 hover:border-slate-300'"
                >
                    <div class="p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-base shadow-sm shrink-0">
                                    {{ ws.name.substring(0, 1) }}
                                </div>
                                <div class="truncate">
                                    <h3 class="font-extrabold text-slate-900 text-sm truncate">
                                        {{ ws.name }}
                                    </h3>
                                    <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                        slug: {{ ws.slug }}
                                    </p>
                                </div>
                            </div>
                            <span
                                v-if="ws.is_current"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0"
                            >
                                Aktif
                            </span>
                        </div>

                        <!-- Info Pills -->
                        <div class="mt-4 pt-3.5 border-t border-slate-100 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Peran Anda:</span>
                                <span class="font-bold text-slate-900 px-2 py-0.5 rounded-lg bg-slate-100 text-[11px]">
                                    {{ ws.role_label }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Anggota Tim:</span>
                                <span class="font-semibold text-slate-800">{{ ws.members_count }} orang</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Zona Waktu:</span>
                                <span class="font-mono text-[11px] text-slate-500">{{ ws.timezone }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Button -->
                    <div class="p-4 bg-slate-50/70 border-t border-slate-100">
                        <button
                            v-if="!ws.is_current"
                            @click="switchWorkspace(ws.id)"
                            type="button"
                            class="w-full py-2.5 rounded-xl bg-white hover:bg-slate-100 active:scale-97 border border-slate-200 text-xs font-bold text-emerald-700 shadow-2xs transition flex items-center justify-center space-x-1"
                        >
                            <span>Gunakan Workspace Ini &rarr;</span>
                        </button>
                        <div v-else class="text-xs font-bold text-emerald-700 flex items-center justify-center space-x-1.5 py-1">
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>Sedang Digunakan Saat Ini</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Workspace -->
        <Modal :show="isCreateModalOpen" @close="closeCreateModal" max-width="md">
            <div class="p-5 sm:p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900">
                        Tambah Perusahaan / Workspace Baru
                    </h3>
                    <button @click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreateWorkspace" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Nama Workspace / Perusahaan" class="font-bold text-xs" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full text-sm rounded-xl"
                            placeholder="Contoh: PT Agronex Makmur"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="timezone" value="Zona Waktu Operasional" class="font-bold text-xs" />
                        <select
                            id="timezone"
                            v-model="form.timezone"
                            class="mt-1 block w-full rounded-xl border-slate-300 shadow-xs focus:border-emerald-500 focus:ring-emerald-500 text-xs font-semibold"
                        >
                            <option value="Asia/Jakarta">WIB (Asia/Jakarta - UTC+7)</option>
                            <option value="Asia/Makassar">WITA (Asia/Makassar - UTC+8)</option>
                            <option value="Asia/Jayapura">WIT (Asia/Jayapura - UTC+9)</option>
                            <option value="UTC">UTC</option>
                        </select>
                        <InputError :message="form.errors.timezone" class="mt-1" />
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 pt-4 border-t border-slate-100">
                        <SecondaryButton @click="closeCreateModal" type="button" class="text-xs rounded-xl">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton :disabled="form.processing" class="text-xs font-bold bg-emerald-600 hover:bg-emerald-700 rounded-xl">
                            {{ form.processing ? 'Menyimpan...' : 'Buat Workspace' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
