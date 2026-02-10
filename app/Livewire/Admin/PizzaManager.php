<?php

namespace App\Livewire\Admin;

use App\Models\Pizza;
use App\Models\Topping;
use App\Services\ActivityLogService;
use Livewire\Component;
use Livewire\WithPagination;

class PizzaManager extends Component
{
    use WithPagination;

    // Form fields
    public $pizzaId = null;
    public $name = '';
    public $description = '';
    public $base_price = '';
    public $image_url = '';
    public $is_active = true;
    public $size = 'medium';
    public $crust = 'regular';
    public $selectedToppings = [];

    // UI state
    public $showForm = false;
    public $isEditing = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'base_price' => 'required|numeric|min:0.01|max:999.99',
        'image_url' => 'nullable|string|max:500',
        'is_active' => 'boolean',
        'size' => 'required|in:small,medium,large',
        'crust' => 'required|in:thin,regular,thick,stuffed',
        'selectedToppings' => 'array',
        'selectedToppings.*' => 'exists:toppings,id',
    ];

    /**
     * Open the form for creating a new pizza.
     */
    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->isEditing = false;
    }

    /**
     * Open the form for editing an existing pizza.
     */
    public function edit(int $id)
    {
        $pizza = Pizza::with('toppings')->findOrFail($id);

        $this->pizzaId = $pizza->id;
        $this->name = $pizza->name;
        $this->description = $pizza->description ?? '';
        $this->base_price = $pizza->base_price;
        $this->image_url = $pizza->image_url ?? '';
        $this->is_active = $pizza->is_active;
        $this->size = $pizza->size;
        $this->crust = $pizza->crust;
        $this->selectedToppings = $pizza->toppings->pluck('id')->toArray();

        $this->showForm = true;
        $this->isEditing = true;
    }

    /**
     * Save the pizza (create or update).
     */
    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'base_price' => $this->base_price,
            'image_url' => $this->image_url ?: null,
            'is_active' => $this->is_active,
            'size' => $this->size,
            'crust' => $this->crust,
        ];

        if ($this->isEditing) {
            $pizza = Pizza::findOrFail($this->pizzaId);
            $pizza->update($data);
            $pizza->toppings()->sync($this->selectedToppings);

            ActivityLogService::log('pizza_updated', 'Pizza', $pizza->id, [
                'name' => $pizza->name,
                'changes' => $data,
            ]);

            session()->flash('message', 'Pizza updated successfully.');
        } else {
            $pizza = Pizza::create($data);
            $pizza->toppings()->sync($this->selectedToppings);

            ActivityLogService::log('pizza_created', 'Pizza', $pizza->id, [
                'name' => $pizza->name,
            ]);

            session()->flash('message', 'Pizza created successfully.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * Delete a pizza.
     */
    public function delete(int $id)
    {
        $pizza = Pizza::findOrFail($id);
        $pizzaName = $pizza->name;

        ActivityLogService::log('pizza_deleted', 'Pizza', $pizza->id, [
            'name' => $pizzaName,
        ]);

        $pizza->toppings()->detach();
        $pizza->delete();

        session()->flash('message', "Pizza '{$pizzaName}' deleted.");
    }

    /**
     * Toggle the active status of a pizza.
     */
    public function toggleActive(int $id)
    {
        $pizza = Pizza::findOrFail($id);
        $pizza->update(['is_active' => !$pizza->is_active]);

        $status = $pizza->is_active ? 'enabled' : 'disabled';
        ActivityLogService::log("pizza_{$status}", 'Pizza', $pizza->id, [
            'name' => $pizza->name,
        ]);

        session()->flash('message', "Pizza '{$pizza->name}' {$status}.");
    }

    /**
     * Cancel form editing.
     */
    public function cancel()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * Reset form fields.
     */
    private function resetForm()
    {
        $this->pizzaId = null;
        $this->name = '';
        $this->description = '';
        $this->base_price = '';
        $this->image_url = '';
        $this->is_active = true;
        $this->size = 'medium';
        $this->crust = 'regular';
        $this->selectedToppings = [];
        $this->resetValidation();
    }

    public function render()
    {
        $pizzas = Pizza::with('toppings')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        $allToppings = Topping::orderBy('name')->get();

        return view('livewire.admin.pizza-manager', [
            'pizzas' => $pizzas,
            'allToppings' => $allToppings,
        ])->layout('layouts.admin');
    }
}
