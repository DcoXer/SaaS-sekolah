<?php

namespace Tests\Unit\Services;

use App\Models\Teacher;
use App\Models\User;
use App\Services\TeacherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherServiceTest extends TestCase
{
    use RefreshDatabase;

    private TeacherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeacherService();
    }

    public function test_can_create_teacher_with_user(): void
    {
        $data = [
            'name'     => 'Budi Santoso',
            'email'    => 'budi@sekolah.test',
            'password' => 'password',
            'type'     => 'guru_bidang',
            'nip'      => '123456789',
            'gender'   => 'L',
            'phone'    => '08123456789',
        ];

        $teacher = $this->service->create($data);

        $this->assertInstanceOf(Teacher::class, $teacher);
        $this->assertDatabaseHas('teachers', ['nip' => '123456789']);
        $this->assertDatabaseHas('users', ['email' => 'budi@sekolah.test']);
        $this->assertTrue($teacher->user->hasRole('guru'));
    }

    public function test_can_update_teacher(): void
    {
        $teacher = $this->service->create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@sekolah.test',
            'password' => 'password',
            'type'     => 'guru_bidang',
            'gender'   => 'L',
        ]);

        $updated = $this->service->update($teacher, [
            'name'   => 'Budi Updated',
            'email'  => 'budi@sekolah.test',
            'type'   => 'guru_bidang',
            'gender' => 'L',
        ]);

        $this->assertEquals('Budi Updated', $updated->user->name);
    }

    public function test_delete_teacher_also_deletes_user(): void
    {
        $teacher = $this->service->create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@sekolah.test',
            'password' => 'password',
            'type'     => 'guru_bidang',
            'gender'   => 'L',
        ]);

        $userId = $teacher->user_id;
        $this->service->delete($teacher);

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_can_create_guru_kelas(): void
    {
        $teacher = $this->service->create([
            'name'     => 'Budi Santoso',
            'email'    => 'budi@sekolah.test',
            'password' => 'password',
            'type'     => 'guru_kelas',
            'gender'   => 'L',
        ]);

        $this->assertEquals('guru_kelas', $teacher->type);
        $this->assertTrue($teacher->isGuruKelas());
        $this->assertFalse($teacher->isGuruBidang());
    }

    public function test_can_create_guru_bidang(): void
    {
        $teacher = $this->service->create([
            'name'     => 'Siti Aminah',
            'email'    => 'siti@sekolah.test',
            'password' => 'password',
            'type'     => 'guru_bidang',
            'gender'   => 'P',
        ]);

        $this->assertEquals('guru_bidang', $teacher->type);
        $this->assertTrue($teacher->isGuruBidang());
    }
}