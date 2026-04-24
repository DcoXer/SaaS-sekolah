<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicYearService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearServiceTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYearService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AcademicYearService();
    }

    public function test_can_create_academic_year(): void
    {
        $data = [
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
        ];

        $academicYear = $this->service->create($data);

        $this->assertInstanceOf(AcademicYear::class, $academicYear);
        $this->assertDatabaseHas('academic_years', [
            'name'   => '2024/2025',
            'status' => 'pending',
        ]);
    }

    public function test_new_academic_year_status_is_pending(): void
    {
        $academicYear = $this->service->create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
        ]);

        $this->assertTrue($academicYear->isPending());
        $this->assertFalse($academicYear->isActive());
    }

    public function test_approve_sets_status_to_active(): void
    {
        $academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'pending',
        ]);

        $this->service->approve($academicYear);

        $this->assertTrue($academicYear->fresh()->isActive());
    }

    public function test_approve_closes_previous_active_academic_year(): void
    {
        $active = AcademicYear::create([
            'name'       => '2023/2024',
            'start_date' => '2023-07-15',
            'end_date'   => '2024-06-30',
            'status'     => 'active',
        ]);

        $pending = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'pending',
        ]);

        $this->service->approve($pending);

        $this->assertTrue($active->fresh()->isClosed());
        $this->assertTrue($pending->fresh()->isActive());
    }

    public function test_get_active_returns_active_academic_year(): void
    {
        AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $active = $this->service->getActive();

        $this->assertNotNull($active);
        $this->assertTrue($active->isActive());
    }

    public function test_get_active_returns_null_when_no_active_year(): void
    {
        $active = $this->service->getActive();

        $this->assertNull($active);
    }

    public function test_close_sets_status_to_closed(): void
    {
        $academicYear = AcademicYear::create([
            'name'       => '2024/2025',
            'start_date' => '2024-07-15',
            'end_date'   => '2025-06-30',
            'status'     => 'active',
        ]);

        $this->service->close($academicYear);

        $this->assertTrue($academicYear->fresh()->isClosed());
    }

    public function test_approve_promotes_students(): void
    {
        // Buat siswa grade 1-6
        foreach (range(1, 6) as $grade) {
            Student::create([
                'nis'    => "NIS00{$grade}",
                'name'   => "Siswa Grade {$grade}",
                'gender' => 'L',
                'grade'  => $grade,
                'status' => 'active',
            ]);
        }

        $pending = AcademicYear::create([
            'name'       => '2025/2026',
            'start_date' => '2025-07-15',
            'end_date'   => '2026-06-30',
            'status'     => 'pending',
        ]);

        $this->service->approve($pending);

        // Grade 1-5 naik satu tingkat
        foreach (range(1, 5) as $originalGrade) {
            $this->assertDatabaseHas('students', [
                'nis'   => "NIS00{$originalGrade}",
                'grade' => $originalGrade + 1,
            ]);
        }

        // Grade 6 jadi alumni
        $grade6 = Student::where('nis', 'NIS006')->first();
        $this->assertEquals('alumni', $grade6->status);
    }

    public function test_approve_sets_alumni_expires_at_for_graduating_students(): void
    {
        $user = User::create([
            'name'     => 'Wali Murid',
            'email'    => 'wali@test.test',
            'password' => bcrypt('password'),
        ]);

        Student::create([
            'user_id' => $user->id,
            'nis'     => 'NIS006',
            'name'    => 'Siswa Lulus',
            'gender'  => 'L',
            'grade'   => 6,
            'status'  => 'active',
        ]);

        $pending = AcademicYear::create([
            'name'       => '2025/2026',
            'start_date' => '2025-07-15',
            'end_date'   => '2026-06-30',
            'status'     => 'pending',
        ]);

        $this->service->approve($pending);

        $this->assertNotNull($user->fresh()->alumni_expires_at);
    }
}