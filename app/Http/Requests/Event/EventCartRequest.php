<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class EventCartRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.idEvent' => 'required|exists:event,id_event',
            'items.*.jumlah' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'Data item tidak boleh kosong.',
            'items.*.idEvent.required' => 'ID Event wajib diisi.',
            'items.*.idEvent.exists' => 'ID Event tidak valid.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah minimal 1.',
        ];
    }
}
