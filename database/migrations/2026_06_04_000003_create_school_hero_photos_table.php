<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_hero_photos', function (Blueprint $table) {
            $table->id();
            $table->enum('page', ['welcome', 'tentang', 'galeri', 'ekskul']);
            $table->string('file_path');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_hero_photos');
    }
};
