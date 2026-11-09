@props(['label', 'value', 'note' => null, 'href' => null])

@php
    $tag = $href ? 'a' : 'div';
    $classes = 'relative isolate block overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100'
        . ($href ? ' transition hover:ring-brand-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500' : '');
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif class="{{ $classes }}">
    {{-- Aksen gelembung di pojok kartu, berada di belakang teks --}}
    <x-laundry.bubbles class="pointer-events-none absolute -right-6 -top-6 -z-10 w-24 text-brand-400 opacity-25" />

    <p class="text-sm text-ink-700/70">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold text-ink-700">{{ $value }}</p>
    @if ($note)
        <p class="mt-1 text-xs text-ink-700/60">{{ $note }}</p>
    @endif
</{{ $tag }}>