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
}
