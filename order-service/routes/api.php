<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Memeriksa kesehatan Order Service.
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'order-service',
    ]);
});

// Membuat pesanan melalui proses checkout.
Route::post('/orders', [OrderController::class, 'store']);

// Mengambil detail pesanan berdasarkan ID.
Route::get('/orders/{id}', [OrderController::class, 'show'])
    ->whereNumber('id');
