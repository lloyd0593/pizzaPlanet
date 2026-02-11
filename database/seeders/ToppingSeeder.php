<?php

namespace Database\Seeders;

use App\Models\Topping;
use Illuminate\Database\Seeder;

class ToppingSeeder extends Seeder
{
    /**
     * Seed the toppings table.
     */
    public function run(): void
    {
        $toppings = [
            ['name' => 'Ham', 'price' => 1.00],
            ['name' => 'Olives', 'price' => 1.00],
            ['name' => 'Mushrooms', 'price' => 1.00],
            ['name' => 'Bacon', 'price' => 1.00],
            ['name' => 'Mince', 'price' => 1.00],
            ['name' => 'Pepperoni', 'price' => 1.00],
            ['name' => 'Spicy Mince', 'price' => 1.00],
            ['name' => 'Onion', 'price' => 1.00],
            ['name' => 'Green Pepper', 'price' => 1.00],
            ['name' => 'Jalapenos', 'price' => 1.00],
        ];

        foreach ($toppings as $topping) {
            Topping::firstOrCreate(
                ['name' => $topping['name']],
                ['price' => $topping['price'], 'is_active' => true]
            );
        }
    }
}
