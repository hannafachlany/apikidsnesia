<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LogoutRequest extends FormRequest
{
    // 1. Izinkan hanya user yang sudah login
    public function authorize()
    {
        return $this->user() != null;
    }

    // 2. Tidak ada field input yang divalidasi
    public function rules()
    {
        return [];
    }


    // Bisa juga validasi header token pakai method prepareForValidation() jika mau
}
