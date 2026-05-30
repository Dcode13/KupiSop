@props(['status' => 'pending'])

@php
    $map = [
        'pending'  => ['Pending', 'bg-stone-100 text-stone-600'],
        'diproses' => ['Diproses', 'bg-amber-100 text-amber-700'],
        'selesai'  => ['Selesai', 'bg-emerald-100 text-emerald-700'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst($status), 'bg-stone-100 text-stone-600'];
@endphp

<span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full {{ $classes }}">
    {{ $label }}
</span>
