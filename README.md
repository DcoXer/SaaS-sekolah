# School Management System

Sistem manajemen sekolah berbasis web untuk SD/MI yang mencakup manajemen akademik, keuangan, dan administrasi surat menyurat.

Built with **Laravel 13** + **Vue 3** + **Inertia.js**.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.3+) |
| Frontend | Vue 3 + Inertia.js |
| Auth | Laravel Breeze + Spatie Permission |
| Database | MySQL |
| Payment | Midtrans |
| UI | Tailwind CSS |
| Testing | PHPUnit |

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

## Quick Start

```bash
# Install dependencies
composer install && npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate && php artisan db:seed

# Storage & assets
php artisan storage:link
npm run build

# Run server
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
php artisan test
```

---

## Dokumentasi Lengkap

Lihat folder [`docs/`](docs/) untuk dokumentasi detail per modul.
