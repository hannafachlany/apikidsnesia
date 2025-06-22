<?php

namespace App\Http\Requests\Merchandise;

use Illuminate\Foundation\Http\FormRequest;

class UploadBuktiBayarMerchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware authtoken sudah mengatur akses
    }

    public function rules(): array
    {
        return [
            'buktiBayarMerch' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'buktiBayarMerch.required' => 'File bukti bayar wajib diunggah.',
            'buktiBayarMerch.image' => 'File harus berupa gambar.',
            'buktiBayarMerch.mimes' => 'Format gambar harus jpg, jpeg, atau png.',
            'buktiBayarMerch.max' => 'Ukuran maksimal file adalah 2MB.',
        ];
    }
}
