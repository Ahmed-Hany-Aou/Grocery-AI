<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
    {
            public function run(): void
        {
                    User::updateOrCreate(
                                    ['email' => 'ahmed.hany.boshra@gmail.com'],
                                    ['name' => 'Admin Mohammed', 'password' => Hash::make('1234')]
                                );

                $fruits = Category::updateOrCreate(['name' => 'Fruits'], ['name_ar' => 'Fruits_AR']);
                    $vegetables = Category::updateOrCreate(['name' => 'Vegetables'], ['name_ar' => 'Vegetables_AR']);

                Product::updateOrCreate(['name' => 'Red Apple'], ['name_ar' => 'Apple_AR', 'barcode' => 'APPLE001', 'price' => 1.99, 'stock_quantity' => 100, 'category_id' => $fruits->id]);
                    Product::updateOrCreate(['name' => 'Organic Carrot'], ['name_ar' => 'Carrot_AR', 'barcode' => 'CARROT001', 'price' => 0.99, 'stock_quantity' => 200, 'category_id' => $vegetables->id]);
        }
    }
