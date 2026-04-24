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
| `closed` | Sudah selesai, data read-only, bisa di-export |

### Flow
```
Operator buat → status: pending
Kamad approve → status: active (yang lama otomatis closed)
```

### Rules
- Hanya **satu** tahun ajaran yang bisa `active` dalam satu waktu
- Saat Kamad approve tahun ajaran baru, yang sebelumnya otomatis `closed`
- Data tahun ajaran `closed` tidak bisa diubah

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

### Rules
- Email guru harus unik di tabel `users`
- NIP bersifat opsional tapi harus unik jika diisi
- Menghapus guru akan menghapus akun user-nya juga (cascade)

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/teachers` | `Operator\TeacherController@index` | List guru |
| POST | `/operator/teachers` | `Operator\TeacherController@store` | Tambah guru |
| GET | `/operator/teachers/{id}` | `Operator\TeacherController@show` | Detail guru |
| PUT | `/operator/teachers/{id}` | `Operator\TeacherController@update` | Update guru |
| DELETE | `/operator/teachers/{id}` | `Operator\TeacherController@destroy` | Hapus guru |

### Database
```
teachers
  id, user_id, nip, gender, phone, photo

users (auto created)
  id, name, email, password, role: guru
```

---

## 3. Kelas (Classroom)

### Deskripsi
Kelas dibuat per tahun ajaran. Setiap kelas memiliki wali kelas (homeroom teacher) yang opsional.

### Rules
- Grade harus antara 1-6
- Wali kelas bersifat opsional
- Satu kelas per tahun ajaran
- Guru kelas 1-3: mengajar semua mapel di kelasnya
- Guru kelas 4-6: guru mapel spesifik

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
  id, academic_year_id, name, grade (1-6), homeroom_teacher_id
```

---

## 4. Mata Pelajaran (Subject)

### Deskripsi
Mata pelajaran didefinisikan per grade. Satu mapel bisa diajarkan di grade tertentu.

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

## 5. Siswa (Student)

### Deskripsi
Data siswa dikelola oleh operator. Setiap siswa bisa memiliki akun wali murid (opsional) dengan role `siswa`.

### Rules
- NIS harus unik
- Akun wali murid dibuat otomatis jika email diisi
- Satu siswa hanya bisa ada di satu kelas per tahun ajaran
- Menghapus siswa akan menghapus akun wali murid-nya juga

### Status Siswa
| Status | Deskripsi |
|--------|-----------|
| `active` | Siswa aktif |
| `alumni` | Sudah lulus |
| `mutasi` | Pindah sekolah |

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/students` | `Operator\StudentController@index` | List siswa |
| POST | `/operator/students` | `Operator\StudentController@store` | Tambah siswa |
| GET | `/operator/students/{id}` | `Operator\StudentController@show` | Detail siswa |
| PUT | `/operator/students/{id}` | `Operator\StudentController@update` | Update siswa |
| DELETE | `/operator/students/{id}` | `Operator\StudentController@destroy` | Hapus siswa |
| PATCH | `/operator/students/{id}/assign-classroom` | `Operator\StudentController@assignClassroom` | Assign ke kelas |

### Database
```
students
  id, user_id (nullable), nis, name, gender
  birth_date, address, photo
  status: active | alumni | mutasi

student_classrooms (pivot)
  id, student_id, classroom_id, academic_year_id
```

---

## 6. Penugasan Guru-Mapel (Teacher Subject)

### Deskripsi
Mendefinisikan guru mana mengajar mapel apa di kelas mana pada tahun ajaran tertentu.

### Rules
- Kombinasi `teacher_id + subject_id + classroom_id + academic_year_id` harus unik
- Assign bersifat idempotent (tidak duplikat jika assign ulang)
- Bisa sync seluruh assignment di satu kelas sekaligus

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
```

---

## 7. Pengaturan Sekolah (School Settings)

### Deskripsi
Data sekolah yang digunakan di seluruh sistem, terutama untuk generate surat dan raport.

### Data yang Disimpan
- Nama sekolah
- NPSN
- Nama & NIP kepala sekolah
- Alamat, telepon, email, website
- Logo sekolah
- Stempel sekolah

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/school-settings` | `Operator\SchoolSettingController@index` | Lihat pengaturan |
| POST | `/operator/school-settings` | `Operator\SchoolSettingController@save` | Simpan pengaturan |

### Database
```
school_settings
  id, name, npsn, principal_name, principal_nip
  address, phone, email, website, logo, stamp
```
