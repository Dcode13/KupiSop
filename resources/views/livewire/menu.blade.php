<div x-data="{ cartOpen: false }" @order-placed.window="cartOpen = false">
    <!-- Hero -->
    <section class="relative overflow-hidden text-white bg-gradient-to-br from-amber-700 via-amber-800 to-stone-900">
        <div class="absolute rounded-full -top-20 -right-10 w-72 h-72 bg-white/10 blur-3xl"></div>
        <div class="relative max-w-6xl px-4 py-12 mx-auto sm:py-16">
            <p class="font-medium text-amber-200">Selamat datang di</p>
            <h1 class="mt-1 text-3xl font-bold sm:text-4xl">{{ config('app.name', 'CodeCoffee') }} ☕</h1>
            <p class="max-w-lg mt-3 text-amber-100/90">Pilih menu favoritmu, atur pesanan, lalu pesan langsung dari mejamu. Pembayaran di kasir.</p>

            <div class="relative max-w-md mt-6">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari menu…"
                    class="w-full py-3 pl-12 pr-4 text-sm border-0 rounded-xl text-stone-800 shadow-lg focus:ring-2 focus:ring-amber-400">
            </div>
        </div>
    </section>

    <div class="max-w-6xl px-4 mx-auto">
        <!-- Filter kategori -->
        <div class="flex gap-2 py-5 -mx-1 overflow-x-auto">
            <button wire:click="$set('category', '')"
                class="px-4 py-2 text-sm whitespace-nowrap rounded-full border {{ $category === '' ? 'bg-amber-700 text-white border-amber-700' : 'bg-white text-stone-600 border-stone-200 hover:border-amber-300' }}">
                Semua
            </button>
            @foreach ($this->categories as $cat)
                <button wire:click="$set('category', '{{ $cat->id }}')"
                    class="px-4 py-2 text-sm whitespace-nowrap rounded-full border {{ (string) $category === (string) $cat->id ? 'bg-amber-700 text-white border-amber-700' : 'bg-white text-stone-600 border-stone-200 hover:border-amber-300' }}">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- Grid produk -->
        <div class="grid grid-cols-2 gap-4 pb-32 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($this->products as $product)
                <div wire:key="prod-{{ $product->id }}" class="flex flex-col overflow-hidden bg-white border rounded-2xl border-stone-100 shadow-sm">
                    <div class="relative">
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="object-cover w-full h-32 bg-stone-100 sm:h-40">
                        <span class="absolute px-2 py-1 text-xs font-medium rounded-full top-2 left-2 bg-white/90 text-stone-600">{{ $product->category->name ?? '' }}</span>
                    </div>
                    <div class="flex flex-col flex-1 p-3">
                        <p class="text-sm font-semibold text-stone-800">{{ $product->name }}</p>
                        @if ($product->description)
                            <p class="mt-0.5 text-xs text-stone-400 line-clamp-2">{{ $product->description }}</p>
                        @endif
                        <p class="mt-2 text-base font-bold text-amber-700">{{ rupiah($product->price) }}</p>

                        <div class="mt-3">
                            @if (isset($cart[$product->id]))
                                <div class="flex items-center justify-between p-1 rounded-lg bg-amber-50">
                                    <button wire:click="decrement({{ $product->id }})" class="flex items-center justify-center text-lg rounded-md w-8 h-8 bg-white text-amber-700 shadow-sm">−</button>
                                    <span class="text-sm font-semibold text-amber-800">{{ $cart[$product->id]['quantity'] }}</span>
                                    <button wire:click="increment({{ $product->id }})" class="flex items-center justify-center text-lg rounded-md w-8 h-8 bg-white text-amber-700 shadow-sm">+</button>
                                </div>
                            @else
                                <button wire:click="addToCart({{ $product->id }})" @click="cartOpen = false"
                                    class="w-full py-2 text-sm font-medium text-white rounded-lg bg-amber-700 hover:bg-amber-800">
                                    + Tambah
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="py-16 text-center col-span-full text-stone-400">Menu tidak ditemukan.</p>
            @endforelse
        </div>
    </div>

    <!-- Sticky bar keranjang (muncul bila ada item) -->
    @if ($this->count > 0)
        <div class="fixed inset-x-0 bottom-0 z-20 p-4">
            <div class="max-w-6xl mx-auto">
                <button @click="cartOpen = true"
                    class="flex items-center justify-between w-full gap-4 px-5 py-4 text-white shadow-2xl rounded-2xl bg-amber-700 hover:bg-amber-800">
                    <span class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 text-sm font-bold rounded-full bg-white/20">{{ $this->count }}</span>
                        <span class="font-medium">Lihat Pesanan</span>
                    </span>
                    <span class="font-bold">{{ rupiah($this->total) }}</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Drawer keranjang -->
    <div x-show="cartOpen" x-cloak class="fixed inset-0 z-40" style="display:none">
        <div x-show="cartOpen" x-transition.opacity @click="cartOpen = false" class="absolute inset-0 bg-black/50"></div>

        <div x-show="cartOpen"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="absolute inset-y-0 right-0 flex flex-col w-full max-w-md bg-stone-50 shadow-xl">

            <div class="flex items-center justify-between px-5 h-16 bg-white border-b border-stone-200">
                <h2 class="font-semibold text-stone-800">🛒 Pesanan Anda</h2>
                <button @click="cartOpen = false" class="text-2xl text-stone-400 hover:text-stone-600">&times;</button>
            </div>

            <!-- Item -->
            <div class="flex-1 p-4 space-y-2 overflow-y-auto">
                @forelse ($cart as $id => $item)
                    <div wire:key="cart-{{ $id }}" class="flex items-center gap-3 p-3 bg-white rounded-xl">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate text-stone-700">{{ $item['name'] }}</p>
                            <p class="text-xs text-stone-400">{{ rupiah($item['price']) }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="decrement({{ $id }})" class="flex items-center justify-center w-7 h-7 rounded bg-stone-100 text-stone-600">−</button>
                            <span class="w-6 text-sm text-center">{{ $item['quantity'] }}</span>
                            <button wire:click="increment({{ $id }})" class="flex items-center justify-center w-7 h-7 rounded bg-stone-100 text-stone-600">+</button>
                        </div>
                        <span class="w-20 text-sm font-semibold text-right text-stone-700">{{ rupiah($item['subtotal']) }}</span>
                    </div>
                @empty
                    <div class="py-16 text-center text-stone-400">
                        <p class="text-4xl">🧺</p>
                        <p class="mt-2 text-sm">Keranjang masih kosong.</p>
                    </div>
                @endforelse
            </div>

            <!-- Form pesan -->
            @if (! empty($cart))
                <div class="p-4 space-y-3 bg-white border-t border-stone-200">
                    <div>
                        <input type="text" wire:model="customerName" placeholder="Nama Anda *"
                            class="w-full px-3 py-2.5 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        @error('customerName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <input type="text" wire:model="note" placeholder="Catatan (mis. nomor meja, tanpa gula)"
                        class="w-full px-3 py-2.5 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">

                    <div>
                        <p class="mb-1.5 text-xs font-medium text-stone-500">Metode Pembayaran</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="$set('paymentChannel', 'online')"
                                class="px-3 py-2 text-sm rounded-lg border {{ $paymentChannel === 'online' ? 'border-amber-500 bg-amber-50 text-amber-700 font-medium' : 'border-stone-200 text-stone-600' }}">
                                💳 Bayar Online
                            </button>
                            <button type="button" wire:click="$set('paymentChannel', 'counter')"
                                class="px-3 py-2 text-sm rounded-lg border {{ $paymentChannel === 'counter' ? 'border-amber-500 bg-amber-50 text-amber-700 font-medium' : 'border-stone-200 text-stone-600' }}">
                                🏪 Bayar di Kasir
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-lg font-bold text-stone-800">
                        <span>Total</span>
                        <span>{{ rupiah($this->total) }}</span>
                    </div>

                    <button wire:click="placeOrder" wire:loading.attr="disabled" wire:target="placeOrder"
                        class="w-full py-3 text-sm font-semibold text-white rounded-lg bg-amber-700 hover:bg-amber-800 disabled:opacity-60">
                        <span wire:loading.remove wire:target="placeOrder">{{ $paymentChannel === 'online' ? 'Lanjut ke Pembayaran' : 'Pesan Sekarang' }}</span>
                        <span wire:loading wire:target="placeOrder">Memproses…</span>
                    </button>
                    <p class="text-xs text-center text-stone-400">
                        {{ $paymentChannel === 'online' ? 'Pilih QRIS / e-wallet / VA bank di langkah berikutnya.' : 'Pembayaran dilakukan di kasir.' }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Popup pembayaran / konfirmasi -->
    @if ($placedOrder)
        @php
            $paid = $placedOrder->payment_status === 'paid';
            $failed = in_array($placedOrder->payment_status, ['failed', 'expired']);
            $isCounter = $placedOrder->payment_method === 'cash';
            $details = $placedOrder->payment_details ?? [];
            $hasCharge = ! empty($details);
            $isQr = $hasCharge && ! empty($details['qr_url']);
            $isVa = $hasCharge && ! empty($details['va_number']);
            $pollPayment = ! $paid && ! $isCounter && $hasCharge;
            $methodLabels = ['qris' => 'QRIS', 'gopay' => 'GoPay', 'dana' => 'DANA', 'bca' => 'BCA Virtual Account', 'bni' => 'BNI Virtual Account', 'bri' => 'BRI Virtual Account'];
            $methodLabel = $methodLabels[$placedOrder->payment_method] ?? 'Pembayaran';
        @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60"></div>
            <div wire:key="pay-{{ $placedOrder->id }}-{{ $placedOrder->payment_status }}-{{ $placedOrder->payment_ref }}"
                @if ($pollPayment) wire:poll.5s="confirmPayment" @endif
                class="relative w-full max-w-md overflow-hidden bg-white shadow-2xl rounded-2xl max-h-[92vh] overflow-y-auto">

                {{-- Overlay loading saat charge --}}
                <div wire:loading.flex wire:target="selectPayment" class="absolute inset-0 z-20 flex-col items-center justify-center hidden gap-3 bg-white/85">
                    <svg class="w-8 h-8 animate-spin text-amber-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <p class="text-sm text-stone-500">Menyiapkan pembayaran…</p>
                </div>

                @if ($paid)
                    {{-- ===== LUNAS ===== --}}
                    <div class="p-6 text-center">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto text-3xl rounded-full bg-emerald-100">✅</div>
                        <h2 class="mt-4 text-xl font-bold text-stone-800">Pembayaran Berhasil!</h2>
                        <p class="mt-1 text-sm text-stone-500">Terima kasih, {{ $placedOrder->customer_name }}. Pesanan Anda sedang disiapkan.</p>
                        <div class="p-4 my-4 rounded-xl bg-emerald-50">
                            <p class="text-xs text-stone-400">No. Pesanan</p>
                            <p class="text-lg font-bold tracking-wide text-emerald-700">{{ $placedOrder->invoice_number }}</p>
                            <p class="mt-1 text-sm font-semibold text-stone-700">{{ rupiah($placedOrder->total) }} · Lunas</p>
                        </div>
                        <button wire:click="newOrder" class="w-full py-2.5 text-sm font-medium rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100">Pesan Lagi</button>
                    </div>

                @elseif ($isCounter)
                    {{-- ===== BAYAR DI KASIR ===== --}}
                    <div class="p-6 text-center">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto text-3xl rounded-full bg-amber-100">🧾</div>
                        <h2 class="mt-4 text-xl font-bold text-stone-800">Pesanan Diterima!</h2>
                        <p class="mt-1 text-sm text-stone-500">Tunjukkan nomor ini & bayar di kasir.</p>
                        <div class="p-4 my-4 rounded-xl bg-stone-50">
                            <p class="text-xs text-stone-400">No. Pesanan</p>
                            <p class="text-lg font-bold tracking-wide text-amber-700">{{ $placedOrder->invoice_number }}</p>
                            <p class="mt-1 text-sm font-semibold text-stone-700">{{ rupiah($placedOrder->total) }}</p>
                        </div>
                        <button wire:click="newOrder" class="w-full py-2.5 text-sm font-medium rounded-lg text-stone-600 bg-stone-100 hover:bg-stone-200">Pesan Lagi</button>
                    </div>

                @elseif (! $hasCharge)
                    {{-- ===== PILIH METODE PEMBAYARAN ===== --}}
                    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                        <div>
                            <p class="text-xs text-stone-400">Total Pembayaran</p>
                            <p class="text-xl font-bold text-amber-700">{{ rupiah($placedOrder->total) }}</p>
                        </div>
                        <button wire:click="newOrder" class="text-2xl leading-none text-stone-400 hover:text-stone-600">&times;</button>
                    </div>
                    <div class="p-5">
                        <p class="mb-4 text-xs text-stone-400">No. Pesanan {{ $placedOrder->invoice_number }}</p>

                        @if ($failed)
                            <p class="p-2 mb-3 text-xs text-center text-red-600 rounded-lg bg-red-50">Pembayaran sebelumnya gagal/kedaluwarsa. Silakan pilih lagi.</p>
                        @endif

                        <p class="mb-2 text-xs font-semibold tracking-wide uppercase text-stone-400">E-Wallet & QRIS</p>
                        <div class="space-y-2">
                            @foreach ([['qris', 'QRIS', 'Scan dengan e-wallet / m-banking apa pun', 'bg-violet-600', 'QR'], ['gopay', 'GoPay', 'Bayar dari aplikasi Gojek', 'bg-emerald-600', 'go'], ['dana', 'DANA', 'Scan QRIS dengan aplikasi DANA', 'bg-sky-600', 'da']] as [$m, $label, $desc, $color, $badge])
                                <button wire:click="selectPayment('{{ $m }}')" wire:loading.attr="disabled" wire:target="selectPayment"
                                    class="flex items-center w-full gap-3 p-3 text-left transition border rounded-xl border-stone-200 hover:border-amber-400 hover:bg-amber-50 disabled:opacity-50">
                                    <span class="flex items-center justify-center text-xs font-bold text-white uppercase rounded-lg w-11 h-11 {{ $color }}">{{ $badge }}</span>
                                    <span class="flex-1">
                                        <span class="block text-sm font-medium text-stone-700">{{ $label }}</span>
                                        <span class="block text-xs text-stone-400">{{ $desc }}</span>
                                    </span>
                                    <span class="text-stone-300">›</span>
                                </button>
                            @endforeach
                        </div>

                        <p class="mt-5 mb-2 text-xs font-semibold tracking-wide uppercase text-stone-400">Transfer Bank (Virtual Account)</p>
                        <div class="space-y-2">
                            @foreach ([['bca', 'BCA Virtual Account', 'bg-blue-700'], ['bni', 'BNI Virtual Account', 'bg-orange-600'], ['bri', 'BRI Virtual Account', 'bg-blue-900']] as [$m, $label, $color])
                                <button wire:click="selectPayment('{{ $m }}')" wire:loading.attr="disabled" wire:target="selectPayment"
                                    class="flex items-center w-full gap-3 p-3 text-left transition border rounded-xl border-stone-200 hover:border-amber-400 hover:bg-amber-50 disabled:opacity-50">
                                    <span class="flex items-center justify-center text-[10px] font-bold text-white rounded-lg w-11 h-11 {{ $color }}">{{ strtoupper($m) }}</span>
                                    <span class="flex-1 text-sm font-medium text-stone-700">{{ $label }}</span>
                                    <span class="text-stone-300">›</span>
                                </button>
                            @endforeach
                        </div>

                        <button wire:click="payAtCounter" class="w-full mt-5 text-sm text-stone-500 hover:text-amber-700">atau bayar tunai di kasir →</button>
                    </div>

                @else
                    {{-- ===== INSTRUKSI PEMBAYARAN (QR / VA) ===== --}}
                    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                        <div>
                            <p class="text-xs text-stone-400">{{ $methodLabel }} · {{ rupiah($placedOrder->total) }}</p>
                            <p class="text-sm font-semibold text-stone-700">{{ $placedOrder->invoice_number }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu
                        </span>
                    </div>

                    <div class="p-5 text-center">
                        @if ($isQr)
                            <p class="text-sm text-stone-500">Pindai QR berikut dengan aplikasi pembayaran Anda.</p>
                            <div class="inline-block p-3 my-4 bg-white border rounded-xl border-stone-200">
                                <img src="{{ $details['qr_url'] }}" alt="QR Pembayaran" class="object-contain w-56 h-56 mx-auto">
                            </div>
                            @if (! empty($details['deeplink']))
                                <div>
                                    <a href="{{ $details['deeplink'] }}" target="_blank"
                                        class="inline-block px-4 py-2 text-sm font-medium text-white rounded-lg bg-emerald-600 hover:bg-emerald-700">Buka Aplikasi GoPay</a>
                                </div>
                            @endif
                        @elseif ($isVa)
                            <p class="text-sm text-stone-500">Transfer ke Virtual Account berikut:</p>
                            <div x-data="{ copied: false }" class="p-4 my-4 rounded-xl bg-stone-50">
                                <p class="text-xs text-stone-400">{{ $details['bank'] }} Virtual Account</p>
                                <p class="text-2xl font-bold tracking-wider break-all text-stone-800">{{ $details['va_number'] }}</p>
                                <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $details['va_number'] }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="px-3 py-1 mt-2 text-xs font-medium rounded-lg text-amber-700 bg-amber-100 hover:bg-amber-200">
                                    <span x-text="copied ? '✓ Tersalin' : 'Salin Nomor'"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-between px-1 text-sm">
                                <span class="text-stone-400">Total transfer</span>
                                <span class="font-bold text-stone-800">{{ rupiah($placedOrder->total) }}</span>
                            </div>
                            <p class="mt-2 text-xs text-stone-400">Bayar tepat sejumlah di atas via {{ $details['bank'] }} mobile/internet banking atau ATM.</p>
                        @endif

                        @if (! empty($details['expiry_time']))
                            <p class="mt-3 text-xs text-stone-400">⏱️ Selesaikan sebelum {{ \Illuminate\Support\Carbon::parse($details['expiry_time'])->format('d/m/Y H:i') }}</p>
                        @endif

                        <div class="flex gap-2 mt-5">
                            <button wire:click="changeMethod" class="flex-1 py-2.5 text-sm rounded-lg text-stone-600 bg-stone-100 hover:bg-stone-200">Ganti Metode</button>
                            <button wire:click="confirmPayment" wire:loading.attr="disabled" wire:target="confirmPayment"
                                class="flex-1 py-2.5 text-sm font-semibold text-white rounded-lg bg-amber-700 hover:bg-amber-800 disabled:opacity-60">
                                <span wire:loading.remove wire:target="confirmPayment">Cek Status</span>
                                <span wire:loading wire:target="confirmPayment">Mengecek…</span>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-stone-400">Status diperbarui otomatis tiap 5 detik setelah Anda membayar.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
