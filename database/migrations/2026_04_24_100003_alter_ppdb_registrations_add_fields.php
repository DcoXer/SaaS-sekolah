<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->string('nik_siswa', 16)->nullable()->after('full_name');
            $table->string('no_kk', 16)->nullable()->after('nik_siswa');
            $table->string('province')->nullable()->after('address');
            $table->string('regency')->nullable()->after('province');
            $table->string('district')->nullable()->after('regency');
            $table->string('village')->nullable()->after('district');
            $table->string('father_name')->nullable()->after('parent_email');
            $table->string('father_nik', 16)->nullable()->after('father_name');
            $table->string('father_phone', 20)->nullable()->after('father_nik');
            $table->string('mother_name')->nullable()->after('father_phone');
            $table->string('mother_nik', 16)->nullable()->after('mother_name');
            $table->string('mother_phone', 20)->nullable()->after('mother_nik');
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'nik_siswa', 'no_kk',
                'province', 'regency', 'district', 'village',
                'father_name', 'father_nik', 'father_phone',
                'mother_name', 'mother_nik', 'mother_phone',
            ]);
        });
    }
};
