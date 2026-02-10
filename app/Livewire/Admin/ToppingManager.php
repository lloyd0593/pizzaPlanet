<?php

namespace App\Livewire\Admin;

use App\Models\Topping;
use App\Services\ActivityLogService;
use Livewire\Component;
use Livewire\WithPagination;

class ToppingManager extends Component
{
    use WithPagination;

    // Form fields
    public $toppingId = null;
    public $name = '';
    public $price = '';
    public $is_active = true;

    // UI state
    public $showForm = false;
    public $isEditing = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0.01|max:99.99',
        'is_active' => 'boolean',
    ];

    /**
     * Open the form for creating a new topping.
     */
    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->isEditing = false;
    }

    /**
     * Open the form for editing an existing topping.
     */
    public function edit(int $id)
    {
        $topping = Topping::findOrFail($id);

        $this->toppingId = $topping->id;
        $this->name = $topping->name;
        $this->price = $topping->price;
        $this->is_active = $topping->is_active;

        $this->showForm = true;
        $this->isEditing = true;
    }

    /**
     * Save the topping (create or update).
     */
    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'price' => $this->price,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing) {
            $topping = Topping::findOrFail($this->toppingId);
            $topping->update($data);

            ActivityLogService::log('topping_updated', 'Topping', $topping->id, [
                'name' => $topping->name,
                'changes' => $data,
            ]);

            session()->flash('message', 'Topping updated successfully.');
        } else {
            $topping = Topping::create($data);

            ActivityLogService::log('topping_created', 'Topping', $topping->id, [
                'name' => $topping->name,
            ]);

            session()->flash('message', 'Topping created successfully.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * Delete a topping.
     */
    public function delete(int $id)
    {
        $topping = Topping::findOrFail($id);
        $toppingName = $topping->name;

        ActivityLogService::log('topping_deleted', 'Topping', $topping->id, [
            'name' => $toppingName,
        ]);

        $topping->pizzas()->detach();
        $topping->delete();

        session()->flash('message', "Topping '{$toppingName}' deleted.");
    }

    /**
     * Toggle the active status of a topping.
     */
    public function toggleActive(int $id)
    {
        $topping = Topping::findOrFail($id);
        $topping->update(['is_active' => !$topping->is_active]);

        $status = $topping->is_active ? 'enabled' : 'disabled';
        ActivityLogService::log("topping_{$status}", 'Topping', $topping->id, [
            'name' => $topping->name,
        ]);

        session()->flash('message', "Topping '{$topping->name}' {$status}.");
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
        $this->toppingId = null;
        $this->name = '';
        $this->price = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $toppings = Topping::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.topping-manager', [
            'toppings' => $toppings,
        ])->layout('layouts.admin');
    }
}
