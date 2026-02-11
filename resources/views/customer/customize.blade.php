@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('menu') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-6 transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Menu
    </a>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="h-48 bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
            <div class="text-center text-white">
                <i class="fas fa-wand-magic-sparkles text-5xl mb-2"></i>
                <h1 class="text-3xl font-extrabold">Build Your Own Pizza</h1>
                <p class="text-purple-100">Starting at €10.00</p>
            </div>
        </div>

        <div class="p-8">
            <form method="POST" action="{{ route('cart.add') }}" id="customPizzaForm" data-ajax-cart>
                @csrf
                <input type="hidden" name="is_custom" value="1">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    {{-- Size --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-ruler mr-1 text-orange-500"></i> Choose Size
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-orange-400 transition {{ old('size') === 'small' ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                                <input type="radio" name="size" value="small" {{ old('size', 'medium') === 'small' ? 'checked' : '' }} class="text-orange-600 mr-3">
                                <div>
                                    <span class="font-semibold">Small</span>
                                    <span class="text-gray-400 text-sm block">10" - 0.8x price</span>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-orange-400 transition {{ old('size', 'medium') === 'medium' ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                                <input type="radio" name="size" value="medium" {{ old('size', 'medium') === 'medium' ? 'checked' : '' }} class="text-orange-600 mr-3">
                                <div>
                                    <span class="font-semibold">Medium</span>
                                    <span class="text-gray-400 text-sm block">12" - Standard price</span>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-orange-400 transition {{ old('size') === 'large' ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                                <input type="radio" name="size" value="large" {{ old('size') === 'large' ? 'checked' : '' }} class="text-orange-600 mr-3">
                                <div>
                                    <span class="font-semibold">Large</span>
                                    <span class="text-gray-400 text-sm block">14" - 1.3x price</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Crust --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-bread-slice mr-1 text-yellow-600"></i> Choose Crust
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-orange-400 transition border-gray-200">
                                <input type="radio" name="crust" value="thin" {{ old('crust') === 'thin' ? 'checked' : '' }} class="text-orange-600 mr-3">
                                <div>
                                    <span class="font-semibold">Thin</span>
                                    <span class="text-gray-400 text-sm block">Crispy & light</span>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-orange-400 transition border-gray-200">
                                <input type="radio" name="crust" value="regular" {{ old('crust', 'regular') === 'regular' ? 'checked' : '' }} class="text-orange-600 mr-3">
                                <div>
                                    <span class="font-semibold">Regular</span>
                                    <span class="text-gray-400 text-sm block">Classic style</span>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-orange-400 transition border-gray-200">
                                <input type="radio" name="crust" value="thick" {{ old('crust') === 'thick' ? 'checked' : '' }} class="text-orange-600 mr-3">
                                <div>
                                    <span class="font-semibold">Thick</span>
                                    <span class="text-gray-400 text-sm block">Deep dish +€1.50</span>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-orange-400 transition border-gray-200">
                                <input type="radio" name="crust" value="stuffed" {{ old('crust') === 'stuffed' ? 'checked' : '' }} class="text-orange-600 mr-3">
                                <div>
                                    <span class="font-semibold">Stuffed</span>
                                    <span class="text-gray-400 text-sm block">Cheese-filled +€2.50</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-hashtag mr-1 text-blue-500"></i> Quantity
                        </label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="20"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                </div>

                {{-- Toppings Selection --}}
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">
                        <i class="fas fa-layer-group mr-1 text-green-500"></i> Choose Your Toppings
                        <span class="text-sm text-gray-400 font-normal">(select at least one)</span>
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($toppings as $topping)
                            <label class="flex items-center justify-between p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-400 hover:bg-green-50 transition topping-label">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="toppings[]" value="{{ $topping->id }}"
                                           {{ is_array(old('toppings')) && in_array($topping->id, old('toppings')) ? 'checked' : '' }}
                                           class="rounded text-green-600 focus:ring-green-500 topping-checkbox">
                                    <span class="font-medium text-gray-700">{{ $topping->name }}</span>
                                </div>
                                <span class="text-sm font-semibold text-green-600">+€{{ number_format($topping->price, 2) }}</span>
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
                        class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-4 px-6 rounded-lg transition shadow-lg hover:shadow-xl text-lg">
                    <i class="fas fa-cart-plus mr-2"></i> Add Custom Pizza to Cart
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Highlight selected toppings
    document.querySelectorAll('.topping-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const label = this.closest('.topping-label');
            if (this.checked) {
                label.classList.add('border-green-500', 'bg-green-50');
                label.classList.remove('border-gray-200');
            } else {
                label.classList.remove('border-green-500', 'bg-green-50');
                label.classList.add('border-gray-200');
            }
        });
        // Initialize state
        if (cb.checked) {
            const label = cb.closest('.topping-label');
            label.classList.add('border-green-500', 'bg-green-50');
            label.classList.remove('border-gray-200');
        }
    });
</script>
@endsection
