<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Menu' }} — {{ config('app.name', 'Coffee Shop') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-stone-100 text-stone-800">
    <!-- Navbar publik -->
    <header class="sticky top-0 z-20 border-b bg-white/80 backdrop-blur border-stone-200">
        <div class="flex items-center justify-between max-w-6xl px-4 mx-auto h-16">
            <a href="{{ route('menu') }}" wire:navigate class="flex items-center gap-2">
                <span class="flex items-center justify-center text-xl rounded-xl w-10 h-10 bg-amber-700 text-white">☕</span>
                <span class="text-lg font-bold text-stone-800">{{ config('app.name', 'Coffee Shop') }}</span>
            </a>
            <a href="{{ route('login') }}"
                class="text-sm font-medium text-stone-500 hover:text-amber-700">Masuk Staff →</a>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="py-8 mt-12 text-sm text-center border-t text-stone-400 border-stone-200">
        © {{ date('Y') }} {{ config('app.name', 'Coffee Shop') }} · Pesan langsung dari meja Anda ☕
    </footer>

    @livewireScripts
</body>

</html>
