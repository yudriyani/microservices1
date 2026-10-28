<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    // Mengizinkan permintaan checkout diproses setelah validasi.
    public function authorize(): bool
    {
        return true;
    }

    // Memeriksa data yang dikirim saat checkout pesanan.
    public function rules(): array
    {
        return [
            // ID pengguna wajib diisi dan harus berupa bilangan bulat positif.
            'user_id' => ['required', 'integer', 'min:1'],
        ];
    }

    // Menyediakan pesan validasi dalam Bahasa Indonesia.
    public function messages(): array
    {
        return [
            'user_id.required' => 'ID pengguna wajib diisi.',
            'user_id.integer' => 'ID pengguna harus berupa bilangan bulat.',
            'user_id.min' => 'ID pengguna harus lebih besar dari nol.',
        ];
    }
}
