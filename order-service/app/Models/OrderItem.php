<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    // Kolom yang boleh diisi.
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'price',
        'quantity',
        'subtotal',
    ];

    // Detail pesanan dimiliki oleh satu pesanan.
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}