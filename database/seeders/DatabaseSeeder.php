<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = ['admin', 'kasir', 'barista'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2. Default users (satu per role)
        $admin = User::firstOrCreate(
            ['email' => 'admin@coffee.test'],
            ['name' => 'Administrator', 'password' => Hash::make('password')]
        );
        $admin->syncRoles('admin');

        $kasir = User::firstOrCreate(
            ['email' => 'kasir@coffee.test'],
            ['name' => 'Kasir Satu', 'password' => Hash::make('password')]
        );
        $kasir->syncRoles('kasir');

        $barista = User::firstOrCreate(
            ['email' => 'barista@coffee.test'],
            ['name' => 'Barista Satu', 'password' => Hash::make('password')]
        );
        $barista->syncRoles('barista');

        // 3. Bahan baku contoh
        $ingredients = [
            ['name' => 'Biji Kopi', 'unit' => 'gram', 'stock' => 5000, 'min_stock' => 1000],
            ['name' => 'Susu', 'unit' => 'ml', 'stock' => 8000, 'min_stock' => 2000],
            ['name' => 'Gula', 'unit' => 'gram', 'stock' => 4000, 'min_stock' => 1000],
            ['name' => 'Es Batu', 'unit' => 'gram', 'stock' => 10000, 'min_stock' => 2000],
            ['name' => 'Cokelat Bubuk', 'unit' => 'gram', 'stock' => 1500, 'min_stock' => 500],
            ['name' => 'Roti', 'unit' => 'pcs', 'stock' => 40, 'min_stock' => 10],
        ];
        $ingredientModels = collect($ingredients)->mapWithKeys(
            fn ($data) => [$data['name'] => Ingredient::firstOrCreate(['name' => $data['name']], $data)]
        );

        // 4. Kategori + produk contoh
        $data = [
            'Coffee' => [
                ['name' => 'Espresso', 'price' => 18000, 'recipe' => ['Biji Kopi' => 18]],
                ['name' => 'Cappuccino', 'price' => 25000, 'recipe' => ['Biji Kopi' => 18, 'Susu' => 150]],
                ['name' => 'Caffe Latte', 'price' => 27000, 'recipe' => ['Biji Kopi' => 18, 'Susu' => 200]],
                ['name' => 'Es Kopi Susu', 'price' => 22000, 'recipe' => ['Biji Kopi' => 18, 'Susu' => 100, 'Gula' => 15, 'Es Batu' => 100]],
            ],
            'Non-Coffee' => [
                ['name' => 'Cokelat Panas', 'price' => 23000, 'recipe' => ['Cokelat Bubuk' => 30, 'Susu' => 200]],
                ['name' => 'Es Cokelat', 'price' => 25000, 'recipe' => ['Cokelat Bubuk' => 30, 'Susu' => 150, 'Es Batu' => 100]],
            ],
            'Snack' => [
                ['name' => 'Roti Bakar', 'price' => 15000, 'recipe' => ['Roti' => 1]],
            ],
        ];

        foreach ($data as $categoryName => $products) {
            $category = Category::firstOrCreate(['name' => $categoryName]);

            foreach ($products as $product) {
                $model = Product::firstOrCreate(
                    ['name' => $product['name']],
                    [
                        'category_id' => $category->id,
                        'price' => $product['price'],
                        'is_active' => true,
                        'description' => $product['name'].' khas CodeCoffee.',
                    ]
                );

                // Resep produk -> bahan baku (pivot quantity)
                $recipe = collect($product['recipe'])->mapWithKeys(
                    fn ($qty, $name) => [$ingredientModels[$name]->id => ['quantity' => $qty]]
                )->all();
                $model->ingredients()->syncWithoutDetaching($recipe);
            }
        }
    }
}
