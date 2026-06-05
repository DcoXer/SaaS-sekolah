# Modul Master Data

Modul ini dikelola oleh **Operator** dan menjadi fondasi seluruh sistem. Semua modul lain bergantung pada data yang ada di sini.

---

## 1. Tahun Ajaran (Academic Year)

### Deskripsi
Tahun ajaran menggunakan approval workflow. Operator mengajukan, Kamad yang menyetujui.

### Status
| Status | Deskripsi |
|--------|-----------|
| `pending` | Baru dibuat operator, menunggu persetujuan Kamad |
| `active` | Sedang berjalan, hanya satu yang aktif dalam satu waktu |
| `closed` | Sudah selesai, data read-only |

### Flow
```
Operator buat → status: pending
Kamad approve → status: active (yang lama otomatis closed)
               + promoteStudents() dijalankan otomatis
```

### Promosi Siswa (Auto)
Saat Kamad approve tahun ajaran baru:
- Siswa grade 1-5 → grade +1
- Siswa grade 6 → `status = alumni`, `user.alumni_expires_at = now() + 5 tahun`

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/academic-years` | `Operator\AcademicYearController@index` | List tahun ajaran |
| POST | `/operator/academic-years` | `Operator\AcademicYearController@store` | Buat tahun ajaran |
| GET | `/kamad/academic-years` | `Kamad\AcademicYearController@index` | List untuk Kamad |
| PATCH | `/kamad/academic-years/{id}/approve` | `Kamad\AcademicYearController@approve` | Approve tahun ajaran |

### Database
```
academic_years
  id, name, start_date, end_date
  status: pending | active | closed
```

---

## 2. Guru (Teacher)

### Deskripsi
Setiap guru memiliki akun user dengan role `guru`. Dibuat otomatis saat operator menambah data guru.

### Tipe Guru
Disimpan permanen di kolom `teachers.type`:

| Tipe | Grade | Mengajar | Wali Kelas |
|------|-------|----------|------------|
| `guru_kelas` | 1-3 | Semua mapel di satu kelas | Otomatis |
| `guru_bidang` | 4-6 | Mapel spesifik, bisa banyak kelas | Opsional, maks. 1 kelas |

### Rules
- Email harus unik di tabel `users`
- NIP opsional tapi unik jika diisi
- Menghapus guru akan menghapus akun user-nya juga (cascade)

### Import/Export
- Format: Excel (.xlsx)
- Download template tersedia
- Import dengan preview sebelum konfirmasi

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/teachers` | `Operator\TeacherController@index` | List guru |
| POST | `/operator/teachers` | `Operator\TeacherController@store` | Tambah guru |
| GET | `/operator/teachers/{id}/edit` | `Operator\TeacherController@edit` | Form edit guru |
| PUT | `/operator/teachers/{id}` | `Operator\TeacherController@update` | Update guru |
| DELETE | `/operator/teachers/{id}` | `Operator\TeacherController@destroy` | Hapus guru |
| POST | `/operator/teachers/import` | `Operator\TeacherImportExportController@import` | Import dari Excel |
| GET | `/operator/teachers/export` | `Operator\TeacherImportExportController@export` | Export ke Excel |

### Database
```
teachers
  id, user_id, nip, type: guru_kelas|guru_bidang
  position, gender, phone, photo

users (auto created)
  id, name, email, password, avatar, signature
  role: guru (via Spatie)
```

---

## 3. Konfigurasi Jam Pelajaran (Teaching Hours)

### Deskripsi
Konfigurasi tarif honorarium per guru per tahun ajaran. Data ini digunakan untuk menghitung slip honor.

### Data yang Dikonfigurasi
| Field | Deskripsi |
|-------|-----------|
| `total_hours` | Jumlah jam pelajaran per bulan |
| `hourly_rate` | Tarif per jam (Rp) |
| `daily_transport_rate` | Uang transport per hari hadir (Rp) |
| `position_name` | Nama jabatan (opsional) |
| `position_allowance` | Tunjangan jabatan per bulan (Rp) |

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/teaching-hours` | `Operator\TeachingHourController@index` | List konfigurasi |
| POST | `/operator/teaching-hours` | `Operator\TeachingHourController@store` | Tambah konfigurasi |
| DELETE | `/operator/teaching-hours/{id}` | `Operator\TeachingHourController@destroy` | Hapus konfigurasi |

### Database
```
teacher_teaching_hours
  id, teacher_id, academic_year_id
  total_hours, hourly_rate
  daily_transport_rate
  position_name (nullable), position_allowance
