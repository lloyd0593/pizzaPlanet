<?php

namespace Tests\Unit\Models;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Pizza;
use App\Models\Topping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    // ─── User ───────────────────────────────────────────────────────

    public function test_user_is_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_has_many_orders(): void
    {
        $user = User::factory()->create();
        Order::create([
            'user_id' => $user->id,
            'session_id' => 'sess-1',
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'subtotal' => 10, 'tax' => 0.80, 'total' => 10.80,
            'status' => 'pending',
        ]);

        $this->assertCount(1, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    public function test_user_has_many_cart_items(): void
    {
        $user = User::factory()->create();
        $pizza = Pizza::create([
            'name' => 'Test', 'description' => 'T', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'pizza_id' => $pizza->id,
            'quantity' => 1, 'size' => 'medium', 'crust' => 'regular',
            'is_custom' => false, 'unit_price' => 10.00,
        ]);

        $this->assertCount(1, $user->cartItems);
    }

    // ─── Pizza ──────────────────────────────────────────────────────

    public function test_pizza_has_many_toppings(): void
    {
        $pizza = Pizza::create([
            'name' => 'Margherita', 'description' => 'Classic', 'base_price' => 12.99,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $t1 = Topping::create(['name' => 'Mozzarella', 'price' => 1.50, 'is_active' => true]);
        $t2 = Topping::create(['name' => 'Basil', 'price' => 0.50, 'is_active' => true]);
        $pizza->toppings()->attach([$t1->id, $t2->id]);

        $this->assertCount(2, $pizza->toppings);
    }

    public function test_pizza_active_scope(): void
    {
        Pizza::create([
            'name' => 'Active', 'description' => 'A', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        Pizza::create([
            'name' => 'Inactive', 'description' => 'I', 'base_price' => 10,
            'is_active' => false, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $this->assertCount(1, Pizza::active()->get());
        $this->assertEquals('Active', Pizza::active()->first()->name);
    }

    // ─── Topping ────────────────────────────────────────────────────

    public function test_topping_belongs_to_many_pizzas(): void
    {
        $topping = Topping::create(['name' => 'Cheese', 'price' => 1.00, 'is_active' => true]);
        $p1 = Pizza::create([
            'name' => 'P1', 'description' => 'P', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $p2 = Pizza::create([
            'name' => 'P2', 'description' => 'P', 'base_price' => 12,
            'is_active' => true, 'size' => 'large', 'crust' => 'thick',
        ]);
        $topping->pizzas()->attach([$p1->id, $p2->id]);

        $this->assertCount(2, $topping->pizzas);
    }

    public function test_topping_active_scope(): void
    {
        Topping::create(['name' => 'Active', 'price' => 1.00, 'is_active' => true]);
        Topping::create(['name' => 'Inactive', 'price' => 1.00, 'is_active' => false]);

        $this->assertCount(1, Topping::active()->get());
    }

    // ─── CartItem ───────────────────────────────────────────────────

    public function test_cart_item_belongs_to_pizza(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test', 'description' => 'T', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $cartItem = CartItem::create([
            'session_id' => 'test-session',
            'pizza_id' => $pizza->id,
            'quantity' => 1, 'size' => 'medium', 'crust' => 'regular',
            'is_custom' => false, 'unit_price' => 10.00,
        ]);

        $this->assertInstanceOf(Pizza::class, $cartItem->pizza);
        $this->assertEquals($pizza->id, $cartItem->pizza->id);
    }

    public function test_cart_item_has_toppings(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test', 'description' => 'T', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $topping = Topping::create(['name' => 'Olives', 'price' => 1.00, 'is_active' => true]);

        $cartItem = CartItem::create([
            'session_id' => 'test-session',
            'pizza_id' => $pizza->id,
            'quantity' => 2, 'size' => 'medium', 'crust' => 'regular',
            'is_custom' => false, 'unit_price' => 10.00,
        ]);
        $cartItem->toppings()->attach($topping->id);

        $this->assertCount(1, $cartItem->toppings);
    }

    public function test_cart_item_total_price_attribute(): void
    {
        $pizza = Pizza::create([
            'name' => 'Test', 'description' => 'T', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $topping = Topping::create(['name' => 'Cheese', 'price' => 2.00, 'is_active' => true]);

        $cartItem = CartItem::create([
            'session_id' => 'test-session',
            'pizza_id' => $pizza->id,
            'quantity' => 3, 'size' => 'medium', 'crust' => 'regular',
            'is_custom' => false, 'unit_price' => 10.00,
        ]);
        $cartItem->toppings()->attach($topping->id);
        $cartItem->load('toppings');

        // (10.00 + 2.00) * 3 = 36.00
        $this->assertEquals(36.00, $cartItem->total_price);
    }

    // ─── Order ──────────────────────────────────────────────────────

    public function test_order_has_many_items(): void
    {
        $order = Order::create([
            'session_id' => 'sess', 'customer_name' => 'Test', 'customer_email' => 'test@t.com',
            'subtotal' => 20, 'tax' => 1.60, 'total' => 21.60, 'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'pizza_name' => 'P1', 'quantity' => 1,
            'size' => 'medium', 'crust' => 'regular', 'is_custom' => false,
            'unit_price' => 10, 'toppings_price' => 0, 'total_price' => 10,
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'pizza_name' => 'P2', 'quantity' => 1,
            'size' => 'large', 'crust' => 'thick', 'is_custom' => false,
            'unit_price' => 10, 'toppings_price' => 0, 'total_price' => 10,
        ]);

        $this->assertCount(2, $order->items);
    }

    public function test_order_has_one_payment(): void
    {
        $order = Order::create([
            'session_id' => 'sess', 'customer_name' => 'Test', 'customer_email' => 'test@t.com',
            'subtotal' => 20, 'tax' => 1.60, 'total' => 21.60, 'status' => 'confirmed',
        ]);
        Payment::create([
            'order_id' => $order->id, 'payment_method' => 'credit_card',
            'amount' => 21.60, 'status' => 'success', 'transaction_id' => 'TXN-123',
        ]);

        $this->assertInstanceOf(Payment::class, $order->payment);
    }

    // ─── OrderItem ──────────────────────────────────────────────────

    public function test_order_item_belongs_to_order(): void
    {
        $order = Order::create([
            'session_id' => 'sess', 'customer_name' => 'Test', 'customer_email' => 'test@t.com',
            'subtotal' => 10, 'tax' => 0.80, 'total' => 10.80, 'status' => 'pending',
        ]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id, 'pizza_name' => 'Test', 'quantity' => 1,
            'size' => 'medium', 'crust' => 'regular', 'is_custom' => false,
            'unit_price' => 10, 'toppings_price' => 0, 'total_price' => 10,
        ]);

        $this->assertInstanceOf(Order::class, $orderItem->order);
    }

    public function test_order_item_has_toppings_with_pivot(): void
    {
        $order = Order::create([
            'session_id' => 'sess', 'customer_name' => 'Test', 'customer_email' => 'test@t.com',
            'subtotal' => 10, 'tax' => 0.80, 'total' => 10.80, 'status' => 'pending',
        ]);
        $topping = Topping::create(['name' => 'Pepperoni', 'price' => 1.50, 'is_active' => true]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id, 'pizza_name' => 'Test', 'quantity' => 1,
            'size' => 'medium', 'crust' => 'regular', 'is_custom' => false,
            'unit_price' => 10, 'toppings_price' => 1.50, 'total_price' => 11.50,
        ]);
        $orderItem->toppings()->attach($topping->id, [
            'topping_name' => 'Pepperoni',
            'topping_price' => 1.50,
        ]);

        $this->assertCount(1, $orderItem->toppings);
        $this->assertEquals('Pepperoni', $orderItem->toppings->first()->pivot->topping_name);
        $this->assertEquals(1.50, (float) $orderItem->toppings->first()->pivot->topping_price);
    }

    // ─── Payment ────────────────────────────────────────────────────

    public function test_payment_belongs_to_order(): void
    {
        $order = Order::create([
            'session_id' => 'sess', 'customer_name' => 'Test', 'customer_email' => 'test@t.com',
            'subtotal' => 10, 'tax' => 0.80, 'total' => 10.80, 'status' => 'confirmed',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id, 'payment_method' => 'paypal',
            'amount' => 10.80, 'status' => 'success',
        ]);

        $this->assertInstanceOf(Order::class, $payment->order);
        $this->assertEquals($order->id, $payment->order->id);
    }
}
