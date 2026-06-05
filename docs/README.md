# School Management System — Dokumentasi

Sistem manajemen sekolah untuk SD/MI. Single tenant. Dibangun dengan Laravel 13 + Vue 3 + Inertia.js.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.3+) |
| Frontend | Vue 3 + Inertia.js |
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
| `kamad` | Kepala Madrasah — approve tahun ajaran, publish raport, approve surat, monitor |
| `operator` | Super admin — input master data, kelola PPDB, berita, galeri, ekskul |
| `tu_keuangan` | Staf keuangan — kelola tagihan, pembayaran, honor guru |
| `guru` | Ada dua tipe: `guru_kelas` (grade 1-3, semua mapel) dan `guru_bidang` (grade 4-6, mapel spesifik) |
| `siswa` | Wali murid — lihat nilai, bayar tagihan, request surat |

---

## Arsitektur

```
Request → FormRequest (validasi) → Controller → Service → Model → Response
```

### Folder Controllers
```
app/Http/Controllers/
├── Kamad/         → /kamad/*
├── Operator/      → /operator/*
├── Keuangan/      → /keuangan/*
├── Guru/          → /guru/*
├── Siswa/         → /siswa/*
└── (root)         → public + auth + profile + notifikasi
```

### Seluruh Services
| Service | Tanggung Jawab |
|---------|----------------|
| AcademicYearService | Tahun ajaran, promosi siswa, tutup tahun |
| TeacherService | CRUD guru, akun user |
| StudentService | CRUD siswa, assign kelas, alumni |
| ClassroomService | Kelas, wali kelas, assign guru/siswa |
| SubjectService | Mata pelajaran |
| TeacherSubjectService | Penugasan guru-mapel-kelas |
| TeachingHourService | Konfigurasi jam pelajaran + tarif honorarium |
| TeacherAttendanceService | Absensi harian, kalender, recap, completeness check |
| TeacherHonorariumService | Generate slip, mark paid, batch operations |
| PredicateConfigService | Konfigurasi predikat nilai (A/B/C/D) |
| AssessmentComponentService | Komponen penilaian per kelas/mapel/semester |
| StudentAssessmentService | Input nilai, kalkulasi nilai akhir |
| ReportCardService | Generate raport, publish, verifikasi |
| PaymentTypeService | Jenis tagihan (SPP, dll) |
| InvoiceService | Tagihan per siswa, kalkulasi status |
| PaymentService | Catat pembayaran, Midtrans callback |
| FinancialReportService | Laporan keuangan |
| LetterTypeService | Jenis surat |
| LetterTemplateService | Template surat dengan placeholder |
| LetterService | Surat keterangan & pemberitahuan |
| PpdbService | Pendaftaran PPDB, quota, status |
| SchoolSettingService | Pengaturan sekolah |
| SchoolPostService | Berita & pengumuman |
| ExtracurricularService | Ekskul, foto, prestasi |
| SchoolGalleryService | Galeri foto publik |
| NotificationService | Notifikasi in-app |
| WhatsAppService | Kirim pesan/dokumen via Fonnte |

---

## Modul & Dokumentasi

| Modul | File |
|-------|------|
| Master Data, PPDB, Halaman Publik | [master-data.md](master-data.md) |
| Akademik (nilai, raport) | [akademik.md](akademik.md) |
| Keuangan, Honor Guru, Absensi | [keuangan.md](keuangan.md) |
| Surat | [surat.md](surat.md) |

---

## Routes Overview

### Public (tanpa auth)
```
GET  /                     Landing page
GET  /tentang              Profil sekolah
GET  /galeri               Galeri foto
GET  /ekskul               Daftar ekskul
GET  /ekskul/{id}          Detail ekskul
GET  /berita               Berita & pengumuman
GET  /berita/{slug}        Detail berita
GET  /ppdb                 Formulir PPDB
POST /ppdb/daftar          Submit pendaftaran PPDB
GET  /ppdb/cek             Cek status PPDB
GET  /verify/{code}        Verifikasi surat
GET  /receipt/{code}       Verifikasi kwitansi
GET  /slip-honor/{code}    Verifikasi slip honor guru
GET  /verify-raport/{code} Verifikasi raport
POST /midtrans/callback    Midtrans webhook
```

Semua route publik dilindungi `throttle` (rate limiting).

### Authenticated
```
/kamad/*      → role: kamad
/operator/*   → role: operator
/keuangan/*   → role: tu_keuangan
/guru/*       → role: guru
/siswa/*      → role: siswa
/profile      → semua role
/notifications → semua role
```

---

## Key Business Rules

### Academic Year
- Hanya satu `active` dalam satu waktu
- Operator buat → `pending`, Kamad approve → `active`, yang lama → `closed`
- Saat approve → trigger `promoteStudents()` otomatis (grade +1, grade 6 jadi alumni)

### Alumni
- Grade 6 yang naik kelas → `status = alumni`, `alumni_expires_at = now() + 5 tahun`
- Setelah expired → tidak bisa login (middleware `CheckAlumniExpiry`)
- Data siswa tetap ada di `students`, hanya akun `users` yang ter-expire

### Guru Kelas vs Guru Bidang
- `guru_kelas`: grade 1-3, satu kelas, otomatis wali kelas, input semua mapel
- `guru_bidang`: grade 4-6, bisa banyak kelas, mapel spesifik

### Honor Guru
- Tidak bisa generate slip jika absensi bulan tersebut belum lengkap (semua hari kerja)
- Tombol kirim WA di-disable sampai slip ditandai lunas
- Slip memiliki QR code untuk verifikasi publik

### Notifikasi
- In-app: `notifications` table, lazy loaded lewat Inertia shared props
- Real-time: polling setiap 30 detik di `AppLayout.vue`
- WhatsApp: async via queue job (Fonnte API)
