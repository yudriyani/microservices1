<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutConflictException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    // Menyiapkan Service untuk menjalankan proses bisnis pesanan.
    public function __construct(
        private OrderService $orderService
    ) {
    }

    // Menangani checkout dan mengembalikan hasilnya dalam format JSON.
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            // Mengambil data yang sudah lolos validasi.
            $data = $request->validated();

            // Mengambil keranjang dan menolak keranjang kosong.
            $cart = $this->orderService->getCartForCheckout(
                (int) $data['user_id']
            );

            // Memeriksa produk dan stok sebelum membuat pesanan.
            $items = $this->orderService->validateCartProducts($cart);

            // Menyimpan pesanan dan detailnya dalam transaksi database.
            $order = $this->orderService->createOrder(
                (int) $data['user_id'],
                $items
            );

            try {
                // Mengosongkan keranjang setelah pesanan berhasil dibuat.
                $this->orderService->clearCartAfterCheckout(
                    (int) $data['user_id']
                );
            } catch (Throwable $e) {
                // Kegagalan mengosongkan keranjang tidak membatalkan pesanan.
                Log::warning(
                    'Pesanan tersimpan, tetapi keranjang gagal dikosongkan',
                    [
                        'user_id' => (int) $data['user_id'],
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]
                );
            }

            return response()->json([
                'message' => 'Pesanan berhasil dibuat.',
                'data' => $order,
            ], 201);
        } catch (CheckoutConflictException $e) {
            // Mengembalikan 409 untuk konflik bisnis saat checkout.
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        } catch (ServiceUnavailableException $e) {
            // Mencatat kegagalan layanan dan mengembalikan 503.
            Log::warning('Layanan pendukung Order Service gagal diakses', [
                'error' => $e->getMessage(),
            ]);

            $body = [
                'message' => 'Layanan sementara tidak tersedia.',
            ];

            // Detail teknis hanya ditampilkan saat debug aktif.
            if (config('app.debug')) {
                $body['error'] = $e->getMessage();
            }

            return response()->json($body, 503);
        } catch (Throwable $e) {
            // Mencatat kesalahan tak terduga tanpa membocorkan detail.
            Log::error('Gagal membuat pesanan', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $body = [
                'message' => 'Terjadi kesalahan saat membuat pesanan.',
            ];

            // Detail teknis hanya ditampilkan saat debug aktif.
            if (config('app.debug')) {
                $body['error'] = $e->getMessage();
            }

            return response()->json($body, 500);
        }
    }

    // Mengambil satu pesanan beserta detail itemnya.
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        // Mengembalikan 404 jika pesanan tidak ditemukan.
        if ($order === null) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'data' => $order,
        ], 200);
    }
}
