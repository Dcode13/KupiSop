<div>
    <x-slot name="header">Laporan</x-slot>

    <x-page-header title="Laporan Penjualan" subtitle="Rekap penjualan berdasarkan periode.">
        <a href="{{ route('reports.export.csv', ['from' => $from, 'to' => $to]) }}"
            class="px-3 py-2 text-sm rounded-lg text-stone-600 bg-stone-100 hover:bg-stone-200">⬇️ CSV</a>
        <a href="{{ route('reports.export.pdf', ['from' => $from, 'to' => $to]) }}" target="_blank"
            class="px-3 py-2 text-sm font-medium text-white rounded-lg bg-amber-700 hover:bg-amber-800">🧾 PDF</a>
    </x-page-header>

    <!-- Filter -->
    <div class="flex flex-col gap-3 p-4 mb-6 bg-white rounded-xl shadow-sm md:flex-row md:items-end">
        <div>
            <label class="block mb-1 text-xs font-medium text-stone-500">Dari</label>
            <input type="date" wire:model.live="from"
                class="px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
        </div>
        <div>
            <label class="block mb-1 text-xs font-medium text-stone-500">Sampai</label>
            <input type="date" wire:model.live="to"
                class="px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
        </div>
        <div class="flex gap-2">
            <button wire:click="setPeriod('today')" class="px-3 py-2 text-sm rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200">Hari Ini</button>
            <button wire:click="setPeriod('week')" class="px-3 py-2 text-sm rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200">Minggu Ini</button>
            <button wire:click="setPeriod('month')" class="px-3 py-2 text-sm rounded-lg bg-stone-100 text-stone-600 hover:bg-stone-200">Bulan Ini</button>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card title="Total Pendapatan" :value="rupiah($summary['revenue'])" icon="💰" color="emerald" />
        <x-stat-card title="Jumlah Transaksi" :value="$summary['count']" icon="🧾" color="amber" />
        <x-stat-card title="Item Terjual" :value="(int) $summary['items']" icon="📦" color="sky" />
        <x-stat-card title="Rata-rata / Transaksi" :value="rupiah($summary['avg'])" icon="📊" color="stone" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Produk terjual -->
        <div class="p-5 bg-white rounded-xl shadow-sm">
            <h2 class="mb-4 font-semibold text-stone-700">Produk Terjual</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b text-stone-400 border-stone-100">
                            <th class="py-2">Produk</th>
                            <th class="py-2 text-center">Qty</th>
                            <th class="py-2 text-right">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-50">
                        @forelse ($products as $p)
                            <tr>
                                <td class="py-2 font-medium text-stone-700">{{ $p->product_name }}</td>
                                <td class="py-2 text-center text-stone-600">{{ $p->qty }}</td>
                                <td class="py-2 font-semibold text-right text-stone-700">{{ rupiah($p->revenue) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-6 text-center text-stone-400">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Penjualan harian -->
        <div class="p-5 bg-white rounded-xl shadow-sm">
            <h2 class="mb-4 font-semibold text-stone-700">Penjualan Harian</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b text-stone-400 border-stone-100">
                            <th class="py-2">Tanggal</th>
                            <th class="py-2 text-center">Transaksi</th>
                            <th class="py-2 text-right">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-50">
                        @forelse ($daily as $d)
                            <tr>
                                <td class="py-2 text-stone-700">{{ \Illuminate\Support\Carbon::parse($d->date)->format('d/m/Y') }}</td>
                                <td class="py-2 text-center text-stone-600">{{ $d->count }}</td>
                                <td class="py-2 font-semibold text-right text-stone-700">{{ rupiah($d->revenue) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-6 text-center text-stone-400">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
