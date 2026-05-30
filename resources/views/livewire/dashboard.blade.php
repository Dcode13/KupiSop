<div>
    <x-slot name="header">Dashboard</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-800">Selamat datang, {{ auth()->user()->name }} 👋</h1>
        <p class="text-stone-500">Ringkasan aktivitas coffee shop hari ini.</p>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card title="Penjualan Hari Ini" :value="rupiah($stats['today_sales'])" icon="💰" color="emerald" />
        <x-stat-card title="Transaksi Hari Ini" :value="$stats['today_count']" icon="🧾" color="amber" />
        <x-stat-card title="Penjualan Bulan Ini" :value="rupiah($stats['month_sales'])" icon="📅" color="sky" />
        <x-stat-card title="Pesanan Berjalan" :value="$stats['pending_orders']" icon="⏳" color="rose" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Produk terlaris -->
        <div class="p-5 bg-white rounded-xl shadow-sm lg:col-span-2">
            <h2 class="mb-4 font-semibold text-stone-700">🔥 Produk Terlaris</h2>
            @if ($topProducts->isEmpty())
                <p class="text-sm text-stone-400">Belum ada penjualan.</p>
            @else
                <div class="space-y-3">
                    @foreach ($topProducts as $i => $product)
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-7 h-7 text-sm font-semibold rounded-full bg-amber-100 text-amber-700">{{ $i + 1 }}</span>
                            <div class="flex-1">
                                <p class="font-medium text-stone-700">{{ $product->product_name }}</p>
                                <div class="w-full h-1.5 mt-1 rounded-full bg-stone-100">
                                    <div class="h-1.5 rounded-full bg-amber-500"
                                        style="width: {{ $topProducts->max('qty') > 0 ? ($product->qty / $topProducts->max('qty') * 100) : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-stone-700">{{ $product->qty }} terjual</p>
                                <p class="text-xs text-stone-400">{{ rupiah($product->revenue) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Stok menipis -->
        <div class="p-5 bg-white rounded-xl shadow-sm">
            <h2 class="mb-4 font-semibold text-stone-700">⚠️ Stok Menipis</h2>
            @if ($lowStock->isEmpty())
                <p class="text-sm text-stone-400">Semua stok aman 👍</p>
            @else
                <div class="space-y-2">
                    @foreach ($lowStock as $ingredient)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-red-50">
                            <span class="text-sm font-medium text-stone-700">{{ $ingredient->name }}</span>
                            <span class="text-sm font-semibold text-red-600">
                                {{ rtrim(rtrim(number_format($ingredient->stock, 2), '0'), '.') }} {{ $ingredient->unit }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Transaksi terbaru -->
    <div class="p-5 mt-6 bg-white rounded-xl shadow-sm">
        <h2 class="mb-4 font-semibold text-stone-700">🕒 Transaksi Terbaru</h2>
        @if ($recent->isEmpty())
            <p class="text-sm text-stone-400">Belum ada transaksi.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-stone-400 border-b border-stone-100">
                            <th class="pb-2">Invoice</th>
                            <th class="pb-2">Kasir</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2 text-right">Total</th>
                            <th class="pb-2 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-50">
                        @foreach ($recent as $trx)
                            <tr>
                                <td class="py-2 font-medium text-stone-700">{{ $trx->invoice_number }}</td>
                                <td class="py-2 text-stone-500">{{ $trx->user->name ?? '-' }}</td>
                                <td class="py-2"><x-status-badge :status="$trx->status" /></td>
                                <td class="py-2 font-semibold text-right text-stone-700">{{ rupiah($trx->total) }}</td>
                                <td class="py-2 text-right text-stone-400">{{ $trx->created_at->format('d/m H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
