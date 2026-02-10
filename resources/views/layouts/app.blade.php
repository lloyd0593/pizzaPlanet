<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PizzaPlanet' }} - PizzaPlanet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pizza: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .pizza-gradient { background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%); }
        .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    {{-- Navigation --}}
    <nav class="pizza-gradient text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('menu') }}" class="flex items-center space-x-2">
                    <i class="fas fa-pizza-slice text-2xl"></i>
                    <span class="text-xl font-bold tracking-tight">PizzaPlanet</span>
                </a>
                <div class="flex items-center space-x-6">
                    <a href="{{ route('menu') }}" class="hover:text-pizza-200 transition font-medium">
                        <i class="fas fa-utensils mr-1"></i> Menu
                    </a>
                    <a href="{{ route('pizza.customize') }}" class="hover:text-pizza-200 transition font-medium">
                        <i class="fas fa-wand-magic-sparkles mr-1"></i> Build Your Own
                    </a>
                    <a href="{{ route('cart.index') }}" class="relative hover:text-pizza-200 transition font-medium">
                        <i class="fas fa-shopping-cart mr-1"></i> Cart
                        @php
                            $cartCount = app(\App\Services\CartService::class)->getItemCount();
                        @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-2 -right-4 bg-yellow-400 text-gray-900 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
                <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">&times;</button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
                <span><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">&times;</button>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <i class="fas fa-pizza-slice text-pizza-500 text-xl"></i>
                <span class="text-white font-bold text-lg">PizzaPlanet</span>
            </div>
            <p class="text-sm">&copy; {{ date('Y') }} PizzaPlanet. All rights reserved.</p>
            <p class="text-xs mt-1">Fresh pizza, delivered to your door.</p>
        </div>
    </footer>
</body>
</html>
