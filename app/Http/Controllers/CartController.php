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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pizza added to cart!',
                'cartCount' => $this->cartService->getItemCount(),
            ]);
        }

        return redirect()->back()->with('success', 'Pizza added to cart!');
    }

    /**
     * Get cart data as JSON (for sidebar).
     */
    public function sidebar()
    {
        $items = $this->cartService->getItems();
        $subtotal = $this->cartService->getSubtotal();
        $tax = round($subtotal * 0.08, 2);
        $total = round($subtotal + $tax, 2);

        return response()->json([
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->pizza?->name ?? $item->custom_name ?? 'Custom Pizza',
                    'size' => ucfirst($item->size),
                    'crust' => ucfirst($item->crust),
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'toppings_total' => $item->toppings->sum('price'),
                    'total_price' => ($item->unit_price + $item->toppings->sum('price')) * $item->quantity,
                    'toppings' => $item->toppings->pluck('name')->toArray(),
                    'is_custom' => $item->is_custom,
                ];
            }),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'count' => $this->cartService->getItemCount(),
        ]);
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
