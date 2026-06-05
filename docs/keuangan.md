# Modul Keuangan

Modul ini dikelola oleh **TU Keuangan** dan mencakup pengelolaan tagihan siswa, pembayaran, honor guru, dan absensi guru.

---

## Alur Tagihan Siswa

```
TU Keuangan buat jenis tagihan
    ↓
Sistem auto-generate tagihan per siswa
    ↓
Wali murid bayar (cash atau online via Midtrans)
    ↓
TU Keuangan catat pembayaran cash
    ↓
Sistem update status tagihan otomatis
    ↓
Kamad pantau laporan keuangan
```

---

## 1. Jenis Tagihan (Payment Type)

### Deskripsi
Jenis tagihan dibuat oleh TU Keuangan secara fleksibel. Nama bebas, tidak hardcoded.

### Siklus Tagihan
| Cycle | Deskripsi | Contoh |
|-------|-----------|--------|
| `monthly` | Tagihan bulanan | SPP |
| `yearly` | Tagihan tahunan, bisa dicicil | Uang kegiatan |
| `once` | Tagihan sekali, bisa dicicil | Uang semester, daftar ulang |

### Rules
- `grade` nullable — jika null berlaku untuk semua kelas, jika diisi hanya untuk grade tersebut
- `is_exam_related = true` → siswa tidak bisa ikut ujian jika belum lunas
- Setelah jenis tagihan dibuat, tagihan per siswa **auto-generate**
- SPP bisa di-generate batch untuk seluruh bulan dalam tahun ajaran aktif

### Auto Generate SPP
Saat TU Keuangan klik "Generate SPP":
1. Buat PaymentType untuk setiap bulan dalam tahun ajaran aktif
2. Format nama: `SPP [Bulan] [Tahun]` (contoh: `SPP Juli 2025`)
3. Auto-generate invoice untuk semua siswa aktif

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/keuangan/payment-types` | `Keuangan\PaymentTypeController@index` | List jenis tagihan |
| POST | `/keuangan/payment-types` | `Keuangan\PaymentTypeController@store` | Buat jenis tagihan |
| PUT | `/keuangan/payment-types/{id}` | `Keuangan\PaymentTypeController@update` | Update |
| DELETE | `/keuangan/payment-types/{id}` | `Keuangan\PaymentTypeController@destroy` | Hapus |
| POST | `/keuangan/payment-types/generate-spp` | `Keuangan\PaymentTypeController@generateSpp` | Generate SPP bulanan |

### Database
```
payment_types
  id, academic_year_id, name, cycle: monthly|yearly|once
  amount, due_date, grade (nullable)
  is_exam_related (boolean), is_active (boolean)
```

---

## 2. Tagihan Siswa (Invoice)

### Deskripsi
Tagihan per siswa yang di-generate otomatis saat jenis tagihan dibuat.

### Status Tagihan
| Status | Deskripsi |
|--------|-----------|
| `unpaid` | Belum ada pembayaran |
| `partial` | Sudah dibayar sebagian |
| `paid` | Lunas |

### Kalkulasi Status
```
total_paid = SUM(payments.amount) WHERE invoice_id = X
remaining  = invoice.amount - total_paid

status = unpaid  → total_paid = 0
status = partial → 0 < total_paid < invoice.amount
status = paid    → total_paid >= invoice.amount
```

### Laporan Keuangan (Kamad)
- Total tagihan keseluruhan, sudah dibayar, belum dibayar
- Breakdown per jenis pembayaran
- List siswa belum bayar lengkap dengan kelas dan rombel

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/keuangan/invoices` | `Keuangan\InvoiceController@index` | Dashboard tagihan |
| GET | `/keuangan/invoices/{student}` | `Keuangan\InvoiceController@show` | Tagihan per siswa |
| POST | `/keuangan/invoices/send-spp-reminders` | index | Kirim reminder WA |
| GET | `/siswa/invoices` | `Siswa\InvoiceController@index` | Tagihan wali murid |

### Database
```
invoices
  id, student_id, payment_type_id, academic_year_id
  amount, status: unpaid|partial|paid, due_date
  receipt_code (unique, untuk verifikasi QR)

UNIQUE: student_id + payment_type_id
```

---

## 3. Pembayaran (Payment)

