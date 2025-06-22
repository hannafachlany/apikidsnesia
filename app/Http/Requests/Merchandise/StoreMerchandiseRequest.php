<?php

namespace App\Http\Requests\Merchandise;

use Illuminate\Foundation\Http\FormRequest;

class StoreMerchandiseRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Atur sesuai kebutuhan autentikasi
    }

    public function rules()
    {
        return [
            'nama_merchandise'      => 'required|string',
            'harga_merchandise'     => 'required|numeric',
            'deskripsi_merchandise' => 'nullable|string',
            'stok'             => 'required|integer',
            'foto_merchandise'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
