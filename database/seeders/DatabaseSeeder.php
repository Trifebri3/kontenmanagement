<?php

namespace Database\Seeders;

use App\Enums\MemberStatus;
use App\Enums\WorkspaceRole;
use App\Models\ActivityLog;
use App\Models\ContentItem;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Core Users
        $budi = User::updateOrCreate(
            ['email' => 'admin@arenlestari.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $rian = User::updateOrCreate(
            ['email' => 'lead@arenlestari.com'],
            [
                'name' => 'Rian Pratama',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $dimas = User::updateOrCreate(
            ['email' => 'field@arenlestari.com'],
            [
                'name' => 'Dimas Anggara',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $siti = User::updateOrCreate(
            ['email' => 'editor@arenlestari.com'],
            [
                'name' => 'Siti Rahma',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $dewi = User::updateOrCreate(
            ['email' => 'approver@arenlestari.com'],
            [
                'name' => 'Dewi Lestari',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Workspace 1: Aren Lestari
        $arenWorkspace = Workspace::updateOrCreate(
            ['slug' => 'aren-lestari'],
            [
                'name' => 'Aren Lestari',
                'owner_id' => $budi->id,
                'timezone' => 'Asia/Jakarta',
                'settings' => [
                    'brand_color' => '#10B981',
                    'description' => 'Produksi gula aren organik dan ekowisata perkebunan',
                ],
            ]
        );

        $arenWorkspace->users()->sync([
            $budi->id => ['role' => WorkspaceRole::Admin->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()],
            $rian->id => ['role' => WorkspaceRole::Member->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()],
            $dimas->id => ['role' => WorkspaceRole::Member->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()],
            $siti->id => ['role' => WorkspaceRole::Member->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()],
            $dewi->id => ['role' => WorkspaceRole::Member->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()],
        ]);

        ActivityLog::record(
            workspaceId: $arenWorkspace->id,
            actorId: $budi->id,
            entityType: 'workspace',
            entityId: $arenWorkspace->id,
            action: 'workspace_initialized',
            after: ['name' => 'Aren Lestari'],
        );

        // 2.1 Demo Content 1: Video Panen Nira (Syuting)
        $content1 = ContentItem::updateOrCreate(
            ['code' => 'AL-01', 'workspace_id' => $arenWorkspace->id],
            [
                'title' => 'Video Reel: Proses Deres & Panen Nira Pagi',
                'channel' => 'tiktok',
                'status' => 'shooting',
                'hook' => 'Pernah lihat air pohon disulap jadi pemanis alami paling gurih?',
                'concept' => 'Dokumentasi cinematic panen nira di perkebunan aren organik saat sunrise.',
                'assignee_id' => $dimas->id,
                'created_by' => $rian->id,
                'target_date' => now()->addDays(1)->setTime(18, 0),
                'notes' => 'Fokus pada detail tetesan air nira jernih dan suasana asri perkebunan.',
            ]
        );

        $content1->footages()->delete();
        $content1->footages()->createMany([
            ['title' => 'Drone shot hamparan pohon aren diselimuti kabut pagi', 'visual_notes' => 'Ambil wide angle saat matahari terbit (golden hour)', 'shot_type' => 'drone', 'is_captured' => true, 'order' => 1],
            ['title' => 'Pak Tani memanjat pohon aren dengan tangga bambu', 'visual_notes' => 'Follow shot dari bawah ke atas', 'shot_type' => 'medium', 'is_captured' => true, 'order' => 2],
            ['title' => 'Close-up tetesan nira bening mengalir ke bumbung bambu', 'visual_notes' => 'Macro lens 60fps slow-motion', 'shot_type' => 'macro', 'is_captured' => false, 'order' => 3],
            ['title' => 'Penuangan nira segar ke wajan perebusan tembaga', 'visual_notes' => 'Asap uap wajan terlihat dramatis', 'shot_type' => 'close_up', 'is_captured' => false, 'order' => 4],
            ['title' => 'Pak Tani mencicipi nira segar dan tersenyum puas', 'visual_notes' => 'Ekspresi natural senyum menghadap kamera', 'shot_type' => 'medium', 'is_captured' => false, 'order' => 5],
        ]);

        $content1->todos()->delete();
        $content1->todos()->createMany([
            ['title' => 'Tes audio mic wireless & bersihkan lensa kamera HP', 'is_completed' => true, 'order' => 1],
            ['title' => 'Rekam voiceover kutipan suara Pak Tani', 'is_completed' => false, 'order' => 2],
            ['title' => 'Seleksi footage terbaik dan sync tempo beat musik', 'is_completed' => false, 'order' => 3],
            ['title' => 'Pasang subtitle dinamis warna kuning di tengah layar', 'is_completed' => false, 'order' => 4],
            ['title' => 'Review bersama tim sebelum jadwalkan tayang', 'is_completed' => false, 'order' => 5],
        ]);

        // 2.2 Demo Content 2: Edukasi Gula Aren (Editing)
        $content2 = ContentItem::updateOrCreate(
            ['code' => 'AL-02', 'workspace_id' => $arenWorkspace->id],
            [
                'title' => '3 Trik Membedakan Gula Aren Asli vs Campuran',
                'channel' => 'instagram',
                'status' => 'editing',
                'hook' => 'Jangan sampai tertipu! Ini cara tes keaslian gula aren cuma pakai air panas.',
                'concept' => 'Video edukasi eksperimen dapur langsung untuk membuktikan kemurnian gula aren.',
                'assignee_id' => $siti->id,
                'created_by' => $budi->id,
                'target_date' => now()->addDays(2)->setTime(12, 0),
                'notes' => 'Gunakan subtitle teks tegas dan pacing cepat agar retensi penonton tinggi.',
            ]
        );

        $content2->footages()->delete();
        $content2->footages()->createMany([
            ['title' => 'Dua mangkok gula aren bersanding (Asli vs Campuran)', 'visual_notes' => 'Pencahayaan terang, latar bersih', 'shot_type' => 'medium', 'is_captured' => true, 'order' => 1],
            ['title' => 'Menuangkan air mendidih ke kedua mangkok bersamaan', 'visual_notes' => 'Top-down angle', 'shot_type' => 'medium', 'is_captured' => true, 'order' => 2],
            ['title' => 'Close up larutan: asli larut sempurna tanpa sisa pasir/gula pasir', 'visual_notes' => 'Macro shot detail ke dasar gelas', 'shot_type' => 'macro', 'is_captured' => true, 'order' => 3],
            ['title' => 'Talenta memegang kemasan resmi Aren Lestari', 'visual_notes' => 'Kemasan terlihat jelas & rapi', 'shot_type' => 'close_up', 'is_captured' => false, 'order' => 4],
        ]);

        $content2->todos()->delete();
        $content2->todos()->createMany([
            ['title' => 'Script hook 3 detik selesai disepakati', 'is_completed' => true, 'order' => 1],
            ['title' => 'Shooting seluruh adegan eksperimen', 'is_completed' => true, 'order' => 2],
            ['title' => 'Potong jeda kosong (jump cut) tanpa hening', 'is_completed' => false, 'order' => 3],
            ['title' => 'Konfirmasi approval dari rekan tim', 'is_completed' => false, 'order' => 4],
        ]);

        // 2.3 Demo Content 3: Resep Kopi Aren (Review)
        $content3 = ContentItem::updateOrCreate(
            ['code' => 'AL-03', 'workspace_id' => $arenWorkspace->id],
            [
                'title' => 'Resep Es Kopi Susu Gula Aren Ala Barista Cafe',
                'channel' => 'tiktok',
                'status' => 'review',
                'hook' => 'Kopi susu kafe viral bisa kamu bikin sendiri cuma modal Rp 5.000!',
                'concept' => 'Video resep praktis pouring layer aesthetic yang memanjakan mata.',
                'assignee_id' => $rian->id,
                'created_by' => $dewi->id,
                'target_date' => now()->addDays(3)->setTime(19, 0),
                'notes' => 'Video sudah selesai diedit oleh Siti, sedang menunggu review rekan tim.',
            ]
        );

        $content3->footages()->delete();
        $content3->footages()->createMany([
            ['title' => 'Drizzle sirup gula aren kental di dinding gelas kaca', 'visual_notes' => 'Slow-mo pouring aesthetic', 'shot_type' => 'close_up', 'is_captured' => true, 'order' => 1],
            ['title' => 'Es batu dimasukkan bergemerincing', 'visual_notes' => 'Audio ice clinking jelas', 'shot_type' => 'medium', 'is_captured' => true, 'order' => 2],
            ['title' => 'Tuang fresh milk perlahan hingga terbentuk layer putih', 'visual_notes' => 'Side view layer', 'shot_type' => 'medium', 'is_captured' => true, 'order' => 3],
            ['title' => 'Pouring espresso kental di layer paling atas', 'visual_notes' => 'Gradasi warna kopi susu sempurna', 'shot_type' => 'close_up', 'is_captured' => true, 'order' => 4],
        ]);

        $content3->todos()->delete();
        $content3->todos()->createMany([
            ['title' => 'Shooting semua adegan selesai', 'is_completed' => true, 'order' => 1],
            ['title' => 'Editing video & pasang audio viral selesai', 'is_completed' => true, 'order' => 2],
            ['title' => 'Review caption & takaran resep di kolom komentar', 'is_completed' => false, 'order' => 3],
        ]);

        // 3. Workspace 2: AGRONEX
        $agronexWorkspace = Workspace::updateOrCreate(
            ['slug' => 'agronex'],
            [
                'name' => 'AGRONEX',
                'owner_id' => $budi->id,
                'timezone' => 'Asia/Jakarta',
                'settings' => [
                    'brand_color' => '#3B82F6',
                    'description' => 'Smart agriculture IoT and precision farming systems',
                ],
            ]
        );

        $agronexWorkspace->users()->sync([
            $budi->id => ['role' => WorkspaceRole::Admin->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()],
            $rian->id => ['role' => WorkspaceRole::Member->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()],
            $dimas->id => ['role' => WorkspaceRole::Member->value, 'status' => MemberStatus::Active->value, 'joined_at' => now()],
        ]);

        ActivityLog::record(
            workspaceId: $agronexWorkspace->id,
            actorId: $budi->id,
            entityType: 'workspace',
            entityId: $agronexWorkspace->id,
            action: 'workspace_initialized',
            after: ['name' => 'AGRONEX'],
        );
    }
}
