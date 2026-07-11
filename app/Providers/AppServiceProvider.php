<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // ← Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Force HTTPS di semua URL
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    public function register(): void
    {
        //
    }
}