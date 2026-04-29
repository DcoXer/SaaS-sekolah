# 🚀 Review & Improvement Plan - Sistem Sekolah (Integrasi EMIS)

## 📊 Summary

* Struktur sistem: **cukup solid (7/10)**
* Kesiapan integrasi EMIS: **masih lemah (3/10)**
* Masalah utama: **data identity & sync logic**

---

# ❗ 1. Critical Issue: Identity Salah (NIS vs NISN)

## Masalah

Saat ini sistem menggunakan:

```
nis (unique)
```

Ini tidak cocok untuk integrasi EMIS.

## Kenapa ini salah?

* **NIS** = internal sekolah → bisa berubah
* **NISN** = nasional → tetap dan konsisten

## Dampak nyata

* Duplicate siswa
* Gagal match saat import
* Data tidak konsisten antar sistem

## ✅ Solusi (WAJIB)

Ubah struktur menjadi:

```
nisn (string, unique)
nis (string, nullable)
```

## Aturan

* Gunakan **NISN sebagai primary identity**
* NIS hanya atribut tambahan

---

# 🧠 2. Struktur Kelas (Sudah Bagus, Tapi Kurang Enforcement)

## Current Design

* `classrooms` ✔
* `student_classrooms` (pivot per tahun ajaran) ✔

## Masalah

* Tidak ada jaminan siswa punya kelas aktif
* Bisa hanya punya relasi lama

## Dampak

* Data siswa terlihat “tanpa kelas”
* Reporting jadi kacau

## ✅ Solusi

* Enforce:

  * setiap siswa **harus punya kelas aktif**
* Buat helper:

```
getCurrentClass(student)
```

---

# ⚠️ 3. Tidak Ada Konsep Data Source

## Masalah

Sistem tidak membedakan:

* data dari EMIS
* data manual

## Dampak

* overwrite tanpa kontrol
* konflik data
* operator bingung

## ✅ Solusi (WAJIB)

Tambahkan field di tabel `students`:

```
source ENUM('emis', 'manual')
synced_at TIMESTAMP
sync_hash STRING
```

---

# 🔁 4. Tidak Ada Sync Mechanism

## Masalah

* Belum ada logic import/sync
* Sistem masih pure CRUD

## Dampak

* Import akan overwrite semua data
* Tidak bisa mendeteksi perubahan

---

# 🧱 5. Sync Engine (Core System)

## Flow Ideal

### Step 1: Parse File

* Read Excel
* Normalize data:

  * trim string
  * bersihkan karakter aneh
  * format tanggal

---

### Step 2: Match Data

Gunakan:

```
NISN
```

---

### Step 3: Compare Data

Generate hash:

```
md5(name + birth_date + class_id)
```

---

### Step 4: Decision Logic

* Jika NISN tidak ada → **INSERT**
* Jika NISN ada & hash berbeda → **UPDATE**
* Jika NISN ada & hash sama → **SKIP**

---

# 🏫 6. Class Synchronization

## Masalah

Relasi kelas tidak otomatis sinkron saat import

## Dampak

* siswa tetap di kelas lama
* data tidak akurat

## ✅ Solusi

Saat import:

1. Ambil tahun ajaran aktif
2. Update `student_classrooms`:

   * assign kelas baru
   * update jika berubah

---

# 🧪 7. Edge Cases (WAJIB HANDLE)

## ❌ NISN kosong

→ SKIP (jangan masuk DB)

---

## ❌ Duplicate dalam file

→ ambil data terakhir / log warning

---

## ❌ Format kelas tidak konsisten

Contoh:

* "Kelas 2"
* "2A"
* "II"

## Solusi:

* normalize di parsing layer

---

# 💣 8. Risiko Jika Tidak Diperbaiki

Jika sistem dilanjutkan tanpa perbaikan:

* Duplicate siswa
* Kelas tidak update
* Data overwrite tanpa kontrol
* Operator bingung
* Data corruption jangka panjang

---

# 🚀 9. Prioritas Implementasi

## PRIORITAS 1 (WAJIB)

* Tambah NISN
* Refactor identity system

---

## PRIORITAS 2

Tambahkan:

* source
* synced_at
* sync_hash

---

## PRIORITAS 3

Bangun:

* Import Service
* Sync logic (compare & update)

---

## PRIORITAS 4

Perbaiki:

* Class synchronization (pivot update)

---

# 🧠 10. Insight Penting

Sistem saat ini:
✔ Sudah siap sebagai sistem akademik

Tapi:
❌ Belum siap menerima data eksternal

---

# 🎯 Final Conclusion

| Aspek             | Nilai  |
| ----------------- | ------ |
| Struktur Database | 7/10   |
| Integrasi EMIS    | 3/10   |
| Risiko Saat Ini   | Tinggi |

---

# 🔥 Next Step

Pilih langkah berikut:

* [ ] Refactor identity (NIS → NISN)
* [ ] Implement ImportService
* [ ] Tambahkan sync logic
* [ ] Bangun preview perubahan (opsional tapi powerful)

---

# ⚡ Final Insight

Lu sekarang lagi di titik penting:

Kalau lu benerin sekarang:
→ sistem lu scalable & reliable

Kalau lu skip:
→ lu bakal refactor dari nol dalam beberapa bulan

---

**Fokus utama:**
👉 bukan fitur
👉 tapi **data integrity & consistency**
