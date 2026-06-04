<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('website');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->integer('attendance_radius')->default(100)->after('longitude');
        });

        Schema::table('teacher_attendances', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('notes');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'attendance_radius']);
        });

        Schema::table('teacher_attendances', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
