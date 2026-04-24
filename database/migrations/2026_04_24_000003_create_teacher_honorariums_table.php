<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_honorariums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            // snapshot saat generate (agar perubahan rate tidak ubah slip lama)
            $table->unsignedSmallInteger('teaching_hours');
            $table->unsignedInteger('hourly_rate');
            $table->unsignedSmallInteger('transport_days');
            $table->unsignedInteger('daily_transport_rate');
            // kalkulasi
            $table->unsignedBigInteger('teaching_hours_amount');
            $table->unsignedBigInteger('transport_amount');
            $table->unsignedBigInteger('total_amount');
            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('tu_keuangan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('slip_code')->nullable()->unique();
            $table->timestamps();
            $table->unique(['teacher_id', 'period_month', 'period_year'], 'th_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_honorariums');
    }
};
