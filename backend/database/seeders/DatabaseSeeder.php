<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
    {
            /**
     * Seed the application's database.
         */
    public function run(): void
        {
                    User::updateOrCreate(
                                    ['email' => 'ahmed.hany.boshra@gmail.com'],
                                    [
                                        'name' => 'Admin Mohammed',
                                        'password' => Hash::make('1234'),
                                        'role' => 'admin',
                                    ]
                                );

                $fruits = Category::updateOrCreate(['slug' => 'fruits'], ['name' => 'Fruits', 'icon' => 'fruit-icon']);
                    $vegetables = Category::updateOrCreate(['slug' => 'vegetables'], ['name' => 'Vegetables', 'icon' => 'veg-icon']);

                Product::updateOrCreate(['slug' => 'apple'], [
                                                    'name' => 'Red Apple',
                                                    'price' => 1.99,
                                                    'description' => 'Fresh red apple',
                                                    'stock_quantity' => 100,
                                                    'category_id' => $fruits->id,
                                                    'barcode' => '123456789',
                                                    'sku' => 'FRT-APL-001'
                                                ]);

                Product::updateOrCreate(['slug' => 'carrot'], [
                                                    'name' => 'Organic Carrot',
                                                    'price' => 0.99,
                                                    'description' => 'Fresh organic carrot',
                                                    'stock_quantity' => 200,
                                                    'category_id' => $vegetables->id,
                                                    'barcode' => '987654321',
                                                    'sku' => 'VEG-CRT-001'
                                                ]);
        }
    }
