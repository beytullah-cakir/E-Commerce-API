<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // siparişleri listele
    public function index(Request $request)
    {
        $user = $request->user();

        // admin tüm siparişleri görür, kullanıcı sadece kendinkini
        if ($user->isAdmin()) {
            $orders = Order::with('items.product')->latest()->paginate(10);
        } else {
            $orders = Order::with('items.product')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);
        }

        return OrderResource::collection($orders);
    }

    // tek sipariş detayı
    public function show(Request $request, $id)
    {
        $user  = $request->user();
        $order = Order::with('items.product')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        // başkasının siparişini göremez (admin hariç)
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Bu siparişi görme yetkiniz yok.'], 403);
        }

        return new OrderResource($order);
    }

    // sepetten sipariş oluştur
    public function store(StoreOrderRequest $request)
    {
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

            $totalPrice = 0;
            foreach ($cart->items as $item) {
                $totalPrice += $item->product->price * $item->quantity;
            }

            $order = Order::create([
                'user_id'     => $user->id,
                'status'      => 'pending',
                'total_price' => $totalPrice,
                'notes'       => $request->notes,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
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
            'order'   => new OrderResource($order->load('items.product')),
        ], 201);
    }

    // sipariş durumunu güncelle (sadece admin)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order = Order::with('items.product')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Sipariş bulunamadı.'], 404);
        }

        // iptal edilince stok geri ekle
        if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Sipariş durumu güncellendi.',
            'order'   => new OrderResource($order),
        ]);
    }
}
