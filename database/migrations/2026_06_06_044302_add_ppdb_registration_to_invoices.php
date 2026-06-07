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
        Schema::table('invoices', function (Blueprint $table) {
            // student_id jadi nullable — invoice bisa dibuat sebelum siswa terdaftar (fase PPDB)
            $table->foreignId('student_id')->nullable()->change();
            // Link ke pendaftar PPDB sebelum dikonversi jadi siswa
            $table->foreignId('ppdb_registration_id')->nullable()->after('student_id')
                  ->constrained('ppdb_registrations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['ppdb_registration_id']);
            $table->dropColumn('ppdb_registration_id');
            $table->foreignId('student_id')->nullable(false)->change();
        });
    }
};
