<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('hero_welcome')->nullable()->after('stamp');
            $table->string('hero_tentang')->nullable()->after('hero_welcome');
            $table->string('hero_galeri')->nullable()->after('hero_tentang');
            $table->string('hero_ekskul')->nullable()->after('hero_galeri');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['hero_welcome', 'hero_tentang', 'hero_galeri', 'hero_ekskul']);
        });
    }
};
