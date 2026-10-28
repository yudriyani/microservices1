<?php

namespace App\Exceptions;

use RuntimeException;

class ServiceUnavailableException extends RuntimeException
{
    // Menerima nama layanan dan error asli jika tersedia.
    public function __construct(
        string $service,
        ?\Throwable $previous = null
    ) {
        // Menyimpan pesan yang aman dan error penyebabnya.
        parent::__construct(
            "{$service} tidak dapat dihubungi.",
            0,
            $previous
        );
    }
}
