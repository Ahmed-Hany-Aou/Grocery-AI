<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'ahmed.hany.boshra@gmail.com'],
            [
                'name' => 'Admin Mohammed',
                'password' => \Illuminate\Support\Facades\Hash::make('1234'),
            ]
        );
    }
}
