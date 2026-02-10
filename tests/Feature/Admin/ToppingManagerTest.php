<?php

namespace Tests\Feature\Admin;

use App\Models\Pizza;
use App\Models\Topping;
use App\Models\User;
use App\Livewire\Admin\ToppingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ToppingManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    // ─── Render ─────────────────────────────────────────────────────

    public function test_topping_manager_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->assertStatus(200);
    }

    public function test_topping_manager_lists_toppings(): void
    {
        Topping::create(['name' => 'Mozzarella', 'price' => 1.50, 'is_active' => true]);

        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->assertSee('Mozzarella')
            ->assertSee('1.50');
    }

    // ─── Create Topping ─────────────────────────────────────────────

    public function test_create_topping(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('create')
            ->assertSet('showForm', true)
            ->assertSet('isEditing', false)
            ->set('name', 'Jalapeños')
            ->set('price', 1.25)
            ->set('is_active', true)
            ->call('save');

        $this->assertDatabaseHas('toppings', [
            'name' => 'Jalapeños',
            'price' => 1.25,
            'is_active' => true,
        ]);
    }

    public function test_create_topping_validates_required_fields(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('create')
            ->set('name', '')
            ->set('price', '')
            ->call('save')
            ->assertHasErrors(['name', 'price']);
    }

    public function test_create_topping_validates_price_range(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('create')
            ->set('name', 'Test')
            ->set('price', 0)
            ->call('save')
            ->assertHasErrors('price');
    }

    public function test_create_topping_validates_max_price(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('create')
            ->set('name', 'Test')
            ->set('price', 100.00)
            ->call('save')
            ->assertHasErrors('price');
    }

    // ─── Edit Topping ───────────────────────────────────────────────

    public function test_edit_topping(): void
    {
        $topping = Topping::create(['name' => 'Old Name', 'price' => 1.00, 'is_active' => true]);

        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('edit', $topping->id)
            ->assertSet('showForm', true)
            ->assertSet('isEditing', true)
            ->assertSet('name', 'Old Name')
            ->set('name', 'Updated Name')
            ->set('price', 2.50)
            ->call('save');

        $this->assertDatabaseHas('toppings', [
            'id' => $topping->id,
            'name' => 'Updated Name',
            'price' => 2.50,
        ]);
    }

    // ─── Delete Topping ─────────────────────────────────────────────

    public function test_delete_topping(): void
    {
        $topping = Topping::create(['name' => 'Delete Me', 'price' => 1.00, 'is_active' => true]);

        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('delete', $topping->id);

        $this->assertDatabaseMissing('toppings', ['id' => $topping->id]);
    }

    public function test_delete_topping_detaches_from_pizzas(): void
    {
        $topping = Topping::create(['name' => 'Attached', 'price' => 1.00, 'is_active' => true]);
        $pizza = Pizza::create([
            'name' => 'Test', 'description' => 'T', 'base_price' => 10,
            'is_active' => true, 'size' => 'medium', 'crust' => 'regular',
        ]);
        $pizza->toppings()->attach($topping->id);

        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('delete', $topping->id);

        $this->assertDatabaseMissing('pizza_toppings', ['topping_id' => $topping->id]);
    }

    // ─── Toggle Active ──────────────────────────────────────────────

    public function test_toggle_active_status(): void
    {
        $topping = Topping::create(['name' => 'Toggle', 'price' => 1.00, 'is_active' => true]);

        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('toggleActive', $topping->id);

        $this->assertFalse($topping->fresh()->is_active);

        Livewire::test(ToppingManager::class)
            ->call('toggleActive', $topping->id);

        $this->assertTrue($topping->fresh()->is_active);
    }

    // ─── Search ─────────────────────────────────────────────────────

    public function test_search_filters_toppings(): void
    {
        Topping::create(['name' => 'Mozzarella', 'price' => 1.50, 'is_active' => true]);
        Topping::create(['name' => 'Pepperoni', 'price' => 1.75, 'is_active' => true]);

        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->set('search', 'Mozz')
            ->assertSee('Mozzarella')
            ->assertDontSee('Pepperoni');
    }

    // ─── Cancel ─────────────────────────────────────────────────────

    public function test_cancel_hides_form_and_resets(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('create')
            ->set('name', 'Something')
            ->assertSet('showForm', true)
            ->call('cancel')
            ->assertSet('showForm', false)
            ->assertSet('name', '');
    }

    // ─── Activity Logging ───────────────────────────────────────────

    public function test_create_topping_logs_activity(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('create')
            ->set('name', 'Logged Topping')
            ->set('price', 1.00)
            ->call('save');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'topping_created',
            'entity_type' => 'Topping',
        ]);
    }

    public function test_update_topping_logs_activity(): void
    {
        $topping = Topping::create(['name' => 'Log Update', 'price' => 1.00, 'is_active' => true]);

        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('edit', $topping->id)
            ->set('name', 'Updated Log')
            ->call('save');

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'topping_updated',
            'entity_type' => 'Topping',
            'entity_id' => $topping->id,
        ]);
    }

    public function test_delete_topping_logs_activity(): void
    {
        $topping = Topping::create(['name' => 'Log Delete', 'price' => 1.00, 'is_active' => true]);

        $this->actingAs($this->admin);

        Livewire::test(ToppingManager::class)
            ->call('delete', $topping->id);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'topping_deleted',
            'entity_type' => 'Topping',
            'entity_id' => $topping->id,
        ]);
    }
}
