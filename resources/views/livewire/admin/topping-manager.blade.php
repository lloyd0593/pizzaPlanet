<div>
    @section('header', 'Manage Toppings')

    {{-- Header Actions --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search toppings..."
                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>
        <button wire:click="create"
                class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg transition shadow">
            <i class="fas fa-plus mr-2"></i> New Topping
        </button>
    </div>

    {{-- Form --}}
    @if($showForm)
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-2 border-orange-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-layer-group mr-2 text-yellow-500"></i>
                {{ $isEditing ? 'Edit Topping' : 'Create New Topping' }}
            </h3>

            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Name *</label>
                        <input type="text" wire:model="name"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Price *</label>
                        <input type="number" step="0.01" wire:model="price"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center space-x-2 cursor-pointer pb-2">
                            <input type="checkbox" wire:model="is_active"
                                   class="rounded text-orange-600 focus:ring-orange-500">
                            <span class="text-sm font-semibold text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> {{ $isEditing ? 'Update' : 'Create' }} Topping
                    </button>
                    <button type="button" wire:click="cancel"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Toppings Table --}}
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3 text-left">ID</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Price</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($toppings as $topping)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-500 text-sm">#{{ $topping->id }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $topping->name }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-900">${{ number_format($topping->price, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="toggleActive({{ $topping->id }})"
                                    class="px-3 py-1 rounded-full text-xs font-semibold transition cursor-pointer
                                    {{ $topping->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                {{ $topping->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $topping->id }})"
                                    class="text-blue-600 hover:text-blue-800 mr-3 transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="delete({{ $topping->id }})"
                                    wire:confirm="Are you sure you want to delete this topping?"
                                    class="text-red-500 hover:text-red-700 transition" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            <i class="fas fa-layer-group text-4xl mb-2"></i>
                            <p>No toppings found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t bg-gray-50">
            {{ $toppings->links() }}
        </div>
    </div>
</div>
