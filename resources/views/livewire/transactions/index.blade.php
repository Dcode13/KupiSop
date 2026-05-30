<div>
    <x-slot name="header">Transaksi</x-slot>

    <x-page-header title="Riwayat Transaksi" subtitle="Daftar transaksi penjualan.">
    </x-page-header>

    <div class="p-4 bg-white rounded-xl shadow-sm">
        <div class="flex flex-col gap-3 mb-4 md:flex-row md:items-end">
            <div>
                <label class="block mb-1 text-xs font-medium text-stone-500">Cari Invoice</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="INV-..."
                    class="w-full px-3 py-2 text-sm border rounded-lg md:w-48 border-stone-300 focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-stone-500">Dari Tanggal</label>
                <input type="date" wire:model.live="from"
                    class="px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-stone-500">Sampai Tanggal</label>
                <input type="date" wire:model.live="to"
                    class="px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
            </div>
            <button wire:click="resetFilters" class="px-3 py-2 text-sm rounded-lg text-stone-600 bg-stone-100 hover:bg-stone-200">Reset</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b text-stone-400 border-stone-100">
                        <th class="py-2">Invoice</th>
                        <th class="py-2">Tanggal</th>
                        <th class="py-2">Kasir</th>
                        <th class="py-2 text-center">Item</th>
                        <th class="py-2 text-center">Pembayaran</th>
                        <th class="py-2 text-center">Status</th>
                        <th class="py-2 text-right">Total</th>
                        <th class="py-2 text-right">Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse ($transactions as $trx)
                        <tr>
                            <td class="py-3 font-medium text-stone-700">{{ $trx->invoice_number }}</td>
                            <td class="py-3 text-stone-500">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 text-stone-500">{{ $trx->user->name ?? '-' }}</td>
                            <td class="py-3 text-center text-stone-600">{{ $trx->items_count }}</td>
                            <td class="py-3 text-center">
                                <x-payment-badge :status="$trx->payment_status" />
                                <span class="block mt-0.5 text-[10px] uppercase text-stone-400">{{ $trx->payment_method }}</span>
                            </td>
                            <td class="py-3 text-center"><x-status-badge :status="$trx->status" /></td>
                            <td class="py-3 font-semibold text-right text-stone-700">{{ rupiah($trx->total) }}</td>
                            <td class="py-3 text-right">
                                <a href="{{ route('transactions.receipt', $trx) }}" target="_blank"
                                    class="text-amber-700 hover:underline">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-stone-400">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
</div>
