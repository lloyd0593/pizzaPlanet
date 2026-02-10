<?php

namespace Tests\Feature;

use App\Models\Pizza;
use App\Models\Topping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── Menu Page ──────────────────────────────────────────────────

    public function test_menu_page_loads_successfully(): void
    {
        $response = $this->get(route('menu'));
        $response->assertStatus(200);
        $response->assertViewIs('customer.menu');
    }

    public function test_menu_displays_active_pizzas(): void
    {
        $active = Pizza::create([
            'name' => 'Active Pizza', 'description' => 'Visible', 'base_price' => 12.99,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $inactive = Pizza::create([
            'name' => 'Inactive Pizza', 'description' => 'Hidden', 'base_price' => 10.99,
            'is_active' => false, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $response = $this->get(route('menu'));

        $response->assertSee('Active Pizza');
        $response->assertDontSee('Inactive Pizza');
    }

    public function test_menu_passes_pizzas_and_toppings_to_view(): void
    {
        Pizza::create([
            'name' => 'Test', 'description' => 'T', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        Topping::create(['name' => 'Cheese', 'price' => 1.00, 'is_active' => true]);

        $response = $this->get(route('menu'));

        $response->assertViewHas('pizzas');
        $response->assertViewHas('toppings');
    }

    // ─── Pizza Detail Page ──────────────────────────────────────────

    public function test_pizza_detail_page_loads(): void
    {
        $pizza = Pizza::create([
            'name' => 'Margherita', 'description' => 'Classic', 'base_price' => 12.99,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $response = $this->get(route('pizza.show', $pizza));

        $response->assertStatus(200);
        $response->assertViewIs('customer.pizza-detail');
        $response->assertSee('Margherita');
    }

    public function test_pizza_detail_shows_toppings(): void
    {
        $pizza = Pizza::create([
            'name' => 'Pepperoni', 'description' => 'Classic', 'base_price' => 14.99,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $topping = Topping::create(['name' => 'Pepperoni Topping', 'price' => 1.50, 'is_active' => true]);
        $pizza->toppings()->attach($topping->id);

        $response = $this->get(route('pizza.show', $pizza));

        $response->assertViewHas('allToppings');
    }

    public function test_pizza_detail_returns_404_for_missing_pizza(): void
    {
        $response = $this->get('/pizza/99999');
        $response->assertStatus(404);
    }

    // ─── Customize Page ─────────────────────────────────────────────

    public function test_customize_page_loads(): void
    {
        $response = $this->get(route('pizza.customize'));

        $response->assertStatus(200);
        $response->assertViewIs('customer.customize');
        $response->assertViewHas('toppings');
    }

    public function test_customize_page_shows_active_toppings_only(): void
    {
        Topping::create(['name' => 'Active Topping', 'price' => 1.00, 'is_active' => true]);
        Topping::create(['name' => 'Inactive Topping', 'price' => 1.00, 'is_active' => false]);

        $response = $this->get(route('pizza.customize'));

        $response->assertSee('Active Topping');
        $response->assertDontSee('Inactive Topping');
    }
}
