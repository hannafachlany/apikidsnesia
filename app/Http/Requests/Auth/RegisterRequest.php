<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    // 1. Semua orang boleh akses endpoint ini
    public function authorize(): bool
    {
        return true;
    }

    // 2. Aturan validasi saat register
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:pelanggan,email',
            'password' => [
                'required',
                'string',
                'min:12',
                'regex:/[a-z]/', //huruf kecil
                'regex:/[A-Z]/', //huruf besar
                'regex:/[0-9]/', //angka
                'regex:/[\W]/', //simbol
            ],
            'nama_pelanggan' => 'required',
            'no_hp_pelanggan' => 'required',
        ];
    }

    // 3. Pesan kustom
    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah dipakai',
            'password.min' => 'Password minimal 12 karakter',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol',
        ];
    }

}
