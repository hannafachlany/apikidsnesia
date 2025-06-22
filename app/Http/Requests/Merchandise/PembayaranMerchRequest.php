<?php

namespace App\Http\Requests\Merchandise;

use Illuminate\Foundation\Http\FormRequest;

class PembayaranMerchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware authtoken sudah menangani otorisasi
    }

    public function rules()
    {
        return [
            'bankPengirim' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'bankPengirim.required' => 'Bank wajib diisi.',
        ];
    }
}
