<?php

use Illuminate\Support\Facades\Route;

// Endpoint untuk memeriksa kesehatan Order Service.
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'order-service',
    ]);
});
