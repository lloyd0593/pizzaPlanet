<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Pizza;
use App\Models\Topping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    private Pizza $pizza;
    private Topping $topping;

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

    private function addItemToCart(): void
    {
        $this->post(route('cart.add'), [
            'pizza_id' => $this->pizza->id,
            'size' => 'medium',
            'crust' => 'regular',
            'quantity' => 1,
        ]);
    }

    // ─── Checkout Page ──────────────────────────────────────────────

    public function test_checkout_page_loads_with_cart_items(): void
    {
        $this->addItemToCart();

        $response = $this->get(route('checkout'));

        $response->assertStatus(200);
        $response->assertViewIs('customer.checkout');
        $response->assertViewHas(['items', 'subtotal', 'tax', 'total']);
    }

    public function test_checkout_redirects_when_cart_empty(): void
    {
        $response = $this->get(route('checkout'));

        $response->assertRedirect(route('menu'));
        $response->assertSessionHas('error');
    }

    // ─── Submit Checkout ────────────────────────────────────────────

    public function test_checkout_creates_order(): void
    {
        $this->addItemToCart();

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '555-1234',
            'delivery_address' => '123 Main St',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals('John Doe', $order->customer_name);
        $this->assertEquals('john@example.com', $order->customer_email);
        $this->assertEquals('pending', $order->status);

        $response->assertRedirect(route('checkout.payment', $order));
    }

    public function test_checkout_validates_required_fields(): void
    {
        $this->addItemToCart();

        $response = $this->post(route('checkout.store'), []);

        $response->assertSessionHasErrors(['customer_name', 'customer_email']);
    }

    public function test_checkout_validates_email_format(): void
    {
        $this->addItemToCart();

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('customer_email');
    }

    public function test_checkout_store_redirects_when_cart_empty(): void
    {
        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
        ]);

        $response->assertRedirect(route('menu'));
    }

    // ─── Payment Page ───────────────────────────────────────────────

    public function test_payment_page_loads_for_pending_order(): void
    {
        $this->addItemToCart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
        ]);

        $order = Order::first();

        $response = $this->get(route('checkout.payment', $order->id));

        $response->assertStatus(200);
        $response->assertViewIs('customer.payment');
        $response->assertViewHas('order');
    }

    public function test_payment_page_redirects_for_confirmed_order(): void
    {
        $this->addItemToCart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
        ]);

        $order = Order::first();
        $order->update(['status' => 'confirmed']);

        $response = $this->get(route('checkout.payment', $order->id));

        $response->assertRedirect(route('order.confirmation', $order->id));
    }

    // ─── Process Payment ────────────────────────────────────────────

    public function test_process_credit_card_payment_success(): void
    {
        $this->addItemToCart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
        ]);

        $order = Order::first();

        $response = $this->post(route('checkout.processPayment', $order->id), [
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect(route('order.confirmation', $order->id));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('confirmed', $order->status);

        $payment = Payment::first();
        $this->assertEquals('success', $payment->status);
        $this->assertEquals('credit_card', $payment->payment_method);
    }

    public function test_process_credit_card_payment_failure(): void
    {
        $this->addItemToCart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
        ]);

        $order = Order::first();

        $response = $this->post(route('checkout.processPayment', $order->id), [
            'payment_method' => 'credit_card',
            'card_number' => '4111111111110000',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect(route('checkout.payment', $order->id));
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('pending', $order->status);
    }

    public function test_process_paypal_payment_success(): void
    {
        $this->addItemToCart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'Jane',
            'customer_email' => 'jane@test.com',
        ]);

        $order = Order::first();

        $response = $this->post(route('checkout.processPayment', $order->id), [
            'payment_method' => 'paypal',
            'paypal_email' => 'jane@paypal.com',
        ]);

        $response->assertRedirect(route('order.confirmation', $order->id));

        $payment = Payment::first();
        $this->assertEquals('success', $payment->status);
        $this->assertEquals('paypal', $payment->payment_method);
    }

    public function test_process_paypal_payment_failure(): void
    {
        $this->addItemToCart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'Jane',
            'customer_email' => 'jane@test.com',
        ]);

        $order = Order::first();

        $response = $this->post(route('checkout.processPayment', $order->id), [
            'payment_method' => 'paypal',
            'paypal_email' => 'fail_user@paypal.com',
        ]);

        $response->assertRedirect(route('checkout.payment', $order->id));
        $response->assertSessionHas('error');
    }

    public function test_payment_validates_method(): void
    {
        $this->addItemToCart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
        ]);

        $order = Order::first();

        $response = $this->post(route('checkout.processPayment', $order->id), [
            'payment_method' => 'bitcoin',
        ]);

        $response->assertSessionHasErrors('payment_method');
    }

    public function test_credit_card_payment_validates_card_fields(): void
    {
        $this->addItemToCart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
        ]);

        $order = Order::first();

        $response = $this->post(route('checkout.processPayment', $order->id), [
            'payment_method' => 'credit_card',
            // missing card fields
        ]);

        $response->assertSessionHasErrors(['card_number', 'card_expiry', 'card_cvv']);
    }

    public function test_successful_payment_clears_cart(): void
    {
        $this->addItemToCart();
        $this->assertEquals(1, CartItem::count());

        $this->post(route('checkout.store'), [
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
        ]);

        $order = Order::first();

        $this->post(route('checkout.processPayment', $order->id), [
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
        ]);

        $this->assertEquals(0, CartItem::count());
    }

    // ─── Confirmation Page ──────────────────────────────────────────

    public function test_confirmation_page_loads(): void
    {
        $order = Order::create([
            'session_id' => session()->getId(),
            'customer_name' => 'Test',
            'customer_email' => 'test@test.com',
            'subtotal' => 10.00,
            'tax' => 0.80,
            'total' => 10.80,
            'status' => 'confirmed',
        ]);

        $response = $this->get(route('order.confirmation', $order->id));

        $response->assertStatus(200);
        $response->assertViewIs('customer.confirmation');
        $response->assertViewHas('order');
    }
}
