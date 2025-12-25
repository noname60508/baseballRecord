<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use App\Http\Middleware\authentication;

$namespace = 'App\Http\Controllers';
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () use ($namespace): void {
            $names = [
                'A00',
                // 'B00',
                // 'C00',
                // 'Z00',
            ];

            foreach ($names as $name) {
                Route::middleware(['api', 'authentication'/* , 'tokenAuthentication' */])
                    ->prefix('api/' . $name)
                    ->namespace($namespace . '\\' . $name)
                    ->group(base_path('routes/api/' . $name . '_system_api.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'abilities'      => CheckAbilities::class,
            'ability'        => CheckForAnyAbility::class,
            // token檢查
            'authentication' => authentication::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
