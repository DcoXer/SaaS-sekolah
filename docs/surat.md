# Modul Surat

Modul ini mencakup dua jenis surat: **Surat Keterangan** (request dari wali murid) dan **Surat Pemberitahuan** (dari operator ke wali murid). Dilengkapi dengan approval workflow dan TTD digital berupa barcode verifikasi.

---

## Jenis Surat

| Jenis | Siapa yang Buat | Flow | Contoh |
|-------|-----------------|------|--------|
| `keterangan` | Wali murid request, Operator proses | Request → Proses → Approval Kamad → Approved + Barcode | Surat siswa aktif, nilai rata-rata, prestasi |
| `pemberitahuan` | Operator langsung | Buat → Langsung published | Libur sekolah, jadwal ujian |

---

## Alur Surat Keterangan

```
Wali murid request surat (pilih template)
    ↓
Sistem auto-fill data siswa (nama, NIS, kelas, dll)
    ↓
Operator review & submit ke Kamad → status: waiting_approval
    ↓
Kamad approve → status: approved + barcode generated
     atau
Kamad reject (wajib isi alasan) → status: rejected
    ↓
Wali murid download PDF/Word
    ↓
Siapapun bisa verifikasi keaslian via scan barcode
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
Wali murid lihat di portal
```

---

## 1. Jenis Surat (Letter Type)

### Deskripsi
Kategori surat yang dikelola operator. Bersifat fleksibel, tidak hardcoded.

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/letter-types` | `Operator\LetterTypeController@index` | List jenis surat |
| POST | `/operator/letter-types` | `Operator\LetterTypeController@store` | Tambah jenis surat |
| PUT | `/operator/letter-types/{id}` | `Operator\LetterTypeController@update` | Update jenis surat |
| DELETE | `/operator/letter-types/{id}` | `Operator\LetterTypeController@destroy` | Hapus jenis surat |

### Database
```
letter_types
  id, name, category: keterangan|pemberitahuan
  description, is_active
```

---

## 2. Template Surat (Letter Template)

### Deskripsi
Template konten surat dengan placeholder. Operator bisa edit konten, placeholder sudah tersedia tinggal insert.

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
| `{{barcode}}` | Barcode verifikasi |

### Contoh Template
```html
<p>Yang bertanda tangan di bawah ini, Kepala {{school.name}}, 
menerangkan bahwa:</p>

<table>
  <tr><td>Nama</td><td>: {{student.name}}</td></tr>
  <tr><td>NIS</td><td>: {{student.nis}}</td></tr>
  <tr><td>Kelas</td><td>: {{classroom.name}}</td></tr>
</table>

<p>adalah benar siswa aktif di {{school.name}} 
Tahun Ajaran {{academic_year.name}}.</p>

<p>{{letter.date}}</p>
<p>{{principal.name}}</p>
<p>{{barcode}}</p>
```

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/letter-templates` | `Operator\LetterTemplateController@index` | List template |
| POST | `/operator/letter-templates` | `Operator\LetterTemplateController@store` | Tambah template |
| PUT | `/operator/letter-templates/{id}` | `Operator\LetterTemplateController@update` | Update template |
| DELETE | `/operator/letter-templates/{id}` | `Operator\LetterTemplateController@destroy` | Hapus template |

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
| `approved` | Kamad approve, barcode generated |
| `rejected` | Kamad reject, ada alasan |
| `published` | Surat pemberitahuan yang sudah terkirim |

### Barcode Verifikasi
Saat Kamad approve surat keterangan:
- Sistem generate UUID unik sebagai `barcode_code`
- Barcode ini bisa di-embed ke surat sebagai QR code yang link ke URL verifikasi
- URL verifikasi: `GET /verify/{barcodeCode}`
- Halaman verifikasi menampilkan konfirmasi bahwa surat benar ditandatangani Kamad

### Rules
- Wali murid hanya bisa request surat keterangan
- Data siswa auto-filled dari akun wali murid yang login
- Operator tidak bisa approve/reject, hanya submit ke Kamad
- Kamad wajib isi `rejection_note` minimal 10 karakter saat reject
- Surat pemberitahuan auto-published saat dibuat

### Routes

**Operator:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/letters` | `Operator\LetterController@index` | List semua surat |
| POST | `/operator/letters/notification` | `Operator\LetterController@storeNotification` | Buat surat pemberitahuan |
| PATCH | `/operator/letters/{letter}/submit` | `Operator\LetterController@submitForApproval` | Submit ke Kamad |

**Kamad:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/kamad/letters` | `Kamad\LetterController@index` | List surat (waiting/approved/rejected) |
| PATCH | `/kamad/letters/{letter}/approve` | `Kamad\LetterController@approve` | Approve surat |
| PATCH | `/kamad/letters/{letter}/reject` | `Kamad\LetterController@reject` | Reject surat |

**Siswa/Wali:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/siswa/letters` | `Siswa\LetterController@index` | List surat & pemberitahuan |
| POST | `/siswa/letters` | `Siswa\LetterController@store` | Request surat keterangan |
| GET | `/siswa/letters/{letter}` | `Siswa\LetterController@show` | Detail surat |
| PATCH | `/siswa/letters/{letter}/read` | `Siswa\LetterController@markAsRead` | Tandai sudah dibaca |

**Public (tanpa auth):**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/verify/{barcodeCode}` | `Kamad\LetterController@verify` | Verifikasi keaslian surat |

### Database
```
letters
  id, letter_template_id, category: keterangan|pemberitahuan
  requested_by (user_id, nullable)
  student_id (nullable), target_grade (nullable)
  status: draft|waiting_approval|approved|rejected|published
  rejection_note (nullable)
  content (longText, final content setelah placeholder di-replace)
  barcode_code (unique, nullable)
  approved_by (user_id, nullable), approved_at (nullable)
  published_at (nullable)

letter_recipients (untuk surat pemberitahuan)
  id, letter_id, student_id, read_at (nullable)

UNIQUE: letter_id + student_id
```

---

## 4. Pengaturan Sekolah (School Settings)

Data sekolah digunakan untuk mengisi placeholder `{{school.*}}` dan `{{principal.*}}` di template surat.

Pastikan data sekolah sudah diisi sebelum generate surat apapun. Jika belum diisi, placeholder akan di-replace dengan tanda `-`.

Lihat detail di [docs/master-data.md](master-data.md#7-pengaturan-sekolah-school-settings).
