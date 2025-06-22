<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;



class PembayaranEventRequest extends FormRequest
{

    
    public function authorize()
    {
        return true; // kamu bisa tambahkan token check di middleware
    }

    public function rules()
    {
        return [
            'bankPengirim' => 'required|string|max:100', // sesuaikan daftar bank
        ];
    }
}
