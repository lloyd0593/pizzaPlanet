<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Pizza;
use App\Models\Topping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    private Pizza $pizza;
    private Topping $topping;
    private array $sessionCookies = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->pizza = Pizza::create([
            'name' => 'Margherita', 'description' => 'Classic', 'base_price' => 12.99,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $this->topping = Topping::create(['name' => 'Mozzarella', 'price' => 1.50, 'is_active' => true]);
        $this->pizza->toppings()->attach($this->topping->id);
    }

    private function captureSession($response): void
    {
        $cookie = $response->getCookie(config('session.cookie'));
        if ($cookie) {
            $this->sessionCookies[config('session.cookie')] = $cookie->getValue();
        }
    }

    private function withSessionCookies()
    {
        return $this->withCookies($this->sessionCookies);
    }

    // ─── View Cart ──────────────────────────────────────────────────

    public function test_cart_page_loads_empty(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertViewIs('customer.cart');
        $response->assertViewHas('items');
    }

    public function test_cart_page_shows_items(): void
    {
        $addResponse = $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 2,
        ]);
        $this->captureSession($addResponse);

        $response = $this->withSessionCookies()->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertSee('Margherita');
    }

    // ─── Add to Cart ────────────────────────────────────────────────

    public function test_add_pizza_to_cart(): void
    {
        $response = $this->from(route('menu'))->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('cart_items', [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 1,
        ]);
    }

    public function test_add_pizza_with_custom_toppings(): void
    {
        $extraTopping = Topping::create(['name' => 'Olives', 'price' => 1.00, 'is_active' => true]);

        $response = $this->from(route('menu'))->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'large',
            'crust' => 'thick',
            'quantity' => 1,
            'toppings' => [$this->topping->id, $extraTopping->id],
        ]);

        $response->assertRedirect();
        $cartItem = CartItem::first();
        $this->assertCount(2, $cartItem->toppings);
    }

    public function test_add_custom_pizza_to_cart(): void
    {
        $response = $this->from(route('menu'))->post(route('cart.add'), [
            'is_custom' => true,
            'size' => 'large',
            'crust' => 'stuffed',
            'quantity' => 1,
            'toppings' => [$this->topping->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'is_custom' => true,
            'size' => 'large',
            'crust' => 'stuffed',
        ]);
    }

    public function test_add_to_cart_validates_required_fields(): void
    {
        $response = $this->post(route('cart.add'), []);

        $response->assertSessionHasErrors(['size', 'crust', 'quantity']);
    }

    public function test_add_to_cart_validates_size_values(): void
    {
        $response = $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'gigantic',
            'crust' => 'regular',
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('size');
    }

    public function test_add_to_cart_validates_quantity_range(): void
    {
        $response = $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    // ─── Update Cart ────────────────────────────────────────────────

    public function test_update_cart_item_quantity(): void
    {
        $addResponse = $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 1,
        ]);
        $this->captureSession($addResponse);

        $cartItem = CartItem::first();

        $response = $this->withSessionCookies()->patch(route('cart.update', $cartItem->id), [
            'quantity' => 5,
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertEquals(5, $cartItem->fresh()->quantity);
    }

    public function test_update_quantity_to_zero_removes_item(): void
    {
        $addResponse = $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 1,
        ]);
        $this->captureSession($addResponse);

        $cartItem = CartItem::first();

        $response = $this->withSessionCookies()->patch(route('cart.update', $cartItem->id), [
            'quantity' => 0,
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    // ─── Remove from Cart ───────────────────────────────────────────

    public function test_remove_cart_item(): void
    {
        $addResponse = $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 1,
        ]);
        $this->captureSession($addResponse);

        $cartItem = CartItem::first();

        $response = $this->withSessionCookies()->delete(route('cart.remove', $cartItem->id));

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    // ─── Clear Cart ─────────────────────────────────────────────────

    public function test_clear_cart(): void
    {
        $addResponse = $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 1,
        ]);
        $this->captureSession($addResponse);

        $this->withSessionCookies()->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'large',
            'crust' => 'thick',
            'quantity' => 2,
        ]);

        $this->assertEquals(2, CartItem::count());

        $response = $this->withSessionCookies()->delete(route('cart.clear'));

        $response->assertRedirect(route('cart.index'));
        $this->assertEquals(0, CartItem::count());
    }

    // ─── Authenticated User Cart ────────────────────────────────────

    public function test_authenticated_user_cart_is_separate(): void
    {
        $user = User::factory()->create();

        // Add as guest
        $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 1,
        ]);

        // Add as authenticated user
        $this->actingAs($user);
        $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'large',
            'crust' => 'thick',
            'quantity' => 3,
        ]);

        $response = $this->get(route('cart.index'));
        $response->assertStatus(200);

        // User should only see their own item
        $userItems = CartItem::where('user_id', $user->id)->get();
        $this->assertCount(1, $userItems);
        $this->assertEquals(3, $userItems->first()->quantity);
    }
}
