<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendResetEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // 1. Izinkan semua user melakukan request
    public function authorize()
    {
        return true;
    }

    // 2. Validasi email input
    public function rules()
    {
        return [
            'email' => 'required|email',
        ];
    }

    // 3. Pesan error
    public function messages()
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak ditemukan.',
        ];
    }

    
}
