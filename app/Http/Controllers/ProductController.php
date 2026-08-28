<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // tüm ürünleri listele - arama ve filtreleme + pagination
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        // isme göre arama: ?search=telefon
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // kategoriye göre filtre: ?category_id=2
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // minimum fiyat filtresi: ?min_price=50
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // maksimum fiyat filtresi: ?max_price=500
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // sayfa başına 10 ürün
        $products = $query->paginate(10);

        return ProductResource::collection($products);
    }

    // tek ürün getir
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Ürün bulunamadı.'], 404);
        }

        return new ProductResource($product);
    }

    // yeni ürün oluştur (sadece admin)
    public function store(StoreProductRequest $request)
    {
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
            'product' => new ProductResource($product->load('category')),
        ], 201);
    }

    // ürün güncelle (sadece admin)
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Ürün bulunamadı.'], 404);
        }

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
            'product' => new ProductResource($product->load('category')),
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
