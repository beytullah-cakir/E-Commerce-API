<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'is_active',
    ];

    // boolean olarak gelmesi için cast yapıyoruz
    protected $casts = [
        'is_active' => 'boolean',
        'price'     => 'decimal:2',
    ];

    // bir ürün bir kategoriye ait
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // bir ürünün çok sepet kalemi olabilir
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // bir ürünün çok sipariş kalemi olabilir
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
