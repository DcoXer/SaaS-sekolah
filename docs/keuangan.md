# Modul Keuangan

Modul ini dikelola oleh **TU Keuangan** dan mencakup pengelolaan tagihan, pembayaran, dan laporan keuangan sekolah.

---

## Alur Lengkap

```
TU Keuangan buat jenis tagihan
    ↓
Sistem auto-generate tagihan per siswa
    ↓
Wali murid bayar (cash atau online via Midtrans)
    ↓
TU Keuangan catat pembayaran cash + upload bukti
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
- Nama tagihan bebas diisi TU Keuangan
- `grade` nullable — jika null berlaku untuk semua kelas, jika diisi hanya untuk grade tersebut
- `is_exam_related` — jika `true`, siswa tidak bisa ikut ujian jika belum lunas
- Setelah jenis tagihan dibuat, **tagihan per siswa auto-generate**
- SPP bisa di-generate otomatis untuk seluruh bulan dalam tahun ajaran aktif

### Auto Generate SPP
Saat TU Keuangan klik "Generate SPP", sistem akan:
1. Buat PaymentType untuk setiap bulan dalam tahun ajaran aktif
2. Format nama: `SPP [Bulan] [Tahun]` (contoh: `SPP Juli 2024`)
3. Auto-generate invoice untuk semua siswa aktif

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/keuangan/payment-types` | `Keuangan\PaymentTypeController@index` | List jenis tagihan |
| POST | `/keuangan/payment-types` | `Keuangan\PaymentTypeController@store` | Buat jenis tagihan |
| PUT | `/keuangan/payment-types/{id}` | `Keuangan\PaymentTypeController@update` | Update jenis tagihan |
| DELETE | `/keuangan/payment-types/{id}` | `Keuangan\PaymentTypeController@destroy` | Hapus jenis tagihan |
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

### Rules
- Status dihitung **otomatis** dari total pembayaran vs nominal tagihan
- Cicilan bebas — wali murid bisa bayar berapa saja kapan saja asal sebelum `due_date`
- Tagihan dengan `is_exam_related = true` harus `paid` untuk dapat kartu ujian

### Kalkulasi Status
```
total_paid = SUM(payments.amount) WHERE invoice_id = X
remaining  = invoice.amount - total_paid

status = unpaid  jika total_paid = 0
status = partial jika 0 < total_paid < invoice.amount
status = paid    jika total_paid >= invoice.amount
```

### Laporan Keuangan (Kamad)
Summary yang tersedia:
- Total tagihan keseluruhan
- Total yang sudah dibayar
- Total yang belum dibayar
- Breakdown per jenis pembayaran (nama, total, paid, unpaid, jumlah siswa)
- List siswa belum bayar (lengkap dengan kelas dan rombel)

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/keuangan/invoices` | `Keuangan\InvoiceController@index` | Dashboard tagihan |
| GET | `/keuangan/invoices/{student}` | `Keuangan\InvoiceController@show` | Tagihan per siswa |
| GET | `/siswa/invoices` | `Siswa\InvoiceController@index` | Tagihan wali murid |

### Database
```
invoices
  id, student_id, payment_type_id, academic_year_id
  amount, status: unpaid|partial|paid, due_date

UNIQUE: student_id + payment_type_id
```

---

## 3. Pembayaran (Payment)

### Deskripsi
Mendukung dua metode pembayaran: cash dan online via Midtrans.

### Metode Pembayaran

#### Cash
1. Wali murid download kwitansi dari portal
2. Wali murid datang ke TU Keuangan dengan kwitansi
3. TU Keuangan isi nominal dan upload bukti pembayaran
4. Sistem update status tagihan otomatis

#### Midtrans (Online)
1. Wali murid klik "Bayar Online" di portal
2. Sistem generate Snap Token dari Midtrans
3. Wali murid bayar via payment page Midtrans
4. Midtrans kirim callback ke sistem
5. Sistem update status tagihan otomatis

### Rules
- Nominal pembayaran tidak boleh melebihi sisa tagihan
- Menghapus pembayaran akan recalculate status tagihan otomatis
- File bukti pembayaran disimpan di `storage/app/public/payments/proofs`

### Midtrans Callback
Endpoint public (tidak perlu auth):
```
POST /midtrans/callback
```
Sistem handle status `settlement` dan `capture` sebagai pembayaran berhasil.

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| POST | `/keuangan/invoices/{invoice}/payments` | `Keuangan\PaymentController@store` | Catat pembayaran cash |
| DELETE | `/keuangan/payments/{payment}` | `Keuangan\PaymentController@destroy` | Hapus pembayaran |
| GET | `/keuangan/invoices/{invoice}/receipt` | `Keuangan\PaymentController@receipt` | Lihat kwitansi (TU) |
| POST | `/siswa/invoices/{invoice}/pay` | `Siswa\PaymentController@initiate` | Inisiasi bayar online |
| GET | `/siswa/invoices/{invoice}/receipt` | `Siswa\PaymentController@receipt` | Lihat kwitansi (wali) |
| POST | `/midtrans/callback` | `Siswa\PaymentController@callback` | Midtrans webhook |

### Database
```
payments
  id, invoice_id, tu_keuangan_id
  amount, method: cash|midtrans
  midtrans_order_id (nullable, unique)
  midtrans_status (nullable)
  proof_file (nullable)
  note (nullable), paid_at
```

---

## Kartu Ujian

Kartu ujian otomatis terkunci jika ada tagihan dengan `is_exam_related = true` yang belum `paid`.

```php
// Cek akses ujian siswa
$hasAccess = $invoiceService->hasExamAccess($student, $academicYear);
// true  = boleh ikut ujian
// false = tidak boleh ikut ujian
```

Endpoint yang mengembalikan status `hasExamAccess`:
```
GET /siswa/invoices → { invoices: [...], hasExamAccess: true|false }
```
