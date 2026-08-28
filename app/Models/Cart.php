<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    // sepetin sahibi olan kullanıcı
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // sepetteki ürünler (cart items)
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // sepet toplamını hesapla
    public function getTotalAttribute()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->product->price * $item->quantity;
        }
        return $total;
    }
}
