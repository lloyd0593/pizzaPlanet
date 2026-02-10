<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Topping;
use App\Models\User;
use App\Livewire\Admin\OrderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    private function createOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'session_id' => 'sess-' . uniqid(),
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'subtotal' => 20.00,
            'tax' => 1.60,
            'total' => 21.60,
            'status' => 'pending',
        ], $overrides));
    }

    // ─── Render ─────────────────────────────────────────────────────

    public function test_order_manager_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->assertStatus(200);
    }

    public function test_order_manager_lists_orders(): void
    {
        $this->createOrder(['customer_name' => 'John Doe']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->assertSee('John Doe')
            ->assertSee('21.60');
    }

    // ─── View Order Detail ──────────────────────────────────────────

    public function test_view_order_detail(): void
    {
        $order = $this->createOrder(['customer_name' => 'Jane Smith']);
        OrderItem::create([
            'order_id' => $order->id, 'pizza_name' => 'Margherita', 'quantity' => 2,
            'size' => 'medium', 'crust' => 'regular', 'is_custom' => false,
            'unit_price' => 12.99, 'toppings_price' => 1.50, 'total_price' => 28.98,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->call('viewOrder', $order->id)
            ->assertSet('showDetail', true)
            ->assertSee('Jane Smith')
            ->assertSee('Margherita');
    }

    public function test_close_order_detail(): void
    {
        $order = $this->createOrder();

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->call('viewOrder', $order->id)
            ->assertSet('showDetail', true)
            ->call('closeDetail')
            ->assertSet('showDetail', false)
            ->assertSet('selectedOrder', null);
    }

    // ─── Update Status ──────────────────────────────────────────────

    public function test_update_order_status(): void
    {
        $order = $this->createOrder(['status' => 'pending']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->call('updateStatus', $order->id, 'confirmed');

        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    public function test_update_status_to_preparing(): void
    {
        $order = $this->createOrder(['status' => 'confirmed']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->call('updateStatus', $order->id, 'preparing');

        $this->assertEquals('preparing', $order->fresh()->status);
    }

    public function test_update_status_to_ready(): void
    {
        $order = $this->createOrder(['status' => 'preparing']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->call('updateStatus', $order->id, 'ready');

        $this->assertEquals('ready', $order->fresh()->status);
    }

    public function test_update_status_to_delivered(): void
    {
        $order = $this->createOrder(['status' => 'ready']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->call('updateStatus', $order->id, 'delivered');

        $this->assertEquals('delivered', $order->fresh()->status);
    }

    public function test_update_status_to_cancelled(): void
    {
        $order = $this->createOrder(['status' => 'pending']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->call('updateStatus', $order->id, 'cancelled');

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_update_status_refreshes_selected_order(): void
    {
        $order = $this->createOrder(['status' => 'pending']);

        $this->actingAs($this->admin);

        $component = Livewire::test(OrderManager::class)
            ->call('viewOrder', $order->id)
            ->call('updateStatus', $order->id, 'confirmed');

        $this->assertEquals('confirmed', $component->get('selectedOrder')->status);
    }

    // ─── Search ─────────────────────────────────────────────────────

    public function test_search_by_customer_name(): void
    {
        $this->createOrder(['customer_name' => 'Alice Johnson']);
        $this->createOrder(['customer_name' => 'Bob Smith']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->set('search', 'Alice')
            ->assertSee('Alice Johnson')
            ->assertDontSee('Bob Smith');
    }

    public function test_search_by_customer_email(): void
    {
        $this->createOrder(['customer_name' => 'Alice', 'customer_email' => 'alice@test.com']);
        $this->createOrder(['customer_name' => 'Bob', 'customer_email' => 'bob@test.com']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->set('search', 'alice@test')
            ->assertSee('Alice')
            ->assertDontSee('Bob');
    }

    // ─── Status Filter ──────────────────────────────────────────────

    public function test_filter_by_status(): void
    {
        $this->createOrder(['customer_name' => 'Pending Order', 'status' => 'pending']);
        $this->createOrder(['customer_name' => 'Confirmed Order', 'status' => 'confirmed']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->set('statusFilter', 'pending')
            ->assertSee('Pending Order')
            ->assertDontSee('Confirmed Order');
    }

    public function test_clear_status_filter_shows_all(): void
    {
        $this->createOrder(['customer_name' => 'Pending', 'status' => 'pending']);
        $this->createOrder(['customer_name' => 'Confirmed', 'status' => 'confirmed']);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->set('statusFilter', '')
            ->assertSee('Pending')
            ->assertSee('Confirmed');
    }

    // ─── Payment Info ───────────────────────────────────────────────

    public function test_order_shows_payment_status(): void
    {
        $order = $this->createOrder(['status' => 'confirmed']);
        Payment::create([
            'order_id' => $order->id, 'payment_method' => 'credit_card',
            'amount' => 21.60, 'status' => 'success', 'transaction_id' => 'TXN-123',
            'card_last_four' => '1111',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(OrderManager::class)
            ->assertSee('Success');
    }
}