```

---

## 4. Kelas (Classroom)

### Deskripsi
Kelas dibuat per tahun ajaran. Wali kelas ditetapkan sesuai tipe guru (otomatis untuk guru_kelas, manual untuk guru_bidang).

### Rules
- Grade 1-6
- Guru kelas (grade 1-3): satu guru hanya bisa di-assign ke satu kelas per tahun ajaran
- Guru bidang (grade 4-6): bisa jadi wali kelas maksimal di satu rombel
- Assign ulang manual setiap tahun ajaran baru

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/classrooms` | `Operator\ClassroomController@index` | List kelas |
| POST | `/operator/classrooms` | `Operator\ClassroomController@store` | Tambah kelas |
| GET | `/operator/classrooms/{id}` | `Operator\ClassroomController@show` | Detail kelas |
| PUT | `/operator/classrooms/{id}` | `Operator\ClassroomController@update` | Update kelas |
| DELETE | `/operator/classrooms/{id}` | `Operator\ClassroomController@destroy` | Hapus kelas |

### Database
```
classrooms
  id, academic_year_id, name, grade (1-6), homeroom_teacher_id (nullable)
```

---

## 5. Mata Pelajaran (Subject)

### Deskripsi
Mata pelajaran didefinisikan per grade. Bisa digunakan di banyak kelas yang sama grade-nya.

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/subjects` | `Operator\SubjectController@index` | List mapel |
| POST | `/operator/subjects` | `Operator\SubjectController@store` | Tambah mapel |
| PUT | `/operator/subjects/{id}` | `Operator\SubjectController@update` | Update mapel |
| DELETE | `/operator/subjects/{id}` | `Operator\SubjectController@destroy` | Hapus mapel |

### Database
```
subjects
  id, name, grade (1-6)
```

---

## 6. Siswa (Student)

### Deskripsi
Data siswa dengan field `grade` independen dari rombel. Operator assign ke rombel secara manual tiap tahun ajaran.

### Status Siswa
| Status | Deskripsi |
|--------|-----------|
| `active` | Siswa aktif |
| `alumni` | Sudah lulus (grade 6 naik kelas) |
| `mutasi` | Pindah sekolah |

### Rules
- NIS harus unik
- NISN opsional
- `grade` field independen dari rombel/kelas
- Saat assign ke rombel: siswa yang tampil hanya yang `grade` sama dan belum masuk rombel manapun di tahun ajaran aktif
- Menghapus siswa akan menghapus akun wali murid-nya juga

### Alumni Flow
- Grade 6 naik kelas → `status = alumni`, `user.alumni_expires_at = now() + 5 tahun`
- Setelah expired → tidak bisa login (middleware `CheckAlumniExpiry`)
- Data siswa di tabel `students` tetap ada

### Import/Export
- Format: Excel (.xlsx)
- Download template tersedia
- Import dengan preview sebelum konfirmasi

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/students` | `Operator\StudentController@index` | List siswa |
| POST | `/operator/students` | `Operator\StudentController@store` | Tambah siswa |
| GET | `/operator/students/{id}/edit` | `Operator\StudentController@edit` | Form edit siswa |
| PUT | `/operator/students/{id}` | `Operator\StudentController@update` | Update siswa |
| DELETE | `/operator/students/{id}` | `Operator\StudentController@destroy` | Hapus siswa |
| POST | `/operator/students/import` | `Operator\StudentImportExportController@import` | Import Excel |
| GET | `/operator/students/export` | `Operator\StudentImportExportController@export` | Export Excel |

### Database
```
students
  id, user_id (nullable), nisn (nullable), nik, nis, name, gender, grade (1-6)
  birth_place, birth_date, address
  father_name, father_phone, mother_name, mother_phone
  guardian_name, guardian_phone, parent_phone
  photo, status: active|alumni|mutasi

student_classrooms (pivot)
  id, student_id, classroom_id, academic_year_id
  UNIQUE: student_id + academic_year_id
```

