<?php

// bootstrap/app.php
// Titik konfigurasi utama aplikasi Laravel: route, middleware, dan penanganan error.

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua error yang lolos dari controller dibalas JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $e) => true,
        );
    })
    ->create();