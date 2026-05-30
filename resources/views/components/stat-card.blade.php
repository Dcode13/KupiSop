@props([
    'title' => '',
    'value' => '',
    'icon' => '📊',
    'color' => 'amber',
])

@php
    $colors = [
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'rose' => 'bg-rose-100 text-rose-700',
        'stone' => 'bg-stone-100 text-stone-700',
    ];
    $badge = $colors[$color] ?? $colors['amber'];
@endphp

<div class="flex items-center gap-4 p-5 bg-white rounded-xl shadow-sm">
    <div class="flex items-center justify-center text-2xl rounded-xl w-12 h-12 {{ $badge }}">
        {{ $icon }}
    </div>
    <div>
        <p class="text-sm text-stone-500">{{ $title }}</p>
        <p class="text-xl font-bold text-stone-800">{{ $value }}</p>
    </div>
</div>
