# School Management System — Project Context

## Overview
Sistem manajemen sekolah untuk SD/MI. Single tenant. Dibangun berdua — backend (Laravel) dan frontend (Vue).

---

## Tech Stack
- **Backend**: Laravel 13 (PHP 8.3+)
- **Frontend**: Vue 3 + Inertia.js
- **Auth**: Laravel Breeze + Spatie Permission
- **Database**: MySQL (Laragon, Windows)
- **Payment**: Midtrans
- **Storage**: Local public disk
- **UI**: Tailwind CSS
- **Testing**: PHPUnit

---

## Roles
| Role | Deskripsi |
|------|-----------|
| `kamad` | Kepala Madrasah — approve, monitor, TTD digital surat |
| `operator` | Super admin — input semua master data |
| `tu_keuangan` | Staf keuangan — kelola tagihan & pembayaran |
| `guru` | Ada dua tipe permanent di field `teachers.type`: **`guru_kelas`** (kelas 1-3, satu guru satu kelas, mengajar semua mapel, otomatis jadi wali kelas) dan **`guru_bidang`** (kelas 4-6, bisa mengajar banyak kelas dengan mapel berbeda-beda). Keduanya pakai role Spatie yang sama (`guru`) |
| `siswa` | Wali murid — lihat nilai, bayar tagihan, request surat |

---

