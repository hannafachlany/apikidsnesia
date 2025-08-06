<?php

namespace App\Http\Requests\Merchandise;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMerchandiseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_merchandise' => 'sometimes|string|max:255',
            'harga_merchandise' => 'sometimes|numeric',
            'deskripsi_merchandise' => 'nullable|string',
            'stok' => 'sometimes|integer',
            'foto_merchandise' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
    public function messages()
    {
        return [
            'foto_merchandise.image' => 'File yang diunggah harus berupa gambar.',
            'foto_merchandise.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'foto_merchandise.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ];
    }
}
