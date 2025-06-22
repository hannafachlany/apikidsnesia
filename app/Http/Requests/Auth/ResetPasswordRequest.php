<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Tentukan apakah user boleh melakukan request ini.
     *
     * @return bool
     */
    public function authorize()
    {
        // Biasanya dikembalikan true kalau kamu sudah pastikan user valid lewat middleware
        return true;
    }

    /**
     * Aturan validasi yang harus dipenuhi.
     *
     * @return array<string, mixed>
     */
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
    public function messages(): array
        {
            return [
               
                'password.min' => 'Password minimal 12 karakter',
                'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ];
        }
}