---

## 7. Penugasan Guru-Mapel (Teacher Subject)

### Deskripsi
Mendefinisikan guru mana mengajar mapel apa di kelas mana pada tahun ajaran tertentu.

### Rules
- Kombinasi `teacher + subject + classroom + academic_year` harus unik
- Assign bersifat idempotent
- `guru_kelas`: saat assign ke kelas, otomatis di-assign ke semua mapel di kelas tersebut

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/teacher-subjects` | `Operator\TeacherSubjectController@index` | List assignment |
| POST | `/operator/teacher-subjects` | `Operator\TeacherSubjectController@store` | Assign guru |
| DELETE | `/operator/teacher-subjects/{id}` | `Operator\TeacherSubjectController@destroy` | Hapus assignment |

### Database
```
teacher_subjects
  id, teacher_id, subject_id, classroom_id, academic_year_id
  UNIQUE: teacher_id + subject_id + classroom_id + academic_year_id
```

---

## 8. Pengaturan Sekolah (School Settings)

### Deskripsi
Data sekolah yang digunakan di seluruh sistem: surat, raport, halaman publik, absensi GPS.

### Data yang Disimpan
- Identitas: nama, NPSN, tagline
- Kepala sekolah: nama, NIP
- Kontak: alamat, telepon, email, website
- Profil: deskripsi, visi, misi, sejarah
- Media: logo, stempel
- GPS: latitude, longitude, `attendance_radius` (meter) — untuk validasi absensi guru
- Hero images: per halaman (welcome, tentang, galeri, ekskul)
- Galeri foto publik

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/school-settings` | `Operator\SchoolSettingController@index` | Lihat pengaturan |
| POST | `/operator/school-settings` | `Operator\SchoolSettingController@save` | Simpan pengaturan |
| POST | `/operator/hero-photos` | `Operator\SchoolHeroPhotoController@store` | Upload hero image |
| DELETE | `/operator/hero-photos/{id}` | `Operator\SchoolHeroPhotoController@destroy` | Hapus hero image |
| POST | `/operator/school-galleries` | `Operator\SchoolGalleryController@store` | Upload foto galeri |
| DELETE | `/operator/school-galleries/{id}` | `Operator\SchoolGalleryController@destroy` | Hapus foto galeri |

### Database
```
school_settings
  id, name, tagline, npsn
  principal_name, principal_nip
  address, phone, email, website
  description, vision, mission, history
  logo, stamp
  latitude, longitude, attendance_radius

school_hero_photos
  id, school_setting_id, page (welcome|tentang|galeri|ekskul)
  image, sort_order

school_galleries
  id, title, image, sort_order
```

---

## 9. PPDB (Penerimaan Peserta Didik Baru)

### Deskripsi
Modul pendaftaran siswa baru secara online. Pendaftar mengisi form publik, operator memproses, Kamad bisa memantau.

### Flow
```
Operator buka pendaftaran (PpdbSetting)
    ↓
Calon siswa daftar online → status: pending
    ↓
Operator review: accept / reject / waitlist
    ↓
Calon siswa cek status via nomor pendaftaran
```

### Status Pendaftaran
| Status | Deskripsi |
|--------|-----------|
| `pending` | Baru mendaftar, menunggu review |
| `accepted` | Diterima |
| `rejected` | Ditolak |
| `waitlisted` | Cadangan |

### Rules
- Satu batch PPDB dikonfigurasi lewat `PpdbSetting` (tanggal buka/tutup, kuota, persyaratan)
- Quota enforcement dengan database lock (race condition safe)
- Duplikat dicegah dengan unique check `no_kk + full_name` per batch
- Dokumen yang diupload: foto, KK, akta kelahiran
- Soft-delete pada `PpdbRegistration`

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/ppdb` | `PublicPpdbController@index` | Form pendaftaran publik |
| POST | `/ppdb/daftar` | `PublicPpdbController@register` | Submit pendaftaran |
| GET | `/ppdb/cek` | `PublicPpdbController@check` | Cek status via nomor |
| GET | `/operator/ppdb` | `Operator\PpdbController@index` | List pendaftar |
| POST | `/operator/ppdb/settings` | `Operator\PpdbController@saveSettings` | Simpan pengaturan |
| PATCH | `/operator/ppdb/{id}/accept` | `Operator\PpdbController@accept` | Terima pendaftar |
| PATCH | `/operator/ppdb/{id}/reject` | `Operator\PpdbController@reject` | Tolak pendaftar |
| PATCH | `/operator/ppdb/{id}/waitlist` | `Operator\PpdbController@waitlist` | Waitlist pendaftar |
| GET | `/kamad/ppdb` | `Kamad\PpdbController@index` | Pantau PPDB (read-only) |

### Database
```
ppdb_settings
  id, title, description, requirements
  registration_start, registration_end, announcement_date
  quota, is_open

