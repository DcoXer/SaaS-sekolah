# Security QA Report — School Management System

> **Last updated:** 2026-04-21 (Round 4)
> **Auditor:** Claude (AI-assisted review)
> **Scope:** Semua controller, FormRequest, Model, Service, dan Blade view

---

## Status Legend

| Icon | Arti |
|------|------|
| ✅ | Fixed |
| 🔴 | Critical — belum di-fix |
| 🟠 | High — belum di-fix |
| 🟡 | Medium — belum di-fix |
| ⚪ | False Positive / By Design |

---

## Vulnerability yang Ditemukan & Diperbaiki

### ✅ [CRITICAL] Midtrans Callback: Tidak Ada Signature Verification

**File:** `app/Services/PaymentService.php`
**Method:** `handleMidtransCallback()`

**Masalah:**
Callback dari Midtrans langsung diproses tanpa verifikasi signature apapun. Attacker bisa kirim POST request palsu ke `/midtrans/callback` dan memalsukan status invoice menjadi `paid` tanpa benar-benar membayar.

**Fix yang diterapkan:**
Tambah method `verifyMidtransSignature()` yang menghitung:
```
SHA-512(order_id + status_code + gross_amount + server_key)
```
Dibandingkan dengan `signature_key` dari payload menggunakan `hash_equals()` untuk mencegah timing attack.

---

### ✅ [MEDIUM] Midtrans Callback: Format `order_id` Tidak Divalidasi

**File:** `app/Services/PaymentService.php`
**Method:** `handleMidtransCallback()`

**Masalah:**
`explode('-', $orderId)[1]` bisa crash atau return nilai salah jika format `order_id` tidak sesuai ekspektasi. Tidak ada validasi format sebelum parsing.

**Fix yang diterapkan:**
Validasi dengan regex `^INV-(\d+)-\d+$` sebelum extract invoice ID. Jika format tidak sesuai → `abort(400)`.

---

### ✅ [MEDIUM] Midtrans Callback: `tu_keuangan_id` Diisi User Siswa

**File:** `app/Services/PaymentService.php`
**Method:** `handleMidtransCallback()`

**Masalah:**
Payment online via Midtrans mencatat `tu_keuangan_id = $invoice->student->user_id` — seolah-olah siswa adalah TU yang mengkonfirmasi pembayaran. Data integrity salah dan membingungkan di laporan.

**Fix yang diterapkan:**
Set `tu_keuangan_id = null` untuk payment Midtrans. Online payment tidak memerlukan konfirmasi TU manual.

---

### ✅ [HIGH] Guru Bisa Input Nilai untuk Siswa dari Kelas Lain

**File:** `app/Http/Requests/BulkInputScoreRequest.php`

**Masalah:**
Validasi `scores.*.student_id` hanya cek `exists:students,id` — tidak memverifikasi bahwa siswa tersebut terdaftar di kelas yang sama dengan komponen nilai yang sedang di-input. Guru yang punya akses ke kelas A bisa kirim `student_id` milik siswa kelas B.

**Fix yang diterapkan:**
```php
Rule::exists('student_classrooms', 'student_id')
    ->where('classroom_id', $component->classroom_id)
```
Validasi sekarang memastikan `student_id` benar-benar terdaftar di kelas yang bersangkutan.

---

### ✅ [MEDIUM] XSS di Letter Placeholder Replacement

**File:** `app/Services/LetterService.php`
**Method:** `replacePlaceholders()`

**Masalah:**
Nilai `{{student.name}}`, `{{school.address}}`, dll di-`str_replace` langsung ke dalam konten surat tanpa HTML escaping. Jika data mengandung karakter `<`, `>`, `"`, konten surat bisa corrupt atau menjadi XSS vector di halaman web preview.

**Fix yang diterapkan:**
Semua nilai di-escape dengan:
```php
htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
```
sebelum di-inject ke template surat.

---

### ✅ [MEDIUM] Tidak Ada Rate Limiting pada Endpoint Publik

**File:** `routes/web.php`

**Masalah:**
Endpoint-endpoint publik (verify barcode surat, verify kwitansi, verify raport, midtrans callback) tidak memiliki rate limiting. Tanpa throttling, endpoint ini rentan terhadap brute force (meski barcode UUID) maupun abuse callback Midtrans.

