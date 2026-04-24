<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    User::whereNotNull('alumni_expires_at')
        ->where('alumni_expires_at', '<', now())
        ->each(function ($user) {
            // Hapus user account saja, data siswa tetap ada
            $user->delete();
        });
})->dailyAt('02:00')->name('cleanup-expired-alumni');
