<?php

namespace App\Clients;

use App\Exceptions\ServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ProductServiceClient
{
    // Mengambil detail produk berdasarkan ID.
    public function getProduct(int $productId): array
    {
        // Mengambil alamat Product Service dari konfigurasi.
        $url = rtrim(config('services.product.url'), '/');

        try {
            // Meminta detail produk dengan timeout maksimal 5 detik.
            $response = Http::timeout(5)
                ->get($url . '/products/' . $productId);
        } catch (ConnectionException $e) {
            // Menandai kegagalan koneksi sebagai error layanan.
            throw new ServiceUnavailableException('Product Service', $e);
        }

        // Produk yang tidak ditemukan merupakan kesalahan bisnis.
        if ($response->status() === 404) {
            throw new RuntimeException('Produk tidak ditemukan.');
        }

        // Respons HTTP gagal lainnya dianggap sebagai kegagalan layanan.
        if (!$response->successful()) {
            throw new ServiceUnavailableException('Product Service');
        }

        // Mengembalikan data produk dari respons API.
        return $response->json('data') ?? [];
    }
}
