<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\StudentAssessment;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class StudentAssessmentSeeder extends Seeder
{
    // Narasi per mapel
    private array $narratives = [
        'Pendidikan Agama Islam' => [
            'Peserta didik mampu membaca Al-Qur\'an dengan tartil dan memahami makna ayat-ayat pilihan.',
            'Peserta didik menunjukkan sikap terpuji dalam kehidupan sehari-hari sesuai ajaran Islam.',
            'Peserta didik aktif dalam kegiatan keagamaan dan hafal surat-surat pendek dengan baik.',
            'Peserta didik perlu meningkatkan konsistensi dalam pelaksanaan ibadah harian.',
        ],
        'Bahasa Indonesia' => [
            'Peserta didik mampu membaca teks dengan lancar dan memahami isi bacaan dengan baik.',
            'Peserta didik mampu menulis kalimat efektif dan menyampaikan pendapat secara lisan.',
            'Peserta didik menunjukkan kemampuan berbahasa Indonesia yang baik dalam komunikasi sehari-hari.',
            'Peserta didik perlu memperbanyak latihan membaca untuk meningkatkan pemahaman teks.',
        ],
        'Matematika' => [
            'Peserta didik mampu menyelesaikan soal operasi hitung dengan tepat dan teliti.',
            'Peserta didik memahami konsep bilangan dan mampu menerapkannya dalam pemecahan masalah.',
            'Peserta didik menunjukkan kemampuan berpikir logis dalam menyelesaikan soal matematika.',
            'Peserta didik perlu lebih banyak berlatih soal agar kemampuan hitung semakin meningkat.',
        ],
        'IPA' => [
            'Peserta didik mampu mengidentifikasi gejala alam dan menjelaskan proses ilmiah sederhana.',
            'Peserta didik aktif dalam kegiatan percobaan dan mampu menyimpulkan hasil pengamatan.',
            'Peserta didik menunjukkan rasa ingin tahu yang tinggi terhadap fenomena alam sekitar.',
            'Peserta didik perlu meningkatkan pemahaman konsep materi yang bersifat abstrak.',
        ],
        'IPS' => [
            'Peserta didik memahami kondisi geografis Indonesia dan keberagaman budaya bangsa.',
            'Peserta didik mampu menganalisis peristiwa sejarah dan menghubungkannya dengan kehidupan masa kini.',
            'Peserta didik menunjukkan sikap bangga sebagai warga negara Indonesia.',
            'Peserta didik perlu memperluas wawasan dengan rajin membaca buku pengetahuan sosial.',
        ],
        'PPKn' => [
            'Peserta didik memahami hak dan kewajiban sebagai warga negara yang baik.',
            'Peserta didik mampu menerapkan nilai-nilai Pancasila dalam kehidupan sehari-hari.',
            'Peserta didik menunjukkan sikap demokratis dan menghargai perbedaan.',
            'Peserta didik perlu lebih aktif berpartisipasi dalam kegiatan kelompok di kelas.',
        ],
        'PJOK' => [
            'Peserta didik aktif dalam kegiatan olahraga dan memahami pentingnya hidup sehat.',
            'Peserta didik mampu melakukan gerakan dasar atletik dan permainan bola dengan baik.',
            'Peserta didik menunjukkan semangat sportivitas dan kerja sama dalam kegiatan olahraga.',
            'Peserta didik perlu meningkatkan kebugaran fisik melalui latihan rutin.',
        ],
        'SBdP' => [
            'Peserta didik mampu mengekspresikan ide melalui karya seni dengan kreativitas tinggi.',
            'Peserta didik menunjukkan apresiasi terhadap seni budaya daerah dan nasional.',
            'Peserta didik aktif berpartisipasi dalam kegiatan seni dan menghasilkan karya yang baik.',
            'Peserta didik perlu mengembangkan kepercayaan diri dalam berkarya seni.',
        ],
        'Bahasa Inggris' => [
            'Peserta didik mampu berkomunikasi dalam bahasa Inggris sederhana dengan percaya diri.',
            'Peserta didik memahami kosakata dan pola kalimat dasar bahasa Inggris dengan baik.',
            'Peserta didik menunjukkan minat dan antusias dalam belajar bahasa Inggris.',
            'Peserta didik perlu memperbanyak latihan percakapan untuk meningkatkan kelancaran berbicara.',
        ],
    ];

    // Rentang nilai per profil siswa (index 0 = nilai rendah, index n = nilai tinggi)
    private function scoreRange(int $studentIndex, int $total, string $componentName): int
    {
        // Bagi siswa ke dalam 4 kelompok kemampuan
        $quartile = (int) floor(($studentIndex / max($total, 1)) * 4);

        $ranges = [
            0 => [60, 72],  // bawah
            1 => [70, 80],  // menengah bawah
            2 => [78, 88],  // menengah atas
            3 => [85, 95],  // atas
        ];

        // PAS cenderung lebih rendah dari tugas harian
        $penalty = match ($componentName) {
            'Penilaian Akhir Semester'  => -3,
            'Penilaian Tengah Semester' => -2,
            default                     => 0,
        };

        [$min, $max] = $ranges[$quartile];
        return max(0, min(100, rand($min + $penalty, $max + $penalty)));
    }

    public function run(): void
    {
        $year       = AcademicYear::where('status', 'active')->firstOrFail();
        $classrooms = Classroom::where('academic_year_id', $year->id)
                               ->with('students')
                               ->get();

        // Ambil satu user guru per grade sebagai inputBy
        $teachers = Teacher::with('user')->get()->keyBy(fn($t) => $t->user->email);

        $inputByGrade = [
            1 => $teachers['ahmad.fauzi@sekolah.test']->user->id,
            2 => $teachers['siti.rahmawati@sekolah.test']->user->id,
            3 => $teachers['budi.santoso@sekolah.test']->user->id,
            4 => $teachers['dewi.kartika@sekolah.test']->user->id,
            5 => $teachers['hendra.gunawan@sekolah.test']->user->id,
            6 => $teachers['ratna.dewi@sekolah.test']->user->id,
        ];

        foreach ($classrooms as $classroom) {
            $inputBy  = $inputByGrade[$classroom->grade];
            $students = $classroom->students;
            $total    = $students->count();

            $components = AssessmentComponent::with('subject')
                ->where('classroom_id', $classroom->id)
                ->get();

            foreach ([1, 2] as $semester) {
                $semesterComponents = $components->where('semester', $semester);

                foreach ($students as $idx => $student) {
                    foreach ($semesterComponents as $component) {
                        $data = [
                            'student_id'              => $student->id,
                            'assessment_component_id' => $component->id,
                            'academic_year_id'        => $year->id,
                            'classroom_id'            => $classroom->id,
                            'input_by'                => $inputBy,
                            'semester'                => $semester,
                        ];

                        if ($component->type === 'numeric') {
                            $data['score'] = $this->scoreRange($idx, $total, $component->name);

                        } elseif ($component->type === 'predicate') {
                            // Predicate berdasarkan quartile siswa
                            $quartile        = (int) floor(($idx / max($total, 1)) * 4);
                            $data['predicate'] = match ($quartile) {
                                3       => 'A',
                                2       => 'B',
                                1       => 'C',
                                default => 'D',
                            };

                        } elseif ($component->type === 'narrative') {
                            $subjectName     = $component->subject->name ?? '';
                            $texts           = $this->narratives[$subjectName]
                                               ?? ['Peserta didik menunjukkan perkembangan yang baik.'];
                            // Siswa lebih tinggi dapat narasi yang lebih positif
                            $quartile        = (int) floor(($idx / max($total, 1)) * count($texts));
                            $quartile        = min($quartile, count($texts) - 1);
                            $data['narrative'] = $texts[$quartile];
                        }

                        StudentAssessment::create($data);
                    }
                }
            }
        }
    }
}
