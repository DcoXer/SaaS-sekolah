<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->after('nisn');
            $table->string('birth_place', 100)->nullable()->after('nik');
            $table->string('father_name', 100)->nullable()->after('address');
            $table->string('mother_name', 100)->nullable()->after('father_name');
            $table->string('guardian_name', 100)->nullable()->after('mother_name');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['nik', 'birth_place', 'father_name', 'mother_name', 'guardian_name']);
        });
    }
};
