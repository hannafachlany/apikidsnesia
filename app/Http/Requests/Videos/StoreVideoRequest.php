<?php

namespace App\Http\Requests\Videos;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    public function authorize()
    {
        // Atur sesuai logic akses, misal hanya admin bisa
        return true;
    }

    public function rules()
    {
        return [
            'judul_video' => 'required|string|max:255',
            'deskripsi_video' => 'nullable|string',
            'file_video' => 'required|file|mimetypes:video/mp4,video/mpeg,video/quicktime|max:60000', // max 50MB, sesuaikan
            'thumbnail' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
