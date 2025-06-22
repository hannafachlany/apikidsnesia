<?php

namespace App\Http\Requests\Profil;

use Illuminate\Foundation\Http\FormRequest;

class UploadFotoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'foto_profil' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
