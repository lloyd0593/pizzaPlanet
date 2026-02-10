<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Pizza;
use App\Models\Topping;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'totalPizzas' => Pizza::count(),
            'activePizzas' => Pizza::active()->count(),
            'totalToppings' => Topping::count(),
            'activeToppings' => Topping::active()->count(),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'confirmedOrders' => Order::where('status', 'confirmed')->count(),
            'recentOrders' => Order::with('payment')->latest()->take(5)->get(),
            'totalRevenue' => Order::whereHas('payment', fn($q) => $q->where('status', 'success'))->sum('total'),
        ])->layout('layouts.admin');
    }
}
