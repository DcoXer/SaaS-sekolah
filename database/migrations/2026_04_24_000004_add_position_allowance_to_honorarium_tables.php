<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_teaching_hours', function (Blueprint $table) {
            $table->string('position_name')->nullable()->after('daily_transport_rate');
            $table->unsignedInteger('position_allowance')->nullable()->after('position_name');
        });

        Schema::table('teacher_honorariums', function (Blueprint $table) {
            $table->string('position_name')->nullable()->after('daily_transport_rate');
            $table->unsignedBigInteger('position_allowance')->default(0)->after('position_name');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_teaching_hours', function (Blueprint $table) {
            $table->dropColumn(['position_name', 'position_allowance']);
        });

        Schema::table('teacher_honorariums', function (Blueprint $table) {
            $table->dropColumn(['position_name', 'position_allowance']);
        });
    }
};
