<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // admin kullanıcı oluştur
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        // normal kullanıcılar
        User::factory(5)->create([
            'role' => 'user',
        ]);

        // kategoriler oluştur
        $categories = [
            ['name' => 'Elektronik',    'slug' => 'elektronik',    'description' => 'Telefon, bilgisayar ve elektronik ürünler'],
            ['name' => 'Giyim',         'slug' => 'giyim',         'description' => 'Erkek, kadın ve çocuk giysileri'],
            ['name' => 'Kitap',         'slug' => 'kitap',         'description' => 'Her türden kitap'],
            ['name' => 'Spor',          'slug' => 'spor',          'description' => 'Spor ekipmanları ve giysileri'],
            ['name' => 'Ev ve Yaşam',   'slug' => 'ev-ve-yasam',   'description' => 'Ev dekorasyon ve yaşam ürünleri'],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create($categoryData);

            // her kategoriye 5 ürün ekle
            for ($i = 0; $i < 5; $i++) {
                $name = fake()->words(3, true);
                Product::create([
                    'category_id' => $category->id,
                    'name'        => ucwords($name),
                    'slug'        => Str::slug($name) . '-' . $category->id . '-' . $i,
                    'description' => fake()->paragraph(),
                    'price'       => fake()->randomFloat(2, 20, 1000),
                    'stock'       => fake()->numberBetween(5, 50),
                    'is_active'   => true,
                ]);
            }
        }
    }
}
