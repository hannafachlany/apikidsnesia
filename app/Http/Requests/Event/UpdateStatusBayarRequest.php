<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusBayarRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'status_pembayaran' => 'required|in:Berhasil,Gagal',
            'tanggal_bayar' => 'required|date',
        ];
    }
}
