<div>
    @section('header', 'Manage Orders')

    {{-- Filters --}}
    <div class="flex items-center gap-4 mb-6">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, email, or order #..."
                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>
        <select wire:model.live="statusFilter"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="preparing">Preparing</option>
            <option value="ready">Ready</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="flex gap-6">
        {{-- Orders Table --}}
        <div class="flex-1">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Order #</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-left">Total</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Payment</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50 cursor-pointer {{ $selectedOrder && $selectedOrder->id === $order->id ? 'bg-orange-50' : '' }}"
                                wire:click="viewOrder({{ $order->id }})">
                                <td class="px-4 py-3 font-bold text-gray-900">#{{ $order->id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800 text-sm">{{ $order->customer_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->customer_email }}</p>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-900">${{ number_format($order->total, 2) }}</td>
                                <td class="px-4 py-3">
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
                                <td class="px-4 py-3">
                                    @if($order->payment)
                                        <span class="text-xs font-semibold {{ $order->payment->status === 'success' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ucfirst($order->payment->status) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Awaiting</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $order->created_at->format('M d, H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button wire:click.stop="viewOrder({{ $order->id }})"
                                            class="text-blue-600 hover:text-blue-800 transition" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                    <i class="fas fa-receipt text-4xl mb-2"></i>
                                    <p>No orders found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>

        {{-- Order Detail Sidebar --}}
        @if($showDetail && $selectedOrder)
            <div class="w-96 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden sticky top-4">
                    {{-- Header --}}
                    <div class="bg-gray-800 text-white px-6 py-4 flex items-center justify-between">
                        <h3 class="font-bold text-lg">Order #{{ $selectedOrder->id }}</h3>
                        <button wire:click="closeDetail" class="text-gray-400 hover:text-white transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- Customer Info --}}
                    <div class="px-6 py-4 border-b">
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Customer</h4>
                        <p class="font-semibold text-gray-800">{{ $selectedOrder->customer_name }}</p>
                        <p class="text-sm text-gray-600">{{ $selectedOrder->customer_email }}</p>
                        @if($selectedOrder->customer_phone)
                            <p class="text-sm text-gray-600">{{ $selectedOrder->customer_phone }}</p>
                        @endif
                        @if($selectedOrder->delivery_address)
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-map-marker-alt mr-1"></i> {{ $selectedOrder->delivery_address }}
                            </p>
                        @endif
                    </div>

                    {{-- Status Management --}}
                    <div class="px-6 py-4 border-b">
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Update Status</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'] as $status)
                                <button wire:click="updateStatus({{ $selectedOrder->id }}, '{{ $status }}')"
                                        class="px-3 py-1 rounded-full text-xs font-semibold transition
                                        {{ $selectedOrder->status === $status ? 'ring-2 ring-offset-1 ring-gray-400' : '' }}
                                        {{ $status === 'confirmed' ? 'bg-green-100 text-green-700 hover:bg-green-200' : '' }}
                                        {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : '' }}
                                        {{ $status === 'preparing' ? 'bg-blue-100 text-blue-700 hover:bg-blue-200' : '' }}
                                        {{ $status === 'cancelled' ? 'bg-red-100 text-red-700 hover:bg-red-200' : '' }}
                                        {{ $status === 'delivered' ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : '' }}
                                        {{ $status === 'ready' ? 'bg-purple-100 text-purple-700 hover:bg-purple-200' : '' }}">
                                    {{ ucfirst($status) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div class="px-6 py-4 border-b">
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Items</h4>
                        <div class="space-y-3">
                            @foreach($selectedOrder->items as $item)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">
                                                {{ $item->quantity }}x {{ $item->pizza_name }}
                                                @if($item->is_custom)
                                                    <span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">Custom</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">{{ ucfirst($item->size) }}, {{ ucfirst($item->crust) }}</p>
                                        </div>
                                        <span class="font-semibold text-sm text-gray-700">${{ number_format($item->total_price, 2) }}</span>
                                    </div>
                                    @if($item->toppings->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($item->toppings as $topping)
                                                <span class="text-xs bg-white text-gray-500 px-1.5 py-0.5 rounded border">
                                                    {{ $topping->pivot->topping_name }} (${{ number_format($topping->pivot->topping_price, 2) }})
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Payment Info --}}
                    @if($selectedOrder->payment)
                        <div class="px-6 py-4 border-b">
                            <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Payment</h4>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($selectedOrder->payment->payment_method === 'credit_card')
                                        <i class="fas fa-credit-card text-gray-500"></i>
                                        <span class="text-sm">**** {{ $selectedOrder->payment->card_last_four }}</span>
                                    @else
                                        <i class="fab fa-paypal text-blue-500"></i>
                                        <span class="text-sm">{{ $selectedOrder->payment->paypal_email }}</span>
                                    @endif
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $selectedOrder->payment->status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($selectedOrder->payment->status) }}
                                </span>
                            </div>
                            @if($selectedOrder->payment->transaction_id)
                                <p class="text-xs text-gray-400 mt-1">TXN: {{ $selectedOrder->payment->transaction_id }}</p>
                            @endif
                            @if($selectedOrder->payment->failure_reason)
                                <p class="text-xs text-red-500 mt-1">{{ $selectedOrder->payment->failure_reason }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Totals --}}
                    <div class="px-6 py-4 bg-gray-50">
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>${{ number_format($selectedOrder->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span>${{ number_format($selectedOrder->tax, 2) }}</span>
                            </div>
                            <hr class="my-1">
                            <div class="flex justify-between font-extrabold text-gray-900">
                                <span>Total</span>
                                <span class="text-orange-600">${{ number_format($selectedOrder->total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @if($selectedOrder->notes)
                        <div class="px-6 py-4 border-t">
                            <h4 class="text-xs font-bold text-gray-500 uppercase mb-1">Notes</h4>
                            <p class="text-sm text-gray-600">{{ $selectedOrder->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
