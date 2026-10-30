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

// Mengambil daftar pesanan milik pengguna.
Route::get('/orders/user/{userId}', [
    OrderController::class,
    'indexByUser',
])->whereNumber('userId');

// Mengambil detail pesanan berdasarkan ID.
Route::get('/orders/{id}', [
    OrderController::class,
    'show',
])->whereNumber('id');

// Memproses pembayaran pesanan.
Route::patch('/orders/{id}/pay', [
    OrderController::class,
    'pay',
])->whereNumber('id');