<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Nama hari/bulan dalam Bahasa Indonesia untuk translatedFormat()
        Carbon::setLocale(config('app.locale', 'id'));
    }
}
