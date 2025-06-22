<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class UploadBuktiMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bukti_transfer' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'bukti_transfer.required' => 'Bukti transfer harus diunggah.',
            'bukti_transfer.image' => 'File harus berupa gambar.',
            'bukti_transfer.mimes' => 'Format yang diperbolehkan: JPG, JPEG, PNG.',
            'bukti_transfer.max' => 'Ukuran maksimal 2MB.',
        ];
    }
}
