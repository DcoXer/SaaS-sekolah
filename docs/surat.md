# Modul Surat

Modul ini mencakup dua jenis surat: **Surat Keterangan** (request dari wali murid) dan **Surat Pemberitahuan** (dari operator ke seluruh wali murid). Dilengkapi approval workflow dan QR barcode verifikasi.

---

## Jenis Surat

| Jenis | Siapa yang Buat | Flow | Contoh |
|-------|-----------------|------|--------|
| `keterangan` | Wali murid request | Request → Proses Operator → Approval Kamad → Approved + Barcode | Surat siswa aktif, surat keterangan lainnya |
| `pemberitahuan` | Operator langsung | Buat → Langsung published → Auto recipients | Libur sekolah, jadwal ujian, pengumuman |

---

## Alur Surat Keterangan

```
Wali murid request surat (pilih template)
    ↓
Sistem auto-fill data siswa (nama, NIS, kelas, dll)
    ↓
Operator review & submit ke Kamad → status: waiting_approval
    ↓
Kamad approve → status: approved + barcode_code (UUID) generated
     atau
Kamad reject (WAJIB isi alasan min. 10 karakter) → status: rejected
    ↓
Wali murid download PDF surat
    ↓
Siapapun bisa verifikasi keaslian via scan QR barcode
```

---

## Alur Surat Pemberitahuan

```
Operator buat surat dari template
    ↓
Pilih target: semua siswa atau grade tertentu
    ↓
Sistem generate recipients otomatis → status: published
    ↓
Wali murid lihat di portal (notifikasi in-app)
```

---

## 1. Jenis Surat (Letter Type)

### Deskripsi
Kategori surat yang dikelola operator. Bersifat fleksibel, tidak hardcoded.

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/letter-types` | `Operator\LetterTypeController@index` | List jenis surat |
| POST | `/operator/letter-types` | store | Tambah jenis surat |
| PUT | `/operator/letter-types/{id}` | update | Update jenis surat |
| DELETE | `/operator/letter-types/{id}` | destroy | Hapus jenis surat |

### Database
```
letter_types
  id, name, category: keterangan|pemberitahuan
  description, is_active
```

---

## 2. Template Surat (Letter Template)

### Deskripsi
Template konten surat dengan placeholder. Operator bisa edit konten dengan placeholder yang sudah tersedia.

### Placeholders yang Tersedia

| Placeholder | Deskripsi |
|-------------|-----------|
| `{{student.name}}` | Nama siswa |
| `{{student.nis}}` | NIS siswa |
| `{{classroom.name}}` | Nama kelas |
| `{{academic_year.name}}` | Tahun ajaran |
| `{{letter.date}}` | Tanggal surat |
| `{{letter.number}}` | Nomor surat |
| `{{principal.name}}` | Nama kepala sekolah |
| `{{principal.nip}}` | NIP kepala sekolah |
| `{{school.name}}` | Nama sekolah |
| `{{school.address}}` | Alamat sekolah |
| `{{school.phone}}` | Telepon sekolah |
| `{{barcode}}` | QR code verifikasi |

### Contoh Template
```
Yang bertanda tangan di bawah ini, Kepala {{school.name}}, menerangkan bahwa:

Nama    : {{student.name}}
NIS     : {{student.nis}}
Kelas   : {{classroom.name}}

adalah benar siswa aktif di {{school.name}} Tahun Ajaran {{academic_year.name}}.

{{letter.date}}
{{principal.name}}
{{barcode}}
```

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/letter-templates` | `Operator\LetterTemplateController@index` | List template |
| POST | `/operator/letter-templates` | store | Tambah template |
| PUT | `/operator/letter-templates/{id}` | update | Update template |
| DELETE | `/operator/letter-templates/{id}` | destroy | Hapus template |

### Database
```
letter_templates
  id, letter_type_id, name
  content (longText, HTML + placeholders)
  available_placeholders (JSON array)
  is_active
```

---

## 3. Surat (Letter)

### Status Surat

| Status | Deskripsi |
|--------|-----------|
| `draft` | Baru direquest wali murid |
| `waiting_approval` | Operator submit ke Kamad |
| `approved` | Kamad approve, QR barcode generated |
| `rejected` | Kamad reject, ada alasan |
| `published` | Surat pemberitahuan yang sudah terkirim ke recipients |

### QR Barcode Verifikasi
Saat Kamad approve surat keterangan:
- Sistem generate UUID unik sebagai `barcode_code`
- QR code di-embed ke PDF surat, mengarah ke URL verifikasi
- URL: `GET /verify/{barcodeCode}`
- Halaman verifikasi publik (tanpa login): konfirmasi surat benar ditandatangani Kamad

### PDF Surat
- Generate via barryvdh/dompdf
- Kop sekolah (logo + nama + alamat)
- Konten surat (setelah placeholder di-replace)
- TTD Kamad (nama + NIP + stempel sekolah)
- QR barcode verifikasi di bagian bawah

### Rules
- Wali murid hanya bisa request surat keterangan
- Data siswa auto-filled dari akun wali murid yang login
- Operator tidak bisa approve/reject, hanya submit ke Kamad
- Kamad wajib isi `rejection_note` minimal 10 karakter saat reject
- Surat pemberitahuan auto-published saat dibuat operator

### Routes

**Operator:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/letters` | `Operator\LetterController@index` | List semua surat |
| POST | `/operator/letters/notification` | storeNotification | Buat surat pemberitahuan |
| PATCH | `/operator/letters/{letter}/submit` | submitForApproval | Submit ke Kamad |

**Kamad:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/kamad/letters` | `Kamad\LetterController@index` | List surat |
| PATCH | `/kamad/letters/{letter}/approve` | approve | Approve surat |
| PATCH | `/kamad/letters/{letter}/reject` | reject | Reject surat (wajib isi alasan) |
| GET | `/verify/{barcodeCode}` | verify | Verifikasi publik (tanpa auth) |

**Siswa/Wali:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/siswa/letters` | `Siswa\LetterController@index` | List surat & pemberitahuan |
| POST | `/siswa/letters` | store | Request surat keterangan |
| GET | `/siswa/letters/{letter}` | show | Detail surat |
| PATCH | `/siswa/letters/{letter}/read` | markAsRead | Tandai sudah dibaca |

**PDF:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/letters/{letter}/pdf` | `LetterPdfController@download` | Download PDF surat |

### Database
```
letters
  id, letter_template_id, category: keterangan|pemberitahuan
  requested_by (user_id, nullable)
  student_id (nullable), target_grade (nullable)
  status: draft|waiting_approval|approved|rejected|published
  rejection_note (nullable)
  content (longText, final content setelah placeholder di-replace)
  barcode_code (uuid, unique, nullable)
  approved_by (user_id, nullable), approved_at (nullable)
  published_at (nullable)

letter_recipients (untuk surat pemberitahuan)
  id, letter_id, student_id, read_at (nullable)
  UNIQUE: letter_id + student_id
```

---

## 4. Pengaturan Sekolah untuk Surat

Data sekolah digunakan untuk mengisi placeholder `{{school.*}}`, `{{principal.*}}`, dan kop surat.

Pastikan data sekolah sudah diisi sebelum generate surat. Jika belum, placeholder di-replace dengan tanda `-`.

Fields yang diperlukan:
- `school_settings.name` → nama sekolah
- `school_settings.principal_name` + `principal_nip` → TTD
- `school_settings.address`, `phone` → kop surat
- `school_settings.logo` → logo di kop surat
- `school_settings.stamp` → stempel di TTD

Lihat detail di [docs/master-data.md](master-data.md#8-pengaturan-sekolah-school-settings).
