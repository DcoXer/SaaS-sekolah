<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->date('registration_start');
            $table->date('registration_end');
            $table->date('announcement_date')->nullable();
            $table->unsignedInteger('quota')->default(0);
            $table->unsignedBigInteger('uang_masuk_amount')->nullable();
            $table->unsignedBigInteger('dp_amount')->nullable();
            $table->boolean('is_open')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_settings');
    }
};
