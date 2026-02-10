@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('menu') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-6 transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Menu
    </a>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        {{-- Pizza Image --}}
        <div class="h-64 bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center">
            <i class="fas fa-pizza-slice text-white text-8xl opacity-80"></i>
        </div>

        <div class="p-8">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900">{{ $pizza->name }}</h1>
                    <p class="text-gray-600 mt-2">{{ $pizza->description }}</p>
                </div>
                <span class="text-3xl font-extrabold text-orange-600">${{ number_format($pizza->base_price, 2) }}</span>
            </div>

            {{-- Default Info --}}
            <div class="flex gap-3 mb-6">
                <span class="bg-orange-100 text-orange-700 text-sm font-semibold px-3 py-1 rounded-full">
                    <i class="fas fa-ruler mr-1"></i> {{ ucfirst($pizza->size) }}
                </span>
                <span class="bg-yellow-100 text-yellow-700 text-sm font-semibold px-3 py-1 rounded-full">
                    <i class="fas fa-bread-slice mr-1"></i> {{ ucfirst($pizza->crust) }} Crust
                </span>
            </div>

            {{-- Default Toppings --}}
            @if($pizza->toppings->isNotEmpty())
                <div class="mb-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase mb-2">Included Toppings</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($pizza->toppings as $topping)
                            <span class="bg-green-100 text-green-700 text-sm px-3 py-1 rounded-full">
                                <i class="fas fa-check mr-1"></i> {{ $topping->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <hr class="my-6">

            {{-- Add to Cart Form --}}
            <form method="POST" action="{{ route('cart.add') }}">
                @csrf
                <input type="hidden" name="pizza_id" value="{{ $pizza->id }}">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    {{-- Size --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Size</label>
                        <select name="size" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="small" {{ $pizza->size === 'small' ? 'selected' : '' }}>Small (0.8x)</option>
                            <option value="medium" {{ $pizza->size === 'medium' ? 'selected' : '' }}>Medium (1x)</option>
                            <option value="large" {{ $pizza->size === 'large' ? 'selected' : '' }}>Large (1.3x)</option>
                        </select>
                    </div>

                    {{-- Crust --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Crust</label>
                        <select name="crust" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="thin" {{ $pizza->crust === 'thin' ? 'selected' : '' }}>Thin</option>
                            <option value="regular" {{ $pizza->crust === 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="thick" {{ $pizza->crust === 'thick' ? 'selected' : '' }}>Thick (+$1.50)</option>
                            <option value="stuffed" {{ $pizza->crust === 'stuffed' ? 'selected' : '' }}>Stuffed (+$2.50)</option>
                        </select>
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" max="20"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                </div>

                {{-- Customize Toppings --}}
                <div class="mb-6">
                    <h3 class="text-sm font-bold text-gray-700 mb-3">
                        <i class="fas fa-layer-group mr-1"></i> Customize Toppings
                        <span class="text-gray-400 font-normal">(optional - leave unchecked to use defaults)</span>
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($allToppings as $topping)
                            <label class="flex items-center space-x-2 bg-gray-50 rounded-lg px-3 py-2 cursor-pointer hover:bg-orange-50 transition">
                                <input type="checkbox" name="toppings[]" value="{{ $topping->id }}"
                                       {{ $pizza->toppings->contains($topping->id) ? 'checked' : '' }}
                                       class="rounded text-orange-600 focus:ring-orange-500">
                                <span class="text-sm text-gray-700">{{ $topping->name }}</span>
                                <span class="text-xs text-gray-400">+${{ number_format($topping->price, 2) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button type="submit"
                        class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-6 rounded-lg transition shadow-lg hover:shadow-xl text-lg">
                    <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
