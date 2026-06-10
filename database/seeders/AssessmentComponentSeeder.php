<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\TeacherSubject;
use Illuminate\Database\Seeder;

class AssessmentComponentSeeder extends Seeder
{
    public function run(): void
    {
        $year       = AcademicYear::where('status', 'active')->firstOrFail();
        $classrooms = Classroom::where('academic_year_id', $year->id)->get();

        foreach ($classrooms as $classroom) {
            // Ambil mapel yang sudah dimasukkan ke kelas ini via teacher_subjects
            $subjectIds = TeacherSubject::where('classroom_id', $classroom->id)
                ->where('academic_year_id', $year->id)
                ->pluck('subject_id')
                ->unique();

            foreach ($subjectIds as $subjectId) {
                foreach ([1, 2] as $semester) {
                    foreach (['ki3', 'ki4'] as $ki) {
                        // Tugas Harian
                        AssessmentComponent::create([
                            'academic_year_id' => $year->id,
                            'classroom_id'     => $classroom->id,
                            'subject_id'       => $subjectId,
                            'name'             => 'Tugas Harian',
                            'type'             => 'numeric',
                            'ki'               => $ki,
                            'weight'           => 30,
                            'min_score'        => 0,
                            'max_score'        => 100,
                            'order'            => 1,
                            'semester'         => $semester,
                        ]);

                        // PTS
                        AssessmentComponent::create([
                            'academic_year_id' => $year->id,
                            'classroom_id'     => $classroom->id,
                            'subject_id'       => $subjectId,
                            'name'             => 'Penilaian Tengah Semester',
                            'type'             => 'numeric',
                            'ki'               => $ki,
                            'weight'           => 30,
                            'min_score'        => 0,
                            'max_score'        => 100,
                            'order'            => 2,
                            'semester'         => $semester,
                        ]);

                        // PAS
                        AssessmentComponent::create([
                            'academic_year_id' => $year->id,
                            'classroom_id'     => $classroom->id,
                            'subject_id'       => $subjectId,
                            'name'             => 'Penilaian Akhir Semester',
                            'type'             => 'numeric',
                            'ki'               => $ki,
                            'weight'           => 40,
                            'min_score'        => 0,
                            'max_score'        => 100,
                            'order'            => 3,
                            'semester'         => $semester,
                        ]);
                    }

                    // Narasi (tidak pakai ki)
                    AssessmentComponent::create([
                        'academic_year_id' => $year->id,
                        'classroom_id'     => $classroom->id,
                        'subject_id'       => $subjectId,
                        'name'             => 'Deskripsi Capaian',
                        'type'             => 'narrative',
                        'ki'               => null,
                        'weight'           => 0,
                        'min_score'        => 0,
                        'max_score'        => 0,
                        'order'            => 7,
                        'semester'         => $semester,
                    ]);
                }
            }
        }
    }
}
