<?php
namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UploadBuktiBayarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'buktiBayarEvent' => 'required|image|mimes:jpg,jpeg,png|max:2048', // max 2MB
        ];
    }
}
