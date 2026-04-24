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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_template_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['keterangan', 'pemberitahuan']);
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('target_grade')->nullable();
            $table->enum('status', [
                'draft',
                'waiting_approval',
                'approved',
                'rejected',
                'published'
            ])->default('draft');
            $table->text('rejection_note')->nullable();
            $table->longText('content');
            $table->string('barcode_code')->nullable()->unique();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
