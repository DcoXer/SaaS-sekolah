<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Services\ClassroomService;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function __construct(private ClassroomService $service) {}

    public function run(): void
    {
        $year     = AcademicYear::where('status', 'active')->firstOrFail();
        $teachers = Teacher::with('user')->get()->keyBy(fn($t) => $t->user->email);
        $subjects = Subject::all()->keyBy('name');

        // ── Grade 1–3: buat kelas + assignGuruKelas ────────────────────────────
        // Mapel untuk kelas 1–3 (semua diajar guru kelas)
        $mapelBawah = [
            'Pendidikan Agama Islam',
            'Bahasa Indonesia',
            'Matematika',
            'PPKn',
            'PJOK',
            'SBdP',
        ];

        $lowerClasses = [
            ['name' => '1A', 'grade' => 1, 'teacher' => 'ahmad.fauzi@sekolah.test'],
            ['name' => '2A', 'grade' => 2, 'teacher' => 'siti.rahmawati@sekolah.test'],
            ['name' => '3A', 'grade' => 3, 'teacher' => 'budi.santoso@sekolah.test'],
        ];

        foreach ($lowerClasses as $data) {
            $classroom = Classroom::create([
                'academic_year_id' => $year->id,
                'name'             => $data['name'],
                'grade'            => $data['grade'],
            ]);

            // Tambahkan mapel ke kelas terlebih dahulu
            foreach ($mapelBawah as $subjectName) {
                $subject = $subjects[$subjectName] ?? null;
                if ($subject) {
                    $this->service->addSubjectToClassroom($classroom, $subject, $year);
                }
            }

            // assignGuruKelas → set homeroom_teacher_id + update semua teacher_subjects
            $this->service->assignGuruKelas($classroom, $teachers[$data['teacher']], $year);
        }

        // ── Grade 4–6: buat kelas + assignWaliKelas + assign guru per mapel ────
        //
        // Distribusi mapel (9 mapel per grade 4–6):
        //   Yusuf Hidayat    → PAI, SBdP
        //   Nurul Aini       → Bahasa Indonesia, Bahasa Inggris
        //   Agus Setiawan    → Matematika, IPA
        //   Fitri Handayani  → IPS, PPKn
        //   PJOK             → wali kelas masing-masing
        $upperClasses = [
            ['name' => '4A', 'grade' => 4, 'wali' => 'dewi.kartika@sekolah.test'],
            ['name' => '5A', 'grade' => 5, 'wali' => 'hendra.gunawan@sekolah.test'],
            ['name' => '6A', 'grade' => 6, 'wali' => 'ratna.dewi@sekolah.test'],
        ];

        // Mapel → email guru pengajar (berlaku untuk semua grade 4–6)
        $subjectTeacherMap = [
            'Pendidikan Agama Islam' => 'yusuf.hidayat@sekolah.test',
            'Bahasa Indonesia'       => 'nurul.aini@sekolah.test',
            'Matematika'             => 'agus.setiawan@sekolah.test',
            'IPA'                    => 'agus.setiawan@sekolah.test',
            'IPS'                    => 'fitri.handayani@sekolah.test',
            'PPKn'                   => 'fitri.handayani@sekolah.test',
            'SBdP'                   => 'yusuf.hidayat@sekolah.test',
            'Bahasa Inggris'         => 'nurul.aini@sekolah.test',
        ];

        // PJOK diajar wali kelas masing-masing
        $pjokTeacherByGrade = [
            4 => 'dewi.kartika@sekolah.test',
            5 => 'hendra.gunawan@sekolah.test',
            6 => 'ratna.dewi@sekolah.test',
        ];

        foreach ($upperClasses as $data) {
            $classroom = Classroom::create([
                'academic_year_id' => $year->id,
                'name'             => $data['name'],
                'grade'            => $data['grade'],
            ]);

            // Set wali kelas
            $this->service->assignWaliKelas($classroom, $teachers[$data['wali']], $year);

            // Tambah mapel + assign guru bidang per mapel
            foreach ($subjectTeacherMap as $subjectName => $teacherEmail) {
                $subject = $subjects[$subjectName] ?? null;
                $teacher = $teachers[$teacherEmail] ?? null;
                if (!$subject || !$teacher) continue;

                // Tambah mapel ke kelas (teacher_id nullable dulu)
                TeacherSubject::firstOrCreate(
                    [
                        'subject_id'       => $subject->id,
                        'classroom_id'     => $classroom->id,
                        'academic_year_id' => $year->id,
                    ],
                    ['teacher_id' => $teacher->id]
                );
            }

            // PJOK
            $pjok        = $subjects['PJOK'] ?? null;
            $pjokTeacher = $teachers[$pjokTeacherByGrade[$data['grade']]] ?? null;
            if ($pjok && $pjokTeacher) {
                TeacherSubject::firstOrCreate(
                    [
                        'subject_id'       => $pjok->id,
                        'classroom_id'     => $classroom->id,
                        'academic_year_id' => $year->id,
                    ],
                    ['teacher_id' => $pjokTeacher->id]
                );
            }
        }
    }
}
