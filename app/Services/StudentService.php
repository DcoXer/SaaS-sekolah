<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    public function generateNis(): string
    {
        $prefix = now()->format('Ym'); // e.g., "202606"
        $last   = Student::where('nis', 'like', $prefix . '%')
                         ->lockForUpdate()
                         ->orderByDesc('nis')
                         ->value('nis');
        $seq    = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
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
                    // Generate password aman jika tidak diisi — hindari default 'password'
                    'password' => Hash::make($data['password'] ?? Str::password(12)),
                ]);
                $user->assignRole('siswa');
                $userId = $user->id;
            }

            $student = Student::create([
                'user_id'       => $userId,
                'nisn'          => $data['nisn'] ?? null,
                'nik'           => $data['nik'] ?? null,
                'nis'           => !empty($data['nis']) ? $data['nis'] : $this->generateNis(),
                'name'          => $data['name'],
                'gender'        => $data['gender'],
                'grade'         => $data['grade'],
                'birth_place'   => $data['birth_place'] ?? null,
                'birth_date'    => $data['birth_date'] ?? null,
                'address'       => $data['address'] ?? null,
                'father_name'   => $data['father_name'] ?? null,
                'mother_name'   => $data['mother_name'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'status'        => 'active',
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
                'nisn'          => $data['nisn'] ?? null,
                'nik'           => $data['nik'] ?? null,
                'nis'           => $data['nis'] ?? null,
                'name'          => $data['name'],
                'gender'        => $data['gender'],
                'grade'         => $data['grade'],
                'birth_place'   => $data['birth_place'] ?? null,
                'birth_date'    => $data['birth_date'] ?? null,
                'address'       => $data['address'] ?? null,
                'father_name'   => $data['father_name'] ?? null,
                'mother_name'   => $data['mother_name'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
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
        // Cek status tahun ajaran — hanya boleh assign ke tahun yang masih active
        $academicYear = AcademicYear::findOrFail($academicYearId);
        abort_if(
            $academicYear->status === 'closed',
            422,
            'Tidak dapat assign siswa ke kelas pada tahun ajaran yang sudah ditutup.'
        );

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

    public function generateAccount(Student $student, array $data): void
    {
        abort_if($student->user_id !== null, 422, 'Siswa ini sudah memiliki akun.');

        DB::transaction(function () use ($student, $data) {
            $user = User::create([
                'name'     => $data['parent_name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('siswa');
            $student->update(['user_id' => $user->id]);
        });
    }

    public function bulkGenerateAccounts(): array
    {
        $students = Student::whereNull('user_id')->get();
        if ($students->isEmpty()) return [];

        $credentials = [];
        $chars       = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';

        // Pre-load existing emails untuk O(1) uniqueness check (1 query)
        $takenEmails = User::where('email', 'like', '%@siswa.sekolah.id')
            ->pluck('email')
            ->flip();

        // Ambil role ID sekali — hindari N query Spatie (1 query)
        $siswaRoleId = DB::table('roles')
            ->where('name', 'siswa')
            ->where('guard_name', 'web')
            ->value('id');

        DB::transaction(function () use ($students, $chars, $siswaRoleId, &$takenEmails, &$credentials) {
            $newUserIds = []; // [student_id => user_id]

            foreach ($students as $student) {
                $base   = $student->nisn ?? $student->nis ?? 'siswa' . $student->id;
                $base   = Str::slug($base, '.');
                $email  = $base . '@siswa.sekolah.id';
                $suffix = 1;
                while (isset($takenEmails[$email])) {
                    $email = $base . $suffix++ . '@siswa.sekolah.id';
                }
                $takenEmails[$email] = true;

                $password = '';
                for ($i = 0; $i < 10; $i++) {
                    $password .= $chars[random_int(0, strlen($chars) - 1)];
                }

                $parentName = $student->guardian_name ?? $student->father_name ?? $student->name;

                $user = User::create([
                    'name'     => $parentName,
                    'email'    => $email,
                    'password' => Hash::make($password),
                ]);

                $newUserIds[$student->id] = $user->id;

                $credentials[] = [
                    'student_name' => $student->name,
                    'parent_name'  => $parentName,
                    'email'        => $email,
                    'password'     => $password,
                ];
            }

            // Batch assign role — 1 insert ganti N assignRole() + N cache invalidation
            if ($siswaRoleId && !empty($newUserIds)) {
                DB::table('model_has_roles')->insert(array_map(fn($userId) => [
                    'role_id'    => $siswaRoleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id'   => $userId,
                ], array_values($newUserIds)));
            }

            // Batch update student user_id — 1 query CASE WHEN ganti N individual updates
            if (!empty($newUserIds)) {
                $studentIds = array_keys($newUserIds);
                $cases = implode(' ', array_map(
                    fn($sid) => "WHEN {$sid} THEN {$newUserIds[$sid]}",
                    $studentIds
                ));
                DB::statement(
                    'UPDATE students SET user_id = CASE id ' . $cases . ' END WHERE id IN (' . implode(',', $studentIds) . ')'
                );
            }
        });

        // Reset Spatie permission cache sekali setelah bulk insert
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return $credentials;
    }

    public function bulkResetAccounts(): array
    {
        $activeYearId = AcademicYear::where('status', 'active')->value('id');

        $students = Student::whereNotNull('user_id')
            ->with(['user', 'classrooms' => function ($q) use ($activeYearId) {
                if ($activeYearId) {
                    $q->wherePivot('academic_year_id', $activeYearId);
                }
            }])
            ->get();

        $grouped = [];
        $chars   = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';

        DB::transaction(function () use ($students, $chars, &$grouped) {
            foreach ($students as $student) {
                if (!$student->user) continue;

                $password = '';
                for ($i = 0; $i < 10; $i++) {
                    $password .= $chars[random_int(0, strlen($chars) - 1)];
                }

                $student->user->update(['password' => Hash::make($password)]);

                // Kelompokkan per rombel, fallback ke "Kelas {grade}"
                $classroom  = $student->classrooms->first();
                $sheetName  = $classroom?->name ?? 'Kelas ' . $student->grade;

                $grouped[$sheetName][] = [
                    'student_name' => $student->name,
                    'parent_name'  => $student->user->name,
                    'email'        => $student->user->email,
                    'password'     => $password,
                ];
            }
        });

        // Urutkan sheet: nama rombel ascending, "Kelas X" tanpa rombel di akhir
        uksort($grouped, function ($a, $b) {
            $aIsRombel = !preg_match('/^Kelas \d+$/', $a);
            $bIsRombel = !preg_match('/^Kelas \d+$/', $b);
            if ($aIsRombel !== $bIsRombel) return $aIsRombel ? -1 : 1;
            return strnatcmp($a, $b);
        });

        return $grouped;
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
