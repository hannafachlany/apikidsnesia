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
            'file_video' => 'sometimes|file|mimetypes:video/mp4,video/mpeg,video/quicktime|max:60000',
            'thumbnail' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ];
    
    }

    // App\Http\Requests\Videos\StoreVideoRequest.php
public function messages()
{
    return [
        'file_video.mimes' => 'Format video harus MP4, MOV, atau AVI.',
        'file_video.max' => 'Ukuran video tidak boleh melebihi 50MB.',
        'thumbnail.image' => 'Thumbnail harus berupa gambar.',
        'thumbnail.mimes' => 'Format thumbnail harus JPG, JPEG, atau PNG.',
        'thumbnail.max' => 'Ukuran thumbnail tidak boleh lebih dari 2MB.',
    ];
}


}