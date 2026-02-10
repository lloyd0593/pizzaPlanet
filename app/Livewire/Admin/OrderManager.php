<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $selectedOrder = null;
    public $showDetail = false;

    /**
     * View order details.
     */
    public function viewOrder(int $id)
    {
        $this->selectedOrder = Order::with(['items.toppings', 'payment'])->findOrFail($id);
        $this->showDetail = true;
    }

    /**
     * Close order detail view.
     */
    public function closeDetail()
    {
        $this->selectedOrder = null;
        $this->showDetail = false;
    }

    /**
     * Update order status.
     */
    public function updateStatus(int $id, string $status)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $status]);

        if ($this->selectedOrder && $this->selectedOrder->id === $id) {
            $this->selectedOrder = $order->fresh(['items.toppings', 'payment']);
        }

        session()->flash('message', "Order #{$id} status updated to {$status}.");
    }

    public function render()
    {
        $orders = Order::with(['payment'])
            ->when($this->search, function ($q) {
                $q->where('customer_name', 'like', "%{$this->search}%")
                  ->orWhere('customer_email', 'like', "%{$this->search}%")
                  ->orWhere('id', $this->search);
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.order-manager', [
            'orders' => $orders,
        ])->layout('layouts.admin');
    }
}
