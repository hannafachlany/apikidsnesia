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
}
