<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // tüm kategorileri listele (herkes görebilir)
    public function index()
    {
        $categories = Category::all();

        return response()->json($categories);
    }

    // tek kategori getir
    public function show($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı.'], 404);
        }

        return response()->json($category);
    }

    // yeni kategori oluştur (sadece admin)
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $category = Category::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json([
            'message'  => 'Kategori oluşturuldu.',
            'category' => $category,
        ], 201);
    }

    // kategori güncelle (sadece admin)
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori bulunamadı.'], 404);
        }

        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json([
            'message'  => 'Kategori güncellendi.',
            'category' => $category,
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
