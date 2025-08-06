<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_event' => 'sometimes|string|max:255',
            'harga_event' => 'sometimes|numeric',
            'jadwal_event' => 'sometimes|date',
            'deskripsi_event' => 'sometimes|string',
            'kuota' => 'sometimes|integer',
            'foto_event' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
    public function messages()
    {
        return [
            'nama_event.max' => 'Nama event tidak boleh lebih dari 255 karakter.',
            'harga_event.numeric' => 'Harga event harus berupa angka.',
            'jadwal_event.date' => 'Jadwal event harus berupa tanggal yang valid.',
            'deskripsi_event.string' => 'Deskripsi event harus berupa teks.',
            'kuota.integer' => 'Kuota harus berupa angka.',
            'foto_event.image' => 'File yang diunggah harus berupa gambar.',
            'foto_event.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'foto_event.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
        ];
    }


}
