<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    /**
     * Display the cart page.
     */
    public function index()
    {
        $items = $this->cartService->getItems();
        $subtotal = $this->cartService->getSubtotal();
        $tax = round($subtotal * 0.08, 2);
        $total = round($subtotal + $tax, 2);

        return view('customer.cart', compact('items', 'subtotal', 'tax', 'total'));
    }

    /**
     * Add a pizza to the cart.
     */
    public function add(AddToCartRequest $request)
    {
        $validated = $request->validated();
        $toppingIds = $validated['toppings'] ?? [];

        if (!empty($validated['is_custom'])) {
            $this->cartService->addCustomPizza(
                $validated['size'],
                $validated['crust'],
                $toppingIds,
                $validated['quantity']
            );
        } else {
            $this->cartService->addPizza(
                $validated['pizza_id'],
                $validated['size'],
                $validated['crust'],
                $validated['quantity'],
                $toppingIds
            );
        }

        return redirect()->route('cart.index')->with('success', 'Pizza added to cart!');
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, int $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:20',
        ]);

        if ($request->quantity <= 0) {
            $this->cartService->removeItem($cartItemId);
            return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
        }

        $this->cartService->updateQuantity($cartItemId, $request->quantity);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(int $cartItemId)
    {
        $this->cartService->removeItem($cartItemId);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        $this->cartService->clearCart();

        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }
}