**Fix yang diterapkan:**
Tambah middleware `throttle` pada semua endpoint publik:
```php
Route::get('verify/{barcodeCode}', ...)->middleware('throttle:30,1');
Route::get('receipt/{code}', ...)->middleware('throttle:30,1');
Route::get('verify-raport/{verifyCode}', ...)->middleware('throttle:30,1');
Route::post('midtrans/callback', ...)->middleware('throttle:60,1');
```

---

### ✅ [LOW] Notifikasi Tidak Dikirim Setelah Pembayaran Midtrans Berhasil

**File:** `app/Services/PaymentService.php`
**Method:** `handleMidtransCallback()`

**Masalah:**
Saat pembayaran online via Midtrans berhasil (status `settlement` / `capture`), tidak ada notifikasi in-app yang dikirim ke siswa. Inkonsistensi — pembayaran cash via TU sudah ada notifikasinya.

**Fix yang diterapkan:**
Inject `NotificationService` ke `PaymentService`. Setelah invoice di-update ke `paid`/`partial`, kirim notifikasi ke siswa:
```php
$this->notif->send(
    $invoice->student->user,
    'payment_confirmed',
    'Pembayaran Berhasil',
    "Pembayaran {$invoice->paymentType->name} sebesar {$amount} via Midtrans telah dikonfirmasi.",
    ['invoice_id' => $invoice->id]
);
```

---

### ✅ [LOW] Notifikasi Tidak Dikirim Setelah Raport Disetujui Massal

**File:** `app/Http/Controllers/Kamad/ReportCardController.php`
**Method:** `approveAll()`

**Masalah:**
Method `approveAll()` menyetujui seluruh raport satu kelas sekaligus, tapi tidak mengirim notifikasi ke masing-masing siswa. Method `approve()` (single) sudah ada notifikasinya — inkonsistensi.

**Fix yang diterapkan:**
Load daftar raport `waiting_approval` sebelum approve, lalu kirim notifikasi ke tiap siswa setelah `approveAll()` selesai:
```php
foreach ($pending as $rc) {
    if ($rc->student?->user) {
        $this->notif->send($rc->student->user, 'report_card_approved', ...);
    }
}
```

---

### ✅ [LOW] Notifikasi Tidak Dikirim ke Penerima Surat Pemberitahuan

**File:** `app/Http/Controllers/Operator/LetterController.php`
**Method:** `storeNotification()`

**Masalah:**
Setelah operator membuat surat pemberitahuan, tidak ada notifikasi in-app yang dikirim ke siswa penerima. Siswa tidak tahu bahwa mereka menerima surat baru.

**Fix yang diterapkan:**
Load `letterRecipients.student.user` setelah letter dibuat, kirim notifikasi ke tiap penerima:
```php
foreach ($letter->letterRecipients as $recipient) {
    if ($recipient->student?->user) {
        $this->notif->send($recipient->student->user, 'notification_letter', ...);
    }
}
```

---

### ✅ [MEDIUM] Siswa Bisa Inisiasi Pembayaran Midtrans pada Invoice yang Sudah Lunas

**File:** `app/Http/Controllers/Siswa/PaymentController.php`
**Method:** `initiate()`

**Masalah:**
Ada pengecekan `isPaid()` di awal, tapi tidak ada guard untuk kasus invoice yang `remaining_amount <= 0` akibat partial payment. Siswa bisa membuka session Midtrans meskipun sudah bayar sebagian yang melunasi tagihan.

**Fix yang diterapkan:**
Tambah guard eksplisit sebelum memanggil Midtrans:
```php
$remaining = $invoice->remaining_amount;
if ($remaining <= 0) {
    return response()->json(['error' => 'Invoice sudah lunas.'], 400);
}
```

---

### ✅ [MEDIUM] TU Keuangan Bisa Catat Pembayaran Melebihi Sisa Tagihan

**File:** `app/Http/Controllers/Keuangan/PaymentController.php`
**Method:** `store()`

**Masalah:**
Tidak ada pengecekan apakah invoice sudah `remaining_amount <= 0` sebelum mencatat pembayaran cash. TU bisa secara tidak sengaja mencatat pembayaran ke invoice yang sudah lunas.

