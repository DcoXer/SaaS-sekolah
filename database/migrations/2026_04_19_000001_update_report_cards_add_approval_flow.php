<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->enum('status', ['draft', 'waiting_approval', 'approved'])
                  ->default('draft')
                  ->change();

            $table->string('verify_code')->nullable()->unique()->after('status');
            $table->timestamp('approved_at')->nullable()->after('verify_code');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
        });

        // Hapus kolom published_at & published_by yang sudah diganti
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropColumn(['published_at', 'published_by']);
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['verify_code', 'approved_at', 'approved_by']);

            $table->enum('status', ['draft', 'published'])->default('draft')->change();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }
};
