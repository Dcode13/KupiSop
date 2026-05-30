<div wire:poll.15s>
    <x-slot name="header">Pesanan</x-slot>

    <x-page-header title="Manajemen Pesanan" subtitle="Pantau & perbarui status pesanan (otomatis menyegarkan).">
    </x-page-header>

    <!-- Tab status -->
    @php
        $tabs = [
            'pending' => ['Pending', $counts['pending']],
            'diproses' => ['Diproses', $counts['diproses']],
            'selesai' => ['Selesai', $counts['selesai']],
            'all' => ['Semua', array_sum($counts)],
        ];
    @endphp
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach ($tabs as $key => [$label, $count])
            <button wire:click="$set('status', '{{ $key }}')"
                class="px-4 py-2 text-sm rounded-lg border {{ $status === $key ? 'border-amber-500 bg-amber-50 text-amber-700 font-medium' : 'border-stone-200 bg-white text-stone-600 hover:bg-stone-50' }}">
                {{ $label }}
                <span class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-stone-200 text-stone-700">{{ $count }}</span>
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($orders as $order)
            <div wire:key="order-{{ $order->id }}" class="flex flex-col p-4 bg-white rounded-xl shadow-sm">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="font-semibold text-stone-700">{{ $order->invoice_number }}</p>
                        <p class="text-xs text-stone-400">{{ $order->created_at->format('d/m H:i') }} · {{ $order->user->name ?? '-' }}</p>
                    </div>
                    <x-status-badge :status="$order->status" />
                </div>

                @if ($order->customer_name)
                    <p class="mb-1 text-sm text-stone-500">👤 {{ $order->customer_name }}</p>
                @endif
                @if ($order->note)
                    <p class="mb-2 text-xs px-2 py-1 rounded bg-amber-50 text-amber-700">📝 {{ $order->note }}</p>
                @endif

                <ul class="flex-1 mb-3 space-y-1 text-sm text-stone-600">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between">
                            <span>{{ $item->quantity }}× {{ $item->product_name }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="flex gap-2 pt-3 border-t border-stone-100">
                    @if ($order->status === \App\Models\Transaction::STATUS_PENDING)
                        <button wire:click="updateStatus({{ $order->id }}, 'diproses')"
                            class="flex-1 px-3 py-2 text-sm font-medium text-white rounded-lg bg-amber-600 hover:bg-amber-700">
                            ▶️ Proses
                        </button>
                    @elseif ($order->status === \App\Models\Transaction::STATUS_PROCESSING)
                        <button wire:click="updateStatus({{ $order->id }}, 'selesai')"
                            class="flex-1 px-3 py-2 text-sm font-medium text-white rounded-lg bg-emerald-600 hover:bg-emerald-700">
                            ✅ Selesaikan
                        </button>
                    @else
                        <span class="flex-1 px-3 py-2 text-sm text-center rounded-lg text-emerald-700 bg-emerald-50">Pesanan selesai</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="col-span-full py-10 text-center text-stone-400">Tidak ada pesanan pada status ini.</p>
        @endforelse
    </div>

    <div class="mt-5">{{ $orders->links() }}</div>
</div>
