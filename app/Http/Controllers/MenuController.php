<?php

namespace App\Http\Controllers;

use App\Models\Pizza;
use App\Models\Topping;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display the pizza menu page.
     */
    public function index()
    {
        $pizzas = Pizza::active()->with('toppings')->get();
        $toppings = Topping::active()->orderBy('name')->get();

        return view('customer.menu', compact('pizzas', 'toppings'));
    }

    /**
     * Display a single pizza detail page.
     */
    public function show(Pizza $pizza)
    {
        $pizza->load('toppings');
        $allToppings = Topping::active()->orderBy('name')->get();

        return view('customer.pizza-detail', compact('pizza', 'allToppings'));
    }

    /**
     * Display the custom pizza builder page.
     */
    public function customize()
    {
        $toppings = Topping::active()->orderBy('name')->get();

        return view('customer.customize', compact('toppings'));
    }
}
