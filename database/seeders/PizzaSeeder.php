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
                'description' => 'Classic pizza with fresh mozzarella, tomatoes, and basil on a crispy crust.',
                'base_price' => 9.99,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => ['Extra Cheese', 'Tomatoes'],
            ],
            [
                'name' => 'Pepperoni Feast',
                'description' => 'Loaded with double pepperoni and extra cheese for the ultimate meat lover.',
                'base_price' => 12.99,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => ['Pepperoni', 'Extra Cheese'],
            ],
            [
                'name' => 'Veggie Supreme',
                'description' => 'A garden of flavors with mushrooms, peppers, onions, olives, and spinach.',
                'base_price' => 11.99,
                'size' => 'medium',
                'crust' => 'thin',
                'toppings' => ['Mushrooms', 'Green Peppers', 'Onions', 'Black Olives', 'Spinach'],
            ],
            [
                'name' => 'Meat Lovers',
                'description' => 'Piled high with pepperoni, sausage, bacon, and ham for serious carnivores.',
                'base_price' => 14.99,
                'size' => 'large',
                'crust' => 'thick',
                'toppings' => ['Pepperoni', 'Sausage', 'Bacon', 'Ham'],
            ],
            [
                'name' => 'Hawaiian',
                'description' => 'Sweet pineapple and savory ham on a bed of melted cheese.',
                'base_price' => 11.49,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => ['Pineapple', 'Ham', 'Extra Cheese'],
            ],
            [
                'name' => 'BBQ Chicken',
                'description' => 'Grilled chicken, onions, and bacon drizzled with tangy BBQ sauce.',
                'base_price' => 13.49,
                'size' => 'medium',
                'crust' => 'regular',
                'toppings' => ['Grilled Chicken', 'Onions', 'Bacon'],
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
