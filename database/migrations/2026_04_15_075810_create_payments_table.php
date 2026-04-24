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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tu_keuangan_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('method', ['cash', 'midtrans']);
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_status')->nullable();
            $table->string('proof_file')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
