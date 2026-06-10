<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_teaching_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('total_hours');
            $table->unsignedInteger('hourly_rate');
            $table->unsignedInteger('daily_transport_rate');
            $table->string('position_name')->nullable();
            $table->unsignedInteger('position_allowance')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'academic_year_id'], 'tth_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_teaching_hours');
    }
};
