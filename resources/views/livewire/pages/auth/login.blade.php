<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Isi cepat kredensial demo (hanya untuk development lokal).
     */
    public function fillDemo(string $role): void
    {
        $accounts = [
            'admin' => ['admin@coffee.test', 'adminkupisop'],
            'kasir' => ['kasir@coffee.test', 'kasirkupisop'],
            'barista' => ['barista@coffee.test', 'baristakupisop'],
        ];

        [$email, $password] = $accounts[$role] ?? $accounts['admin'];

        $this->form->email = $email;
        $this->form->password = $password;
    }
}; ?>

<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-stone-800">Selamat datang kembali 👋</h2>
        <p class="mt-1 text-sm text-stone-500">Masuk ke akun Anda untuk melanjutkan.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-5" x-data="{ showPassword: false }">
        <!-- Email -->
        <div>
            <label for="email" class="block mb-1.5 text-sm font-medium text-stone-600">Email</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                    placeholder="nama@coffee.test"
                    class="w-full py-2.5 pl-10 pr-3 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block mb-1.5 text-sm font-medium text-stone-600">Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <input wire:model="form.password" id="password" name="password" required autocomplete="current-password"
                    :type="showPassword ? 'text' : 'password'" placeholder="••••••••"
                    class="w-full py-2.5 pl-10 pr-10 text-sm border rounded-lg border-stone-300 focus:border-amber-500 focus:ring-amber-500">
                <button type="button" @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-stone-400 hover:text-stone-600">
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember + Forgot -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember"
                    class="rounded border-stone-300 text-amber-700 shadow-sm focus:ring-amber-500">
                <span class="text-sm ms-2 text-stone-600">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate
                    class="text-sm text-amber-700 hover:text-amber-900 hover:underline">Lupa password?</a>
            @endif
        </div>

        <!-- Tombol -->
        <button type="submit" wire:loading.attr="disabled" wire:target="login"
            class="flex items-center justify-center w-full gap-2 px-4 py-2.5 text-sm font-semibold text-white transition rounded-lg bg-amber-700 hover:bg-amber-800 disabled:opacity-60">
            <svg wire:loading wire:target="login" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login">Memproses…</span>
        </button>
    </form>

    <!-- Akun demo: hanya tampil saat development lokal (disembunyikan di production demi keamanan) -->
    @if (app()->environment('local'))
        <div class="pt-5 mt-6 border-t border-stone-100">
            <p class="mb-2 text-xs font-medium text-center text-stone-400">Isi cepat akun demo (lokal)</p>
            <div class="grid grid-cols-3 gap-2">
                <button wire:click="fillDemo('admin')" type="button"
                    class="px-2 py-2 text-xs font-medium transition border rounded-lg border-stone-200 text-stone-600 hover:border-amber-300 hover:bg-amber-50">
                    👑 Admin
                </button>
                <button wire:click="fillDemo('kasir')" type="button"
                    class="px-2 py-2 text-xs font-medium transition border rounded-lg border-stone-200 text-stone-600 hover:border-amber-300 hover:bg-amber-50">
                    🛒 Kasir
                </button>
                <button wire:click="fillDemo('barista')" type="button"
                    class="px-2 py-2 text-xs font-medium transition border rounded-lg border-stone-200 text-stone-600 hover:border-amber-300 hover:bg-amber-50">
                    ☕ Barista
                </button>
            </div>
        </div>
    @endif
</div>
