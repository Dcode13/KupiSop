<div>
    <x-slot name="header">Kasir (POS)</x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Daftar produk -->
        <div class="lg:col-span-2">
            <div class="flex flex-col gap-3 mb-4 sm:flex-row">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..."
                    class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                <select wire:model.live="category"
                    class="w-full px-3 py-2 text-sm border rounded-lg sm:w-48 border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                @forelse ($this->products as $product)
                    <button wire:click="addToCart({{ $product->id }})" wire:key="prod-{{ $product->id }}"
                        class="overflow-hidden text-left transition bg-white border rounded-xl shadow-sm border-stone-100 hover:shadow-md hover:border-amber-300">
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                            class="object-cover w-full h-24 bg-stone-100">
                        <div class="p-3">
                            <p class="text-xs text-stone-400">{{ $product->category->name ?? '' }}</p>
                            <p class="text-sm font-semibold truncate text-stone-700">{{ $product->name }}</p>
                            <p class="mt-1 text-sm font-bold text-amber-700">{{ rupiah($product->price) }}</p>
                        </div>
                    </button>
                @empty
                    <p class="col-span-full py-10 text-center text-stone-400">Tidak ada produk aktif.</p>
                @endforelse
            </div>
        </div>

        <!-- Keranjang -->
        <div class="lg:col-span-1">
            <div class="sticky p-5 bg-white rounded-xl shadow-sm top-20">
                <h2 class="mb-3 font-semibold text-stone-700">🛒 Keranjang</h2>

                <input type="text" wire:model="customerName" placeholder="Nama pelanggan (opsional)"
                    class="w-full px-3 py-2 mb-3 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">

                <div class="space-y-2 overflow-y-auto max-h-72">
                    @forelse ($cart as $id => $item)
                        <div wire:key="cart-{{ $id }}" class="flex items-center gap-2 p-2 rounded-lg bg-stone-50">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate text-stone-700">{{ $item['name'] }}</p>
                                <p class="text-xs text-stone-400">{{ rupiah($item['price']) }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click="decrement({{ $id }})"
                                    class="flex items-center justify-center w-6 h-6 rounded bg-stone-200 text-stone-600 hover:bg-stone-300">−</button>
                                <span class="w-6 text-sm text-center">{{ $item['quantity'] }}</span>
                                <button wire:click="increment({{ $id }})"
                                    class="flex items-center justify-center w-6 h-6 rounded bg-stone-200 text-stone-600 hover:bg-stone-300">+</button>
                            </div>
                            <div class="w-20 text-sm font-semibold text-right text-stone-700">{{ rupiah($item['subtotal']) }}</div>
                            <button wire:click="removeFromCart({{ $id }})" class="text-red-400 hover:text-red-600">&times;</button>
                        </div>
                    @empty
                        <p class="py-6 text-sm text-center text-stone-400">Keranjang kosong. Pilih produk di kiri.</p>
                    @endforelse
                </div>

                <div class="pt-3 mt-3 space-y-3 border-t border-stone-100">
                    <div class="flex items-center justify-between text-lg font-bold text-stone-800">
                        <span>Total</span>
                        <span>{{ rupiah($this->total) }}</span>
                    </div>

                    <div>
                        <label class="block mb-1 text-xs font-medium text-stone-500">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="$set('paymentMethod', 'cash')"
                                class="px-3 py-2 text-sm rounded-lg border {{ $paymentMethod === 'cash' ? 'border-amber-500 bg-amber-50 text-amber-700 font-medium' : 'border-stone-200 text-stone-600' }}">
                                💵 Tunai
                            </button>
                            <button type="button" wire:click="$set('paymentMethod', 'qris')"
                                class="px-3 py-2 text-sm rounded-lg border {{ $paymentMethod === 'qris' ? 'border-amber-500 bg-amber-50 text-amber-700 font-medium' : 'border-stone-200 text-stone-600' }}">
                                📱 QRIS
                            </button>
                        </div>
                    </div>

                    @if ($paymentMethod === 'cash')
                        <div>
                            <label class="block mb-1 text-xs font-medium text-stone-500">Jumlah Bayar</label>
                            <input type="number" wire:model.live.debounce.400ms="paid" placeholder="0"
                                class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                            <div class="flex gap-2 mt-2">
                                <button type="button" wire:click="$set('paid', {{ (int) $this->total }})"
                                    class="px-2 py-1 text-xs rounded bg-stone-100 text-stone-600 hover:bg-stone-200">Uang Pas</button>
                                <button type="button" wire:click="$set('paid', 50000)"
                                    class="px-2 py-1 text-xs rounded bg-stone-100 text-stone-600 hover:bg-stone-200">50rb</button>
                                <button type="button" wire:click="$set('paid', 100000)"
                                    class="px-2 py-1 text-xs rounded bg-stone-100 text-stone-600 hover:bg-stone-200">100rb</button>
                            </div>
                            @error('paid') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex items-center justify-between text-sm text-stone-600">
                            <span>Kembalian</span>
                            <span class="font-semibold">{{ rupiah($this->change) }}</span>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <button wire:click="clearCart" @disabled(empty($cart))
                            class="px-4 py-2.5 text-sm rounded-lg text-stone-600 bg-stone-100 hover:bg-stone-200 disabled:opacity-50">
                            Reset
                        </button>
                        <button wire:click="checkout" @disabled(empty($cart))
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-white rounded-lg bg-amber-700 hover:bg-amber-800 disabled:opacity-50">
                            Bayar & Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
