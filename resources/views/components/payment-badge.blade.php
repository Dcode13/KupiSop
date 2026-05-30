@props(['status' => 'unpaid'])

@php
    $map = [
        'unpaid'  => ['Belum Bayar', 'bg-stone-100 text-stone-600'],
        'pending' => ['Menunggu', 'bg-amber-100 text-amber-700'],
        'paid'    => ['Lunas', 'bg-emerald-100 text-emerald-700'],
        'failed'  => ['Gagal', 'bg-red-100 text-red-700'],
        'expired' => ['Kedaluwarsa', 'bg-red-100 text-red-700'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst((string) $status), 'bg-stone-100 text-stone-600'];
@endphp

<span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full {{ $classes }}">{{ $label }}</span>
