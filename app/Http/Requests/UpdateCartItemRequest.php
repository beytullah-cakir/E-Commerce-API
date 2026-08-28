<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Miktar zorunludur.',
            'quantity.integer'  => 'Miktar tam sayı olmalıdır.',
            'quantity.min'      => 'Miktar en az 1 olmalıdır.',
        ];
    }
}
