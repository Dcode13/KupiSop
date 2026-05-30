<div>
    <x-slot name="header">Kategori</x-slot>

    <x-page-header title="Kategori" subtitle="Kelola kategori menu (Coffee, Non-Coffee, Snack, dll.)">
        <button wire:click="create" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-amber-700 hover:bg-amber-800">
            + Tambah Kategori
        </button>
    </x-page-header>

    <div class="p-4 bg-white rounded-xl shadow-sm">
        <div class="mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari kategori..."
                class="w-full px-3 py-2 text-sm border rounded-lg sm:w-64 border-stone-300 focus:border-amber-500 focus:ring-amber-500">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b text-stone-400 border-stone-100">
                        <th class="py-2">Nama</th>
                        <th class="py-2">Slug</th>
                        <th class="py-2 text-center">Jumlah Produk</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="py-3 font-medium text-stone-700">{{ $category->name }}</td>
                            <td class="py-3 text-stone-400">{{ $category->slug }}</td>
                            <td class="py-3 text-center text-stone-600">{{ $category->products_count }}</td>
                            <td class="py-3 text-right">
                                <button wire:click="edit({{ $category->id }})" class="px-2 py-1 text-amber-700 hover:underline">Edit</button>
                                <button wire:click="delete({{ $category->id }})" wire:confirm="Hapus kategori ini?"
                                    class="px-2 py-1 text-red-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-stone-400">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $categories->links() }}</div>
    </div>

    @if ($showModal)
        <x-modal-panel :title="$editingId ? 'Edit Kategori' : 'Tambah Kategori'">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-stone-600">Nama Kategori</label>
                    <input type="text" wire:model="name"
                        class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
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
