<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CodeCoffee') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-stone-800">
    <div class="grid min-h-screen lg:grid-cols-2">
        <!-- Panel branding (kiri) -->
        <div class="relative hidden overflow-hidden lg:flex flex-col justify-between p-12 text-white bg-gradient-to-br from-amber-700 via-amber-800 to-stone-900">
            <!-- ornamen -->
            <div class="absolute rounded-full -top-24 -right-24 w-80 h-80 bg-white/10 blur-2xl"></div>
            <div class="absolute rounded-full -bottom-32 -left-20 w-96 h-96 bg-amber-500/20 blur-3xl"></div>

            <div class="relative">
                <a href="{{ route('menu') }}" wire:navigate
                    class="inline-flex items-center gap-1.5 mb-10 text-sm font-medium transition text-amber-100/80 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Menu
                </a>

                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center text-3xl rounded-2xl w-14 h-14 bg-white/15 backdrop-blur">☕</span>
                    <span class="text-2xl font-bold tracking-tight">{{ config('app.name', 'CodeCoffee') }}</span>
                </div>
            </div>

            <div class="relative">
                <h1 class="text-4xl font-bold leading-tight">Kelola CodeCoffee<br>jadi lebih mudah.</h1>
                <p class="max-w-md mt-4 text-amber-100/90">
                    Satu sistem untuk kasir, barista, dan owner — penjualan, stok bahan baku,
                    pesanan, hingga laporan, semua dalam satu tempat.
                </p>

                <ul class="mt-8 space-y-3 text-sm">
                    <li class="flex items-center gap-3"><span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15">🛒</span> Kasir (POS) cepat & cetak struk</li>
                    <li class="flex items-center gap-3"><span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15">📦</span> Manajemen menu & stok otomatis</li>
                    <li class="flex items-center gap-3"><span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white/15">📈</span> Laporan penjualan real-time</li>
                </ul>
            </div>

            <p class="relative text-xs text-amber-200/70">© {{ date('Y') }} {{ config('app.name', 'CodeCoffee') }}. Untuk penggunaan internal.</p>
        </div>

        <!-- Panel form (kanan) -->
        <div class="flex flex-col items-center justify-center p-6 bg-stone-100 sm:p-12">
            <!-- tombol kembali untuk mobile (panel branding tersembunyi di layar kecil) -->
            <a href="{{ route('menu') }}" wire:navigate
                class="inline-flex items-center self-start gap-1.5 mb-6 text-sm font-medium transition text-stone-500 hover:text-amber-700 lg:hidden">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Menu
            </a>

            <!-- logo untuk mobile -->
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <span class="flex items-center justify-center text-2xl rounded-xl w-11 h-11 bg-amber-700 text-white">☕</span>
                <span class="text-xl font-bold text-stone-800">{{ config('app.name', 'CodeCoffee') }}</span>
            </div>

            <div class="w-full max-w-md p-8 bg-white shadow-xl rounded-2xl shadow-stone-300/40">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>
