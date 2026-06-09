# School Management System

Sistem manajemen sekolah berbasis web untuk SD/MI. Mencakup manajemen akademik, keuangan, absensi guru, honor guru, administrasi surat, PPDB, dan halaman publik.

Built with **Laravel 13** + **Vue 3** + **Inertia.js**.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.3+) |
| Frontend | Vue 3 + Inertia.js (SSR-ready) |
| Auth | Laravel Breeze + Spatie Permission |
| Database | MySQL 8+ |
| Payment | Midtrans (Snap) |
| WhatsApp | Fonnte API |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/laravel-excel |
| Storage | Local public disk |
| UI | Tailwind CSS |
| Testing | PHPUnit |
| Queue | Database driver |

---

## Roles

| Role | Deskripsi |
|------|-----------|
| `kamad` | Kepala Madrasah — approve tahun ajaran, publish raport, approve surat, monitor semua |
| `operator` | Super admin — input semua master data, kelola PPDB, berita, galeri, ekskul |
| `tu_keuangan` | Staf keuangan — kelola tagihan, pembayaran, honor guru |
| `guru` | Guru kelas/bidang — input nilai, catatan raport, absensi harian |
| `siswa` | Wali murid — lihat nilai, bayar tagihan online/cash, request surat |
| *(publik)* | Tamu — landing page, berita, galeri, ekskul, PPDB, verifikasi dokumen |

---

## Modul

| Modul | Role | Docs |
|-------|------|------|
| Master Data | Operator | [docs/master-data.md](docs/master-data.md) |
| PPDB | Operator, Kamad, Publik | [docs/master-data.md](docs/master-data.md#8-ppdb) |
| Akademik | Operator, Guru, Kamad, Siswa | [docs/akademik.md](docs/akademik.md) |
| Keuangan & Tagihan | TU Keuangan, Siswa | [docs/keuangan.md](docs/keuangan.md) |
| Honor Guru | TU Keuangan, Kamad | [docs/keuangan.md](docs/keuangan.md#4-honor-guru-honorarium) |
| Absensi Guru | Guru, Operator, Kamad | [docs/keuangan.md](docs/keuangan.md#5-absensi-guru) |
| Surat | Operator, Kamad, Siswa | [docs/surat.md](docs/surat.md) |
| Halaman Publik | Publik | [docs/master-data.md](docs/master-data.md#9-halaman-publik) |

---

## Arsitektur

```
Request → FormRequest (validasi) → Controller → Service → Model → Response
```

- **Controller** — tipis, hanya handle HTTP request/response
- **Service** — semua business logic
- **Model** — relasi + casts, menggunakan PHP Attributes (Laravel 13 style)

```
app/
├── Http/Controllers/
│   ├── Kamad/        ← route /kamad/*
│   ├── Operator/     ← route /operator/*
│   ├── Keuangan/     ← route /keuangan/*
│   ├── Guru/         ← route /guru/*
│   ├── Siswa/        ← route /siswa/*
│   └── (root)        ← publik + auth + profile + notifikasi
├── Models/
├── Services/
├── Jobs/             ← SendHonorariumSlipJob, SendSppReminderJob
└── Helpers/          ← QrCodeHelper

[File_Tree.md](File_Tree.md) | Folder Arsitektur Lengkap 
```

---

## Setup & Installation

### Requirements
- PHP 8.3+
- Composer 2+
- Node.js 18+
- MySQL 8+

### Steps

```bash
# 1. Clone repo
git clone <repo-url>
cd school-management

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Database
php artisan migrate
php artisan db:seed

# 5. Storage link
php artisan storage:link

# 6. Build frontend
npm run build

# 7. Run
php artisan serve
# Di tab terpisah (untuk queue):
php artisan queue:work
```

### Environment Variables

```env
# Database
DB_DATABASE=sekolah-app
DB_USERNAME=root
DB_PASSWORD=

# Midtrans (payment gateway)
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false

# Fonnte (WhatsApp notifications)
FONNTE_TOKEN=your_fonnte_token

# Queue
QUEUE_CONNECTION=database
```

---

## Default Users (Seeder)

| Role | Email | Password |
|------|-------|----------|
| Kamad | kamad@sekolah.test | password |
| Operator | operator@sekolah.test | password |
| TU Keuangan | keuangan@sekolah.test | password |
| Guru | guru@sekolah.test | password |
| Wali Murid | siswa@sekolah.test | password |

---

## Public Routes (Tanpa Auth)

| URL | Deskripsi |
|-----|-----------|
| `/` | Landing page |
| `/tentang` | Profil sekolah |
| `/galeri` | Galeri foto |
| `/ekskul` | Daftar ekskul |
| `/berita` | Berita & pengumuman |
| `/ppdb` | Formulir pendaftaran PPDB |
| `/ppdb/cek` | Cek status PPDB |
| `/verify/{code}` | Verifikasi keaslian surat |
| `/receipt/{code}` | Verifikasi kwitansi pembayaran |
| `/slip-honor/{code}` | Verifikasi slip honor guru |
| `/verify-raport/{code}` | Verifikasi raport siswa |

---

## Testing

```bash
# Semua test
php artisan test

# Unit only
php artisan test --testsuite=Unit

# Feature only
php artisan test --testsuite=Feature
```

**Coverage saat ini:** 169 passed / 308 assertions

---

## Dokumentasi Lengkap

| File | Isi |
|------|-----|
| [docs/master-data.md](docs/master-data.md) | Tahun ajaran, guru, kelas, siswa, mapel, PPDB, halaman publik |
| [docs/akademik.md](docs/akademik.md) | Komponen nilai, input nilai, raport, verifikasi |
| [docs/keuangan.md](docs/keuangan.md) | Tagihan, pembayaran, honor guru, absensi, WhatsApp |
| [docs/surat.md](docs/surat.md) | Surat keterangan, pemberitahuan, approval, barcode |
