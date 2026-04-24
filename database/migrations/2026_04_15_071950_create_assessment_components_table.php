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
        Schema::create('assessment_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Nilai Harian", "UTS", "TP-1"
            $table->enum('type', ['numeric', 'predicate', 'narrative']);
            $table->unsignedTinyInteger('weight')->default(0); // persen, total harus 100
            $table->unsignedTinyInteger('min_score')->default(0);
            $table->unsignedTinyInteger('max_score')->default(100);
            $table->unsignedTinyInteger('order')->default(0);
            $table->tinyInteger('semester'); // 1 atau 2
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_components');
    }
};
