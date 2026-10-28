<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // Kolom yang boleh diisi melalui proses mass assignment.
    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
    ];

    // Satu pesanan dapat memiliki banyak detail produk.
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
