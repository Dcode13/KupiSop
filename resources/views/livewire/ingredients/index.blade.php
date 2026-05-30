<div>
    <x-slot name="header">Bahan Baku</x-slot>

    <x-page-header title="Bahan Baku / Stok" subtitle="Kelola stok bahan baku & ambang minimum.">
        <button wire:click="create" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-amber-700 hover:bg-amber-800">
            + Tambah Bahan
        </button>
    </x-page-header>

    <div class="p-4 bg-white rounded-xl shadow-sm">
        <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari bahan baku..."
                class="w-full px-3 py-2 text-sm border rounded-lg sm:w-64 border-stone-300 focus:border-amber-500 focus:ring-amber-500">
            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" wire:model.live="onlyLow" class="rounded text-amber-700 focus:ring-amber-500">
                Hanya stok menipis
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b text-stone-400 border-stone-100">
                        <th class="py-2">Nama Bahan</th>
                        <th class="py-2">Satuan</th>
                        <th class="py-2 text-right">Stok</th>
                        <th class="py-2 text-right">Min. Stok</th>
                        <th class="py-2 text-center">Status</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse ($ingredients as $ingredient)
                        <tr>
                            <td class="py-3 font-medium text-stone-700">{{ $ingredient->name }}</td>
                            <td class="py-3 text-stone-500">{{ $ingredient->unit }}</td>
                            <td class="py-3 font-semibold text-right text-stone-700">
                                {{ rtrim(rtrim(number_format($ingredient->stock, 2), '0'), '.') }}
                            </td>
                            <td class="py-3 text-right text-stone-500">
                                {{ rtrim(rtrim(number_format($ingredient->min_stock, 2), '0'), '.') }}
                            </td>
                            <td class="py-3 text-center">
                                @if ($ingredient->isLow())
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-700">Menipis</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Aman</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <button wire:click="edit({{ $ingredient->id }})" class="px-2 py-1 text-amber-700 hover:underline">Edit</button>
                                <button wire:click="delete({{ $ingredient->id }})" wire:confirm="Hapus bahan baku ini?"
                                    class="px-2 py-1 text-red-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-stone-400">Belum ada bahan baku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $ingredients->links() }}</div>
    </div>

    @if ($showModal)
        <x-modal-panel :title="$editingId ? 'Edit Bahan Baku' : 'Tambah Bahan Baku'">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-stone-600">Nama Bahan</label>
                    <input type="text" wire:model="name"
                        class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-stone-600">Satuan</label>
                        <input type="text" wire:model="unit" placeholder="gram / ml / pcs"
                            class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        @error('unit') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-stone-600">Stok</label>
                        <input type="number" step="0.01" wire:model="stock"
                            class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        @error('stock') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-stone-600">Min. Stok</label>
                        <input type="number" step="0.01" wire:model="min_stock"
                            class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        @error('min_stock') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm rounded-lg text-stone-600 bg-stone-100 hover:bg-stone-200">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-amber-700 hover:bg-amber-800">
                        Simpan
                    </button>
                </div>
            </form>
        </x-modal-panel>
    @endif
</div>
