<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Truncate semua data lama (cascade manual karena TRUNCATE tidak trigger FK cascade)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('student_assessments')->truncate();
        DB::table('assessment_components')->truncate();
        DB::table('teacher_subjects')->truncate();
        DB::table('subjects')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Hapus kolom grade dari subjects
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('grade');
        });

        // Update teacher_subjects: hapus FK + unique lama, buat teacher_id nullable, unique baru
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropUnique('ts_unique');
        });

        DB::statement('ALTER TABLE teacher_subjects MODIFY teacher_id BIGINT UNSIGNED NULL');

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
            $table->unique(['subject_id', 'classroom_id', 'academic_year_id'], 'ts_subject_classroom_year_unique');
        });
    }

    public function down(): void
    {
        // Rollback: kembalikan ke struktur lama (data tidak bisa dikembalikan)
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropUnique('ts_subject_classroom_year_unique');
        });

        DB::statement('ALTER TABLE teacher_subjects MODIFY teacher_id BIGINT UNSIGNED NOT NULL');

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->unique(['teacher_id', 'subject_id', 'classroom_id', 'academic_year_id'], 'ts_unique');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->tinyInteger('grade')->default(1)->after('name');
        });
    }
};
