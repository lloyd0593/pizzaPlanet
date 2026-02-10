<?php

namespace Tests\Feature\Admin;

use App\Models\Pizza;
use App\Models\Topping;
use App\Models\User;
use App\Livewire\Admin\PizzaManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PizzaManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    // ─── Render ─────────────────────────────────────────────────────

    public function test_pizza_manager_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->assertStatus(200);
    }

    public function test_pizza_manager_lists_pizzas(): void
    {
        Pizza::create([
            'name' => 'Margherita', 'description' => 'Classic', 'base_price' => 12.99,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->assertSee('Margherita')
            ->assertSee('12.99');
    }

    // ─── Create Pizza ───────────────────────────────────────────────

    public function test_create_pizza(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('create')
            ->assertSet('showForm', true)
            ->assertSet('isEditing', false)
            ->set('name', 'New Pizza')
            ->set('description', 'A new pizza')
            ->set('base_price', 15.99)
            ->set('size', 'large')
            ->set('crust', 'thick')
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('pizzas', [
            'name' => 'New Pizza',
            'base_price' => 15.99,
            'size' => 'large',
            'crust' => 'thick',
        ]);
    }

    public function test_create_pizza_with_toppings(): void
    {
        $t1 = Topping::create(['name' => 'Cheese', 'price' => 1.00, 'is_active' => true]);
        $t2 = Topping::create(['name' => 'Pepperoni', 'price' => 1.50, 'is_active' => true]);

        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('create')
            ->set('name', 'Loaded Pizza')
            ->set('base_price', 18.99)
            ->set('selectedToppings', [$t1->id, $t2->id])
            ->call('save');

        $pizza = Pizza::where('name', 'Loaded Pizza')->first();
        $this->assertNotNull($pizza);
        $this->assertCount(2, $pizza->toppings);
    }

    public function test_create_pizza_validates_required_fields(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('create')
            ->set('name', '')
            ->set('base_price', '')
            ->call('save')
            ->assertHasErrors(['name', 'base_price']);
    }

    public function test_create_pizza_validates_price_range(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('create')
            ->set('name', 'Test')
            ->set('base_price', 0)
            ->call('save')
            ->assertHasErrors('base_price');
    }

    // ─── Edit Pizza ─────────────────────────────────────────────────

    public function test_edit_pizza(): void
    {
        $pizza = Pizza::create([
            'name' => 'Old Name', 'description' => 'Old', 'base_price' => 10.00,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('edit', $pizza->id)
            ->assertSet('showForm', true)
            ->assertSet('isEditing', true)
            ->assertSet('name', 'Old Name')
            ->set('name', 'Updated Name')
            ->set('base_price', 14.99)
            ->call('save');

        $this->assertDatabaseHas('pizzas', [
            'id' => $pizza->id,
            'name' => 'Updated Name',
            'base_price' => 14.99,
        ]);
    }

    // ─── Delete Pizza ───────────────────────────────────────────────

    public function test_delete_pizza(): void
    {
        $pizza = Pizza::create([
            'name' => 'To Delete', 'description' => 'Delete me', 'base_price' => 10.00,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('delete', $pizza->id);

        $this->assertDatabaseMissing('pizzas', ['id' => $pizza->id]);
    }

    public function test_delete_pizza_detaches_toppings(): void
    {
        $pizza = Pizza::create([
            'name' => 'With Toppings', 'description' => 'T', 'base_price' => 10.00,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $topping = Topping::create(['name' => 'Cheese', 'price' => 1.00, 'is_active' => true]);
        $pizza->toppings()->attach($topping->id);

        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('delete', $pizza->id);

        $this->assertDatabaseMissing('pizza_toppings', ['pizza_id' => $pizza->id]);
    }

    // ─── Toggle Active ──────────────────────────────────────────────

    public function test_toggle_active_status(): void
    {
        $pizza = Pizza::create([
            'name' => 'Toggle Me', 'description' => 'T', 'base_price' => 10.00,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('toggleActive', $pizza->id);

        $this->assertFalse($pizza->fresh()->is_active);

        Livewire::test(PizzaManager::class)
            ->call('toggleActive', $pizza->id);

        $this->assertTrue($pizza->fresh()->is_active);
    }

    // ─── Search ─────────────────────────────────────────────────────

    public function test_search_filters_pizzas(): void
    {
        Pizza::create([
            'name' => 'Margherita', 'description' => 'Classic', 'base_price' => 12.99,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        Pizza::create([
            'name' => 'Hawaiian', 'description' => 'Tropical', 'base_price' => 13.99,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->set('search', 'Marg')
            ->assertSee('Margherita')
            ->assertDontSee('Hawaiian');
    }

    // ─── Cancel ─────────────────────────────────────────────────────

    public function test_cancel_hides_form(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('create')
            ->assertSet('showForm', true)
            ->call('cancel')
            ->assertSet('showForm', false)
            ->assertSet('name', '');
    }

    // ─── Activity Logging ───────────────────────────────────────────

    public function test_create_pizza_logs_activity(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('create')
            ->set('name', 'Logged Pizza')
            ->set('base_price', 10.00)
            ->call('save');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'pizza_created',
            'entity_type' => 'Pizza',
        ]);
    }

    public function test_delete_pizza_logs_activity(): void
    {
        $pizza = Pizza::create([
            'name' => 'Log Delete', 'description' => 'D', 'base_price' => 10.00,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(PizzaManager::class)
            ->call('delete', $pizza->id);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'pizza_deleted',
            'entity_type' => 'Pizza',
            'entity_id' => $pizza->id,
        ]);
    }
}
