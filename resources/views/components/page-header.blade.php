@props(['title' => '', 'subtitle' => null])

<div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-stone-800">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-sm text-stone-500">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex items-center gap-2">
        {{ $slot }}
    </div>
</div>