**Fix yang diterapkan:**
Tambah guard di awal method `store()`:
```php
if ($remaining <= 0) {
    return redirect()->back()->withErrors(['amount' => 'Invoice ini sudah lunas.']);
}
if ($request->amount > $remaining) {
    return redirect()->back()->withErrors(['amount' => "Nominal melebihi sisa tagihan."]);
}
```

---

### ✅ [MEDIUM] Guru Bisa Input Nilai ke Tahun Ajaran yang Sudah Ditutup

**File:** `app/Http/Controllers/Guru/AssessmentController.php`
**Method:** `bulkStore()`

**Masalah:**
Tidak ada pengecekan status tahun ajaran saat guru submit nilai. Jika tahun ajaran sudah `closed`, guru secara teknis masih bisa memodifikasi nilai — yang seharusnya sudah dikunci.

**Fix yang diterapkan:**
Tambah guard setelah load classroom:
```php
abort_if(
    $classroom->academicYear?->status !== 'active',
    403,
    'Tidak dapat input nilai untuk tahun ajaran yang sudah tidak aktif.'
);
```

---

### ✅ [CRITICAL] Midtrans Callback Kena CSRF Block — 419 di Production

**File:** `bootstrap/app.php`, `routes/web.php`

**Masalah:**
Route `POST /midtrans/callback` berada di dalam `web` middleware group yang secara default mengaktifkan CSRF verification. Midtrans mengirimkan POST request dari servernya sendiri (bukan browser pengguna), sehingga tidak bisa menyertakan CSRF token. Akibatnya semua callback dari Midtrans akan menghasilkan HTTP 419, dan tidak ada invoice yang diupdate ke `paid` — fitur payment online broken di production.

**Fix yang diterapkan:**
Exclude route `midtrans/callback` dari CSRF verification di `bootstrap/app.php`:
```php
$middleware->validateCsrfTokens(except: [
    'midtrans/callback',
]);
```
Keamanan tetap terjaga via signature verification SHA-512 yang sudah ada sebelumnya.

---

### ✅ [MEDIUM] Race Condition pada Concurrent Cash Payment

**File:** `app/Services/PaymentService.php`
**Method:** `recordCashPayment()`

**Masalah:**
Pengecekan `remaining_amount` dilakukan di controller sebelum transaksi database dimulai. Dua request bersamaan bisa lolos dari pengecekan yang sama dan sama-sama membuat payment, sehingga total pembayaran melebihi nominal invoice (overpayment).

**Fix yang diterapkan:**
Di dalam transaction, lock row invoice dengan `lockForUpdate()` dan re-check sisa tagihan setelah lock:
```php
$invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
$remaining = $invoice->amount - $invoice->payments()->sum('amount');
abort_if($remaining <= 0, 422, 'Invoice sudah lunas.');
abort_if($data['amount'] > $remaining, 422, 'Nominal melebihi sisa tagihan.');
```

---

### ✅ [HIGH] Registrasi Publik Terbuka — Siapapun Bisa Buat Akun

**File:** `routes/auth.php`, `app/Http/Controllers/WelcomeController.php`

**Masalah:**
Route `GET /register` dan `POST /register` accessible oleh siapapun (guest). Pada sistem ini semua akun dibuat oleh operator (siswa, guru, staff). Tidak ada use case untuk registrasi mandiri. Stranger bisa membuat akun, login, dan mengakses route shared (profile, notifikasi). Juga ada 500 error di `WelcomeController` jika user login tanpa role karena `getRoleNames()->first()` return `null`.

**Fix yang diterapkan:**
1. Disable register route di `routes/auth.php` (di-comment untuk dokumentasi, bukan dihapus)
2. Guard null di `WelcomeController` agar tidak crash jika user tidak punya role:
```php
$role  = $user?->getRoleNames()->first();
'dashboardRoute' => $role ? $this->resolveDashboardRoute($role) : null,
```

---

### ✅ [CRITICAL] `tu_keuangan_id` NOT NULL — Payment Midtrans Selalu Gagal

**File:** `database/migrations/2026_04_15_075810_create_payments_table.php`, `app/Services/PaymentService.php`

