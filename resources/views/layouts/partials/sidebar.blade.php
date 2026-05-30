@php
    // Helper kecil untuk menandai menu aktif berdasarkan nama route.
    $navClass = fn (bool $active) => $active
        ? 'flex items-center gap-3 px-4 py-2.5 rounded-lg bg-amber-700 text-white font-medium'
        : 'flex items-center gap-3 px-4 py-2.5 rounded-lg text-stone-300 hover:bg-stone-800 hover:text-white transition';
@endphp

<aside
    class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto bg-stone-900 transform transition-transform duration-200 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <div class="flex items-center gap-2 px-6 h-16 border-b border-stone-800">
        <span class="text-2xl">☕</span>
        <span class="text-lg font-semibold text-white">{{ config('app.name', 'Coffee Shop') }}</span>
    </div>

    <nav class="px-3 py-4 space-y-1">
        <a href="{{ route('dashboard') }}" wire:navigate class="{{ $navClass(request()->routeIs('dashboard')) }}">
            <span>📊</span> Dashboard
        </a>

        @hasanyrole('admin|kasir')
            <a href="{{ route('pos') }}" wire:navigate class="{{ $navClass(request()->routeIs('pos')) }}">
                <span>🛒</span> Kasir (POS)
            </a>
            <a href="{{ route('transactions.index') }}" wire:navigate class="{{ $navClass(request()->routeIs('transactions.*')) }}">
                <span>🧾</span> Transaksi
            </a>
        @endhasanyrole

        @hasanyrole('admin|barista')
            <a href="{{ route('orders.index') }}" wire:navigate class="{{ $navClass(request()->routeIs('orders.*')) }}">
                <span>📋</span> Pesanan
            </a>
        @endhasanyrole

        @role('admin')
            <p class="px-4 pt-4 pb-1 text-xs font-semibold tracking-wider uppercase text-stone-500">Manajemen</p>
            <a href="{{ route('categories.index') }}" wire:navigate class="{{ $navClass(request()->routeIs('categories.*')) }}">
                <span>🏷️</span> Kategori
            </a>
            <a href="{{ route('products.index') }}" wire:navigate class="{{ $navClass(request()->routeIs('products.*')) }}">
                <span>📦</span> Produk
            </a>
            <a href="{{ route('ingredients.index') }}" wire:navigate class="{{ $navClass(request()->routeIs('ingredients.*')) }}">
                <span>🥛</span> Bahan Baku
            </a>

            <p class="px-4 pt-4 pb-1 text-xs font-semibold tracking-wider uppercase text-stone-500">Lainnya</p>
            <a href="{{ route('reports.index') }}" wire:navigate class="{{ $navClass(request()->routeIs('reports.*')) }}">
                <span>📈</span> Laporan
            </a>
            <a href="{{ route('users.index') }}" wire:navigate class="{{ $navClass(request()->routeIs('users.*')) }}">
                <span>👥</span> Pengguna
            </a>
        @endrole
    </nav>
</aside>
