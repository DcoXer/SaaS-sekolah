<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentService
{
    public function getAll(): Collection
    {
        return Student::with('user')->latest()->get();
    }

    public function getByClassroom(int $classroomId): Collection
    {
        return Student::with('user')
                      ->whereHas('classrooms', fn($q) => $q->where('classroom_id', $classroomId))
                      ->get();
    }

    public function getById(Student $student): Student
    {
        return $student->load(['user', 'classrooms.academicYear']);
    }

    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // Buat user untuk wali murid kalau email diisi
            $userId = null;
            if (!empty($data['email'])) {
                $user = User::create([
                    'name'     => $data['parent_name'],
                    'email'    => $data['email'],
                    'password' => Hash::make($data['password'] ?? 'password'),
                ]);
                $user->assignRole('siswa');
                $userId = $user->id;
            }

            $student = Student::create([
                'user_id'    => $userId,
                'nis'        => $data['nis'],
                'name'       => $data['name'],
                'gender'     => $data['gender'],
                'birth_date' => $data['birth_date'] ?? null,
                'address'    => $data['address'] ?? null,
                'status'     => 'active',
            ]);

            // Assign ke kelas kalau academic_year & classroom dipilih
            if (!empty($data['classroom_id']) && !empty($data['academic_year_id'])) {
                $student->classrooms()->attach($data['classroom_id'], [
                    'academic_year_id' => $data['academic_year_id'],
                ]);
            }

            return $student;
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            $student->update([
                'nis'        => $data['nis'],
                'name'       => $data['name'],
                'gender'     => $data['gender'],
                'birth_date' => $data['birth_date'] ?? null,
                'address'    => $data['address'] ?? null,
            ]);

            if ($student->user && !empty($data['parent_name'])) {
                $student->user->update([
                    'name' => $data['parent_name'],
                ]);

                if (!empty($data['password'])) {
                    $student->user->update([
                        'password' => Hash::make($data['password']),
                    ]);
                }
            }

            return $student->fresh(['user', 'classrooms.academicYear']);
        });
    }

    public function assignToClassroom(Student $student, int $classroomId, int $academicYearId): void
    {
        // Cek apakah sudah ada di tahun ajaran ini
        $exists = $student->classrooms()
                          ->wherePivot('academic_year_id', $academicYearId)
                          ->exists();

        if ($exists) {
            // Update classroom di tahun ajaran yang sama
            $student->classrooms()
                    ->wherePivot('academic_year_id', $academicYearId)
                    ->sync([$classroomId => ['academic_year_id' => $academicYearId]]);
        } else {
            $student->classrooms()->attach($classroomId, [
                'academic_year_id' => $academicYearId,
            ]);
        }
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            if ($student->user) {
                $student->user->delete();
            }
            $student->delete();
        });
    }
}