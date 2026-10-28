<?php

namespace App\Exceptions;

use RuntimeException;

class CheckoutConflictException extends RuntimeException
{
    // Menerima pesan untuk kondisi bisnis yang menghalangi checkout.
    public function __construct(string $message)
    {
        // Menyimpan pesan kesalahan agar dapat ditangani oleh Controller.
        parent::__construct($message);
    }
}
