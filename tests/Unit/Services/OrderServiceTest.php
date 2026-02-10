<?php

namespace Tests\Unit\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Pizza;
use App\Models\Topping;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    // ─── Create Order ───────────────────────────────────────────────

    public function test_create_order_from_cart_items(): void
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

        $cartItem = CartItem::create([
            'session_id' => session()->getId(),
            'pizza_id' => $pizza->id,
            'quantity' => 2,
            'size' => 'medium',
            'crust' => 'regular',
            'is_custom' => false,
            'unit_price' => 12.99,
        ]);
        $cartItem->toppings()->attach($topping->id);
        $cartItem->load('toppings');

        $customerData = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '555-1234',
            'delivery_address' => '123 Main St',
            'notes' => 'Extra napkins please',
        ];

        $order = $this->orderService->createOrder($customerData, collect([$cartItem]));

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('John Doe', $order->customer_name);
        $this->assertEquals('john@example.com', $order->customer_email);
        $this->assertEquals('555-1234', $order->customer_phone);
        $this->assertEquals('123 Main St', $order->delivery_address);
        $this->assertEquals('Extra napkins please', $order->notes);
        $this->assertEquals('pending', $order->status);

        // subtotal = (12.99 + 1.50) * 2 = 28.98
        $this->assertEquals(28.98, (float) $order->subtotal);
        // tax = 28.98 * 0.08 = 2.32 (rounded)
        $this->assertEquals(2.32, (float) $order->tax);
        // total = 28.98 + 2.32 = 31.30
        $this->assertEquals(31.30, (float) $order->total);
    }

    public function test_create_order_creates_order_items(): void
    {
        $pizza = Pizza::create([
            'name' => 'Pepperoni',
            'description' => 'Classic pepperoni',
            'base_price' => 14.99,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $topping = Topping::create(['name' => 'Pepperoni', 'price' => 1.50, 'is_active' => true]);

        $cartItem = CartItem::create([
            'session_id' => session()->getId(),
            'pizza_id' => $pizza->id,
            'quantity' => 1,
            'size' => 'large',
            'crust' => 'thick',
            'is_custom' => false,
            'unit_price' => 20.99,
        ]);
        $cartItem->toppings()->attach($topping->id);
        $cartItem->load('toppings');

        $order = $this->orderService->createOrder(
            ['customer_name' => 'Jane', 'customer_email' => 'jane@test.com'],
            collect([$cartItem])
        );

        $order->load('items.toppings');

        $this->assertCount(1, $order->items);
        $orderItem = $order->items->first();
        $this->assertEquals('Pepperoni', $orderItem->pizza_name);
        $this->assertEquals(1, $orderItem->quantity);
        $this->assertEquals('large', $orderItem->size);
        $this->assertEquals('thick', $orderItem->crust);
        $this->assertFalse($orderItem->is_custom);
        $this->assertCount(1, $orderItem->toppings);
        $this->assertEquals('Pepperoni', $orderItem->toppings->first()->pivot->topping_name);
        $this->assertEquals(1.50, (float) $orderItem->toppings->first()->pivot->topping_price);
    }

    public function test_create_order_with_custom_pizza(): void
    {
        $topping = Topping::create(['name' => 'Bacon', 'price' => 2.00, 'is_active' => true]);

        $cartItem = CartItem::create([
            'session_id' => session()->getId(),
            'pizza_id' => null,
            'quantity' => 1,
            'size' => 'medium',
            'crust' => 'regular',
            'is_custom' => true,
            'custom_name' => 'Custom Pizza',
            'unit_price' => 7.99,
        ]);
        $cartItem->toppings()->attach($topping->id);
        $cartItem->load('toppings');

        $order = $this->orderService->createOrder(
            ['customer_name' => 'Bob', 'customer_email' => 'bob@test.com'],
            collect([$cartItem])
        );

        $order->load('items');
        $orderItem = $order->items->first();

        $this->assertTrue($orderItem->is_custom);
        $this->assertEquals('Custom Pizza', $orderItem->pizza_name);
        $this->assertNull($orderItem->pizza_id);
    }

    public function test_create_order_with_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pizza = Pizza::create([
            'name' => 'Test',
            'description' => 'Test',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'pizza_id' => $pizza->id,
            'quantity' => 1,
            'size' => 'medium',
            'crust' => 'regular',
            'is_custom' => false,
            'unit_price' => 10.00,
        ]);
        $cartItem->load('toppings');

        $order = $this->orderService->createOrder(
            ['customer_name' => 'User', 'customer_email' => 'user@test.com'],
            collect([$cartItem])
        );

        $this->assertEquals($user->id, $order->user_id);
    }

    public function test_create_order_with_multiple_items(): void
    {
        $pizza1 = Pizza::create([
            'name' => 'Pizza A',
            'description' => 'A',
            'base_price' => 10.00,
            'is_active' => true,
            'size' => 'medium',
            'crust' => 'regular',
        ]);
        $pizza2 = Pizza::create([
            'name' => 'Pizza B',
            'description' => 'B',
            'base_price' => 15.00,
            'is_active' => true,
            'size' => 'large',
            'crust' => 'thick',
        ]);

        $item1 = CartItem::create([
            'session_id' => session()->getId(),
            'pizza_id' => $pizza1->id,
            'quantity' => 2,
            'size' => 'medium',
            'crust' => 'regular',
            'is_custom' => false,
            'unit_price' => 10.00,
        ]);
        $item1->load('toppings');

        $item2 = CartItem::create([
            'session_id' => session()->getId(),
            'pizza_id' => $pizza2->id,
            'quantity' => 1,
            'size' => 'large',
            'crust' => 'thick',
            'is_custom' => false,
            'unit_price' => 15.00,
        ]);
        $item2->load('toppings');

        $order = $this->orderService->createOrder(
            ['customer_name' => 'Multi', 'customer_email' => 'multi@test.com'],
            collect([$item1, $item2])
        );

        $order->load('items');
        $this->assertCount(2, $order->items);
        // subtotal = (10*2) + (15*1) = 35.00
        $this->assertEquals(35.00, (float) $order->subtotal);
    }

    // ─── Get Order ──────────────────────────────────────────────────

    public function test_get_order_loads_relations(): void
    {
        $order = Order::create([
            'session_id' => session()->getId(),
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'subtotal' => 10.00,
            'tax' => 0.80,
            'total' => 10.80,
            'status' => 'pending',
        ]);

        $fetched = $this->orderService->getOrder($order->id);

        $this->assertEquals($order->id, $fetched->id);
        $this->assertTrue($fetched->relationLoaded('items'));
        $this->assertTrue($fetched->relationLoaded('payment'));
    }

    public function test_get_order_throws_for_invalid_id(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->orderService->getOrder(99999);
    }
}
