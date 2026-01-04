<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Espresso', 'price' => 18000],
            ['name' => 'Macchiato', 'price' => 20000],
            ['name' => 'Cappuccino', 'price' => 22000],
            ['name' => 'Latte', 'price' => 22000],
            ['name' => 'Black Tea', 'price' => 15000],
            ['name' => 'Milk Tea', 'price' => 17000],
            ['name' => 'Sambusa', 'price' => 12000],
            ['name' => 'Donut', 'price' => 13000],
            ['name' => 'Cake Slice', 'price' => 25000],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                [
                    'price' => $product['price'],
                    'active' => true,
                ]
            );
        }
    }
}
