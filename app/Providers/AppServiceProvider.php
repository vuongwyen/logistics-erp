<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

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
        //
        Paginator::useBootstrap();
        
        // Force HTTPS nếu APP_URL bắt đầu bằng https (ví dụ dùng Cloudflare Tunnel)
        if (\Illuminate\Support\Str::startsWith(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