ppdb_registrations (soft-deletes)
  id, ppdb_setting_id, registration_number (unique)
  full_name, nik_siswa, no_kk, birth_place, birth_date, gender, religion
  address, province, regency, district, village
  father_name, father_phone, mother_name, mother_phone
  guardian_name, guardian_phone
  photo, document_kk, document_akta
  status: pending|accepted|rejected|waitlisted
  notes (nullable), reviewed_at, reviewed_by
```

---

## 10. Berita & Pengumuman (School Post)

### Deskripsi
Sistem berita dan pengumuman yang dikelola operator dan dapat diakses publik.

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/school-posts` | `Operator\SchoolPostController@index` | List semua post |
| POST | `/operator/school-posts` | `Operator\SchoolPostController@store` | Buat post |
| PUT | `/operator/school-posts/{post}` | `Operator\SchoolPostController@update` | Update post |
| PATCH | `/operator/school-posts/{post}/toggle-publish` | toggle | Publish/unpublish |
| DELETE | `/operator/school-posts/{post}` | `Operator\SchoolPostController@destroy` | Hapus post |
| POST | `/operator/school-posts/{post}/images` | gambar | Upload gambar konten |
| DELETE | `/operator/school-posts/{post}/images/{image}` | gambar | Hapus gambar |
| GET | `/berita` | `PublicSchoolPostController@index` | List publik |
| GET | `/berita/{post:slug}` | `PublicSchoolPostController@show` | Detail publik |

### Database
```
school_posts
  id, title, slug (unique), excerpt, content, cover_image
  category, is_published, published_at

school_post_images
  id, school_post_id, image, sort_order
```

---

## 11. Ekskul (Extracurricular)

### Deskripsi
Data kegiatan ekstrakurikuler dengan foto, prestasi, dan jadwal. Ditampilkan di halaman publik.

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/extracurriculars` | index | List ekskul |
| POST | `/operator/extracurriculars` | store | Tambah ekskul |
| PUT | `/operator/extracurriculars/{id}` | update | Update ekskul |
| DELETE | `/operator/extracurriculars/{id}` | destroy | Hapus ekskul |
| GET | `/ekskul` | `ExtracurricularPageController@index` | List publik |
| GET | `/ekskul/{id}` | `ExtracurricularDetailController@show` | Detail publik |

### Database
```
extracurriculars
  id, name, description, coach, schedule, image, is_active, sort_order

extracurricular_photos
  id, extracurricular_id, photo, sort_order

extracurricular_achievements
  id, extracurricular_id, year, title, description, sort_order
```

---

## 12. Halaman Publik

Semua halaman dapat diakses tanpa login. Konten dikelola dari operator panel.

| URL | Konten |
|-----|--------|
| `/` | Landing page: nama sekolah, tagline, hero image, statistik |
| `/tentang` | Profil sekolah: deskripsi, visi, misi, sejarah, kepala sekolah |
| `/galeri` | Foto galeri sekolah |
| `/ekskul` | List ekskul aktif dengan foto dan ringkasan |
| `/ekskul/{id}` | Detail ekskul: foto, jadwal, pelatih, prestasi |
| `/berita` | Berita & pengumuman yang sudah dipublish |
| `/berita/{slug}` | Detail berita dengan galeri gambar |
| `/ppdb` | Form pendaftaran + statistik calon siswa |
| `/ppdb/cek` | Cek status pendaftaran |

Semua route publik dilindungi rate limiting.
