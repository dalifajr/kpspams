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
        // Configure paths for Vercel serverless (writable /tmp directory)
        if (isset($_ENV['VERCEL']) || getenv('VERCEL')) {
            config(['view.compiled' => '/tmp/views']);
            config(['cache.stores.file.path' => '/tmp/cache']);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
