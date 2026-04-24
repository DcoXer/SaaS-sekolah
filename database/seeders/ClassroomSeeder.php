<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ClassroomService;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function __construct(private ClassroomService $service) {}

    public function run(): void
    {
        $year     = AcademicYear::where('status', 'active')->firstOrFail();
        $teachers = Teacher::with('user')->get()->keyBy(fn($t) => $t->user->email);

        // ── Grade 1–3: buat kelas + assignGuruKelas ────────────────────────────
        // assignGuruKelas otomatis set homeroom_teacher_id + buat TeacherSubject
        // untuk semua mapel di grade tersebut.
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

            $this->service->assignGuruKelas($classroom, $teachers[$data['teacher']], $year);
        }

        // ── Grade 4–6: buat kelas + assignWaliKelas + assignGuruBidang ─────────
        //
        // Distribusi mapel (9 mapel per grade 4–6):
        //   Yusuf Hidayat    → PAI, SBdP           (semua grade 4–6)
        //   Nurul Aini       → Bahasa Indonesia,    (semua grade 4–6)
        //                       Bahasa Inggris
        //   Agus Setiawan    → Matematika, IPA      (semua grade 4–6)
        //   Fitri Handayani  → IPS, PPKn            (semua grade 4–6)
        //   Dewi Kartika     → PJOK di grade 4
        //   Hendra Gunawan   → PJOK di grade 5
        //   Ratna Dewi       → PJOK di grade 6
        $upperClasses = [
            ['name' => '4A', 'grade' => 4, 'wali' => 'dewi.kartika@sekolah.test'],
            ['name' => '5A', 'grade' => 5, 'wali' => 'hendra.gunawan@sekolah.test'],
            ['name' => '6A', 'grade' => 6, 'wali' => 'ratna.dewi@sekolah.test'],
        ];

        // Mapel → email guru yang mengajar (berlaku untuk grade 4, 5, 6)
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

        // PJOK diajarkan wali kelas masing-masing
        $pjokTeacher = [
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

            // Assign guru bidang per mapel
            $subjects = Subject::where('grade', $data['grade'])->get()->keyBy('name');

            foreach ($subjectTeacherMap as $subjectName => $teacherEmail) {
                $subject = $subjects[$subjectName] ?? null;
                if ($subject) {
                    $this->service->assignGuruBidang($classroom, $teachers[$teacherEmail], $subject->id, $year);
                }
            }

            // PJOK → wali kelas masing-masing grade
            $pjok = $subjects['PJOK'] ?? null;
            if ($pjok) {
                $this->service->assignGuruBidang($classroom, $teachers[$pjokTeacher[$data['grade']]], $pjok->id, $year);
            }
        }
    }
}
