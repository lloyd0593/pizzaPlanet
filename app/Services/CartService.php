<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Pizza;
use App\Models\Topping;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Size price multipliers.
     */
    private const SIZE_MULTIPLIERS = [
        'small' => 0.8,
        'medium' => 1.0,
        'large' => 1.3,
    ];

    /**
     * Crust price additions.
     */
    private const CRUST_ADDITIONS = [
        'thin' => 0.00,
        'regular' => 0.00,
        'thick' => 1.50,
        'stuffed' => 2.50,
    ];

    /**
     * Get the current cart identifier (user_id or session_id).
     */
    private function getCartQuery()
    {
        $query = CartItem::with(['pizza', 'toppings']);

        if (Auth::check()) {
            return $query->where('user_id', Auth::id());
        }

        return $query->where('session_id', session()->getId());
    }

    /**
     * Get all cart items for the current user/session.
     */
    public function getItems()
    {
        return $this->getCartQuery()->get();
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getItemCount(): int
    {
        return $this->getCartQuery()->sum('quantity');
    }

    /**
     * Get the cart subtotal.
     */
    public function getSubtotal(): float
    {
        $items = $this->getItems();
        $subtotal = 0;

        foreach ($items as $item) {
            $toppingsTotal = $item->toppings->sum('price');
            $subtotal += ($item->unit_price + $toppingsTotal) * $item->quantity;
        }

        return round($subtotal, 2);
    }

    /**
     * Calculate the unit price for a pizza based on size and crust.
     */
    public function calculateUnitPrice(float $basePrice, string $size, string $crust): float
    {
        $multiplier = self::SIZE_MULTIPLIERS[$size] ?? 1.0;
        $crustAddition = self::CRUST_ADDITIONS[$crust] ?? 0.00;

        return round(($basePrice * $multiplier) + $crustAddition, 2);
    }

    /**
     * Add a predefined pizza to the cart.
     */
    public function addPizza(int $pizzaId, string $size, string $crust, int $quantity = 1, array $toppingIds = []): CartItem
    {
        $pizza = Pizza::findOrFail($pizzaId);
        $unitPrice = $this->calculateUnitPrice($pizza->base_price, $size, $crust);

        $cartItem = CartItem::create([
            'session_id' => Auth::check() ? null : session()->getId(),
            'user_id' => Auth::id(),
            'pizza_id' => $pizza->id,
            'quantity' => $quantity,
            'size' => $size,
            'crust' => $crust,
            'is_custom' => false,
            'unit_price' => $unitPrice,
        ]);

        // Attach toppings (use pizza's default toppings if none specified)
        if (empty($toppingIds)) {
            $toppingIds = $pizza->toppings->pluck('id')->toArray();
        }
        $cartItem->toppings()->sync($toppingIds);

        ActivityLogService::log('cart_add_pizza', 'CartItem', $cartItem->id, [
            'pizza_id' => $pizza->id,
            'pizza_name' => $pizza->name,
            'size' => $size,
            'crust' => $crust,
            'quantity' => $quantity,
            'toppings' => $toppingIds,
        ]);

        return $cartItem;
    }

    /**
     * Add a custom pizza to the cart.
     */
    public function addCustomPizza(string $size, string $crust, array $toppingIds, int $quantity = 1): CartItem
    {
        // Custom pizza base price
        $basePrice = 7.99;
        $unitPrice = $this->calculateUnitPrice($basePrice, $size, $crust);

        $cartItem = CartItem::create([
            'session_id' => Auth::check() ? null : session()->getId(),
            'user_id' => Auth::id(),
            'pizza_id' => null,
            'quantity' => $quantity,
            'size' => $size,
            'crust' => $crust,
            'is_custom' => true,
            'custom_name' => 'Custom Pizza',
            'unit_price' => $unitPrice,
        ]);

        $cartItem->toppings()->sync($toppingIds);

        ActivityLogService::log('cart_add_custom_pizza', 'CartItem', $cartItem->id, [
            'size' => $size,
            'crust' => $crust,
            'quantity' => $quantity,
            'toppings' => $toppingIds,
        ]);

        return $cartItem;
    }

    /**
     * Update the quantity of a cart item.
     */
    public function updateQuantity(int $cartItemId, int $quantity): ?CartItem
    {
        $cartItem = $this->getCartQuery()->where('cart_items.id', $cartItemId)->firstOrFail();

        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        $cartItem->update(['quantity' => $quantity]);

        ActivityLogService::log('cart_update_quantity', 'CartItem', $cartItem->id, [
            'new_quantity' => $quantity,
        ]);

        return $cartItem;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $cartItemId): ?CartItem
    {
        $cartItem = $this->getCartQuery()->where('cart_items.id', $cartItemId)->firstOrFail();

        ActivityLogService::log('cart_remove_item', 'CartItem', $cartItem->id, [
            'pizza_name' => $cartItem->pizza?->name ?? $cartItem->custom_name,
        ]);

        $cartItem->toppings()->detach();
        $cartItem->delete();

        return null;
    }

    /**
     * Clear the entire cart.
     */
    public function clearCart(): void
    {
        $items = $this->getCartQuery()->get();

        foreach ($items as $item) {
            $item->toppings()->detach();
            $item->delete();
        }

        ActivityLogService::log('cart_cleared', null, null, [
            'items_removed' => $items->count(),
        ]);
    }

    /**
     * Migrate session cart to user cart after login.
     */
    public function migrateSessionCart(string $sessionId, int $userId): void
    {
        CartItem::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update([
                'user_id' => $userId,
                'session_id' => null,
            ]);
    }
}
