<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    // siparişi kim görebilir
    public function view(User $user, Order $order)
    {
        // admin hepsini görür, kullanıcı sadece kendi siparişini
        return $user->isAdmin() || $order->user_id === $user->id;
    }

    // sipariş durumunu kim güncelleyebilir
    public function updateStatus(User $user, Order $order)
    {
        return $user->isAdmin();
    }
}
