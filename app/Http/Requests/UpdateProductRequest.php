<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'is_active'   => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Seçilen kategori bulunamadı.',
            'price.numeric'      => 'Fiyat sayısal olmalıdır.',
            'stock.integer'      => 'Stok tam sayı olmalıdır.',
        ];
    }
}
