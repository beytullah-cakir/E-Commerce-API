<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
    ];

    // hangi sepete ait
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // hangi ürün
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // bu kalemin toplam fiyatı
    public function getSubtotalAttribute()
    {
        return $this->product->price * $this->quantity;
    }
}