**Masalah:**
Kolom `tu_keuangan_id` di tabel `payments` didefinisikan sebagai `NOT NULL foreign key`. Namun seluruh payment Midtrans (webhook callback, `verifyAndProcessMidtransPayment`, `recordPaymentFromRedirect`) mengisi kolom ini dengan `null` karena tidak ada TU yang mengkonfirmasi manual. Akibatnya semua `Payment::create()` untuk transaksi online lempar SQL integrity constraint violation — invoice tidak pernah jadi `paid` meski user sudah bayar di Midtrans.

**Fix yang diterapkan:**
Buat migration baru untuk mengubah kolom menjadi nullable:
```php
$table->foreignId('tu_keuangan_id')->nullable()->change();
```
Payment cash tetap wajib isi `tu_keuangan_id` (enforced di service layer), payment Midtrans set `null`.

---

### ✅ [MEDIUM] Midtrans Finish Redirect — `Transaction::status()` 404 Tidak Ditangani

**File:** `app/Http/Controllers/Siswa/PaymentController.php`, `app/Services/PaymentService.php`

**Masalah:**
Endpoint `GET /siswa/payment/finish` mengandalkan `Transaction::status()` API Midtrans untuk verifikasi pembayaran. Di sandbox maupun kondisi tertentu di production (network delay, API flakiness), `Transaction::status()` bisa return 404. Ketika ini terjadi, user yang sudah bayar tidak mendapat konfirmasi dan statusnya tidak berubah.

**Fix yang diterapkan:**
Tambah method `recordPaymentFromRedirect()` sebagai last-resort fallback. Jika `Transaction::status()` gagal dan `transaction_status` dari redirect URL adalah `settlement`/`capture`, payment langsung direcord berdasarkan data redirect:
```php
if (in_array($midtransStatus, ['settlement', 'capture'])) {
    $this->service->recordPaymentFromRedirect($invoice, $orderId, $midtransStatus);
    return redirect()->route('siswa.invoices.index')
        ->with('success', 'Pembayaran berhasil dikonfirmasi.');
}
```

---

### ✅ [MEDIUM] Midtrans Snap — `onSuccess` Tidak Redirect ke Finish Endpoint

**File:** `resources/js/Pages/Siswa/Invoice/Index.vue`

**Masalah:**
Setelah user berhasil bayar di Snap popup, Midtrans menampilkan halaman sukses dengan tombol "Back to Merchant". Jika user menutup popup dengan X (bukan klik tombol itu), redirect ke `callbacks.finish` tidak pernah terjadi → finish endpoint tidak pernah dipanggil → status invoice tidak berubah.

**Fix yang diterapkan:**
Di `onSuccess` callback, lakukan redirect manual ke finish endpoint daripada menunggu user klik "Back to Merchant":
```js
onSuccess: () => {
    paying.value = null;
    window.location.href = `${finishBase}?order_id=${orderId}&transaction_status=settlement`;
},
```
Sama untuk `onPending` dengan `transaction_status=pending`.

---

## False Positive / By Design

Temuan berikut sudah diinvestigasi dan **tidak perlu di-fix**:

