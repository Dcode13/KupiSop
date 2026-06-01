<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CodeCoffee') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-stone-100 text-stone-800">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Backdrop (mobile) -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Topbar -->
            <header class="sticky top-0 z-10 flex items-center justify-between h-16 px-4 bg-white border-b border-stone-200 shadow-sm sm:px-6">
                <button @click="sidebarOpen = true" class="text-stone-500 lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1 lg:ml-0">
                    @isset($header)
                        <div class="text-lg font-semibold text-stone-700">{{ $header }}</div>
                    @endisset
                </div>

                <!-- User dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-stone-700">
                        <span class="flex items-center justify-center w-9 h-9 text-white rounded-full bg-amber-700">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </span>
                        <span class="hidden text-left sm:block">
                            <span class="block leading-tight">{{ auth()->user()->name }}</span>
                            <span class="block text-xs capitalize text-stone-400">{{ auth()->user()->getRoleNames()->first() }}</span>
                        </span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                        class="absolute right-0 z-20 w-48 py-1 mt-2 bg-white border rounded-lg shadow-lg border-stone-200">
                        <a href="{{ route('profile') }}" wire:navigate
                            class="block px-4 py-2 text-sm text-stone-600 hover:bg-stone-50">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-sm text-left text-red-600 hover:bg-stone-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Flash / Toast notifications -->
            @include('layouts.partials.toast')

            <!-- Page content -->
            <main class="p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>

</html>
