<?php

use Illuminate\Support\Facades\Schedule;

// Jalankan scheduler: `php artisan schedule:work` (lokal) atau cron `* * * * * php artisan schedule:run` (server).
Schedule::command('reservations:expire-pending')->everyFiveMinutes();
