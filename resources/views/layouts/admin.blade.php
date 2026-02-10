<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - PizzaPlanet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @livewireStyles
    <style>
        .admin-gradient { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 admin-gradient text-white flex-shrink-0">
            <div class="p-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                    <i class="fas fa-pizza-slice text-orange-400 text-2xl"></i>
                    <span class="text-xl font-bold">PizzaPlanet</span>
                </a>
                <p class="text-gray-400 text-xs mt-1">Admin Panel</p>
            </div>
            <nav class="mt-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center px-6 py-3 text-gray-300 hover:bg-white/10 hover:text-white transition {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white border-r-4 border-orange-400' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
                </a>
                <a href="{{ route('admin.pizzas') }}"
                   class="flex items-center px-6 py-3 text-gray-300 hover:bg-white/10 hover:text-white transition {{ request()->routeIs('admin.pizzas') ? 'bg-white/10 text-white border-r-4 border-orange-400' : '' }}">
                    <i class="fas fa-pizza-slice w-5 mr-3"></i> Pizzas
                </a>
                <a href="{{ route('admin.toppings') }}"
                   class="flex items-center px-6 py-3 text-gray-300 hover:bg-white/10 hover:text-white transition {{ request()->routeIs('admin.toppings') ? 'bg-white/10 text-white border-r-4 border-orange-400' : '' }}">
                    <i class="fas fa-layer-group w-5 mr-3"></i> Toppings
                </a>
                <a href="{{ route('admin.orders') }}"
                   class="flex items-center px-6 py-3 text-gray-300 hover:bg-white/10 hover:text-white transition {{ request()->routeIs('admin.orders') ? 'bg-white/10 text-white border-r-4 border-orange-400' : '' }}">
                    <i class="fas fa-receipt w-5 mr-3"></i> Orders
                </a>
            </nav>
            <div class="absolute bottom-0 w-64 p-4 border-t border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-shield text-gray-400"></i>
                        <span class="text-sm text-gray-300">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-400 transition" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
                <a href="{{ route('menu') }}" class="block mt-2 text-xs text-gray-500 hover:text-orange-400 transition">
                    <i class="fas fa-external-link-alt mr-1"></i> View Customer Site
                </a>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow-sm px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        @yield('header', 'Dashboard')
                    </h1>
                </div>
            </header>

            <main class="flex-1 p-8">
                {{-- Flash Messages --}}
                @if(session('message'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                        <span><i class="fas fa-check-circle mr-2"></i>{{ session('message') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">&times;</button>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
