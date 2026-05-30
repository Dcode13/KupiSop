@props(['title' => '', 'maxWidth' => 'max-w-lg'])

{{-- Modal sederhana berbasis Livewire; komponen pemanggil harus punya properti `showModal`. --}}
<div class="fixed inset-0 z-40 flex items-start justify-center p-4 overflow-y-auto sm:items-center" wire:key="modal-panel">
    <div class="fixed inset-0 bg-black/50" wire:click="$set('showModal', false)"></div>

    <div {{ $attributes->merge(['class' => "relative z-10 w-full {$maxWidth} my-8 bg-white rounded-xl shadow-xl"]) }}>
        <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100">
            <h3 class="text-lg font-semibold text-stone-800">{{ $title }}</h3>
            <button type="button" wire:click="$set('showModal', false)"
                class="text-2xl leading-none text-stone-400 hover:text-stone-600">&times;</button>
        </div>
        <div class="px-6 py-5">
            {{ $slot }}
        </div>
    </div>
</div>
