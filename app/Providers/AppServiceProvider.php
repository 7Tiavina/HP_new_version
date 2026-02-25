<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS URLs when APP_URL uses https (fixes redirect loop behind Hostinger proxy)
        $appUrl = config('app.url');
        if (!empty($appUrl)) {
            $scheme = parse_url($appUrl, PHP_URL_SCHEME);
            if ($scheme === 'https') {
                URL::forceRootUrl($appUrl);
                URL::forceScheme('https');
            }
        }
    }
}
