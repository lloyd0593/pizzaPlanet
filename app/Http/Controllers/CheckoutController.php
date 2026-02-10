<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\PaymentRequest;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private PaymentService $paymentService
    ) {}

    /**
     * Display the checkout page.
     */
    public function index()
    {
        $items = $this->cartService->getItems();

        if ($items->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Your cart is empty.');
        }

        $subtotal = $this->cartService->getSubtotal();
        $tax = round($subtotal * 0.08, 2);
        $total = round($subtotal + $tax, 2);

        return view('customer.checkout', compact('items', 'subtotal', 'tax', 'total'));
    }

    /**
     * Process the checkout form and create an order.
     */
    public function store(CheckoutRequest $request)
    {
        $items = $this->cartService->getItems();

        if ($items->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Your cart is empty.');
        }

        $order = $this->orderService->createOrder($request->validated(), $items);

        // Store order ID in session for payment
        session(['pending_order_id' => $order->id]);

        return redirect()->route('checkout.payment', $order);
    }

    /**
     * Display the payment page.
     */
    public function payment($orderId)
    {
        $order = $this->orderService->getOrder($orderId);

        // Ensure the order belongs to this session/user
        if ($order->session_id !== session()->getId() && $order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return redirect()->route('order.confirmation', $order->id);
        }

        return view('customer.payment', compact('order'));
    }

    /**
     * Process the payment.
     */
    public function processPayment(PaymentRequest $request, $orderId)
    {
        $order = $this->orderService->getOrder($orderId);

        // Ensure the order belongs to this session/user
        if ($order->session_id !== session()->getId() && $order->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();

        if ($validated['payment_method'] === 'credit_card') {
            $payment = $this->paymentService->processCreditCard($order, $validated);
        } else {
            $payment = $this->paymentService->processPayPal($order, $validated);
        }

        if ($payment->isSuccessful()) {
            // Clear the cart after successful payment
            $this->cartService->clearCart();
            session()->forget('pending_order_id');

            return redirect()->route('order.confirmation', $order->id)
                ->with('success', 'Payment successful! Your order has been confirmed.');
        }

        return redirect()->route('checkout.payment', $order->id)
            ->with('error', $payment->failure_reason ?? 'Payment failed. Please try again.');
    }

    /**
     * Display the order confirmation page.
     */
    public function confirmation($orderId)
    {
        $order = $this->orderService->getOrder($orderId);

        return view('customer.confirmation', compact('order'));
    }
}