## Architecture Pattern
```
Request → FormRequest (validation) → Controller → Service → Model → Response
```
- **Controller** → tipis, hanya handle request/response, inject service via constructor
- **Service** → semua business logic
- **Model** → Laravel 13 style, pakai PHP Attributes (#[Fillable], #[Hidden])

---

## Folder Structure Controllers
```
app/Http/Controllers/
├── Kamad/
├── Operator/
├── Keuangan/
├── Guru/
└── Siswa/
```

## Folder Structure Services
```
app/Services/
├── AcademicYearService.php
├── TeacherService.php
├── ClassroomService.php
├── SubjectService.php
├── StudentService.php
├── TeacherSubjectService.php
├── PredicateConfigService.php
├── AssessmentComponentService.php
├── StudentAssessmentService.php
├── ReportCardService.php
├── PaymentTypeService.php
├── InvoiceService.php
├── PaymentService.php
├── SchoolSettingService.php
├── LetterTypeService.php
├── LetterTemplateService.php
└── LetterService.php
```

---

## Database Tables
```
# Bawaan Laravel/Spatie
users, roles, permissions, model_has_roles, ...
  users: tambahan kolom alumni_expires_at (timestamp, nullable)

# Master Data
academic_years      → tahun ajaran (status: pending|active|closed)
teachers            → data guru (linked ke users)
                      kolom type: guru_kelas | guru_bidang
classrooms          → kelas per tahun ajaran (grade 1-6)
subjects            → mata pelajaran per grade
students            → data siswa (linked ke users untuk wali)
                      kolom grade: 1-6 (independent dari kelas/rombel)
student_classrooms  → pivot siswa-kelas per tahun ajaran
teacher_subjects    → pivot guru-mapel-kelas per tahun ajaran

# Akademik
predicate_configs       → konfigurasi predikat per tahun ajaran (A/B/C/D)
assessment_components   → komponen nilai (numeric/predicate/narrative) per kelas per mapel
student_assessments     → nilai aktual per siswa per komponen
report_cards            → raport per siswa per semester (draft|published)
report_card_notes       → catatan wali kelas & kamad di raport

# Keuangan
payment_types   → jenis tagihan (monthly/yearly/once), flexible nama
invoices        → tagihan per siswa, auto-generated (unpaid|partial|paid)
payments        → pembayaran aktual (cash|midtrans)

# Surat
school_settings     → data sekolah (nama, kepala sekolah, logo, stempel)
letter_types        → jenis surat (keterangan|pemberitahuan)
letter_templates    → template dengan placeholders
letters             → surat aktual (draft|waiting_approval|approved|rejected|published)
letter_recipients   → penerima surat pemberitahuan
```

---

## Routes Pattern
Semua route authenticated, prefix sesuai role:
```
/kamad/*           → role: kamad
/operator/*        → role: operator
/keuangan/*        → role: tu_keuangan
/guru/*            → role: guru
/siswa/*           → role: siswa
/verify/{code}     → public (verifikasi barcode surat)
/midtrans/callback → public (Midtrans webhook)
```

---

## Model Style (Laravel 13)
```php
#[Fillable(['field1', 'field2'])]
class ModelName extends Model
{
    protected function casts(): array
    {
        return ['field' => 'date'];
    }
}
```

---

## Key Business Rules

### Academic Year
- Hanya satu `active` dalam satu waktu
- Operator buat → `pending`, Kamad approve → `active`, yang lama → `closed`
- Saat Kamad approve tahun ajaran baru → trigger `promoteStudents()` otomatis

### Siswa & Naik Tingkat
- Siswa punya field `grade` sendiri (1-6), independent dari kelas/rombel
- Operator input siswa → wajib pilih `grade`
- Saat operator buat/assign rombel → siswa yang muncul hanya yang:
  - `grade` sama dengan grade kelas
  - Belum masuk rombel manapun di tahun ajaran aktif
- Saat Kamad approve tahun ajaran baru (otomatis):
  - Grade 1-5 → increment grade +1
  - Grade 6 → status jadi `alumni`, akun user di-set `alumni_expires_at = now() + 5 tahun`
- Assign ke rombel tetap **manual** oleh operator setiap tahun ajaran baru
- Akun alumni expired → dihapus oleh **scheduled job** tiap malam jam 02.00
- Safety net: cek `alumni_expires_at` saat login via middleware `CheckAlumniExpiry`
- Data siswa di tabel `students` tetap ada, yang dihapus hanya akun `users`-nya

### Guru & Penugasan Kelas
- Tipe guru disimpan permanent di `teachers.type` (bukan di `teacher_subjects`)

**`guru_kelas`:**
- Hanya untuk kelas grade 1-3
- Satu guru kelas hanya bisa di-assign ke **satu kelas** per tahun ajaran
- Otomatis jadi `homeroom_teacher` sekaligus di-assign ke **semua mapel** di kelasnya
- Assign ulang manual setiap tahun ajaran baru

**`guru_bidang`:**
- Hanya untuk kelas grade 4-6
- Bisa mengajar di **banyak kelas** dengan mapel berbeda-beda (fleksibel)
- Bisa menjadi wali kelas, tapi hanya di **satu rombel** tingkat 4-6
- Meskipun sudah jadi wali kelas, tetap bisa mengajar mapel di kelas lain
- Assign ulang manual setiap tahun ajaran baru

### Nilai & Raport
- Flexible assessment engine — komponen nilai dikonfigurasi per kelas/mapel/semester
- Total bobot komponen `numeric` tidak boleh melebihi 100%
- Nilai akhir = weighted average otomatis
- `guru_kelas` input nilai semua mapel di kelasnya + catatan raport
- `guru_bidang` hanya input nilai mapel yang dia ajarkan di kelas tersebut
- Wali kelas (4-6) input catatan raport, tidak bisa edit nilai guru bidang lain
- Raport per semester, Kamad yang publish

### Keuangan
- Tagihan auto-generate per siswa saat payment type dibuat
- SPP auto-generate per bulan selama tahun ajaran aktif
- Status invoice (unpaid/partial/paid) dihitung otomatis dari total payments
- `is_exam_related = true` → siswa tidak bisa ujian jika belum lunas

### Surat
- Surat keterangan: wali request → operator proses → Kamad approve/reject
- Kamad reject WAJIB isi alasan
- Saat Kamad approve → auto-generate UUID barcode untuk verifikasi
- Surat pemberitahuan: operator buat → langsung published → auto-generate recipients
- Placeholder template: `{{student.name}}`, `{{student.nis}}`, `{{classroom.name}}`, dll

---

## Test Coverage
```
Unit tests    : 105 passed
Feature tests : 169 passed
Total         : 169 passed / 308 assertions (Phase 2-4 selesai)
```

---

## Yang Sudah Selesai
- Fix alur siswa & naik tingkat ✓
- Fix alur guru & penugasan kelas ✓
- Export PDF raport ✓
- Feature tests Phase 2, 3, 4 (akademik, keuangan, surat) ✓
- Frontend Vue pages semua role ✓
- Notifikasi in-app ✓
- PDF kwitansi pembayaran ✓
- PDF surat ✓

---

## Conventions
- Semua invokable controller: `ControllerName::class` (bukan array)
- Service di-inject via constructor DI
- Form Request untuk semua validasi
- `redirect()->back()->with('success', '...')` untuk response sukses
- `redirect()->back()->withErrors([...])` untuk response error validasi custom
- Middleware role Spatie sudah didaftarkan di `bootstrap/app.php`
- Carbon locale: `id` (nama bulan Indonesian)
- Test base `TestCase` sudah auto-seed roles Spatie

---

## Environment
- OS: Windows, Laragon
- Browser: Microsoft Edge
- Project path: `C:/laragon/www/school-management`
- Database: `sekolah-app` (MySQL via Laragon)