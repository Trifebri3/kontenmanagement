<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    workspaceMembers: {
        type: Array,
        default: () => [],
    },
    contents: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const currentUser = computed(() => page.props.auth?.user || { name: 'Pengguna', email: '' });

// PWA Offline and Install State
const isOnline = ref(typeof navigator !== 'undefined' ? navigator.onLine : true);
const deferredInstallPrompt = ref(null);
const showInstallBanner = ref(false);

const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine;
};

onMounted(() => {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredInstallPrompt.value = e;
        showInstallBanner.value = true;
    });
});

onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
});

const installPwa = async () => {
    if (!deferredInstallPrompt.value) return;
    deferredInstallPrompt.value.prompt();
    const { outcome } = await deferredInstallPrompt.value.userChoice;
    if (outcome === 'accepted') {
        showInstallBanner.value = false;
    }
    deferredInstallPrompt.value = null;
};

// 1. MAIN SEGMENT: ACTIVE KONTEN VS ARSIP (AUTO-SORTING)
// Konten dengan status 'published' atau 'archived' otomatis masuk ke Arsip
const mainSegment = ref('active'); // 'active' or 'archive'
const activeTab = ref('all'); // 'all', 'guide', 'calendar'
const activeSubFilter = ref('all'); // 'all', 'shooting', 'editing', 'review', 'ready'

const isArchived = (content) => {
    return content.status === 'published' || content.status === 'archived';
};

// Konten yang sedang berjalan (Bukan arsip)
const activeContents = computed(() => {
    return props.contents.filter(c => !isArchived(c));
});

// Konten yang sudah masuk arsip (Auto sorting)
const archivedContents = computed(() => {
    return props.contents.filter(c => isArchived(c));
});

// List yang ditampilkan sesuai segment & filter
const displayedContents = computed(() => {
    if (mainSegment.value === 'archive') {
        return archivedContents.value;
    }

    if (activeSubFilter.value === 'all') {
        return activeContents.value;
    }
    return activeContents.value.filter(c => c.status === activeSubFilter.value);
});

// 2. MODAL DETAIL KONTEN
const isDetailModalOpen = ref(false);
const activeContent = ref(null);
const detailSubTab = ref('footage'); // 'footage', 'todos', 'brief'

const openContentDetail = (content) => {
    activeContent.value = content;
    detailSubTab.value = 'footage';
    isDetailModalOpen.value = true;
};

const closeContentDetail = () => {
    isDetailModalOpen.value = false;
    activeContent.value = null;
};

