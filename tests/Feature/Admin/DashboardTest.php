<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Pizza;
use App\Models\Topping;
use App\Models\User;
use App\Livewire\Admin\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_dashboard_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertStatus(200);
    }

    public function test_dashboard_shows_pizza_counts(): void
    {
        Pizza::create([
            'name' => 'Active', 'description' => 'A', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        Pizza::create([
            'name' => 'Inactive', 'description' => 'I', 'base_price' => 10,
            'is_active' => false, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertSee('2') // total pizzas
            ->assertSee('1'); // active pizzas (also matches other counts)
    }

    public function test_dashboard_shows_order_counts(): void
    {
        Order::create([
            'session_id' => 'sess1', 'customer_name' => 'A', 'customer_email' => 'a@t.com',
            'subtotal' => 10, 'tax' => 0.80, 'total' => 10.80, 'status' => 'pending',
        ]);
        Order::create([
            'session_id' => 'sess2', 'customer_name' => 'B', 'customer_email' => 'b@t.com',
            'subtotal' => 20, 'tax' => 1.60, 'total' => 21.60, 'status' => 'confirmed',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertSee('2'); // total orders
    }

    public function test_dashboard_calculates_revenue(): void
    {
        $order = Order::create([
            'session_id' => 'sess1', 'customer_name' => 'A', 'customer_email' => 'a@t.com',
            'subtotal' => 20, 'tax' => 1.60, 'total' => 21.60, 'status' => 'confirmed',
        ]);
        Payment::create([
            'order_id' => $order->id, 'payment_method' => 'credit_card',
            'amount' => 21.60, 'status' => 'success', 'transaction_id' => 'TXN-1',
        ]);

        // Failed payment order should not count
        $order2 = Order::create([
            'session_id' => 'sess2', 'customer_name' => 'B', 'customer_email' => 'b@t.com',
            'subtotal' => 10, 'tax' => 0.80, 'total' => 10.80, 'status' => 'pending',
        ]);
        Payment::create([
            'order_id' => $order2->id, 'payment_method' => 'credit_card',
            'amount' => 10.80, 'status' => 'failed',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertSee('21.60');
    }
}
