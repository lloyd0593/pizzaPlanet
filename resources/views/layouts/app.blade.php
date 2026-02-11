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
                    <a href="{{ route('cart.index') }}" id="cart-nav-link" class="relative hover:text-pizza-200 transition font-medium">
                        <i class="fas fa-shopping-cart mr-1"></i> Cart
                        @php
                            $cartCount = app(\App\Services\CartService::class)->getItemCount();
                        @endphp
                        @if($cartCount > 0)
                            <span class="cart-badge absolute -top-2 -right-4 bg-yellow-400 text-gray-900 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
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

    {{-- Toast Notification --}}
    <div id="toast-container" class="fixed top-20 right-4 z-[70] space-y-2"></div>

    {{-- Cart Sidebar Overlay --}}
    <div id="cart-overlay" class="fixed inset-0 bg-black/40 z-[60] hidden transition-opacity duration-300 opacity-0" onclick="closeCartSidebar()"></div>

    {{-- Cart Sidebar --}}
    <div id="cart-sidebar" class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-[60] transform translate-x-full transition-transform duration-300 flex flex-col">
        {{-- Header --}}
        <div class="pizza-gradient text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <h2 class="text-lg font-bold"><i class="fas fa-shopping-cart mr-2"></i>Your Cart</h2>
            <button onclick="closeCartSidebar()" class="text-white/80 hover:text-white transition text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Items --}}
        <div id="cart-sidebar-items" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div class="text-center text-gray-400 py-12">
                <i class="fas fa-spinner fa-spin text-2xl"></i>
            </div>
        </div>

        {{-- Footer --}}
        <div id="cart-sidebar-footer" class="border-t bg-gray-50 px-6 py-4 flex-shrink-0 hidden">
            <div class="space-y-1 text-sm mb-3">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span id="sidebar-subtotal" class="font-semibold"></span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Tax (8%)</span>
                    <span id="sidebar-tax" class="font-semibold"></span>
                </div>
                <hr class="my-1">
                <div class="flex justify-between text-lg font-extrabold text-gray-900">
                    <span>Total</span>
                    <span id="sidebar-total" class="text-orange-600"></span>
                </div>
            </div>
            <a href="{{ route('cart.index') }}" class="block w-full text-center bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-lg transition">
                <i class="fas fa-shopping-cart mr-1"></i> View Full Cart
            </a>
            <a href="{{ route('checkout') }}" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition mt-2">
                <i class="fas fa-credit-card mr-1"></i> Checkout
            </a>
        </div>

        {{-- Empty State --}}
        <div id="cart-sidebar-empty" class="hidden flex-1 flex items-center justify-center">
            <div class="text-center text-gray-400">
                <i class="fas fa-pizza-slice text-5xl mb-3"></i>
                <p class="font-semibold text-lg">Your cart is empty</p>
                <p class="text-sm">Add some delicious pizza!</p>
            </div>
        </div>
    </div>

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
    <script>
        const CART_SIDEBAR_URL = '{{ route("cart.sidebar") }}';

        function openCartSidebar() {
            const overlay = document.getElementById('cart-overlay');
            const sidebar = document.getElementById('cart-sidebar');
            overlay.classList.remove('hidden');
            requestAnimationFrame(() => {
                overlay.classList.add('opacity-100');
                sidebar.classList.remove('translate-x-full');
            });
            refreshCartSidebar();
        }

        function closeCartSidebar() {
            const overlay = document.getElementById('cart-overlay');
            const sidebar = document.getElementById('cart-sidebar');
            overlay.classList.remove('opacity-100');
            sidebar.classList.add('translate-x-full');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }

        function refreshCartSidebar() {
            fetch(CART_SIDEBAR_URL, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => renderCartSidebar(data));
        }

        function renderCartSidebar(data) {
            const itemsEl = document.getElementById('cart-sidebar-items');
            const footerEl = document.getElementById('cart-sidebar-footer');
            const emptyEl = document.getElementById('cart-sidebar-empty');

            if (!data.items || data.items.length === 0) {
                itemsEl.classList.add('hidden');
                footerEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
                emptyEl.classList.add('flex');
                return;
            }

            emptyEl.classList.add('hidden');
            emptyEl.classList.remove('flex');
            itemsEl.classList.remove('hidden');
            footerEl.classList.remove('hidden');

            itemsEl.innerHTML = data.items.map(item => `
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-800 text-sm truncate">
                                ${item.is_custom ? '<i class="fas fa-wand-magic-sparkles text-purple-500 mr-1"></i>' : '<i class="fas fa-pizza-slice text-orange-500 mr-1"></i>'}
                                ${item.name}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">${item.size} &bull; ${item.crust} crust</p>
                            ${item.toppings.length ? '<p class="text-xs text-gray-400 mt-0.5 truncate">' + item.toppings.join(', ') + '</p>' : ''}
                        </div>
                        <div class="text-right flex-shrink-0 ml-3">
                            <p class="font-extrabold text-orange-600 text-sm">&euro;${item.total_price.toFixed(2)}</p>
                            <p class="text-xs text-gray-400">Qty: ${item.quantity}</p>
                        </div>
                    </div>
                </div>
            `).join('');

            document.getElementById('sidebar-subtotal').textContent = '\u20AC' + data.subtotal.toFixed(2);
            document.getElementById('sidebar-tax').textContent = '\u20AC' + data.tax.toFixed(2);
            document.getElementById('sidebar-total').textContent = '\u20AC' + data.total.toFixed(2);
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const isSuccess = type === 'success';
            toast.className = `flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-sm font-semibold transition-all duration-300 transform translate-x-full ${isSuccess ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'}`;
            toast.innerHTML = `<i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            container.appendChild(toast);
            requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function updateCartBadge(count) {
            const cartLink = document.querySelector('a[href*="cart"]');
            if (!cartLink) return;
            let badge = cartLink.querySelector('.cart-badge');
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge absolute -top-2 -right-4 bg-yellow-400 text-gray-900 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center';
                    cartLink.appendChild(badge);
                }
                badge.textContent = count;
            } else if (badge) {
                badge.remove();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Cart nav link opens sidebar instead of navigating
            const cartNavLink = document.getElementById('cart-nav-link');
            if (cartNavLink) {
                cartNavLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    openCartSidebar();
                });
            }

            // Load sidebar on page load if cart has items
            @if($cartCount > 0)
                refreshCartSidebar();
            @endif

            document.querySelectorAll('form[data-ajax-cart]').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = form.querySelector('button[type="submit"]');
                    const originalHTML = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Adding...';

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    })
                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                    .then(({ ok, data }) => {
                        if (ok && data.success) {
                            showToast(data.message);
                            updateCartBadge(data.cartCount);
                            openCartSidebar();
                        } else if (data.errors) {
                            const msg = Object.values(data.errors).flat().join(' ');
                            showToast(msg, 'error');
                        } else {
                            showToast(data.message || 'Something went wrong.', 'error');
                        }
                    })
                    .catch(() => showToast('Failed to add to cart.', 'error'))
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    });
                });
            });
        });
    </script>
</body>
</html>
