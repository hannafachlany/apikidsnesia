<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama_event' => 'required|string|max:255',
            'harga_event' => 'required|integer',
            'jadwal_event' => 'required|date_format:Y-m-d H:i:s',
            'foto_event' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'deskripsi_event' => 'required|string',
            'kuota' => 'required|integer|min:0',
        ];
    }
}
