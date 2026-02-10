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
            ['name' => 'Pepperoni', 'price' => 1.50],
            ['name' => 'Mushrooms', 'price' => 1.00],
            ['name' => 'Onions', 'price' => 0.75],
            ['name' => 'Sausage', 'price' => 1.50],
            ['name' => 'Bacon', 'price' => 1.75],
            ['name' => 'Extra Cheese', 'price' => 1.25],
            ['name' => 'Black Olives', 'price' => 1.00],
            ['name' => 'Green Peppers', 'price' => 0.75],
            ['name' => 'Pineapple', 'price' => 1.00],
            ['name' => 'Spinach', 'price' => 0.75],
            ['name' => 'Jalapeños', 'price' => 0.75],
            ['name' => 'Tomatoes', 'price' => 0.75],
            ['name' => 'Anchovies', 'price' => 1.50],
            ['name' => 'Ham', 'price' => 1.25],
            ['name' => 'Grilled Chicken', 'price' => 2.00],
        ];

        foreach ($toppings as $topping) {
            Topping::firstOrCreate(
                ['name' => $topping['name']],
                ['price' => $topping['price'], 'is_active' => true]
            );
        }
    }
}
