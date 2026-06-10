<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_setting_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number')->unique();
            $table->string('full_name');
            $table->string('nik_siswa', 16)->nullable();
            $table->string('no_kk', 16)->nullable();
            $table->string('birth_place');
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->string('religion')->nullable();
            $table->text('address');
            $table->string('province')->nullable();
            $table->string('regency')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->string('parent_email')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_nik', 16)->nullable();
            $table->string('father_phone', 20)->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_nik', 16)->nullable();
            $table->string('mother_phone', 20)->nullable();
            $table->string('previous_school')->nullable();
            $table->string('photo')->nullable();
            $table->string('document_kk')->nullable();
            $table->string('document_akta')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'waitlisted'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_registrations');
    }
};
