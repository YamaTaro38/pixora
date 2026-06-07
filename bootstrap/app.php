<?php
// bootstrap/app.php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Exclude CSRF untuk route callback Midtrans
        $middleware->validateCsrfTokens(except: [
            '/payment/midtrans-callback',
            'api/midtrans-callback',
            '/booking/*/payment-callback',
            'booking/*/payment',
            'api/*',
        ]);

        $middleware->append(\App\Http\Middleware\Cors::class);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();