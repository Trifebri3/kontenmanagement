# COMP - Content Operations Management Platform

Platform terpadu untuk merencanakan, memproduksi, meninjau, menjadwalkan, dan memublikasikan konten dari berbagai perusahaan dan brand dalam satu sistem kolaboratif.

## Fitur Utama

- **Multi-Company & Multi-Brand**: Kelola beberapa perusahaan/klien dalam satu akun terpisah.
- **Struktur Tim Fleksibel**: Hanya ada peran Admin dan User tanpa hierarki birokrasi, memungkinkan penugasan tugas rekaman/editing secara horizontal antar rekan kerja.
- **Manajemen Berbasis Per Konten**:
  - Setiap konten video memiliki kode unik, target platform (TikTok, Reels, Shorts), dan hook pembuka 3 detik.
  - **List Footage / Shot List Lapangan**: Checklist adegan kamera dengan tipe shot (Wide, Macro, Drone, Close-up) yang bisa dicentang langsung saat syuting.
  - **To-Do Produksi**: Checklist tahapan kerja dari editing, subtitle, hingga review tim.
- **Auto-Sorting ke Arsip**: Konten yang selesai atau berstatus "Sudah Tayang" secara otomatis tersortir masuk ke tab Arsip agar antarmuka kerja harian tetap fokus dan bersih.
- **PWA & Offline-Ready**: Siap dipasang di layar utama HP (Progressive Web App) dengan service worker dan penyimpanan lokal saat jaringan internet di lapangan terbatas.
- **Google OAuth Login**: Dukungan login cepat menggunakan akun Google (Laravel Socialite).
- **Desain Bersih & Ramah Mobile**: Tata letak responsif, navigasi bawah bergaya aplikasi native, dan bebas emotikon.

## Teknologi

- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend**: Inertia.js v2 (Vue 3) + Tailwind CSS
- **Database**: MySQL
- **Autentikasi**: Laravel Breeze & Laravel Socialite (Google OAuth)
- **Pengujian**: PHPUnit (41 feature & unit tests)

## Cara Instalasi Lokal

1. **Clone repositori**:
   ```bash
   git clone https://github.com/Trifebri3/kontenmanagement.git
   cd kontenmanagement
   ```

2. **Instal dependensi**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Atur koneksi database dan kredensial Google OAuth pada file `.env`.*

4. **Jalankan Migrasi & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

5. **Build Aset Frontend**:
   ```bash
   npm run build
   ```

6. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui `http://localhost:8000`.
