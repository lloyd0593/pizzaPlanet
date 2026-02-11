<div>
    @section('header', 'Dashboard')

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Pizzas --}}
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-semibold uppercase">Pizzas</p>
                    <p class="text-3xl font-extrabold text-gray-900">{{ $totalPizzas }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ $activePizzas }} active</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-pizza-slice text-orange-500 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Toppings --}}
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-semibold uppercase">Toppings</p>
                    <p class="text-3xl font-extrabold text-gray-900">{{ $totalToppings }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ $activeToppings }} active</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-layer-group text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Orders --}}
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-semibold uppercase">Orders</p>
                    <p class="text-3xl font-extrabold text-gray-900">{{ $totalOrders }}</p>
                    <p class="text-xs text-blue-600 mt-1">{{ $pendingOrders }} pending, {{ $confirmedOrders }} confirmed</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-receipt text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-semibold uppercase">Revenue</p>
                    <p class="text-3xl font-extrabold text-gray-900">€{{ number_format($totalRevenue, 2) }}</p>
                    <p class="text-xs text-green-600 mt-1">From successful payments</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-euro-sign text-green-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('admin.pizzas') }}" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition flex items-center gap-4">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-plus text-orange-500"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800">Manage Pizzas</p>
                <p class="text-sm text-gray-500">Create, edit, or remove pizzas</p>
            </div>
        </a>
        <a href="{{ route('admin.toppings') }}" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition flex items-center gap-4">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-plus text-yellow-500"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800">Manage Toppings</p>
                <p class="text-sm text-gray-500">Create, edit, or remove toppings</p>
            </div>
        </a>
        <a href="{{ route('admin.orders') }}" class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-eye text-blue-500"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800">View Orders</p>
                <p class="text-sm text-gray-500">Track and manage customer orders</p>
            </div>
        </a>
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fas fa-clock mr-2 text-gray-400"></i> Recent Orders
            </h2>
        </div>
        @if($recentOrders->isEmpty())
            <div class="p-8 text-center text-gray-400">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p>No orders yet.</p>
            </div>
        @else
            <table class="w-full">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Order #</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left">Total</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Payment</th>
                        <th class="px-6 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-bold text-gray-900">#{{ $order->id }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $order->customer_name }}</td>
                            <td class="px-6 py-3 font-semibold text-gray-900">€{{ number_format($order->total, 2) }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $order->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $order->status === 'delivered' ? 'bg-gray-100 text-gray-700' : '' }}
                                    {{ $order->status === 'ready' ? 'bg-purple-100 text-purple-700' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if($order->payment)
                                    <span class="text-xs {{ $order->payment->status === 'success' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ucfirst($order->payment->status) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">No payment</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $order->created_at->format('M d, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
