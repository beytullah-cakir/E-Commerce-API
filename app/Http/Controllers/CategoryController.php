<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // tüm kategorileri listele
    public function index()
    {
        $categories = Category::all();

        return CategoryResource::collection($categories);
    }

    // tek kategori getir
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı.'], 404);
        }

        return new CategoryResource($category);
    }

    // yeni kategori oluştur (sadece admin - form request ile kontrol)
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json([
            'message'  => 'Kategori oluşturuldu.',
            'category' => new CategoryResource($category),
        ], 201);
    }

    // kategori güncelle (sadece admin)
    public function update(StoreCategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı.'], 404);
        }

        $category->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json([
            'message'  => 'Kategori güncellendi.',
            'category' => new CategoryResource($category),
        ]);
    }

    // kategori sil (sadece admin)
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı.'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Kategori silindi.']);
    }
}
