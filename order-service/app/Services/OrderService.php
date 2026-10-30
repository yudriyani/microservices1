<?php

namespace App\Services;

use App\Clients\CartServiceClient;
use App\Clients\ProductServiceClient;
use App\Exceptions\CheckoutConflictException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    // Menyiapkan client untuk mengakses layanan lain.
    public function __construct(
        private ProductServiceClient $productClient,
        private CartServiceClient $cartClient,
    ) {
    }

    // Mengambil keranjang dan memastikan keranjang tidak kosong.
    public function getCartForCheckout(int $userId): array
    {
        $cart = $this->cartClient->getCart($userId);

        // Menolak checkout jika keranjang tidak memiliki item.
        if (empty($cart['items'])) {
            throw new CheckoutConflictException(
                'Keranjang belanja kosong.'
            );
        }

        return $cart;
    }

    // Memeriksa keberadaan produk dan kecukupan stok setiap item.
    public function validateCartProducts(array $cart): array
    {
        $validatedItems = [];

        foreach ($cart['items'] as $item) {
            // Mengambil detail produk terbaru dari Product Service.
            $product = $this->productClient->getProduct(
                (int) $item['product_id']
            );

            $quantity = (int) $item['quantity'];
            $stock = (int) ($product['stock'] ?? 0);

            // Menolak checkout jika stok tidak mencukupi.
            if ($quantity > $stock) {
                throw new CheckoutConflictException(
                    "Stok produk ID {$item['product_id']} tidak mencukupi."
                );
            }

            // Menyimpan data untuk membuat detail pesanan.
            $validatedItems[] = [
                'product_id' => (int) $item['product_id'],
                'product_name' => $product['name'],
                'price' => (float) $product['price'],
                'quantity' => $quantity,
            ];
        }

        return $validatedItems;
    }

    // Menyimpan pesanan dan seluruh detail dalam satu transaksi.
    public function createOrder(int $userId, array $items): Order
    {
        return DB::transaction(function () use ($userId, $items) {
            // Menghitung total harga seluruh produk.
            $totalAmount = collect($items)->sum(
                fn (array $item) => $item['price'] * $item['quantity']
            );

            // Membuat pesanan dengan status awal PENDING.
            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'PENDING',
            ]);

            // Menyimpan detail beserta snapshot nama dan harga produk.
            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            // Mengembalikan pesanan beserta detail yang tersimpan.
            return $order->load('items');
        });
    }
    
    // Mengambil pesanan berdasarkan ID beserta detail itemnya.
    public function getOrderById(int $orderId): ?Order
    {
        return Order::with('items')->find($orderId);
    }

    // Mengosongkan keranjang setelah pesanan berhasil dibuat.
    public function clearCartAfterCheckout(int $userId): void
    {
        $this->cartClient->clearCart($userId);
    }

    // Mengambil daftar pesanan milik pengguna.
    public function getOrdersByUserId(
        int $userId,
        ?string $status = null
    ): \Illuminate\Database\Eloquent\Collection {
        $query = Order::with('items')
            ->where('user_id', $userId);

        // Memfilter pesanan jika status diberikan.
        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->latest()->get();
    }

    
    // Mengubah status pesanan menjadi PAID jika masih PENDING.
    public function payOrder(int $orderId): ?Order
    {
        // Mencari pesanan berdasarkan ID.
        $order = Order::with('items')->find($orderId);

        // Mengembalikan null jika pesanan tidak ditemukan.
        if ($order === null) {
            return null;
        }

        // Menolak pembayaran jika status bukan PENDING.
        if ($order->status !== 'PENDING') {
            throw new CheckoutConflictException(
                'Pesanan tidak dapat dibayar karena statusnya bukan PENDING.'
            );
        }

        // Memperbarui status pesanan setelah validasi.
        $order->status = 'PAID';
        $order->save();

        return $order;
    }



}
