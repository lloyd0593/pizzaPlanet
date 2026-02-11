@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- Hero Section --}}
    <div class="text-center mb-12">
        <h1 class="text-5xl font-extrabold text-gray-900 mb-4">
            Welcome to <span class="text-orange-600">PizzaPlanet</span>
        </h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Handcrafted pizzas made with the freshest ingredients. Choose from our signature creations or build your own masterpiece.
        </p>
        <a href="{{ route('pizza.customize') }}"
           class="inline-block mt-6 bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-8 rounded-full transition shadow-lg hover:shadow-xl">
            <i class="fas fa-wand-magic-sparkles mr-2"></i> Build Your Own Pizza
        </a>
    </div>

    {{-- Pizza Grid --}}
    <h2 class="text-3xl font-bold text-gray-800 mb-6">
        <i class="fas fa-fire text-orange-500 mr-2"></i> Our Menu
    </h2>

    @if($pizzas->isEmpty())
        <div class="text-center py-12 bg-white rounded-xl shadow">
            <i class="fas fa-pizza-slice text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg">No pizzas available right now. Check back soon!</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($pizzas as $pizza)
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover">
                    {{-- Pizza Image Placeholder --}}
                    <div class="h-48 bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center">
                        <i class="fas fa-pizza-slice text-white text-6xl opacity-80"></i>
                    </div>

                    <div class="p-6">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-xl font-bold text-gray-900">{{ $pizza->name }}</h3>
                            <span class="text-2xl font-extrabold text-orange-600">€{{ number_format($pizza->base_price, 2) }}</span>
                        </div>

                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $pizza->description }}</p>

                        {{-- Size & Crust badges --}}
                        <div class="flex gap-2 mb-3">
                            <span class="inline-block bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-1 rounded-full">
                                {{ ucfirst($pizza->size) }}
                            </span>
                            <span class="inline-block bg-yellow-100 text-yellow-700 text-xs font-semibold px-2 py-1 rounded-full">
                                {{ ucfirst($pizza->crust) }} crust
                            </span>
                        </div>

                        {{-- Toppings --}}
                        @if($pizza->toppings->isNotEmpty())
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Toppings</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($pizza->toppings as $topping)
                                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">{{ $topping->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-2">
                            <a href="{{ route('pizza.show', $pizza) }}"
                               class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition text-sm">
                                <i class="fas fa-eye mr-1"></i> Details
                            </a>
                            <form method="POST" action="{{ route('cart.add') }}" class="flex-1" data-ajax-cart>
                                @csrf
                                <input type="hidden" name="pizza_id" value="{{ $pizza->id }}">
                                <input type="hidden" name="size" value="{{ $pizza->size }}">
                                <input type="hidden" name="crust" value="{{ $pizza->crust }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit"
                                        class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition text-sm">
                                    <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
