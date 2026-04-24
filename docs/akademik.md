# Modul Akademik

Modul ini mencakup penilaian siswa dan penerbitan raport. Menggunakan **flexible assessment engine** yang bisa dikonfigurasi per tahun ajaran tanpa perlu ubah kode.

---

## Alur Lengkap

```
Operator setup komponen nilai (per kelas, per mapel, per semester)
    ↓
Operator setup konfigurasi predikat (per tahun ajaran)
    ↓
Guru input nilai per komponen
    ↓
Sistem hitung nilai akhir otomatis (weighted average)
    ↓
Guru kelas tambah catatan raport
    ↓
Kamad generate & publish raport
    ↓
Wali murid lihat & download raport
```

---

## 1. Konfigurasi Predikat (Predicate Config)

### Deskripsi
Konversi nilai angka ke predikat huruf. Dikonfigurasi per tahun ajaran oleh Operator, fleksibel dan tidak hardcoded.

### Contoh Konfigurasi
| Min | Max | Predikat |
|-----|-----|----------|
| 90 | 100 | A |
| 80 | 89 | B |
| 70 | 79 | C |
| 0 | 69 | D |

### Rules
- Konfigurasi bersifat `sync` — saat update, semua config lama dihapus dan diganti baru
- Sistem otomatis lookup predikat berdasarkan nilai akhir
- Jika tidak ada konfigurasi, predikat bernilai `null`

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/predicate-configs` | `Operator\PredicateConfigController@index` | Lihat konfigurasi |
| POST | `/operator/predicate-configs/{academicYear}/sync` | `Operator\PredicateConfigController@sync` | Simpan konfigurasi |

### Database
```
predicate_configs
  id, academic_year_id, min_score, max_score, predicate
```

---

## 2. Komponen Penilaian (Assessment Component)

### Deskripsi
Template struktur penilaian per kelas, per mapel, per semester. Operator yang mendefinisikan komponen ini.

### Tipe Komponen
| Tipe | Deskripsi |
|------|-----------|
| `numeric` | Nilai angka, punya bobot (weight) |
| `predicate` | Nilai predikat huruf |
| `narrative` | Deskripsi naratif dari guru |

### Rules
- Total bobot komponen `numeric` dalam satu mapel, satu kelas, satu semester **tidak boleh melebihi 100%**
- Komponen `predicate` dan `narrative` tidak punya bobot
- Nilai akhir dihitung dari weighted average komponen `numeric`

### Contoh Setup
```
Kelas 1A - Matematika - Semester 1:
  - Nilai Harian (numeric, bobot 40%)
  - UTS (numeric, bobot 30%)
  - UAS (numeric, bobot 30%)
  - Catatan Guru (narrative)
Total bobot: 100% ✓
```

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/operator/assessment-components` | `Operator\AssessmentComponentController@index` | List komponen |
| POST | `/operator/assessment-components` | `Operator\AssessmentComponentController@store` | Tambah komponen |
| PUT | `/operator/assessment-components/{id}` | `Operator\AssessmentComponentController@update` | Update komponen |
| DELETE | `/operator/assessment-components/{id}` | `Operator\AssessmentComponentController@destroy` | Hapus komponen |

### Database
```
assessment_components
  id, academic_year_id, subject_id, classroom_id
  name, type: numeric|predicate|narrative
  weight (%), min_score, max_score, order, semester (1|2)
```

---

## 3. Input Nilai (Student Assessment)

### Deskripsi
Guru menginput nilai siswa per komponen penilaian. Mendukung bulk input untuk seluruh siswa di kelas sekaligus.

### Rules
- **Guru kelas 1-3**: input nilai semua mapel di kelasnya
- **Guru mapel 4-6**: hanya bisa input nilai mapel yang dia ajarkan
- **Guru kelas 4-6**: tidak bisa edit nilai yang sudah diinput guru mapel
- Input bersifat `updateOrCreate` — input ulang akan menimpa nilai sebelumnya

### Kalkulasi Nilai Akhir
```
Nilai Akhir = Σ(nilai_komponen × bobot_komponen) / total_bobot
```

Contoh:
```
Nilai Harian = 80 (bobot 40%)
UTS = 90 (bobot 30%)
UAS = 85 (bobot 30%)

Nilai Akhir = (80×40 + 90×30 + 85×30) / 100
           = (3200 + 2700 + 2550) / 100
           = 84.5
Predikat   = B (jika 80-89 = B)
```

### Routes
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/guru/assessments` | `Guru\AssessmentController@index` | List penugasan guru |
| GET | `/guru/assessments/{classroom}/{component}` | `Guru\AssessmentController@show` | Detail input nilai |
| POST | `/guru/assessments/{component}/bulk` | `Guru\AssessmentController@bulkStore` | Bulk input nilai |

### Database
```
student_assessments
  id, student_id, assessment_component_id
  academic_year_id, classroom_id, input_by
  semester (1|2)
  score (nullable), predicate (nullable), narrative (nullable)

UNIQUE: student_id + assessment_component_id + semester
```

---

## 4. Raport (Report Card)

### Deskripsi
Raport digenerate per kelas per semester oleh Kamad, kemudian dipublish agar bisa diakses wali murid.

### Status Raport
| Status | Deskripsi |
|--------|-----------|
| `draft` | Baru digenerate, belum dipublish |
| `published` | Sudah dipublish, bisa diakses wali murid |

### Flow
```
Kamad generate raport per kelas → status: draft
Guru kelas tambah catatan → homeroom_notes
Kamad publish satu per satu atau sekaligus → status: published
Wali murid bisa lihat & download
```

### Rules
- Generate bersifat idempotent (tidak duplikat)
- Raport yang sudah `published` tidak bisa kembali ke `draft`
- Wali murid hanya bisa lihat raport yang `published`
- Catatan raport hanya bisa diisi oleh wali kelas

### Routes

**Kamad:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/kamad/report-cards` | `Kamad\ReportCardController@index` | Dashboard raport |
| POST | `/kamad/report-cards/{classroom}/generate` | `Kamad\ReportCardController@generate` | Generate raport |
| PATCH | `/kamad/report-cards/{reportCard}/publish` | `Kamad\ReportCardController@publish` | Publish satu raport |
| PATCH | `/kamad/report-cards/{classroom}/publish-all` | `Kamad\ReportCardController@publishAll` | Publish semua |

**Guru:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/guru/report-cards` | `Guru\ReportCardController@index` | List raport kelasnya |
| PATCH | `/guru/report-cards/{reportCard}/notes` | `Guru\ReportCardController@updateNotes` | Isi catatan |

**Siswa/Wali:**
| Method | URL | Controller | Deskripsi |
|--------|-----|------------|-----------|
| GET | `/siswa/report-cards` | `Siswa\ReportCardController@index` | List raport |
| GET | `/siswa/report-cards/{semester}` | `Siswa\ReportCardController@show` | Detail raport |

### Database
```
report_cards
  id, student_id, classroom_id, academic_year_id
  semester (1|2), status: draft|published
  published_at, published_by

report_card_notes
  id, report_card_id
  homeroom_notes, principal_notes

UNIQUE: student_id + academic_year_id + semester
```

---

## Placeholders Nilai di Raport

Data yang tersedia saat build report data per siswa:

```json
{
  "subject": { "id": 1, "name": "Matematika", "grade": 1 },
  "score": 84.5,
  "predicate": "B",
  "narratives": [
    { "narrative": "Siswa menunjukkan kemajuan yang baik..." }
  ]
}
```
