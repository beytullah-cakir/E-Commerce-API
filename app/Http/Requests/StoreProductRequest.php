<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori seçimi zorunludur.',
            'category_id.exists'   => 'Seçilen kategori bulunamadı.',
            'name.required'        => 'Ürün adı zorunludur.',
            'price.required'       => 'Fiyat zorunludur.',
            'price.numeric'        => 'Fiyat sayısal olmalıdır.',
            'stock.required'       => 'Stok miktarı zorunludur.',
            'stock.integer'        => 'Stok tam sayı olmalıdır.',
        ];
    }
}