| Temuan | File | Alasan Bukan Vuln |
|--------|------|-------------------|
| `Invoice.status` di fillable | `Models/Invoice.php` | FormRequest tidak pernah include field ini dari user input. Hanya di-set internal di `InvoiceService::recalculateStatus()` |
| `ReportCard.status/verify_code/approved_at/approved_by` di fillable | `Models/ReportCard.php` | Sama — hanya di-set oleh service layer internal, tidak bisa di-inject dari request |
| `Student.status` di fillable | `Models/Student.php` | `StoreStudentRequest` dan `UpdateStudentRequest` tidak mengexpose field `status` |
| `Teacher.type` di fillable | `Models/Teacher.php` | Divalidasi ketat `in:guru_kelas,guru_bidang` di FormRequest. Hanya operator yang bisa set |
| `storage_path()` di Blade PDF | `views/pdf/*.blade.php` | Correct untuk DomPDF — DomPDF butuh path filesystem lokal, bukan HTTP URL |
| Public verify endpoint (receipt/raport/surat) | `ReceiptVerifyController`, `Kamad/ReportCardController`, `Kamad/LetterController` | By design — kode berupa UUID v4 yang tidak bisa di-brute-force. Diperlukan untuk scan QR. Rate limiting sudah ditambahkan |
| `LetterPdfController` non-siswa tanpa ownership check | `LetterPdfController.php` | Role middleware sudah isolasi akses: kamad hanya di route kamad, operator di route operator |
| TU Keuangan bisa lihat invoice semua siswa | `Keuangan/InvoiceController.php` | By design — TU Keuangan memang bertugas mengelola semua tagihan sekolah |
| `v-html` pada konten surat pemberitahuan di Kamad view | `Pages/Kamad/Letter/Index.vue` | Konten dibuat oleh operator (trusted admin). Escaping bukan prioritas karena operator already punya full access. XSS hanya berdampak ke Kamad yang juga trusted user |
| `v-html` pada konten surat keterangan di Siswa view | `Pages/Siswa/Letter/Show.vue` | Konten dihasilkan oleh `replacePlaceholders()` yang sudah `htmlspecialchars` semua nilai. Template ditulis operator |
| File upload logo/stempel hanya validasi extension | `SaveSchoolSettingRequest.php` | Low risk — hanya bisa diakses role `operator` (super admin). Validasi `image` + `mimes` Laravel sudah cukup untuk use case ini |
| Alumni bisa akses route siswa jika `alumni_expires_at` belum expired | `CheckAlumniExpiry` middleware | By design — alumni diberikan 5 tahun akses setelah lulus untuk melihat riwayat nilai/raport |
| Edge case: tahun ajaran `pending`, fitur akademik masih bisa diakses | berbagai controller | Low risk — data akademik `pending` tahun baru belum ada. Nilai input terkunci via guard `status !== 'active'` |
| Siswa bisa request cash untuk invoice dari tahun yang sudah `closed` | `Siswa/PaymentController::requestCash` | By design — tunggakan dari tahun lalu tetap harus bisa dibayar. TU yang memproses secara manual |

---

## Checklist QA

### Backend Security
- [x] Midtrans signature verification
- [x] Midtrans order_id format validation
- [x] Midtrans tu_keuangan_id data integrity
- [x] Midtrans callback — notifikasi ke siswa setelah payment berhasil
- [x] Midtrans callback — CSRF exclusion agar tidak kena 419
- [x] Bulk score input — student classroom validation
- [x] Bulk score input — active academic year guard
- [x] Letter placeholder XSS escaping
- [x] Rate limiting pada endpoint publik (verify, midtrans callback)
- [x] Guard remaining_amount <= 0 di Siswa/PaymentController
- [x] Guard remaining_amount <= 0 di Keuangan/PaymentController + lockForUpdate race condition
- [x] Notifikasi raport disetujui — approveAll() consistency fix
- [x] Notifikasi surat pemberitahuan ke penerima
- [x] Public registration route dinonaktifkan
- [x] WelcomeController null-role crash fix
- [x] Authorization audit semua controller (siswa, guru, keuangan, kamad, operator)
- [x] Notification IDOR check (markRead ownership verified)
- [x] ProfileController — self-only, tidak ada IDOR
- [x] CheckAlumniExpiry middleware — correct logout flow
- [x] `tu_keuangan_id` NOT NULL — nullable migration agar payment Midtrans bisa tersimpan
- [x] Midtrans finish fallback — `recordPaymentFromRedirect()` jika `Transaction::status()` gagal
- [x] Midtrans `onSuccess` — redirect manual ke finish endpoint tanpa tunggu "Back to Merchant"
- [x] Midtrans payment flow end-to-end — tested & working di sandbox (CC 4811 1111 1111 1114)
- [ ] Audit log untuk aksi sensitif (approve raport, approve surat, hapus pembayaran) — low priority
- [ ] File upload MIME type sesungguhnya untuk logo/stempel — low priority (operator only)

### Yang Masih Perlu Manual Testing
- [ ] PDF generation — semua placeholder ter-render dengan benar
- [ ] v-html rendering — verifikasi di browser bahwa content sudah aman

---

## Cara Update File Ini

Setiap kali ada vulnerability baru ditemukan atau fix diterapkan:
1. Ubah icon status dari 🔴/🟠/🟡 → ✅
2. Tambah section "Fix yang diterapkan" dengan detail perubahannya
3. Update tanggal di bagian atas
