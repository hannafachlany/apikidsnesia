<?php

namespace App\Http\Requests\Videos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    public function authorize()
    {
        // Atur sesuai logic akses, misal hanya admin bisa
        return true;
    }

    public function rules()
    {
        return [
            'judul_video' => 'sometimes|string|max:255',
            'deskripsi_video' => 'sometimes|string',
            'file_video' => 'nullable|file|mimetypes:video/mp4,video/mpeg,video/quicktime|max:60000',
        ];
    
    }

}