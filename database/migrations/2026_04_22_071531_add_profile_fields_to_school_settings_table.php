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
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('tagline', 255)->nullable()->after('name');
            $table->text('description')->nullable()->after('website');
            $table->text('vision')->nullable()->after('description');
            $table->text('mission')->nullable()->after('vision');
            $table->text('history')->nullable()->after('mission');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'description', 'vision', 'mission', 'history']);
        });
    }
};
