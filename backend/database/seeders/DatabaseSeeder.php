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
                                    [
                                        'name' => 'Admin Mohammed',
                                        'password' => Hash::make('1234'),
                                    ]
                                );

                $fruits = Category::updateOrCreate(['name' => 'Fruits'], ['name_ar' => 'Fruits_AR']);
                    $vegetables = Category::updateOrCreate(['name' => 'Vegetables'], ['name_ar' => 'Vegetables_AR']);

                Product::updateOrCreate(['name' => 'Red Apple'], [
                                                    'name_ar' => 'Red Apple AR',
                                                    'price' => 1.99,
                                                    'description' => 'Fresh red apple',
                                                    'stock_quantity' => 100,
                                                    'category_id' => $fruits->id,
                                                    'barcode' => '123456789'
                                                ]);

                Product::updateOrCreate(['name' => 'Organic Carrot'], [
                                                    'name_ar' => 'Organic Carrot AR',
                                                    'price' => 0.99,
                                                    'description' => 'Fresh organic carrot',
                                                    'stock_quantity' => 200,
                                                    'category_id' => $vegetables->id,
                                                    'barcode' => '987654321'
                                                ]);
        }
    }
                                       
