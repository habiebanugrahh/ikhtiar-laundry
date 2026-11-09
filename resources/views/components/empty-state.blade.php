@props(['title' => null])

{{-- Tampilan saat data kosong: mesin cuci kecil dengan gelembung. Dekoratif, teks tetap terbaca. --}}
<div {{ $attributes->class(['flex flex-col items-center py-4 text-center']) }}>
    <div class="relative">
        <x-laundry.bubbles class="pointer-events-none absolute -right-12 -top-2 w-20 text-brand-400 opacity-70" />
        <x-laundry.washer class="w-16" />
    </div>

    @if ($title)
        <p class="mt-3 font-semibold text-ink-700">{{ $title }}</p>
    @endif

    <p class="mt-1 max-w-sm text-sm text-ink-700/70">{{ $slot }}</p>
</div>