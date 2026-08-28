<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // sepet toplamını hesapla
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        return [
            'id'    => $this->id,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'total' => $total,
        ];
    }
}
