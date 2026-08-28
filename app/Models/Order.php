<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    // siparişi veren kullanıcı
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // siparişin kalemleri
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
