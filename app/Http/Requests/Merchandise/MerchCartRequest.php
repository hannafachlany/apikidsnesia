<?php
namespace App\Http\Requests\Merchandise;

use Illuminate\Foundation\Http\FormRequest;

class MerchCartRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'itemsMerch' => 'required|array', // GANTI DARI string ke array karena kamu input array
            'itemsMerch.*.idMerch' => 'required|integer|exists:merchandise,id_merchandise',
            'itemsMerch.*.jumlah' => 'required|integer|min:1',
        ];
    }
}
