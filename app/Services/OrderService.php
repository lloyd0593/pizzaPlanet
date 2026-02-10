<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order from the current cart.
     */
    public function createOrder(array $customerData, iterable $cartItems): Order
    {
        return DB::transaction(function () use ($customerData, $cartItems) {
            // Calculate totals
            $subtotal = 0;
            foreach ($cartItems as $item) {
                $toppingsTotal = $item->toppings->sum('price');
                $subtotal += ($item->unit_price + $toppingsTotal) * $item->quantity;
            }

            $tax = round($subtotal * 0.08, 2); // 8% tax
            $total = round($subtotal + $tax, 2);

            // Create the order
            $order = Order::create([
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'customer_name' => $customerData['customer_name'],
                'customer_email' => $customerData['customer_email'],
                'customer_phone' => $customerData['customer_phone'] ?? null,
                'delivery_address' => $customerData['delivery_address'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending',
                'notes' => $customerData['notes'] ?? null,
            ]);

            // Create order items from cart items
            foreach ($cartItems as $cartItem) {
                $toppingsPrice = $cartItem->toppings->sum('price');
                $itemTotal = ($cartItem->unit_price + $toppingsPrice) * $cartItem->quantity;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'pizza_id' => $cartItem->pizza_id,
                    'pizza_name' => $cartItem->pizza?->name ?? $cartItem->custom_name ?? 'Custom Pizza',
                    'quantity' => $cartItem->quantity,
                    'size' => $cartItem->size,
                    'crust' => $cartItem->crust,
                    'is_custom' => $cartItem->is_custom,
                    'unit_price' => $cartItem->unit_price,
                    'toppings_price' => $toppingsPrice,
                    'total_price' => $itemTotal,
                ]);

                // Copy toppings to order item
                foreach ($cartItem->toppings as $topping) {
                    $orderItem->toppings()->attach($topping->id, [
                        'topping_name' => $topping->name,
                        'topping_price' => $topping->price,
                    ]);
                }
            }

            ActivityLogService::log('order_created', 'Order', $order->id, [
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'total' => $order->total,
                'items_count' => count($cartItems),
            ]);

            return $order;
        });
    }

    /**
     * Get an order by ID with all relations.
     */
    public function getOrder(int $orderId): Order
    {
        return Order::with(['items.toppings', 'payment'])->findOrFail($orderId);
    }
}
