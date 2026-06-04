<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->smallInteger('year');
            $table->string('level'); // kecamatan/kabupaten/kota/provinsi/nasional/internasional
            $table->string('rank');  // e.g. "Juara 1", "Juara 2", "Harapan 1"
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_achievements');
    }
};
