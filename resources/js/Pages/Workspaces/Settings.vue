<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    workspace: {
        type: Object,
        required: true,
    },
    timezones: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.workspace.name,
    timezone: props.workspace.timezone,
});

const updateSettings = () => {
    form.patch(route('workspaces.update', props.workspace.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Pengaturan - ${workspace.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Pengaturan Workspace: {{ workspace.name }}
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Konfigurasi profil perusahaan, zona waktu publikasi, dan identitas tenant.
                </p>
            </div>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 sm:p-8">
                <form @submit.prevent="updateSettings" class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Profil & Konfigurasi Dasar</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Informasi ini digunakan dalam kalender dan laporan publikasi.</p>
                    </div>

                    <div class="border-t border-slate-100 pt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-4">
                            <InputLabel for="name" value="Nama Perusahaan / Workspace" class="font-bold text-xs" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>

                        <div class="sm:col-span-4">
                            <InputLabel for="slug" value="Slug Tenant (Identifier Unik)" class="font-bold text-xs" />
                            <TextInput
                                id="slug"
                                :value="workspace.slug"
                                type="text"
                                class="mt-1 block w-full text-sm bg-slate-50 font-mono text-slate-500 cursor-not-allowed"
                                disabled
                            />
                            <p class="text-[11px] text-slate-400 mt-1">Slug digunakan untuk routing dan isolasi data multi-tenant.</p>
                        </div>

                        <div class="sm:col-span-4">
                            <InputLabel for="timezone" value="Zona Waktu Utama" class="font-bold text-xs" />
                            <select
                                id="timezone"
                                v-model="form.timezone"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-xs focus:border-emerald-500 focus:ring-emerald-500 text-xs font-medium"
                            >
                                <option v-for="(label, tz) in timezones" :key="tz" :value="tz">
                                    {{ label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.timezone" class="mt-1" />
                        </div>

                        <div class="sm:col-span-6 pt-4 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs text-slate-500 bg-slate-50 p-4 rounded-lg">
                                <div>
                                    <span class="font-semibold text-slate-700">Pemilik Workspace (Owner):</span>
                                    <p class="text-slate-900 font-bold mt-0.5">{{ workspace.owner?.name }} ({{ workspace.owner?.email }})</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-semibold text-slate-700">Tanggal Dibuat:</span>
                                    <p class="text-slate-600 font-mono mt-0.5">{{ workspace.created_at }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <PrimaryButton :disabled="form.processing" class="text-xs font-bold bg-emerald-600 hover:bg-emerald-700">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
