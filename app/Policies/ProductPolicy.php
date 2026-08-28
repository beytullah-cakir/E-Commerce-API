<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    // ürünü kim güncelleyebilir
    public function update(User $user, Product $product)
    {
        return $user->isAdmin();
    }

    // ürünü kim silebilir
    public function delete(User $user, Product $product)
    {
        return $user->isAdmin();
    }
}
