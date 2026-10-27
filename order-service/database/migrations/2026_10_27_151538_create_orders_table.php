<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membuat tabel pesanan.
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('user_id'); 
            $table->decimal('total_amount', 12, 2)->default(0); 
            $table->string('status', 20)->default('PENDING'); 
            $table->timestamps(); 

            $table->index('user_id'); // Mempercepat pencarian berdasarkan pengguna.
            $table->index('status'); // Mempercepat pencarian berdasarkan status.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders'); // Menghapus tabel saat rollback.
    }
};