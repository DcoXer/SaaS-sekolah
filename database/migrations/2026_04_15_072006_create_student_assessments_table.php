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
        Schema::create('student_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('input_by')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('semester'); // 1 atau 2
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('predicate', 5)->nullable();
            $table->text('narrative')->nullable();
            $table->timestamps();

            $table->unique(
                ['student_id', 'assessment_component_id', 'semester'],
                'sa_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_assessments');
    }
};
