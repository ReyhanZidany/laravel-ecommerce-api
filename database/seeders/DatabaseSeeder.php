<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $products = [
            ['name' => 'Laptop Pro 15', 'description' => 'High-performance laptop', 'price' => 15000000, 'status' => 'active'],
            ['name' => 'Wireless Mouse', 'description' => null, 'price' => 250000, 'status' => 'active'],
            ['name' => 'USB Hub 7-Port', 'description' => 'Fast USB 3.0 hub', 'price' => 350000, 'status' => 'inactive'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
