<?php
namespace App\Http\Requests\DetailEvent;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetailEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_event' => 'required|exists:event,id_event',
            'foto_kegiatan' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    // App\Http\Requests\Videos\StoreVideoRequest.php
public function messages()
{
    return [
        'judul_video.required' => 'Judul video wajib diisi.',
        'file_video.required' => 'File video wajib diunggah.',
        'file_video.mimes' => 'Format video harus MP4, MOV, atau AVI.',
        'file_video.max' => 'Ukuran video tidak boleh melebihi 50MB.',
        'thumbnail.image' => 'Thumbnail harus berupa gambar.',
        'thumbnail.mimes' => 'Format thumbnail harus JPG, JPEG, atau PNG.',
        'thumbnail.max' => 'Ukuran thumbnail tidak boleh lebih dari 2MB.',
    ];
}



    
}
