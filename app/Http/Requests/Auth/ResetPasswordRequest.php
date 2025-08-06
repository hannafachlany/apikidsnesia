<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class ResetPasswordRequest extends FormRequest
{
    /**
     * Tentukan apakah user boleh melakukan request ini.
     *
     * @return bool
     */
    // 1. Izinkan akses
    public function authorize()
    {
        return true;
    }

    // 2. Aturan validasi password baru
    public function rules()
    {
        return [
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
        ];
    }

    // 3. Pesan kustom validasi
    public function messages(): array
    {
        return [
            'password.min' => 'Password minimal 12 karakter',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ];
    }


    
}
