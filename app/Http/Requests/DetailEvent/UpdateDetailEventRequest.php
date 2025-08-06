<?php
namespace App\Http\Requests\DetailEvent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDetailEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto_kegiatan' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
    public function messages(): array
    {
        return [
            
            'foto_kegiatan.nullable' => 'Foto kegiatan tidak wajib diunggah.',
            'foto_kegiatan.image' => 'File yang diunggah harus berupa gambar.',
            'foto_kegiatan.mimes' => 'Format gambar harus jpg, jpeg, atau png.',
            'foto_kegiatan.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ];
    }
}