### Deskripsi
Mendukung dua metode pembayaran: cash dan online via Midtrans.

### Metode Pembayaran

#### Cash
1. Wali murid request bayar cash di portal
2. Datang ke TU Keuangan
3. TU Keuangan isi nominal + upload bukti
4. Sistem update status tagihan otomatis

#### Midtrans (Online)
1. Wali murid klik "Bayar Online"
2. Sistem generate Snap Token dari Midtrans
3. Wali murid bayar via Midtrans payment page
4. Midtrans kirim callback → sistem update status

### Rules
- Nominal pembayaran tidak boleh melebihi sisa tagihan
- Menghapus pembayaran akan recalculate status tagihan otomatis
- Bukti pembayaran disimpan di `storage/app/public/payments/proofs`

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| POST | `/keuangan/invoices/{invoice}/payments` | `Keuangan\PaymentController@store` | Catat pembayaran cash |
| DELETE | `/keuangan/payments/{payment}` | `Keuangan\PaymentController@destroy` | Hapus pembayaran |
| GET | `/keuangan/invoices/{invoice}/receipt` | receipt | Lihat kwitansi PDF (TU) |
| POST | `/siswa/invoices/{invoice}/pay` | `Siswa\PaymentController@initiate` | Inisiasi bayar online |
| GET | `/siswa/invoices/{invoice}/receipt` | receipt | Lihat kwitansi (wali) |
| POST | `/midtrans/callback` | `Siswa\PaymentController@callback` | Midtrans webhook |
| GET | `/receipt/{code}` | `ReceiptVerifyController@show` | Verifikasi kwitansi (publik) |

### Database
```
payments
  id, invoice_id, tu_keuangan_id
  amount, method: cash|midtrans
  midtrans_order_id (nullable), midtrans_status (nullable)
  proof_file (nullable), note (nullable), paid_at

payment_requests
  id, invoice_id, requested_at, requested_by
```

### Kartu Ujian
Siswa dengan tagihan `is_exam_related = true` yang belum `paid` tidak bisa ikut ujian.
```
GET /siswa/invoices → { invoices: [...], hasExamAccess: true|false }
```

---

## 4. Honor Guru (Honorarium)

### Deskripsi
Penggajian bulanan guru berdasarkan jam pelajaran, hari hadir, dan tunjangan jabatan. Dilengkapi slip PDF dan QR verifikasi.

### Alur Lengkap
```
Operator isi konfigurasi jam pelajaran per guru (TeachingHours)
    ↓
Guru catat absensi harian sepanjang bulan
    ↓
TU Keuangan generate slip honor bulan tersebut
    ↓ (guard: absensi harus lengkap semua hari kerja)
Sistem hitung total honor otomatis
    ↓
TU Keuangan tandai lunas
    ↓ (baru boleh kirim WA setelah lunas)
TU Keuangan kirim slip via WhatsApp
```

### Kalkulasi Honor
```
teaching_hours_amount = total_hours × hourly_rate
transport_amount      = hadir_days × daily_transport_rate
total_amount          = teaching_hours_amount + transport_amount + position_allowance
```

### Guard Generate
Slip **tidak bisa dibuat** jika:
1. Absensi bulan tersebut belum lengkap (ada hari kerja tanpa record)
2. Slip untuk periode yang sama sudah pernah dibuat

### Guard Kirim WA
- Tombol kirim WA per slip di-disable selama status masih `draft`
- Tombol "Kirim Semua" di banner di-disable jika ada slip berstatus `draft`
- Prinsip: slip harus lunas dulu baru boleh dikirim

### Status Slip
| Status | Deskripsi |
|--------|-----------|
| `draft` | Sudah digenerate, belum dibayar |
| `paid` | Sudah ditandai lunas |

