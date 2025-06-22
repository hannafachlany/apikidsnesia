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

}
