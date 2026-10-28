<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Illuminate\Http\Client\ConnectionException;

class ProductServiceClient
{
    // Mengambil detail produk berdasarkan ID.
    public function getProduct(int $productId): array
    {
        // Mengambil URL Product Service dari konfigurasi.
        $url = rtrim(config('services.product.url'), '/');

        try {
            // Meminta data produk dengan batas waktu 5 detik.
            $response = Http::timeout(5)
                ->get($url . '/products/' . $productId);
        } catch (ConnectionException $e) {
            // Menangani jika Product Service tidak bisa dihubungi.
            throw new RuntimeException(
                'Product Service tidak dapat dihubungi.',
                0,
                $e
            );
        }

        // Menangani jika produk tidak ditemukan.
        if ($response->status() === 404) {
            throw new RuntimeException('Produk tidak ditemukan.');
        }

        // Menangani respons gagal dari Product Service.
        if (!$response->successful()) {
            throw new RuntimeException('Product Service gagal merespons.');
        }

        // Mengembalikan data produk dari respons API.
        return $response->json('data') ?? [];
    }
}
