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
}
