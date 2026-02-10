@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('cart.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-6 transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Cart
    </a>

    <h1 class="text-3xl font-extrabold text-gray-900 mb-6">
        <i class="fas fa-clipboard-check text-orange-500 mr-2"></i> Checkout
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Customer Information Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-user mr-2 text-blue-500"></i> Your Information
                </h2>

                <form method="POST" action="{{ route('checkout.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name *</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="John Doe">
                            @error('customer_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email *</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="john@example.com">
                            @error('customer_email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Phone</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="(555) 123-4567">
                        @error('customer_phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Delivery Address</label>
                        <textarea name="delivery_address" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="123 Main St, City, State 12345">{{ old('delivery_address') }}</textarea>
                        @error('delivery_address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Order Notes</label>
                        <textarea name="notes" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="Any special instructions...">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-lg transition shadow-lg hover:shadow-xl text-lg">
                        <i class="fas fa-arrow-right mr-2"></i> Continue to Payment
                    </button>
                </form>
            </div>
        </div>

        {{-- Order Summary Sidebar --}}
        <div>
            <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-receipt mr-2 text-green-500"></i> Order Summary
                </h2>

                <div class="space-y-3 mb-4">
                    @foreach($items as $item)
                        <div class="flex justify-between items-start text-sm">
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ $item->quantity }}x {{ $item->pizza?->name ?? $item->custom_name ?? 'Custom Pizza' }}
                                </p>
                                <p class="text-gray-400 text-xs">{{ ucfirst($item->size) }}, {{ ucfirst($item->crust) }}</p>
                            </div>
                            <span class="font-semibold text-gray-700">${{ number_format($item->total_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <hr class="my-3">

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Tax (8%)</span>
                        <span>${{ number_format($tax, 2) }}</span>
                    </div>
                    <hr>
                    <div class="flex justify-between text-lg font-extrabold text-gray-900">
                        <span>Total</span>
                        <span class="text-orange-600">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
