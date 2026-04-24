<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $year      = AcademicYear::where('status', 'active')->firstOrFail();
        $classrooms = Classroom::where('academic_year_id', $year->id)
                               ->orderBy('grade')
                               ->get()
                               ->keyBy('name');

        // 5 siswa per kelas, format NIS: tahun + 3 digit urut
        // 'grade' wajib diisi sesuai revisi alur siswa (independent dari rombel)
        $studentData = [
            // ── Kelas 1A (grade 1) ─────────────────────────────────────────────
            ['class' => '1A', 'grade' => 1, 'nis' => '2025001', 'name' => 'Rizky Aditya Pratama',    'gender' => 'L', 'birth' => '2018-03-15', 'parent' => ['name' => 'Bapak Andi Pratama',    'email' => 'wali.rizky@sekolah.test']],
            ['class' => '1A', 'grade' => 1, 'nis' => '2025002', 'name' => 'Nayla Putri Rahayu',      'gender' => 'P', 'birth' => '2018-06-22', 'parent' => ['name' => 'Ibu Sri Rahayu',        'email' => 'wali.nayla@sekolah.test']],
            ['class' => '1A', 'grade' => 1, 'nis' => '2025003', 'name' => 'Dafa Arkan Maulana',      'gender' => 'L', 'birth' => '2018-01-10', 'parent' => ['name' => 'Bapak Irwan Maulana',   'email' => 'wali.dafa@sekolah.test']],
            ['class' => '1A', 'grade' => 1, 'nis' => '2025004', 'name' => 'Syifa Aulia Zahra',       'gender' => 'P', 'birth' => '2018-09-05', 'parent' => null],
            ['class' => '1A', 'grade' => 1, 'nis' => '2025005', 'name' => 'Bagas Nur Hidayat',       'gender' => 'L', 'birth' => '2018-11-18', 'parent' => null],

            // ── Kelas 2A (grade 2) ─────────────────────────────────────────────
            ['class' => '2A', 'grade' => 2, 'nis' => '2025006', 'name' => 'Alya Ramadhani',          'gender' => 'P', 'birth' => '2017-04-20', 'parent' => ['name' => 'Ibu Wulandari',         'email' => 'wali.alya@sekolah.test']],
            ['class' => '2A', 'grade' => 2, 'nis' => '2025007', 'name' => 'Farhan Ramadhan',         'gender' => 'L', 'birth' => '2017-07-14', 'parent' => ['name' => 'Bapak Ramdan',          'email' => 'wali.farhan@sekolah.test']],
            ['class' => '2A', 'grade' => 2, 'nis' => '2025008', 'name' => 'Keisya Nur Fadilah',      'gender' => 'P', 'birth' => '2017-02-08', 'parent' => ['name' => 'Ibu Lestari',           'email' => 'wali.keisya@sekolah.test']],
            ['class' => '2A', 'grade' => 2, 'nis' => '2025009', 'name' => 'Arya Wicaksono',          'gender' => 'L', 'birth' => '2017-10-30', 'parent' => null],
            ['class' => '2A', 'grade' => 2, 'nis' => '2025010', 'name' => 'Adinda Salsabila',        'gender' => 'P', 'birth' => '2017-12-25', 'parent' => null],

            // ── Kelas 3A (grade 3) ─────────────────────────────────────────────
            ['class' => '3A', 'grade' => 3, 'nis' => '2025011', 'name' => 'Naufal Hakim',            'gender' => 'L', 'birth' => '2016-05-17', 'parent' => ['name' => 'Bapak Hakim',           'email' => 'wali.naufal@sekolah.test']],
            ['class' => '3A', 'grade' => 3, 'nis' => '2025012', 'name' => 'Zahra Nur Aisyah',        'gender' => 'P', 'birth' => '2016-08-03', 'parent' => ['name' => 'Ibu Aisyah',            'email' => 'wali.zahra@sekolah.test']],
            ['class' => '3A', 'grade' => 3, 'nis' => '2025013', 'name' => 'Gibran Al Fatih',         'gender' => 'L', 'birth' => '2016-01-27', 'parent' => ['name' => 'Bapak Al Fatih',        'email' => 'wali.gibran@sekolah.test']],
            ['class' => '3A', 'grade' => 3, 'nis' => '2025014', 'name' => 'Fira Aulia Putri',        'gender' => 'P', 'birth' => '2016-11-12', 'parent' => null],
            ['class' => '3A', 'grade' => 3, 'nis' => '2025015', 'name' => 'Zaki Ahmad Fauzan',       'gender' => 'L', 'birth' => '2016-06-09', 'parent' => null],

            // ── Kelas 4A (grade 4) ─────────────────────────────────────────────
            ['class' => '4A', 'grade' => 4, 'nis' => '2025016', 'name' => 'Nabila Khairun Nisa',     'gender' => 'P', 'birth' => '2015-03-21', 'parent' => ['name' => 'Ibu Khoirunnisa',       'email' => 'wali.nabila@sekolah.test']],
            ['class' => '4A', 'grade' => 4, 'nis' => '2025017', 'name' => 'Radit Pratama',           'gender' => 'L', 'birth' => '2015-07-07', 'parent' => ['name' => 'Bapak Pratama',         'email' => 'wali.radit@sekolah.test']],
            ['class' => '4A', 'grade' => 4, 'nis' => '2025018', 'name' => 'Rania Putri Kusuma',      'gender' => 'P', 'birth' => '2015-09-19', 'parent' => ['name' => 'Ibu Kusuma',            'email' => 'wali.rania@sekolah.test']],
            ['class' => '4A', 'grade' => 4, 'nis' => '2025019', 'name' => 'Ilham Fajar Nugroho',     'gender' => 'L', 'birth' => '2015-04-04', 'parent' => null],
            ['class' => '4A', 'grade' => 4, 'nis' => '2025020', 'name' => 'Ayu Lestari',             'gender' => 'P', 'birth' => '2015-12-01', 'parent' => null],

            // ── Kelas 5A (grade 5) ─────────────────────────────────────────────
            ['class' => '5A', 'grade' => 5, 'nis' => '2025021', 'name' => 'Hafidz Ramadhan',         'gender' => 'L', 'birth' => '2014-02-14', 'parent' => ['name' => 'Bapak Ramadhan',        'email' => 'wali.hafidz@sekolah.test']],
            ['class' => '5A', 'grade' => 5, 'nis' => '2025022', 'name' => 'Salsa Bila Rahma',        'gender' => 'P', 'birth' => '2014-05-28', 'parent' => ['name' => 'Ibu Rahma',             'email' => 'wali.salsa@sekolah.test']],
            ['class' => '5A', 'grade' => 5, 'nis' => '2025023', 'name' => 'Rafif Akbar',             'gender' => 'L', 'birth' => '2014-08-16', 'parent' => ['name' => 'Bapak Akbar',           'email' => 'wali.rafif@sekolah.test']],
            ['class' => '5A', 'grade' => 5, 'nis' => '2025024', 'name' => 'Putri Andini',            'gender' => 'P', 'birth' => '2014-10-22', 'parent' => null],
            ['class' => '5A', 'grade' => 5, 'nis' => '2025025', 'name' => 'Akbar Maulana',           'gender' => 'L', 'birth' => '2014-01-31', 'parent' => null],

            // ── Kelas 6A (grade 6) ─────────────────────────────────────────────
            ['class' => '6A', 'grade' => 6, 'nis' => '2025026', 'name' => 'Dinda Permata Sari',      'gender' => 'P', 'birth' => '2013-03-10', 'parent' => ['name' => 'Ibu Permata',           'email' => 'wali.dinda@sekolah.test']],
            ['class' => '6A', 'grade' => 6, 'nis' => '2025027', 'name' => 'Farel Prayoga',           'gender' => 'L', 'birth' => '2013-06-25', 'parent' => ['name' => 'Bapak Prayoga',         'email' => 'wali.farel@sekolah.test']],
            ['class' => '6A', 'grade' => 6, 'nis' => '2025028', 'name' => 'Kiara Aurelia',           'gender' => 'P', 'birth' => '2013-09-08', 'parent' => ['name' => 'Ibu Aurelia',           'email' => 'wali.kiara@sekolah.test']],
            ['class' => '6A', 'grade' => 6, 'nis' => '2025029', 'name' => 'Revan Ardiansyah',        'gender' => 'L', 'birth' => '2013-11-03', 'parent' => null],
            ['class' => '6A', 'grade' => 6, 'nis' => '2025030', 'name' => 'Nazwa Aulia Fitri',       'gender' => 'P', 'birth' => '2013-12-17', 'parent' => null],
        ];

        foreach ($studentData as $data) {
            DB::transaction(function () use ($data, $year, $classrooms) {
                $userId = null;

                if ($data['parent']) {
                    $user = User::create([
                        'name'     => $data['parent']['name'],
                        'email'    => $data['parent']['email'],
                        'password' => Hash::make('password'),
                    ]);
                    $user->assignRole('siswa');
                    $userId = $user->id;
                }

                $student = Student::create([
                    'user_id'    => $userId,
                    'nis'        => $data['nis'],
                    'name'       => $data['name'],
                    'gender'     => $data['gender'],
                    'grade'      => $data['grade'],
                    'birth_date' => $data['birth'],
                    'status'     => 'active',
                ]);

                // Assign ke kelas
                $classroom = $classrooms[$data['class']] ?? null;
                if ($classroom) {
                    $student->classrooms()->attach($classroom->id, [
                        'academic_year_id' => $year->id,
                    ]);
                }
            });
        }
    }
}