// Toggle Footage (Support Offline via LocalStorage fallback)
const toggleFootage = (footage) => {
    footage.is_captured = !footage.is_captured;
    if (activeContent.value) {
        const count = activeContent.value.footages.filter(f => f.is_captured).length;
        activeContent.value.captured_footages_count = count;
    }

    // Save offline state
    try {
        const key = `footage_${footage.id}`;
        localStorage.setItem(key, footage.is_captured ? '1' : '0');
    } catch (e) {
        // ignore
    }

    if (isOnline.value) {
        router.patch(route('footages.toggle', footage.id), {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

// Toggle Todo (Support Offline)
const toggleTodo = (todo) => {
    todo.is_completed = !todo.is_completed;
    if (activeContent.value) {
        const count = activeContent.value.todos.filter(t => t.is_completed).length;
        activeContent.value.completed_todos_count = count;
    }

    try {
        const key = `todo_${todo.id}`;
        localStorage.setItem(key, todo.is_completed ? '1' : '0');
    } catch (e) {
        // ignore
    }

    if (isOnline.value) {
        router.patch(route('todos.toggle', todo.id), {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};

// Add Footage
const newFootageTitle = ref('');
const newFootageShotType = ref('medium');
const newFootageNotes = ref('');
const isAddingFootage = ref(false);

const submitNewFootage = () => {
    if (!newFootageTitle.value || !activeContent.value) return;

    isAddingFootage.value = true;
    router.post(route('contents.footages.store', activeContent.value.id), {
        title: newFootageTitle.value,
        shot_type: newFootageShotType.value,
        visual_notes: newFootageNotes.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            newFootageTitle.value = '';
            newFootageNotes.value = '';
            isAddingFootage.value = false;
            const updated = page.props.contents.find(c => c.id === activeContent.value?.id);
            if (updated) activeContent.value = updated;
        },
        onError: () => {
            isAddingFootage.value = false;
        },
    });
};

const deleteFootage = (footageId) => {
    router.delete(route('footages.destroy', footageId), {
        preserveScroll: true,
        onSuccess: (page) => {
            const updated = page.props.contents.find(c => c.id === activeContent.value?.id);
            if (updated) activeContent.value = updated;
        },
    });
};

// Add Todo
const newTodoTitle = ref('');
const isAddingTodo = ref(false);

const submitNewTodo = () => {
    if (!newTodoTitle.value || !activeContent.value) return;

    isAddingTodo.value = true;
    router.post(route('contents.todos.store', activeContent.value.id), {
        title: newTodoTitle.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            newTodoTitle.value = '';
            isAddingTodo.value = false;
            const updated = page.props.contents.find(c => c.id === activeContent.value?.id);
            if (updated) activeContent.value = updated;
        },
        onError: () => {
            isAddingTodo.value = false;
        },
    });
};

const deleteTodo = (todoId) => {
    router.delete(route('todos.destroy', todoId), {
        preserveScroll: true,
        onSuccess: (page) => {
            const updated = page.props.contents.find(c => c.id === activeContent.value?.id);
            if (updated) activeContent.value = updated;
        },
    });
};

// Quick Update Content Status or Assignee (Auto archive if set to published)
const updateContentField = (field, value) => {
    if (!activeContent.value) return;

    const data = { [field]: value };
    router.patch(route('contents.update', activeContent.value.id), data, {
        preserveScroll: true,
        onSuccess: (page) => {
            const updated = page.props.contents.find(c => c.id === activeContent.value?.id);
            if (updated) activeContent.value = updated;
        },
    });
};

// Pindahkan langsung ke arsip
const archiveContent = () => {
    updateContentField('status', 'published');
};

// Pulihkan dari arsip
const restoreFromArchive = () => {
    updateContentField('status', 'ready');
};

// 3. MODAL BUAT KONTEN BARU
const isCreateContentModalOpen = ref(false);
const newContentForm = ref({
    title: '',
    channel: 'tiktok',
    status: 'shooting',
    hook: '',
    concept: '',
    assignee_id: props.workspaceMembers.length > 0 ? props.workspaceMembers[0].id : null,
    target_date: '',
    notes: '',
});

const openCreateContentModal = () => {
    newContentForm.value = {
        title: '',
        channel: 'tiktok',
        status: 'shooting',
        hook: '',
        concept: '',
        assignee_id: props.workspaceMembers.length > 0 ? props.workspaceMembers[0].id : null,
        target_date: '',
        notes: '',
    };
    isCreateContentModalOpen.value = true;
};

const submitNewContent = () => {
    if (!newContentForm.value.title) return;

    router.post(route('contents.store'), newContentForm.value, {
        onSuccess: () => {
            isCreateContentModalOpen.value = false;
        },
    });
};

// Calendar State
const selectedCalendarDate = ref(26);

// SOP Guidance Steps (Tanpa Emotikon)
const activeGuideStep = ref(1);
const guideSteps = [
    {
        step: 1,
        title: 'Tahap 1: Hook Pembuka 3 Detik Pertama',
        badge: 'Pra-Produksi',
        desc: 'Menarik perhatian penonton pada detik-detik awal video vertikal.',
        checklist: [
            'Gunakan kalimat tanya tegas atau fakta penting yang relevan dengan penonton.',
            'Awali dengan adegan aksi visual langsung tanpa jeda pembuka lambat.',
            'Posisikan teks judul di area aman tengah layar agar mudah terbaca.',
        ],
    },
    {
        step: 2,
        title: 'Tahap 2: Susun Daftar Footage dan Instruksi Kamera',
        badge: 'Rencana Lapangan',
        desc: 'Daftar adegan spesifik agar tim yang bertugas memiliki acuan jelas.',
        checklist: [
            'A-Roll: Narasumber atau talenta yang menyampaikan poin pesan utama.',
            'B-Roll: Pengambilan gambar jarak dekat (close-up) dan suasana pendukung.',
            'Gunakan rasio vertikal 9:16 dan resolusi video standar 1080p 60fps.',
            'Penugasan bebas antar anggota tim tanpa hambatan hierarki.',
        ],
    },
    {
        step: 3,
        title: 'Tahap 3: Perekaman dengan Checklist HP',
        badge: 'Produksi',
        desc: 'Buka sistem di HP dan centang daftar adegan yang telah selesai diambil di lokasi.',
        checklist: [
            'Periksa sambungan mikrofon clip-on dan kejernihan tangkapan suara.',
            'Ambil setidaknya dua variasi sudut pengambilan untuk setiap adegan penting.',
            'Centang item footage langsung pada kartu konten di HP.',
        ],
    },
    {
        step: 4,
        title: 'Tahap 4: Penyuntingan Video, Subtitle dan Audio',
        badge: 'Pasca-Produksi',
        desc: 'Penyusunan alur cepat dan penambahan teks visual pendukung.',
        checklist: [
            'Potong bagian hening untuk mempertahankan ritme tontonan.',
            'Tambahkan takarir (subtitle) dengan kontras warna yang jelas.',
            'Pilih audio berlisensi aman dengan volume suara latar yang proporsional.',
        ],
    },
    {
        step: 5,
        title: 'Tahap 5: Peninjauan Bersama dan Penjadwalan',
        badge: 'Distribusi',
        desc: 'Konfirmasi bersama rekan tim dan penentuan waktu publikasi.',
        checklist: [
            'Rekan kerja dapat langsung memeriksa dan memberikan persetujuan.',
            'Lengkapi teks takarir, pesan ajakan bertindak, serta tanda pagar relevan.',
            'Tentukan tanggal dan jam tayang pada kalender konten.',
        ],
    },
];

const channelLabel = (channel) => {
    switch (channel) {
        case 'tiktok': return 'TikTok';
        case 'instagram': return 'Instagram Reels';
        case 'youtube': return 'YouTube Shorts';
        default: return 'Video';
    }
};
</script>

<template>
    <Head title="Dashboard Operasional Konten" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 pb-12">
            <!-- PWA Install Banner (Jika Belum Diinstall di HP) -->
            <div
                v-if="showInstallBanner"
                class="bg-emerald-700 text-white p-3.5 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm"
            >
                <div class="flex items-center space-x-3 text-left w-full sm:w-auto">
                    <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center font-bold text-sm shrink-0">
                        PWA
                    </div>
                    <div>
                        <div class="font-extrabold text-xs">Pasang Aplikasi COMP di Layar Utama HP</div>
                        <div class="text-[11px] text-emerald-100">Akses cepat, ringan, dan tetap dapat dibuka saat tanpa koneksi internet.</div>
                    </div>
                </div>
                <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                    <button
                        @click="showInstallBanner = false"
                        type="button"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold text-emerald-200 hover:text-white"
                    >
                        Nanti
                    </button>
                    <button
                        @click="installPwa"
                        type="button"
                        class="px-4 py-1.5 bg-white text-emerald-900 rounded-xl text-xs font-extrabold hover:bg-emerald-50 transition shadow-xs"
                    >
                        Pasang Sekarang
                    </button>
                </div>
            </div>

            <!-- Offline Status Indicator -->
            <div
                v-if="!isOnline"
                class="bg-amber-600 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center justify-between"
            >
                <span>Mode Offline: Anda sedang tidak terhubung internet. Checklist tetap tersimpan di HP.</span>
                <span class="text-[10px] bg-amber-800 px-2 py-0.5 rounded">Tersimpan Lokal</span>
            </div>

            <!-- 1. Header Profil & Workspace -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-base shrink-0">
                            {{ ($page.props.auth.currentWorkspace?.name || 'C').substring(0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-slate-500 font-medium">Workspace:</span>
                                <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                    {{ $page.props.auth.currentWorkspace?.name || 'Aren Lestari' }}
                                </span>
                            </div>
                            <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight mt-0.5">
                                Halo, {{ currentUser.name }}
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('workspaces.members.index')"
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5"
                        >
                            <span>Tim & Peran ({{ workspaceMembers.length || 5 }})</span>
                        </Link>
                        <Link
                            :href="route('workspaces.index')"
                            class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5"
                        >
                            <span>Ganti Brand</span>
                        </Link>
                    </div>
                </div>

                <!-- Navigation Tabs Atas -->
                <div class="flex items-center space-x-2 mt-4 pt-3 border-t border-slate-100 overflow-x-auto no-scrollbar">
                    <button
                        @click="activeTab = 'all'"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition"
                        :class="activeTab === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Semua Tampilan
                    </button>
                    <button
                        @click="activeTab = 'guide'"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition"
                        :class="activeTab === 'guide' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Panduan SOP
                    </button>
                    <button
                        @click="activeTab = 'calendar'"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition"
                        :class="activeTab === 'calendar' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Kalender Tayang
                    </button>
                </div>
            </div>

            <!-- 2. SECTION: MANAJEMEN KONTEN DENGAN AUTO SORTING KE ARSIP -->
            <section v-if="activeTab === 'all'" class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-4">
                <!-- Header Konten & Button Buat -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                Operasional Konten
                            </h2>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold border border-slate-200">
                                {{ mainSegment === 'active' ? `${activeContents.length} Berjalan` : `${archivedContents.length} Arsip` }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Konten yang sudah tayang secara otomatis tersortir masuk ke Arsip.
                        </p>
                    </div>

                    <button
                        @click="openCreateContentModal"
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center space-x-1.5 px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 active:scale-97 text-white text-xs font-bold rounded-xl shadow-xs transition"
                    >
                        <span>+ Buat Konten Baru</span>
                    </button>
                </div>

                <!-- SEGMENT SWITCHER: KONTEN BERJALAN VS ARSIP -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            @click="mainSegment = 'active'"
                            class="px-4 py-2 rounded-xl text-xs font-extrabold transition flex items-center space-x-1.5"
                            :class="mainSegment === 'active' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        >
                            <span>Konten Berjalan</span>
                            <span class="w-5 h-5 rounded-full bg-white/20 text-[10px] inline-flex items-center justify-center font-bold">
                                {{ activeContents.length }}
                            </span>
                        </button>
                        <button
                            type="button"
                            @click="mainSegment = 'archive'"
                            class="px-4 py-2 rounded-xl text-xs font-extrabold transition flex items-center space-x-1.5"
                            :class="mainSegment === 'archive' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        >
                            <span>Arsip Selesai</span>
                            <span class="w-5 h-5 rounded-full bg-white/20 text-[10px] inline-flex items-center justify-center font-bold">
                                {{ archivedContents.length }}
                            </span>
                        </button>
                    </div>

                    <span class="text-[11px] text-slate-400 hidden sm:inline">
                        {{ mainSegment === 'active' ? 'Fokus tugas harian' : 'Riwayat konten yang sudah tayang' }}
                    </span>
                </div>

                <!-- SUB-FILTER UNTUK KONTEN BERJALAN -->
                <div v-if="mainSegment === 'active'" class="flex items-center space-x-1.5 overflow-x-auto pb-1 no-scrollbar text-xs">
                    <button
                        @click="activeSubFilter = 'all'"
                        class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap"
                        :class="activeSubFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Semua Aktif ({{ activeContents.length }})
                    </button>
                    <button
                        @click="activeSubFilter = 'shooting'"
                        class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap"
                        :class="activeSubFilter === 'shooting' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Syuting Lapangan
                    </button>
                    <button
                        @click="activeSubFilter = 'editing'"
                        class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap"
                        :class="activeSubFilter === 'editing' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Editing Video
                    </button>
                    <button
                        @click="activeSubFilter = 'review'"
                        class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap"
                        :class="activeSubFilter === 'review' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Menunggu Review
                    </button>
                    <button
                        @click="activeSubFilter = 'ready'"
                        class="px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap"
                        :class="activeSubFilter === 'ready' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Siap Tayang
                    </button>
                </div>

                <!-- DAFTAR KARTU KONTEN -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div
                        v-for="item in displayedContents"
                        :key="item.id"
                        class="bg-white rounded-2xl border border-slate-200 hover:border-emerald-600 hover:shadow-md transition p-4 flex flex-col justify-between"
                    >
                        <div>
                            <!-- Header Kartu -->
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center space-x-1.5">
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ item.code || 'CNT' }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 text-white">
                                        {{ channelLabel(item.channel) }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border" :class="item.status_color">
                                    {{ item.status_label }}
                                </span>
                            </div>

                            <!-- Judul Konten -->
                            <h3 class="font-extrabold text-sm text-slate-900 leading-snug">
                                {{ item.title }}
                            </h3>

                            <!-- Hook Preview -->
                            <div v-if="item.hook" class="mt-2 p-2 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600 italic line-clamp-2">
                                "{{ item.hook }}"
                            </div>

                            <!-- Progress List Footage & To-Do -->
                            <div class="mt-3.5 space-y-2 pt-2 border-t border-slate-100">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                                        <span>List Footage Kamera: <strong>{{ item.captured_footages_count }}/{{ item.footages_count }} Shot</strong></span>
                                        <span class="text-[10px] font-mono text-emerald-700 font-bold">
                                            {{ item.footages_count > 0 ? Math.round((item.captured_footages_count / item.footages_count) * 100) : 0 }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div
                                            class="bg-emerald-600 h-1.5 rounded-full transition-all duration-300"
                                            :style="{ width: `${item.footages_count > 0 ? (item.captured_footages_count / item.footages_count) * 100 : 0}%` }"
                                        ></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-600 mb-1">
                                        <span>To-Do Produksi: <strong>{{ item.completed_todos_count }}/{{ item.todos_count }} Selesai</strong></span>
                                        <span class="text-[10px] font-mono text-blue-700 font-bold">
                                            {{ item.todos_count > 0 ? Math.round((item.completed_todos_count / item.todos_count) * 100) : 0 }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div
                                            class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                                            :style="{ width: `${item.todos_count > 0 ? (item.completed_todos_count / item.todos_count) * 100 : 0}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bawah: Penanggung Jawab & Tombol Buka -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <div class="flex items-center space-x-1.5 min-w-0">
                                <div class="w-6 h-6 rounded-full bg-slate-800 text-white text-[9px] flex items-center justify-center font-black shrink-0">
                                    {{ (item.assignee?.name || 'A').substring(0, 1) }}
                                </div>
                                <span class="font-bold text-slate-700 text-[11px] truncate">
                                    {{ item.assignee?.name || 'Belum Ditugaskan' }}
                                </span>
                            </div>

                            <button
                                type="button"
                                @click="openContentDetail(item)"
                                class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 active:scale-95 text-emerald-800 font-extrabold text-[11px] transition shrink-0"
                            >
                                Buka Detail & List &rarr;
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="displayedContents.length === 0" class="text-center py-8 text-xs text-slate-400">
                    {{ mainSegment === 'active' ? 'Tidak ada konten aktif pada kategori ini.' : 'Belum ada konten yang diarsipkan.' }}
                </div>
            </section>

            <!-- 3. SECTION: PANDUAN TUGAS (SOP PRODUKSI) -->
            <section v-if="activeTab === 'all' || activeTab === 'guide'" class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                            Panduan SOP Produksi Konten
                        </h2>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                            5 Langkah Standar
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Alur kerja terstandarisasi untuk tim kreator dan lapangan agar hasil konten konsisten.
                    </p>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="item in guideSteps"
                        :key="item.step"
                        class="rounded-xl border transition overflow-hidden"
                        :class="activeGuideStep === item.step ? 'border-emerald-300 bg-emerald-50/20' : 'border-slate-200 bg-white'"
                    >
                        <button
                            type="button"
                            @click="activeGuideStep = activeGuideStep === item.step ? null : item.step"
                            class="w-full text-left p-3.5 sm:p-4 flex items-center justify-between gap-3 focus:outline-none"
                        >
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-7 h-7 rounded-xl flex items-center justify-center font-black text-xs shrink-0"
                                    :class="activeGuideStep === item.step ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-700'"
                                >
                                    {{ item.step }}
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-xs sm:text-sm text-slate-900">
                                        {{ item.title }}
                                    </h4>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ item.desc }}
                                    </div>
                                </div>
                            </div>
                            <span class="text-slate-400 text-xs shrink-0 font-mono">
                                {{ activeGuideStep === item.step ? 'Tutup' : 'Buka' }}
                            </span>
                        </button>

                        <div v-if="activeGuideStep === item.step" class="px-4 pb-4 pt-1 border-t border-slate-100 space-y-2">
                            <div class="text-xs font-bold text-slate-800 uppercase tracking-wider text-[10px] pt-1">
                                Checklist Eksekusi:
                            </div>
                            <ul class="space-y-1.5">
                                <li
                                    v-for="(point, idx) in item.checklist"
                                    :key="idx"
                                    class="text-xs text-slate-700 flex items-start space-x-2"
                                >
                                    <span class="text-emerald-700 font-bold shrink-0 mt-0.5">[OK]</span>
                                    <span>{{ point }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4. SECTION: KALENDER TAYANG -->
            <section v-if="activeTab === 'all' || activeTab === 'calendar'" class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-2xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                Kalender Tayang Konten
                            </h2>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                Jadwal Terpadu
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Target publikasi untuk setiap konten video di media sosial.
                        </p>
                    </div>
                    <div class="text-xs font-bold text-slate-600">
                        Bulan: Oktober 2026
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center text-xs">
                    <div class="font-bold text-slate-400 py-1 text-[10px] uppercase">Sen</div>
                    <div class="font-bold text-slate-400 py-1 text-[10px] uppercase">Sel</div>
                    <div class="font-bold text-slate-400 py-1 text-[10px] uppercase">Rab</div>
                    <div class="font-bold text-slate-400 py-1 text-[10px] uppercase">Kam</div>
                    <div class="font-bold text-slate-400 py-1 text-[10px] uppercase">Jum</div>
                    <div class="font-bold text-slate-400 py-1 text-[10px] uppercase">Sab</div>
                    <div class="font-bold text-slate-400 py-1 text-[10px] uppercase">Min</div>

                    <button
                        v-for="d in 31"
                        :key="d"
                        type="button"
                        @click="selectedCalendarDate = d"
                        class="p-2 sm:p-2.5 rounded-xl border transition flex flex-col items-center justify-between h-14 sm:h-16 relative"
                        :class="[
                            selectedCalendarDate === d ? 'border-emerald-700 bg-emerald-50 text-emerald-950 font-black ring-2 ring-emerald-600/30' : 'border-slate-100 hover:bg-slate-50 text-slate-700',
                            d === 26 ? 'bg-emerald-50/50 font-extrabold' : ''
                        ]"
                    >
                        <span class="text-xs">{{ d }}</span>
                        <div class="flex items-center space-x-1 mt-auto">
                            <span v-if="[26, 27, 28, 29, 31].includes(d)" class="w-1.5 h-1.5 rounded-full bg-emerald-700"></span>
                        </div>
                    </button>
                </div>
            </section>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL DETAIL KONTEN: LIST FOOTAGE, TO-DO PRODUKSI & PENUGASAN             -->
        <!-- ========================================================================= -->
        <Modal :show="isDetailModalOpen" @close="closeContentDetail" max-width="2xl">
            <div v-if="activeContent" class="p-5 sm:p-6 space-y-4 max-h-[85vh] overflow-y-auto text-left">
                <!-- Modal Top Header -->
                <div class="flex items-start justify-between pb-3 border-b border-slate-100 gap-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                {{ activeContent.code }}
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 text-white">
                                {{ channelLabel(activeContent.channel) }}
                            </span>
                        </div>
                        <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug">
                            {{ activeContent.title }}
                        </h3>
                    </div>

                    <button @click="closeContentDetail" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Status & Penugasan (Bisa Saling Menugaskan Antar User) -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-500 text-[10px] uppercase mb-1">Status Alur Konten:</label>
                        <select
                            :value="activeContent.status"
                            @change="updateContentField('status', $event.target.value)"
                            class="w-full text-xs font-bold py-1.5 px-3 rounded-xl border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 bg-white"
                        >
                            <option value="idea">Ide Konten</option>
                            <option value="shooting">Sedang Syuting Lapangan</option>
                            <option value="editing">Sedang Editing Video</option>
                            <option value="review">Menunggu Review Tim</option>
                            <option value="ready">Siap Tayang</option>
                            <option value="published">Sudah Tayang (Otomatis Masuk Arsip)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-500 text-[10px] uppercase mb-1">Ditugaskan Kepada (PIC):</label>
                        <select
                            :value="activeContent.assignee?.id"
                            @change="updateContentField('assignee_id', $event.target.value)"
                            class="w-full text-xs font-bold py-1.5 px-3 rounded-xl border-slate-300 focus:border-emerald-600 focus:ring-emerald-600 bg-white"
                        >
                            <option :value="null">-- Belum Ditugaskan --</option>
                            <option v-for="m in workspaceMembers" :key="m.id" :value="m.id">
                                {{ m.name }} ({{ m.role }})
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Hook Box -->
                <div v-if="activeContent.hook" class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                    <span class="font-extrabold text-slate-900 text-[11px] block mb-0.5">Hook Pembuka 3 Detik:</span>
                    <p class="text-slate-700 italic">"{{ activeContent.hook }}"</p>
                </div>

                <!-- Tab Switcher: List Footage vs To-Do -->
                <div class="flex items-center space-x-2 border-b border-slate-200 pb-2">
                    <button
                        type="button"
                        @click="detailSubTab = 'footage'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center space-x-1.5"
                        :class="detailSubTab === 'footage' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        <span>List Footage ({{ activeContent.footages.length }})</span>
                        <span class="text-[10px] opacity-80">({{ activeContent.captured_footages_count }} Terambil)</span>
                    </button>
                    <button
                        type="button"
                        @click="detailSubTab = 'todos'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center space-x-1.5"
                        :class="detailSubTab === 'todos' ? 'bg-emerald-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        <span>To-Do Produksi ({{ activeContent.todos.length }})</span>
                        <span class="text-[10px] opacity-80">({{ activeContent.completed_todos_count }} Selesai)</span>
                    </button>
                </div>

                <!-- SUB-TAB 1: LIST FOOTAGE -->
                <div v-if="detailSubTab === 'footage'" class="space-y-3">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Checklist adegan kamera di lokasi:</span>
                        <span class="font-bold text-emerald-800">
                            {{ activeContent.captured_footages_count }}/{{ activeContent.footages.length }} Selesai
                        </span>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="f in activeContent.footages"
                            :key="f.id"
                            class="p-3 rounded-xl border transition flex items-start justify-between gap-3 text-xs"
                            :class="f.is_captured ? 'bg-emerald-50/50 border-emerald-200' : 'bg-white border-slate-200'"
                        >
                            <div class="flex items-start space-x-3 min-w-0">
                                <button
                                    type="button"
                                    @click="toggleFootage(f)"
                                    class="w-5 h-5 rounded-lg border flex items-center justify-center shrink-0 mt-0.5 transition"
                                    :class="f.is_captured ? 'bg-emerald-700 border-emerald-700 text-white' : 'border-slate-300 bg-white hover:border-emerald-600'"
                                >
                                    <svg v-if="f.is_captured" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>

                                <div class="min-w-0">
                                    <div class="flex items-center space-x-1.5 mb-0.5">
                                        <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ f.shot_type }}
                                        </span>
                                        <span class="font-bold text-slate-900" :class="{ 'line-through text-slate-400': f.is_captured }">
                                            {{ f.title }}
                                        </span>
                                    </div>
                                    <p v-if="f.visual_notes" class="text-[11px] text-slate-500">
                                        {{ f.visual_notes }}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="deleteFootage(f.id)"
                                class="text-slate-300 hover:text-rose-600 transition p-1 shrink-0"
                                title="Hapus footage"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tambah Shot Form -->
                    <form @submit.prevent="submitNewFootage" class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                        <div class="font-bold text-xs text-slate-700">
                            + Tambah Shot Adegan Baru
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <input
                                v-model="newFootageTitle"
                                type="text"
                                placeholder="Judul shot adegan..."
                                class="col-span-2 text-xs rounded-xl border-slate-300 focus:border-emerald-600 focus:ring-emerald-600"
                                required
                            />
                            <select
                                v-model="newFootageShotType"
                                class="text-xs rounded-xl border-slate-300 focus:border-emerald-600 focus:ring-emerald-600"
                            >
                                <option value="wide">Wide Shot</option>
                                <option value="medium">Medium Shot</option>
                                <option value="close_up">Close-Up</option>
                                <option value="macro">Macro Lens</option>
                                <option value="drone">Drone View</option>
                                <option value="pov">POV Handheld</option>
                            </select>
                        </div>
                        <input
                            v-model="newFootageNotes"
                            type="text"
                            placeholder="Catatan visual kamera (opsional)..."
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-emerald-600 focus:ring-emerald-600"
                        />
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="isAddingFootage"
                                class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold transition"
                            >
                                {{ isAddingFootage ? 'Menyimpan...' : '+ Tambah ke List' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- SUB-TAB 2: TO-DO PRODUKSI -->
                <div v-if="detailSubTab === 'todos'" class="space-y-3">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>Checklist tahapan kerja:</span>
                        <span class="font-bold text-blue-700">
                            {{ activeContent.completed_todos_count }}/{{ activeContent.todos.length }} Selesai
                        </span>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="t in activeContent.todos"
                            :key="t.id"
                            class="p-3 rounded-xl border transition flex items-center justify-between gap-3 text-xs"
                            :class="t.is_completed ? 'bg-blue-50/50 border-blue-200' : 'bg-white border-slate-200'"
                        >
                            <div class="flex items-center space-x-3 min-w-0">
                                <button
                                    type="button"
                                    @click="toggleTodo(t)"
                                    class="w-5 h-5 rounded-lg border flex items-center justify-center shrink-0 transition"
                                    :class="t.is_completed ? 'bg-blue-700 border-blue-700 text-white' : 'border-slate-300 bg-white hover:border-blue-600'"
                                >
                                    <svg v-if="t.is_completed" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <span class="font-bold text-slate-900 truncate" :class="{ 'line-through text-slate-400': t.is_completed }">
                                    {{ t.title }}
                                </span>
                            </div>

                            <button
                                type="button"
                                @click="deleteTodo(t.id)"
                                class="text-slate-300 hover:text-rose-600 transition p-1 shrink-0"
                                title="Hapus to-do"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tambah Todo Form -->
                    <form @submit.prevent="submitNewTodo" class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center space-x-2">
                        <input
                            v-model="newTodoTitle"
                            type="text"
                            placeholder="Tulis to-do baru..."
                            class="flex-1 text-xs rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600"
                            required
                        />
                        <button
                            type="submit"
                            :disabled="isAddingTodo"
                            class="px-3.5 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-xl text-xs font-bold transition shrink-0"
                        >
                            {{ isAddingTodo ? 'Menyimpan...' : '+ Tambah' }}
                        </button>
                    </form>
                </div>

                <!-- Footer Modal: Aksi Arsip & Tutup -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <button
                            v-if="!isArchived(activeContent)"
                            type="button"
                            @click="archiveContent"
                            class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition"
                        >
                            Pindahkan ke Arsip
                        </button>
                        <button
                            v-else
                            type="button"
                            @click="restoreFromArchive"
                            class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 transition"
                        >
                            Pulihkan ke Konten Aktif
                        </button>
                    </div>

                    <SecondaryButton @click="closeContentDetail" class="text-xs rounded-xl">
                        Tutup
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

        <!-- ========================================================================= -->
        <!-- MODAL: BUAT KONTEN BARU                                                   -->
        <!-- ========================================================================= -->
        <Modal :show="isCreateContentModalOpen" @close="isCreateContentModalOpen = false" max-width="md">
            <div class="p-5 sm:p-6 space-y-4 text-left">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm sm:text-base font-black text-slate-900">
                        + Buat Konten Video Baru
                    </h3>
                    <button @click="isCreateContentModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitNewContent" class="space-y-3.5">
                    <div>
                        <InputLabel for="title" value="Judul Konten Video" class="font-bold text-xs" />
                        <TextInput
                            id="title"
                            v-model="newContentForm.title"
                            type="text"
                            class="mt-1 block w-full text-xs rounded-xl"
                            placeholder="Contoh: Video Reel Resep Kopi Aren Dingin"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <InputLabel for="channel" value="Platform Tayang" class="font-bold text-xs" />
                            <select
                                id="channel"
                                v-model="newContentForm.channel"
                                class="mt-1 block w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-emerald-600 focus:ring-emerald-600"
                            >
                                <option value="tiktok">TikTok</option>
                                <option value="instagram">Instagram Reels</option>
                                <option value="youtube">YouTube Shorts</option>
                            </select>
                        </div>

                        <div>
                            <InputLabel for="status" value="Status Awal" class="font-bold text-xs" />
                            <select
                                id="status"
                                v-model="newContentForm.status"
                                class="mt-1 block w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-emerald-600 focus:ring-emerald-600"
                            >
                                <option value="idea">Ide Konten</option>
                                <option value="shooting">Syuting Lapangan</option>
                                <option value="editing">Editing Video</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="assignee" value="Tugaskan Kepada (PIC)" class="font-bold text-xs" />
                        <select
                            id="assignee"
                            v-model="newContentForm.assignee_id"
                            class="mt-1 block w-full rounded-xl border-slate-300 text-xs font-semibold focus:border-emerald-600 focus:ring-emerald-600"
                        >
                            <option :value="null">-- Pilih Anggota Tim (Bebas Ditugaskan) --</option>
                            <option v-for="m in workspaceMembers" :key="m.id" :value="m.id">
                                {{ m.name }} ({{ m.role }})
                            </option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">
                            Semua anggota tim dapat saling menugaskan tugas rekaman dan editing tanpa hambatan hierarki.
                        </p>
                    </div>

                    <div>
                        <InputLabel for="hook" value="Hook Pembuka 3 Detik (Opsional)" class="font-bold text-xs" />
                        <TextInput
                            id="hook"
                            v-model="newContentForm.hook"
                            type="text"
                            class="mt-1 block w-full text-xs rounded-xl"
                            placeholder="Contoh: Ini alasan kenapa gula aren asli lebih sehat..."
                        />
                    </div>

                    <div class="mt-5 flex justify-end space-x-2 pt-3 border-t border-slate-100">
                        <SecondaryButton @click="isCreateContentModalOpen = false" class="text-xs rounded-xl">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton type="submit" class="text-xs font-bold bg-emerald-700 hover:bg-emerald-800 rounded-xl">
                            Buat Konten
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
