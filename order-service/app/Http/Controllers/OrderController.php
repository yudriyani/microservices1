<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutConflictException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    // Menyiapkan Service untuk menjalankan proses bisnis pesanan.
    public function __construct(
        private OrderService $orderService
    ) {
    }

    // Menangani proses checkout pesanan.
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Mengambil keranjang pengguna.
            $cart = $this->orderService->getCartForCheckout(
                (int) $data['user_id']
            );

            // Memeriksa produk dan ketersediaan stok.
            $items = $this->orderService->validateCartProducts($cart);

            // Menyimpan pesanan beserta detailnya.
            $order = $this->orderService->createOrder(
                (int) $data['user_id'],
                $items
            );

            try {
                // Mengosongkan keranjang setelah pesanan tersimpan.
                $this->orderService->clearCartAfterCheckout(
                    (int) $data['user_id']
                );
            } catch (Throwable $e) {
                // Pesanan tetap berhasil jika pengosongan keranjang gagal.
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
                'message' => 'Pesanan berhasil dibuat',
                'data' => $order,
            ], 201);

        } catch (CheckoutConflictException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);

        } catch (ServiceUnavailableException $e) {
            Log::warning('Layanan pendukung Order Service gagal diakses', [
                'error' => $e->getMessage(),
            ]);

            $body = [
                'message' => 'Layanan pendukung tidak tersedia, coba lagi nanti',
            ];

            if (config('app.debug')) {
                $body['error'] = $e->getMessage();
            }

            return response()->json($body, 503);

        } catch (Throwable $e) {
            Log::error('Gagal membuat pesanan', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $body = [
                'message' => 'Terjadi kesalahan saat membuat pesanan',
            ];

            if (config('app.debug')) {
                $body['error'] = $e->getMessage();
            }

            return response()->json($body, 500);
        }
    }

    // Mengambil detail satu pesanan.
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        if ($order === null) {
            return response()->json([
                'message' => "Pesanan dengan id {$id} tidak ditemukan",
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil pesanan',
            'data' => $order,
        ], 200);
    }

    // Mengambil daftar pesanan milik pengguna.
    public function indexByUser(
        Request $request,
        int $userId
    ): JsonResponse {
        try {
            $validated = $request->validate(
                [
                    'status' => ['sometimes', 'string', 'in:PENDING,PAID'],
                ],
                [
                    'status.in' => 'Status harus PENDING atau PAID',
                ]
            );

            $orders = $this->orderService->getOrdersByUserId(
                $userId,
                $validated['status'] ?? null
            );

            return response()->json([
                'message' => 'Berhasil mengambil daftar pesanan',
                'data' => $orders,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Data yang dikirim tidak valid',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    // Menangani pembayaran pesanan.
    public function pay(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->payOrder($id);

            if ($order === null) {
                return response()->json([
                    'message' => "Pesanan dengan id {$id} tidak ditemukan",
                ], 404);
            }

            return response()->json([
                'message' => 'Pesanan berhasil dibayar',
                'data' => $order,
            ], 200);

        } catch (CheckoutConflictException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);

        } catch (Throwable $e) {
            Log::error('Gagal memproses pembayaran pesanan', [
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);

            $body = [
                'message' => 'Terjadi kesalahan saat memproses pembayaran',
            ];

            if (config('app.debug')) {
                $body['error'] = $e->getMessage();
            }

            return response()->json($body, 500);
        }
    }
}