### Slip PDF
- Format A5 portrait (barryvdh/dompdf)
- Isi: kop sekolah, data guru, rincian honor, QR code verifikasi
- QR mengarah ke `/slip-honor/{slip_code}` (publik, tanpa login)

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/keuangan/honorariums` | `Keuangan\HonorariumController@index` | List slip honor |
| POST | `/keuangan/honorariums/generate` | generate | Buat slip satu guru |
| POST | `/keuangan/honorariums/generate-all` | generateAll | Buat semua slip |
| PATCH | `/keuangan/honorariums/{id}/mark-paid` | markPaid | Tandai lunas |
| POST | `/keuangan/honorariums/mark-all-paid` | markAllPaid | Tandai semua lunas |
| GET | `/keuangan/honorariums/{id}/slip` | downloadSlip | Download PDF |
| POST | `/keuangan/honorariums/{id}/send-slip` | sendSlip | Kirim WA (single) |
| POST | `/keuangan/honorariums/send-all-slips` | sendAllSlips | Kirim WA (batch) |
| DELETE | `/keuangan/honorariums/{id}` | destroy | Hapus slip (draft only) |
| GET | `/slip-honor/{code}` | `HonorariumVerifyController@show` | Verifikasi publik |
| GET | `/kamad/honorariums` | `Kamad\HonorariumController@index` | Pantau (read-only) |

### Database
```
teacher_honorariums
  id, teacher_id, academic_year_id
  period_month, period_year
  teaching_hours, hourly_rate
  transport_days, daily_transport_rate
  position_name (nullable), position_allowance
  teaching_hours_amount, transport_amount, total_amount
  status: draft|paid
  paid_at (nullable), tu_keuangan_id (nullable)
  slip_code (uuid, unique)
```

### Queue Job
**SendHonorariumSlipJob**
- Generate PDF → upload temp → kirim via Fonnte → hapus temp
- Delay: 60 detik antar guru (batch send)
- Retry: 2x, timeout: 60 detik

---

## 5. Absensi Guru

### Deskripsi
Guru mencatat kehadiran harian secara mandiri dengan validasi GPS radius sekolah.

### Status Absensi
| Status | Deskripsi |
|--------|-----------|
| `hadir` | Hadir (masuk radius GPS) |
| `izin` | Izin |
| `sakit` | Sakit |
| `alpha` | Tidak hadir tanpa keterangan |

### Validasi GPS
- Koordinat sekolah + radius dikonfigurasi di `SchoolSetting`
- Saat guru submit absensi, latitude/longitude disimpan
- Jarak dihitung dari koordinat sekolah (Haversine formula)
- Hanya status `hadir` yang memerlukan masuk dalam radius
- Izin/sakit/alpha bisa diisi tanpa GPS

### Rekap Absensi
- Operator & Kamad bisa lihat rekap bulanan semua guru
- Export rekap ke Excel
- Data digunakan untuk kalkulasi `transport_days` saat generate honor

### Completeness Check
Sebelum generate slip honor, sistem cek:
```php
workingDays = count non-weekend days in month
recordCount = count attendance records for teacher in month
isComplete  = recordCount >= workingDays
```

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/guru/attendance` | `Guru\AttendanceController@index` | Kalender absensi guru |
| POST | `/guru/attendance` | `Guru\AttendanceController@store` | Catat absensi |
| PUT | `/guru/attendance/{id}` | `Guru\AttendanceController@update` | Update absensi |
| DELETE | `/guru/attendance/{id}` | `Guru\AttendanceController@destroy` | Hapus absensi |
| GET | `/operator/teacher-attendances/recap` | recap | Rekap semua guru |
| GET | `/operator/teacher-attendances/recap/export` | export | Export Excel |
| GET | `/kamad/teacher-attendances/recap` | recap | Pantau (read-only) |

### Database
```
teacher_attendances
  id, teacher_id, date, status: hadir|izin|sakit|alpha
  notes (nullable), latitude (nullable), longitude (nullable)

UNIQUE: teacher_id + date
```

---

## 6. WhatsApp (Fonnte)

### Konfigurasi
```env
FONNTE_TOKEN=your_token_here
```

### Format Nomor
Input: `0812xxx` atau `812xxx` atau `62812xxx` → Output: `62812xxx`

### Notifikasi yang Dikirim

| Event | Penerima | Isi |
|-------|----------|-----|
| Reminder SPP | Parent phone siswa | Nominal tagihan, sudah bayar, sisa |
| Slip Honor | No. HP guru | Rincian honor + attachment PDF slip |

### Implementasi
```php
// Kirim teks
$this->whatsAppService->sendText($phone, $message);

// Kirim dokumen
$this->whatsAppService->sendDocument($phone, $message, $fileUrl, $filename);
```

Pengiriman dilakukan via Queue job agar tidak blocking request.
