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
        Schema::table('ppdb_settings', function (Blueprint $table) {
            // Nominal total uang masuk (biaya pendaftaran penuh)
            $table->unsignedBigInteger('uang_masuk_amount')->nullable()->after('quota');
            // Nominal DP yang wajib dibayar setelah diterima untuk konfirmasi tempat
            $table->unsignedBigInteger('dp_amount')->nullable()->after('uang_masuk_amount');
            // Payment type yang dipakai untuk invoice uang masuk (cycle: once)
            $table->foreignId('payment_type_id')->nullable()->after('dp_amount')
                  ->constrained('payment_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_settings', function (Blueprint $table) {
            $table->dropForeign(['payment_type_id']);
            $table->dropColumn(['uang_masuk_amount', 'dp_amount', 'payment_type_id']);
        });
    }
};
