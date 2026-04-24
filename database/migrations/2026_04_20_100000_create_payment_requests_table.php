<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'processed'])->default('pending');
            $table->timestamps();

            $table->unique('invoice_id'); // satu invoice satu request aktif
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
