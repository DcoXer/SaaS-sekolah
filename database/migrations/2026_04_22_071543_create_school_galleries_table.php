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
        Schema::create('school_galleries', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->string('file_path')->nullable();   // untuk upload foto
            $table->string('video_url', 500)->nullable(); // YouTube URL
            $table->string('caption', 200)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_galleries');
    }
};
