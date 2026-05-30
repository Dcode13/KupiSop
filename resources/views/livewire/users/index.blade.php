<div>
    <x-slot name="header">Pengguna</x-slot>

    <x-page-header title="Manajemen Pengguna" subtitle="Kelola akun & peran (admin, kasir, barista).">
        <button wire:click="create" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-amber-700 hover:bg-amber-800">
            + Tambah Pengguna
        </button>
    </x-page-header>

    <div class="p-4 bg-white rounded-xl shadow-sm">
        <div class="mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama / email..."
                class="w-full px-3 py-2 text-sm border rounded-lg sm:w-72 border-stone-300 focus:border-amber-500 focus:ring-amber-500">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b text-stone-400 border-stone-100">
                        <th class="py-2">Nama</th>
                        <th class="py-2">Email</th>
                        <th class="py-2">Peran</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse ($users as $user)
                        <tr>
                            <td class="py-3 font-medium text-stone-700">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="ml-1 text-xs text-stone-400">(Anda)</span>
                                @endif
                            </td>
                            <td class="py-3 text-stone-500">{{ $user->email }}</td>
                            <td class="py-3">
                                @forelse ($user->roles as $r)
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-medium capitalize rounded-full bg-amber-100 text-amber-700">{{ $r->name }}</span>
                                @empty
                                    <span class="text-xs text-stone-400">—</span>
                                @endforelse
                            </td>
                            <td class="py-3 text-right">
                                <button wire:click="edit({{ $user->id }})" class="px-2 py-1 text-amber-700 hover:underline">Edit</button>
                                @if ($user->id !== auth()->id())
                                    <button wire:click="delete({{ $user->id }})" wire:confirm="Hapus pengguna ini?"
                                        class="px-2 py-1 text-red-600 hover:underline">Hapus</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-stone-400">Belum ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>

    @if ($showModal)
        <x-modal-panel :title="$editingId ? 'Edit Pengguna' : 'Tambah Pengguna'">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block mb-1 text-sm font-medium text-stone-600">Nama</label>
                    <input type="text" wire:model="name"
                        class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-stone-600">Email</label>
                    <input type="email" wire:model="email"
                        class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                    @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-stone-600">
                            Password {{ $editingId ? '(kosongkan jika tetap)' : '' }}
                        </label>
                        <input type="password" wire:model="password"
                            class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                        @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-stone-600">Peran</label>
                        <select wire:model="role"
                            class="w-full px-3 py-2 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                            @foreach ($roles as $r)
                                <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                        @error('role') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
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
