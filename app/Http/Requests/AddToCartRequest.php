<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        // giriş yapan herkes sepete ekleyebilir
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Ürün seçimi zorunludur.',
            'product_id.exists'   => 'Seçilen ürün bulunamadı.',
            'quantity.required'   => 'Miktar zorunludur.',
            'quantity.integer'    => 'Miktar tam sayı olmalıdır.',
            'quantity.min'        => 'Miktar en az 1 olmalıdır.',
        ];
    }
}
