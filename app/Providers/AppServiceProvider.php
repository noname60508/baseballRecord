<?php

namespace App\Providers;

use Mattiverse\Userstamps\Userstamps;
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
        Userstamps::resolveUsing(function () {
            return auth('sanctum')->id() ?? auth()->id();
        });
    }
}
