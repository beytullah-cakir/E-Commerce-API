<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // tüm ürünleri listele (herkes görebilir)
    public function index()
    {
        // kategori bilgisiyle birlikte getir
        $products = Product::with('category')->where('is_active', true)->get();

        return response()->json($products);
    }

    // tek ürün getir
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Ürün bulunamadı.'], 404);
        }

        return response()->json($product);
    }

    // yeni ürün oluştur (sadece admin)
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $product = Product::create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => Str::slug($request->name) . '-' . time(),
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'is_active'   => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Ürün oluşturuldu.',
            'product' => $product,
        ], 201);
    }

    // ürün güncelle (sadece admin)
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Ürün bulunamadı.'], 404);
        }

        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        // sadece gönderilen alanları güncelle
        if ($request->has('name')) {
            $product->name = $request->name;
            $product->slug = Str::slug($request->name) . '-' . time();
        }
        if ($request->has('category_id')) $product->category_id = $request->category_id;
        if ($request->has('description'))  $product->description  = $request->description;
        if ($request->has('price'))        $product->price        = $request->price;
        if ($request->has('stock'))        $product->stock        = $request->stock;
        if ($request->has('is_active'))    $product->is_active    = $request->is_active;

        $product->save();

        return response()->json([
            'message' => 'Ürün güncellendi.',
            'product' => $product,
        ]);
    }

    // ürün sil (sadece admin)
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Ürün bulunamadı.'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Ürün silindi.']);
    }
}
