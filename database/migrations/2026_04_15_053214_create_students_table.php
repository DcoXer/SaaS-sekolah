<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nik', 20)->nullable();
            $table->string('nis')->nullable()->unique();
            $table->string('name');
            $table->tinyInteger('grade')->default(1);
            $table->enum('gender', ['L', 'P']);
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('father_name', 100)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('guardian_name', 100)->nullable();
            $table->string('parent_phone', 20)->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'alumni', 'mutasi'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
