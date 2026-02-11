<?php

namespace Database\Seeders;

use App\Models\Pizza;
use App\Models\Topping;
use Illuminate\Database\Seeder;

class PizzaSeeder extends Seeder
{
    /**
     * Seed the pizzas table with predefined pizzas and their toppings.
     */
    public function run(): void
    {
        $pizzas = [
            [
                'name' => 'Margherita',
                'description' => 'Classic margherita with no toppings.',
                'base_price' => 10.00,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => [],
            ],
            [
                'name' => 'Romana',
                'description' => 'Ham, olives, and mushrooms on a classic base.',
                'base_price' => 13.00,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => ['Ham', 'Olives', 'Mushrooms'],
            ],
            [
                'name' => 'Americana',
                'description' => 'Bacon, mince, and pepperoni for the meat lover.',
                'base_price' => 13.00,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => ['Bacon', 'Mince', 'Pepperoni'],
            ],
            [
                'name' => 'Mexicana',
                'description' => 'Spicy mince, onion, green pepper, and jalapenos for a fiery kick.',
                'base_price' => 15.00,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => ['Spicy Mince', 'Onion', 'Green Pepper', 'Jalapenos'],
            ],
            [
                'name' => 'Make Your Own',
                'description' => 'Build your own pizza with your choice of toppings. €1 per topping.',
                'base_price' => 10.00,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => [],
            ],
        ];

        foreach ($pizzas as $pizzaData) {
            $toppingNames = $pizzaData['toppings'];
            unset($pizzaData['toppings']);

            $pizza = Pizza::firstOrCreate(
                ['name' => $pizzaData['name']],
                array_merge($pizzaData, ['is_active' => true])
            );

            // Attach toppings
            $toppingIds = Topping::whereIn('name', $toppingNames)->pluck('id');
            $pizza->toppings()->syncWithoutDetaching($toppingIds);
        }
    }
}
