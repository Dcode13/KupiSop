<div>
    <x-slot name="header">Produk</x-slot>

    <x-page-header title="Produk" subtitle="Kelola menu / produk coffee shop.">
        <button wire:click="create" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-amber-700 hover:bg-amber-800">
            + Tambah Produk
        </button>
    </x-page-header>

    <div class="p-4 bg-white rounded-xl shadow-sm">
        <div class="flex flex-col gap-3 mb-4 sm:flex-row">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari produk..."
                class="w-full px-3 py-2 text-sm border rounded-lg sm:w-64 border-stone-300 focus:border-amber-500 focus:ring-amber-500">
            <select wire:model.live="category"
                class="w-full px-3 py-2 text-sm border rounded-lg sm:w-48 border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b text-stone-400 border-stone-100">
                        <th class="py-2">Produk</th>
                        <th class="py-2">Kategori</th>
                        <th class="py-2 text-right">Harga</th>
                        <th class="py-2 text-center">Status</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse ($products as $product)
                        <tr>
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                                        class="object-cover w-10 h-10 rounded-lg bg-stone-100">
                                    <span class="font-medium text-stone-700">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-stone-500">{{ $product->category->name ?? '-' }}</td>
                            <td class="py-3 font-semibold text-right text-stone-700">{{ rupiah($product->price) }}</td>
                            <td class="py-3 text-center">
                                <button wire:click="toggleActive({{ $product->id }})"
                                    class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="py-3 text-right">
                                <button wire:click="edit({{ $product->id }})" class="px-2 py-1 text-amber-700 hover:underline">Edit</button>
                                <button wire:click="delete({{ $product->id }})" wire:confirm="Hapus produk ini?"
                                    class="px-2 py-1 text-red-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-stone-400">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $products->links() }}</div>
    </div>

    @if ($showModal)
        <x-modal-panel :title="$editingId ? 'Edit Produk' : 'Tambah Produk'">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-stone-600">Nama Produk</label>
                    <input type="text" wire:model="name"
                        class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-stone-600">Kategori</label>
                        <select wire:model="category_id"
                            class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                            <option value="">— Pilih —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-stone-600">Harga (Rp)</label>
                        <input type="number" step="0.01" wire:model="price"
                            class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        @error('price') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-stone-600">Deskripsi</label>
                    <textarea wire:model="description" rows="2"
                        class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500"></textarea>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-stone-600">Foto Produk</label>
                    <div x-data="{ preview: '', error: '', loading: false }">
                    <input type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                        x-on:change="
                            error = '';
                            loading = false;
                            const file = $event.target.files[0];

                            if (! file) {
                                preview = '';
                                $wire.set('imageData', '');
                                return;
                            }

                            const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                            if (! allowed.includes(file.type)) {
                                error = 'Format gambar harus JPG, PNG, WebP, atau GIF.';
                                $event.target.value = '';
                                return;
                            }

                            if (file.size > 2 * 1024 * 1024) {
                                error = 'Ukuran gambar maksimal 2 MB.';
                                $event.target.value = '';
                                return;
                            }

                            loading = true;
                                const reader = new FileReader();
                                reader.onload = () => {
                                    preview = reader.result;
                                    Promise.resolve($wire.set('imageData', reader.result))
                                        .finally(() => loading = false);
                                };
                            reader.onerror = () => {
                                error = 'Gambar gagal dibaca.';
                                loading = false;
                                $event.target.value = '';
                            };
                            reader.readAsDataURL(file);
                        "
                        class="w-full text-sm text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200">
                    <div x-show="loading" class="mt-1 text-xs text-stone-400">Menyiapkan gambar...</div>
                    <div x-show="error" x-text="error" class="mt-1 text-xs text-red-600"></div>
                    @error('imageData') <span class="text-xs text-red-600">{{ $message }}</span> @enderror

                    <div class="mt-2">
                        <img x-show="preview" x-bind:src="preview" class="object-cover w-20 h-20 rounded-lg">

                        @if ($existingImage)
                            <img x-show="! preview" src="{{ \App\Models\Product::resolveImageUrl($existingImage) }}" class="object-cover w-20 h-20 rounded-lg">
                        @endif
                    </div>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-stone-600">
                    <input type="checkbox" wire:model="is_active" class="rounded text-amber-700 focus:ring-amber-500">
                    Produk aktif (tampil di POS)
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm rounded-lg text-stone-600 bg-stone-100 hover:bg-stone-200">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-amber-700 hover:bg-amber-800 disabled:opacity-50">
                        Simpan
                    </button>
                </div>
            </form>
        </x-modal-panel>
    @endif
</div>
