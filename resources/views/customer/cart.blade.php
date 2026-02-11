@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6">
        <i class="fas fa-shopping-cart text-orange-500 mr-2"></i> Your Cart
    </h1>

    @if($items->isEmpty())
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <i class="fas fa-shopping-cart text-gray-300 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-400 mb-2">Your cart is empty</h2>
            <p class="text-gray-500 mb-6">Looks like you haven't added any pizzas yet.</p>
            <a href="{{ route('menu') }}"
               class="inline-block bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-8 rounded-full transition">
                <i class="fas fa-utensils mr-2"></i> Browse Menu
            </a>
        </div>
    @else
        <div class="space-y-4 mb-8">
            @foreach($items as $item)
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col md:flex-row items-start md:items-center gap-4">
                    {{-- Pizza Icon --}}
                    <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-pizza-slice text-white text-2xl"></i>
                    </div>

                    {{-- Item Details --}}
                    <div class="flex-1">
                        <h3 class="font-bold text-lg text-gray-900">
                            {{ $item->pizza?->name ?? $item->custom_name ?? 'Custom Pizza' }}
                            @if($item->is_custom)
                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full ml-1">Custom</span>
                            @endif
                        </h3>
                        <div class="flex gap-2 mt-1">
                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">{{ ucfirst($item->size) }}</span>
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">{{ ucfirst($item->crust) }}</span>
                        </div>
                        @if($item->toppings->isNotEmpty())
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-layer-group mr-1"></i>
                                {{ $item->toppings->pluck('name')->join(', ') }}
                            </p>
                        @endif
                        <p class="text-sm text-gray-400 mt-1">
                            Base: €{{ number_format($item->unit_price, 2) }}
                            @if($item->toppings->sum('price') > 0)
                                + Toppings: €{{ number_format($item->toppings->sum('price'), 2) }}
                            @endif
                        </p>
                    </div>

                    {{-- Quantity Control --}}
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('cart.update', $item->id) }}" class="flex items-center gap-1">
                            @csrf
                            @method('PATCH')
                            <button type="submit" name="quantity" value="{{ max(0, $item->quantity - 1) }}"
                                    class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition text-gray-600">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="w-10 text-center font-bold text-lg">{{ $item->quantity }}</span>
                            <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}"
                                    class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition text-gray-600">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Item Total --}}
                    <div class="text-right">
                        <span class="text-xl font-extrabold text-orange-600">€{{ number_format($item->total_price, 2) }}</span>
                    </div>

                    {{-- Remove Button --}}
                    <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600 transition" title="Remove item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        {{-- Cart Summary --}}
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Order Summary</h2>
            <div class="space-y-2 text-gray-600">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span class="font-semibold">€{{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tax (8%)</span>
                    <span class="font-semibold">€{{ number_format($tax, 2) }}</span>
                </div>
                <hr class="my-2">
                <div class="flex justify-between text-xl font-extrabold text-gray-900">
                    <span>Total</span>
                    <span class="text-orange-600">€{{ number_format($total, 2) }}</span>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <form method="POST" action="{{ route('cart.clear') }}" class="flex-shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition">
                        <i class="fas fa-trash mr-1"></i> Clear Cart
                    </button>
                </form>
                <a href="{{ route('checkout') }}"
                   class="flex-1 text-center bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-lg transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-credit-card mr-2"></i> Proceed to Checkout
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
