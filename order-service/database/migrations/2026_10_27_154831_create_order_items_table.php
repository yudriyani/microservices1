<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membuat tabel detail pesanan.
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('order_id'); 
            $table->unsignedBigInteger('product_id'); 
            $table->string('product_name'); 
            $table->decimal('price', 12, 2); 
            $table->unsignedInteger('quantity'); 
            $table->decimal('subtotal', 12, 2); 
            $table->timestamps(); 

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete(); // Hapus detail jika pesanan dihapus.

            $table->index('product_id'); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items'); // Menghapus tabel saat rollback.
    }
};
