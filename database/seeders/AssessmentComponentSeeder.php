<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class AssessmentComponentSeeder extends Seeder
{
    public function run(): void
    {
        $year       = AcademicYear::where('status', 'active')->firstOrFail();
        $classrooms = Classroom::where('academic_year_id', $year->id)->get();

        foreach ($classrooms as $classroom) {
            $subjects = Subject::where('grade', $classroom->grade)->get();

            foreach ($subjects as $subject) {
                foreach ([1, 2] as $semester) {
                    foreach (['ki3', 'ki4'] as $ki) {
                        // Tugas Harian
                        AssessmentComponent::create([
                            'academic_year_id' => $year->id,
                            'classroom_id'     => $classroom->id,
                            'subject_id'       => $subject->id,
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
                            'subject_id'       => $subject->id,
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
                            'subject_id'       => $subject->id,
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

                    // Narasi (tidak perlu ki)
                    AssessmentComponent::create([
                        'academic_year_id' => $year->id,
                        'classroom_id'     => $classroom->id,
                        'subject_id'       => $subject->id,
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
