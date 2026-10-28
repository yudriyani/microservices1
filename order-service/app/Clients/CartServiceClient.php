<?php

namespace App\Clients;

use App\Exceptions\ServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CartServiceClient
{
    // Mengambil keranjang berdasarkan ID pengguna.
    public function getCart(int $userId): array
    {
        // Mengambil alamat Cart Service dari konfigurasi.
        $url = rtrim(config('services.cart.url'), '/');

        try {
            // Meminta data keranjang dengan timeout maksimal 5 detik.
            $response = Http::timeout(5)
                ->get($url . '/carts/' . $userId);
        } catch (ConnectionException $e) {
            // Menandai kegagalan koneksi sebagai error layanan.
            throw new ServiceUnavailableException('Cart Service', $e);
        }

        // Respons HTTP gagal menandakan layanan tidak dapat memenuhi permintaan.
        if (!$response->successful()) {
            throw new ServiceUnavailableException('Cart Service');
        }

        // Mengembalikan data keranjang dari respons API.
        return $response->json('data') ?? [];
    }

    // Mengosongkan keranjang setelah pesanan berhasil dibuat.
    public function clearCart(int $userId): void
    {
        // Mengambil alamat Cart Service dari konfigurasi.
        $url = rtrim(config('services.cart.url'), '/');

        try {
            // Mengirim permintaan penghapusan dengan timeout maksimal 5 detik.
            $response = Http::timeout(5)
                ->delete($url . '/carts/' . $userId);
        } catch (ConnectionException $e) {
            // Menandai kegagalan koneksi sebagai error layanan.
            throw new ServiceUnavailableException('Cart Service', $e);
        }

        // Memastikan Cart Service berhasil mengosongkan keranjang.
        if (!$response->successful()) {
            throw new ServiceUnavailableException('Cart Service');
        }
    }
}
