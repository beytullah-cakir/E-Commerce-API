<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // kullanıcının sepetini getir
    public function index(Request $request)
    {
        $user = $request->user();

        // sepet yoksa boş olarak döndür
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'cart'  => null,
                'items' => [],
                'total' => 0,
            ]);
        }

        // sepet toplamını hesapla
        $total = 0;
        foreach ($cart->items as $item) {
            $total += $item->product->price * $item->quantity;
        }

        return response()->json([
            'cart'  => $cart,
            'total' => $total,
        ]);
    }

    // sepete ürün ekle
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $user    = $request->user();
        $product = Product::find($request->product_id);

        // ürün aktif mi kontrol et
        if (!$product->is_active) {
            return response()->json(['message' => 'Bu ürün şu an satışta değil.'], 422);
        }

        // yeterli stok var mı kontrol et
        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Yeterli stok yok. Mevcut stok: ' . $product->stock,
            ], 422);
        }

        // kullanıcının sepeti yoksa oluştur
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // bu ürün zaten sepette var mı?
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // varsa miktarı artır
            $newQuantity = $cartItem->quantity + $request->quantity;

            // toplam miktar stoktan fazla olmasın
            if ($newQuantity > $product->stock) {
                return response()->json([
                    'message' => 'Toplam miktar stoktan fazla olamaz. Mevcut stok: ' . $product->stock,
                ], 422);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // yoksa yeni kayıt oluştur
            $cartItem = CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
            ]);
        }

        return response()->json([
            'message'   => 'Ürün sepete eklendi.',
            'cart_item' => $cartItem->load('product'),
        ], 201);
    }

    // sepetteki ürün miktarını güncelle
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $user     = $request->user();
        $cart     = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Sepetiniz boş.'], 404);
        }

        $cartItem = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Sepet kalemi bulunamadı.'], 404);
        }

        // stok kontrolü
        if ($cartItem->product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Yeterli stok yok. Mevcut stok: ' . $cartItem->product->stock,
            ], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'message'   => 'Miktar güncellendi.',
            'cart_item' => $cartItem->load('product'),
        ]);
    }

    // sepetten ürün sil
    public function removeItem(Request $request, $itemId)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Sepetiniz boş.'], 404);
        }

        $cartItem = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Sepet kalemi bulunamadı.'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Ürün sepetten silindi.']);
    }

    // sepeti tamamen temizle
    public function clearCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Sepetiniz zaten boş.'], 404);
        }

        // tüm kalemleri sil
        $cart->items()->delete();

        return response()->json(['message' => 'Sepet temizlendi.']);
    }
}
