# School Management System

Sistem manajemen sekolah berbasis web untuk SD/MI yang mencakup manajemen akademik, keuangan, dan administrasi surat menyurat. Dibangun dengan Laravel 13 + Vue 3 + Inertia.js.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.3+) |
| Frontend | Vue 3 + Inertia.js |
| Auth | Laravel Breeze + Spatie Permission |
| Database | MySQL |
| Payment | Midtrans |
| Storage | Local (public disk) |
| UI | Tailwind CSS |
| Testing | PHPUnit / Pest |

---

## Roles

| Role | Deskripsi |
|------|-----------|
| `kamad` | Kepala Madrasah — approve, monitor, TTD digital |
| `operator` | Super admin — input master data, kelola sistem |
| `tu_keuangan` | Staf keuangan — kelola tagihan dan pembayaran |
| `guru` | Guru kelas / mapel — input nilai, catatan raport |
| `siswa` | Wali murid — lihat nilai, bayar tagihan, request surat |

---

## Modul

| Modul | Deskripsi | Docs |
|-------|-----------|------|
| Master Data | Tahun ajaran, kelas, guru, siswa, mapel | [docs/master-data.md](docs/master-data.md) |
| Akademik | Komponen nilai, input nilai, raport | [docs/akademik.md](docs/akademik.md) |
| Keuangan | Tagihan, pembayaran, Midtrans | [docs/keuangan.md](docs/keuangan.md) |
| Surat | Surat keterangan, pemberitahuan, TTD digital | [docs/surat.md](docs/surat.md) |

---

## Struktur Folder

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Kamad/
│   │   ├── Operator/
│   │   ├── Keuangan/
│   │   ├── Guru/
│   │   └── Siswa/
│   └── Requests/
├── Models/
└── Services/

database/
├── migrations/
└── seeders/

tests/
├── Unit/
│   └── Services/
└── Feature/
    └── Http/Controllers/
```

---

## Setup & Installation

### Requirements
- PHP 8.3+
- Composer
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

# 4. Database setup
# Buat database MySQL, update .env
php artisan migrate
php artisan db:seed

# 5. Storage link
php artisan storage:link

# 6. Build assets
npm run build

# 7. Run server
php artisan serve
```

### Environment Variables

```env
DB_DATABASE=school_management
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
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

## Testing

```bash
# Run semua test
php artisan test

# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature
```

**Current test coverage:**
- Unit tests: 105 passed
- Feature tests: 73 passed
- Total: 178 passed / 327 assertions

---

## Routes Overview

```bash
# Lihat semua route
php artisan route:list
```

Semua route authenticated menggunakan prefix sesuai role:
- `/kamad/*` — Kamad only
- `/operator/*` — Operator only
- `/keuangan/*` — TU Keuangan only
- `/guru/*` — Guru only
- `/siswa/*` — Siswa/Wali only

---

## Architecture

Pattern yang digunakan: **Controller → Service → Model**

- **Controller** — thin, hanya handle request/response
- **Service** — business logic
- **Model** — database interaction + relationships

```
Request → FormRequest (validation) → Controller → Service → Model → Response
```
