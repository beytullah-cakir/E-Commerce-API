<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // hangi siparişe ait
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // hangi ürün
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // bu kalemin toplam fiyatı
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}
