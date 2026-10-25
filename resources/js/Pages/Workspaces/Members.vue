<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    workspace: {
        type: Object,
        required: true,
    },
    members: {
        type: Array,
        required: true,
    },
    invitations: {
        type: Array,
        required: true,
    },
    availableRoles: {
        type: Array,
        required: true,
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const isInviteModalOpen = ref(false);
const isDeleteConfirmOpen = ref(false);
const memberToDelete = ref(null);

const inviteForm = useForm({
    email: '',
    role: 'member',
});

const openInviteModal = () => {
    inviteForm.reset();
    inviteForm.clearErrors();
    isInviteModalOpen.value = true;
};

const closeInviteModal = () => {
    isInviteModalOpen.value = false;
};

const submitInvite = () => {
    inviteForm.post(route('workspaces.members.invite'), {
        onSuccess: () => closeInviteModal(),
    });
};

const changeMemberRole = (member, newRole) => {
    if (member.role === newRole) return;
    router.patch(route('workspaces.members.update-role', member.id), {
        role: newRole,
    }, {
        preserveScroll: true,
    });
};

const confirmDeleteMember = (member) => {
    memberToDelete.value = member;
    isDeleteConfirmOpen.value = true;
};

const executeDeleteMember = () => {
    if (!memberToDelete.value) return;
    router.delete(route('workspaces.members.destroy', memberToDelete.value.id), {
        onSuccess: () => {
            isDeleteConfirmOpen.value = false;
            memberToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head :title="`Tim & Peran - ${workspace.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">
                        Tim & Izin: {{ workspace.name }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Kelola penugasan, kolaborator, dan peran produksi konten.
                    </p>
                </div>
                <div v-if="canManage">
                    <button
                        @click="openInviteModal"
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-97 text-white rounded-xl text-xs font-bold shadow-sm transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>+ Undang Anggota Tim</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            <!-- Collaborative Horizontal Team Banner -->
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 border border-emerald-200/80 rounded-2xl p-4 shadow-2xs">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-700 text-white flex items-center justify-center shrink-0 shadow-xs text-xs font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-extrabold text-slate-900">
                            Struktur Tim Fleksibel: Admin & User
                        </h4>
                        <p class="text-[11px] sm:text-xs text-slate-600 mt-0.5 leading-relaxed">
                            Tidak ada hierarki kaku antar <strong>User</strong>. Semua anggota tim dan Admin memiliki akses setara untuk membuat brief, menyusun checklist rekaman, dan <strong>bebas saling menugaskan pekerjaan produksi</strong>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 1. MOBILE CARD VIEW (Active on HP / Small Screens) -->
            <div class="sm:hidden space-y-3">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs font-extrabold uppercase text-slate-400 tracking-wider">
                        Daftar Anggota ({{ members.length }})
                    </span>
                    <span class="text-[11px] text-emerald-600 font-bold">Touch Friendly</span>
                </div>

                <div
                    v-for="member in members"
                    :key="member.id"
                    class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs space-y-3"
                >
                    <!-- User Info & Avatar -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-black text-sm uppercase shrink-0 shadow-xs">
                                {{ member.name.substring(0, 1) }}
                            </div>
                            <div>
                                <div class="font-extrabold text-slate-900 text-sm flex items-center space-x-1.5">
                                    <span>{{ member.name }}</span>
                                    <span v-if="member.is_self" class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.2 rounded">(Anda)</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ member.email }}</div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ member.status_label }}
                        </span>
                    </div>

                    <!-- Role Control on Mobile -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div class="flex items-center space-x-1 text-xs text-slate-500">
                            <span class="font-medium">Peran:</span>
                            <div v-if="canManage && !member.is_owner && !member.is_self">
                                <select
                                    :value="member.role"
                                    @change="changeMemberRole(member, $event.target.value)"
                                    class="text-xs font-bold py-1 px-2.5 rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-slate-50"
                                >
                                    <option v-for="r in availableRoles" :key="r.value" :value="r.value">
                                        {{ r.label }}
                                    </option>
                                </select>
                            </div>
                            <span v-else class="font-bold px-2 py-0.5 rounded-full text-[11px] border" :class="member.badge_color">
                                {{ member.role_label }}
                            </span>
                        </div>

                        <!-- Delete Button on Mobile -->
                        <button
                            v-if="canManage && !member.is_owner && !member.is_self"
                            @click="confirmDeleteMember(member)"
                            type="button"
                            class="text-xs font-semibold text-rose-600 hover:text-rose-800 p-1"
                        >
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. DESKTOP TABLE VIEW (Active on Tablets / Laptops / PC) -->
            <div class="hidden sm:block bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Daftar Anggota Aktif</h3>
                        <p class="text-xs text-slate-500">Total {{ members.length }} kolaborator terdaftar</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                        <thead class="bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-6 py-3">Nama & Email</th>
                                <th class="px-6 py-3">Peran (Role)</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Bergabung Sejak</th>
                                <th v-if="canManage" class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="member in members" :key="member.id" class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                            {{ member.name.substring(0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs flex items-center space-x-1.5">
                                                <span>{{ member.name }}</span>
                                                <span v-if="member.is_self" class="text-[10px] text-slate-400 font-normal">(Anda)</span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-mono">{{ member.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="canManage && !member.is_owner && !member.is_self">
                                        <select
                                            :value="member.role"
                                            @change="changeMemberRole(member, $event.target.value)"
                                            class="text-xs font-semibold py-1 px-2.5 rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-white"
                                        >
                                            <option v-for="r in availableRoles" :key="r.value" :value="r.value">
                                                {{ r.label }}
                                            </option>
                                        </select>
                                    </div>
                                    <div v-else>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border" :class="member.badge_color">
                                            {{ member.role_label }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ member.status_label }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-[11px]">
                                    {{ member.joined_at }}
                                </td>

                                <td v-if="canManage" class="px-6 py-4 whitespace-nowrap text-right">
                                    <button
                                        v-if="!member.is_owner && !member.is_self"
                                        @click="confirmDeleteMember(member)"
                                        type="button"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition"
                                    >
                                        Hapus
                                    </button>
                                    <span v-else class="text-slate-300 text-[11px]">&mdash;</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Invitations Section (Responsive) -->
            <div v-if="invitations.length > 0" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <h3 class="font-bold text-xs sm:text-sm text-slate-900">Undangan Tertunda (Pending)</h3>
                    <p class="text-[11px] text-slate-400">Anggota yang telah diundang tetapi belum menerima undangan</p>
                </div>

                <div class="divide-y divide-slate-100">
                    <div v-for="inv in invitations" :key="inv.id" class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-xs hover:bg-slate-50/50">
                        <div>
                            <span class="font-bold text-slate-900">{{ inv.email }}</span>
                            <span class="text-slate-400 ms-1">&middot; Role: <span class="text-emerald-700 font-bold">{{ inv.role_label }}</span></span>
                        </div>
                        <div class="text-[10px] text-slate-400">
                            Kedaluwarsa: {{ inv.expires_at }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Undang Anggota -->
        <Modal :show="isInviteModalOpen" @close="closeInviteModal" max-width="md">
            <div class="p-5 sm:p-6">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900">
                        Undang Kolaborator ke {{ workspace.name }}
                    </h3>
                    <button @click="closeInviteModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitInvite" class="space-y-4">
                    <div>
                        <InputLabel for="email" value="Alamat Email Calon Anggota" class="font-bold text-xs" />
                        <TextInput
                            id="email"
                            v-model="inviteForm.email"
                            type="email"
                            class="mt-1 block w-full text-sm rounded-xl"
                            placeholder="nama@perusahaan.com"
                            required
                        />
                        <InputError :message="inviteForm.errors.email" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Peran & Tanggung Jawab" class="font-bold text-xs" />
                        <select
                            id="role"
                            v-model="inviteForm.role"
                            class="mt-1 block w-full rounded-xl border-slate-300 shadow-xs focus:border-emerald-500 focus:ring-emerald-500 text-xs font-semibold"
                        >
                            <option v-for="r in availableRoles" :key="r.value" :value="r.value">
                                {{ r.label }}
                            </option>
                        </select>
                        <InputError :message="inviteForm.errors.role" class="mt-1" />
                        <p class="text-[11px] text-slate-500 mt-1">
                            * <strong>User</strong>: Akses penuh untuk membuat brief, checklist lapangan, dan saling menugaskan tugas ke anggota lain.
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 pt-4 border-t border-slate-100">
                        <SecondaryButton @click="closeInviteModal" type="button" class="text-xs rounded-xl">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton :disabled="inviteForm.processing" class="text-xs font-bold bg-emerald-600 hover:bg-emerald-700 rounded-xl">
                            {{ inviteForm.processing ? 'Mengirim...' : 'Kirim Undangan' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Konfirmasi Hapus Anggota -->
        <Modal :show="isDeleteConfirmOpen" @close="isDeleteConfirmOpen = false" max-width="sm">
            <div class="p-5 sm:p-6">
                <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-2">
                    Hapus Anggota Tim?
                </h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Apakah Anda yakin ingin mengeluarkan <strong>{{ memberToDelete?.name }}</strong> dari workspace ini?
                </p>
                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                    <SecondaryButton @click="isDeleteConfirmOpen = false" class="text-xs rounded-xl">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="executeDeleteMember" class="text-xs font-bold rounded-xl">
                        Ya, Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
