<div>
    @section('header', 'Manage Pizzas')

    {{-- Header Actions --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search pizzas..."
                       class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>
        </div>
        <button wire:click="create"
                class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg transition shadow">
            <i class="fas fa-plus mr-2"></i> New Pizza
        </button>
    </div>

    {{-- Form Modal --}}
    @if($showForm)
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border-2 border-orange-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-pizza-slice mr-2 text-orange-500"></i>
                {{ $isEditing ? 'Edit Pizza' : 'Create New Pizza' }}
            </h3>

            <form wire:submit="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Name *</label>
                        <input type="text" wire:model="name"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Base Price *</label>
                        <input type="number" step="0.01" wire:model="base_price"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        @error('base_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Size</label>
                        <select wire:model="size"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="small">Small</option>
                            <option value="medium">Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Crust</label>
                        <select wire:model="crust"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="thin">Thin</option>
                            <option value="regular">Regular</option>
                            <option value="thick">Thick</option>
                            <option value="stuffed">Stuffed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Image URL</label>
                        <input type="text" wire:model="image_url"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                               placeholder="https://...">
                    </div>
                </div>

                {{-- Toppings Selection --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Default Toppings</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach($allToppings as $topping)
                            <label class="flex items-center space-x-2 bg-gray-50 rounded-lg px-3 py-2 cursor-pointer hover:bg-orange-50 transition">
                                <input type="checkbox" wire:model="selectedToppings" value="{{ $topping->id }}"
                                       class="rounded text-orange-600 focus:ring-orange-500">
                                <span class="text-sm text-gray-700">{{ $topping->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Active Toggle --}}
                <div class="mb-6">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" wire:model="is_active"
                               class="rounded text-orange-600 focus:ring-orange-500">
                        <span class="text-sm font-semibold text-gray-700">Active (visible to customers)</span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> {{ $isEditing ? 'Update' : 'Create' }} Pizza
                    </button>
                    <button type="button" wire:click="cancel"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Pizzas Table --}}
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3 text-left">Pizza</th>
                    <th class="px-6 py-3 text-left">Price</th>
                    <th class="px-6 py-3 text-left">Size / Crust</th>
                    <th class="px-6 py-3 text-left">Toppings</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($pizzas as $pizza)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-bold text-gray-900">{{ $pizza->name }}</p>
                                <p class="text-xs text-gray-500 line-clamp-1">{{ $pizza->description }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-900">€{{ number_format($pizza->base_price, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">{{ ucfirst($pizza->size) }}</span>
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">{{ ucfirst($pizza->crust) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($pizza->toppings->take(3) as $topping)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $topping->name }}</span>
                                @endforeach
                                @if($pizza->toppings->count() > 3)
                                    <span class="text-xs text-gray-400">+{{ $pizza->toppings->count() - 3 }} more</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="toggleActive({{ $pizza->id }})"
                                    class="px-3 py-1 rounded-full text-xs font-semibold transition cursor-pointer
                                    {{ $pizza->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                {{ $pizza->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="edit({{ $pizza->id }})"
                                    class="text-blue-600 hover:text-blue-800 mr-3 transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="delete({{ $pizza->id }})"
                                    wire:confirm="Are you sure you want to delete this pizza?"
                                    class="text-red-500 hover:text-red-700 transition" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            <i class="fas fa-pizza-slice text-4xl mb-2"></i>
                            <p>No pizzas found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t bg-gray-50">
            {{ $pizzas->links() }}
        </div>
    </div>
</div>
