<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // kullanıcının siparişlerini listele
    public function index(Request $request)
    {
        $user = $request->user();

        // admin tüm siparişleri görür, normal kullanıcı sadece kendinkini
        if ($user->isAdmin()) {
            $orders = Order::with('user', 'items.product')->latest()->get();
        } else {
            $orders = Order::with('items.product')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return response()->json($orders);
    }

    // tek sipariş detayı
    public function show(Request $request, $id)
    {
        $user  = $request->user();
        $order = Order::with('items.product', 'user')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        // başkasının siparişini göremez (admin hariç)
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Bu siparişi görme yetkiniz yok.'], 403);
        }

        return response()->json($order);
    }

    // sepetten sipariş oluştur
    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        // sepet boş mu?
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Sepetiniz boş, önce ürün ekleyin.'], 422);
        }

        // her ürün için stok kontrolü yap
        foreach ($cart->items as $item) {
            if (!$item->product->is_active) {
                return response()->json([
                    'message' => $item->product->name . ' ürünü artık satışta değil.',
                ], 422);
            }

            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => $item->product->name . ' için yeterli stok yok. Mevcut: ' . $item->product->stock,
                ], 422);
            }
        }

        // database transaction - bir şey hata verirse her şey geri alınır
        $order = DB::transaction(function () use ($cart, $user, $request) {

            // toplam fiyatı hesapla
            $totalPrice = 0;
            foreach ($cart->items as $item) {
                $totalPrice += $item->product->price * $item->quantity;
            }

            // siparişi oluştur
            $order = Order::create([
                'user_id'     => $user->id,
                'status'      => 'pending',
                'total_price' => $totalPrice,
                'notes'       => $request->notes,
            ]);

            // her sepet kalemi için order item oluştur
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    // sipariş anındaki fiyatı kaydediyoruz
                    'price'      => $item->product->price,
                ]);

                // stoktan düş
                $item->product->decrement('stock', $item->quantity);
            }

            // sepeti temizle
            $cart->items()->delete();

            return $order;
        });

        return response()->json([
            'message' => 'Sipariş oluşturuldu!',
            'order'   => $order->load('items.product'),
        ], 201);
    }

    // sipariş durumunu güncelle (sadece admin)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        // iptal edilen sipariş tekrar stoka eklensin
        if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Sipariş durumu güncellendi.',
            'order'   => $order,
        ]);
    }
}
