@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    @if($order->status === 'confirmed')
        {{-- Success State --}}
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check-circle text-green-500 text-4xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Order Confirmed!</h1>
            <p class="text-gray-600">Thank you for your order. Your pizza is being prepared.</p>
        </div>
    @else
        {{-- Pending/Other State --}}
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-clock text-yellow-500 text-4xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Order #{{ $order->id }}</h1>
            <p class="text-gray-600">Status: <span class="font-bold capitalize">{{ $order->status }}</span></p>
        </div>
    @endif

    {{-- Order Details --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        {{-- Order Header --}}
        <div class="bg-gray-50 px-6 py-4 border-b">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Order Number</p>
                    <p class="text-xl font-bold text-gray-900">#{{ $order->id }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Date</p>
                    <p class="font-semibold text-gray-700">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- Customer Info --}}
        <div class="px-6 py-4 border-b">
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Customer Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Name</p>
                    <p class="font-semibold">{{ $order->customer_name }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Email</p>
                    <p class="font-semibold">{{ $order->customer_email }}</p>
                </div>
                @if($order->customer_phone)
                    <div>
                        <p class="text-gray-500">Phone</p>
                        <p class="font-semibold">{{ $order->customer_phone }}</p>
                    </div>
                @endif
                @if($order->delivery_address)
                    <div>
                        <p class="text-gray-500">Delivery Address</p>
                        <p class="font-semibold">{{ $order->delivery_address }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Order Items --}}
        <div class="px-6 py-4 border-b">
            <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Order Items</h3>
            <div class="space-y-3">
                @foreach($order->items as $item)
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $item->quantity }}x {{ $item->pizza_name }}
                                @if($item->is_custom)
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">Custom</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">{{ ucfirst($item->size) }}, {{ ucfirst($item->crust) }} crust</p>
                            @if($item->toppings->isNotEmpty())
                                <p class="text-xs text-gray-400 mt-1">
                                    Toppings: {{ $item->toppings->pluck('pivot.topping_name')->join(', ') }}
                                </p>
                            @endif
                        </div>
                        <span class="font-semibold text-gray-700">€{{ number_format($item->total_price, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Payment Info --}}
        @if($order->payment)
            <div class="px-6 py-4 border-b">
                <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Payment</h3>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if($order->payment->payment_method === 'credit_card')
                            <i class="fas fa-credit-card text-gray-500"></i>
                            <span class="text-sm">Credit Card ending in {{ $order->payment->card_last_four }}</span>
                        @else
                            <i class="fab fa-paypal text-blue-500"></i>
                            <span class="text-sm">PayPal ({{ $order->payment->paypal_email }})</span>
                        @endif
                    </div>
                    <span class="text-sm font-semibold px-2 py-1 rounded-full {{ $order->payment->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($order->payment->status) }}
                    </span>
                </div>
                @if($order->payment->transaction_id)
                    <p class="text-xs text-gray-400 mt-1">Transaction: {{ $order->payment->transaction_id }}</p>
                @endif
            </div>
        @endif

        {{-- Order Totals --}}
        <div class="px-6 py-4 bg-gray-50">
            <div class="space-y-1 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>€{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Tax</span>
                    <span>€{{ number_format($order->tax, 2) }}</span>
                </div>
                <hr class="my-2">
                <div class="flex justify-between text-xl font-extrabold text-gray-900">
                    <span>Total</span>
                    <span class="text-orange-600">€{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-6 text-center">
        <a href="{{ route('menu') }}"
           class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-8 rounded-full transition shadow-lg">
            <i class="fas fa-utensils mr-2"></i> Order More Pizza
        </a>
    </div>
</div>
@endsection
