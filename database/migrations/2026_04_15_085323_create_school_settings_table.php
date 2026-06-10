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
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline', 255)->nullable();
            $table->string('npsn')->nullable();
            $table->string('principal_name');
            $table->string('principal_nip')->nullable();
            $table->text('address');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('attendance_radius')->default(100);
            $table->text('description')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('history')->nullable();
            $table->string('logo')->nullable();
            $table->string('stamp')->nullable(); // stempel sekolah
            $table->string('hero_welcome')->nullable();
            $table->string('hero_tentang')->nullable();
            $table->string('hero_galeri')->nullable();
            $table->string('hero_ekskul')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
