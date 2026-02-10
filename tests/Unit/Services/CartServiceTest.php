<?php

namespace Tests\Unit\Services;

use App\Models\CartItem;
use App\Models\Pizza;
use App\Models\Topping;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
    }

    // ─── Price Calculation ───────────────────────────────────────────

    public function test_calculate_unit_price_medium_regular(): void
    {
        // medium = 1.0x, regular = +0.00
        $price = $this->cartService->calculateUnitPrice(10.00, 'medium', 'regular');
        $this->assertEquals(10.00, $price);
    }

    public function test_calculate_unit_price_small_thin(): void
    {
        // small = 0.8x, thin = +0.00
        $price = $this->cartService->calculateUnitPrice(10.00, 'small', 'thin');
        $this->assertEquals(8.00, $price);
    }

    public function test_calculate_unit_price_large_stuffed(): void
    {
        // large = 1.3x, stuffed = +2.50
        $price = $this->cartService->calculateUnitPrice(10.00, 'large', 'stuffed');
        $this->assertEquals(15.50, $price);
    }

    public function test_calculate_unit_price_large_thick(): void
    {
        // large = 1.3x, thick = +1.50
        $price = $this->cartService->calculateUnitPrice(10.00, 'large', 'thick');
        $this->assertEquals(14.50, $price);
    }

    public function test_calculate_unit_price_unknown_size_defaults(): void
    {
        // unknown size defaults to 1.0x multiplier
        $price = $this->cartService->calculateUnitPrice(10.00, 'unknown', 'regular');
        $this->assertEquals(10.00, $price);
    }

    // ─── Add Pizza to Cart ──────────────────────────────────────────

    public function test_add_pizza_to_cart_as_guest(): void
    {
        $pizza = Pizza::create([
            'name' => 'Margherita',
            'description' => 'Classic',
            'base_price' => 12.99,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $topping = Topping::create(['name' => 'Mozzarella', 'price' => 1.50, 'is_active' => true]);
        $pizza->toppings()->attach($topping->id);

        $cartItem = $this->cartService->addPizza($pizza->id, 'medium', 'regular', 2);

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals($pizza->id, $cartItem->pizza_id);
        $this->assertEquals(2, $cartItem->quantity);
        $this->assertEquals('medium', $cartItem->size);
        $this->assertEquals('regular', $cartItem->crust);
        $this->assertFalse($cartItem->is_custom);
        $this->assertEquals(12.99, $cartItem->unit_price);
        $this->assertNotNull($cartItem->session_id);
        $this->assertNull($cartItem->user_id);
    }

    public function test_add_pizza_uses_default_toppings_when_none_specified(): void
    {
        $pizza = Pizza::create([
            'name' => 'Pepperoni',
            'description' => 'Classic pepperoni',
            'base_price' => 14.99,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $t1 = Topping::create(['name' => 'Pepperoni', 'price' => 1.50, 'is_active' => true]);
        $t2 = Topping::create(['name' => 'Cheese', 'price' => 1.00, 'is_active' => true]);
        $pizza->toppings()->attach([$t1->id, $t2->id]);

        $cartItem = $this->cartService->addPizza($pizza->id, 'medium', 'regular');
        $cartItem->load('toppings');

        $this->assertCount(2, $cartItem->toppings);
    }

    public function test_add_pizza_with_custom_toppings(): void
    {
        $pizza = Pizza::create([
            'name' => 'Margherita',
            'description' => 'Classic',
            'base_price' => 12.99,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $t1 = Topping::create(['name' => 'Olives', 'price' => 1.00, 'is_active' => true]);
        $t2 = Topping::create(['name' => 'Mushrooms', 'price' => 1.25, 'is_active' => true]);

        $cartItem = $this->cartService->addPizza($pizza->id, 'large', 'thick', 1, [$t1->id, $t2->id]);
        $cartItem->load('toppings');

        $this->assertCount(2, $cartItem->toppings);
        $this->assertEquals('large', $cartItem->size);
        $this->assertEquals('thick', $cartItem->crust);
        // large = 1.3 * 12.99 + 1.50 (thick) = 18.387 -> 18.39
        $this->assertEquals(18.39, (float) $cartItem->unit_price);
    }

    public function test_add_pizza_to_cart_as_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pizza = Pizza::create([
            'name' => 'Hawaiian',
            'description' => 'Ham and pineapple',
            'base_price' => 13.99,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $cartItem = $this->cartService->addPizza($pizza->id, 'medium', 'regular');

        $this->assertEquals($user->id, $cartItem->user_id);
        $this->assertNull($cartItem->session_id);
    }

    // ─── Add Custom Pizza ───────────────────────────────────────────

    public function test_add_custom_pizza(): void
    {
        $t1 = Topping::create(['name' => 'Bacon', 'price' => 2.00, 'is_active' => true]);
        $t2 = Topping::create(['name' => 'Onions', 'price' => 0.75, 'is_active' => true]);

        $cartItem = $this->cartService->addCustomPizza('large', 'stuffed', [$t1->id, $t2->id], 3);
        $cartItem->load('toppings');

        $this->assertTrue($cartItem->is_custom);
        $this->assertNull($cartItem->pizza_id);
        $this->assertEquals('Custom Pizza', $cartItem->custom_name);
        $this->assertEquals(3, $cartItem->quantity);
        $this->assertCount(2, $cartItem->toppings);
        // base 7.99 * 1.3 (large) + 2.50 (stuffed) = 12.887 -> 12.89
        $this->assertEquals(12.89, (float) $cartItem->unit_price);
    }

    // ─── Get Items / Count / Subtotal ───────────────────────────────

    public function test_get_items_returns_only_current_session(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test Pizza',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $this->cartService->addPizza($pizza->id, 'medium', 'regular', 1);
        $this->cartService->addPizza($pizza->id, 'large', 'thick', 2);

        // Create an item for a different session
        CartItem::create([
            'session_id' => 'other-session-id',
            'pizza_id' => $pizza->id,
            'quantity' => 1,
            'size' => 'small',
            'crust' => 'thin',
            'is_custom' => false,
            'unit_price' => 8.00,
        ]);

        $items = $this->cartService->getItems();
        $this->assertCount(2, $items);
    }

    public function test_get_item_count(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test Pizza',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $this->cartService->addPizza($pizza->id, 'medium', 'regular', 2);
        $this->cartService->addPizza($pizza->id, 'large', 'regular', 3);

        $this->assertEquals(5, $this->cartService->getItemCount());
    }

    public function test_get_subtotal(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test Pizza',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $topping = Topping::create(['name' => 'Cheese', 'price' => 2.00, 'is_active' => true]);

        // medium/regular = 10.00 unit price, qty 2, + topping 2.00 each = (10+2)*2 = 24.00
        $cartItem = $this->cartService->addPizza($pizza->id, 'medium', 'regular', 2, [$topping->id]);

        $subtotal = $this->cartService->getSubtotal();
        $this->assertEquals(24.00, $subtotal);
    }

    // ─── Update Quantity ────────────────────────────────────────────

    public function test_update_quantity(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test Pizza',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $cartItem = $this->cartService->addPizza($pizza->id, 'medium', 'regular', 1);
        $updated = $this->cartService->updateQuantity($cartItem->id, 5);

        $this->assertEquals(5, $updated->quantity);
    }

    public function test_update_quantity_to_zero_removes_item(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test Pizza',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $cartItem = $this->cartService->addPizza($pizza->id, 'medium', 'regular', 1);
        $this->cartService->updateQuantity($cartItem->id, 0);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    // ─── Remove Item ────────────────────────────────────────────────

    public function test_remove_item(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test Pizza',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $topping = Topping::create(['name' => 'Cheese', 'price' => 1.00, 'is_active' => true]);
        $cartItem = $this->cartService->addPizza($pizza->id, 'medium', 'regular', 1, [$topping->id]);

        $this->cartService->removeItem($cartItem->id);

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
        $this->assertDatabaseMissing('cart_item_toppings', ['cart_item_id' => $cartItem->id]);
    }

    // ─── Clear Cart ─────────────────────────────────────────────────

    public function test_clear_cart(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test Pizza',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $this->cartService->addPizza($pizza->id, 'medium', 'regular', 1);
        $this->cartService->addPizza($pizza->id, 'large', 'thick', 2);

        $this->assertEquals(2, $this->cartService->getItems()->count());

        $this->cartService->clearCart();

        $this->assertEquals(0, $this->cartService->getItems()->count());
    }

    // ─── Migrate Session Cart ───────────────────────────────────────

    public function test_migrate_session_cart_to_user(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test Pizza',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        // Add as guest
        $cartItem = $this->cartService->addPizza($pizza->id, 'medium', 'regular', 1);
        $sessionId = $cartItem->session_id;

        $this->assertNotNull($sessionId);
        $this->assertNull($cartItem->user_id);

        // Migrate to user
        $user = User::factory()->create();
        $this->cartService->migrateSessionCart($sessionId, $user->id);

        $cartItem->refresh();
        $this->assertEquals($user->id, $cartItem->user_id);
        $this->assertNull($cartItem->session_id);
    }
}
