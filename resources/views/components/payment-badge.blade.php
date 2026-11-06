@props(['status'])

@php
    [$label, $color] = match ($status) {
        'paid' => ['Lunas', 'bg-emerald-100 text-emerald-700'],
        'partial' => ['Sebagian', 'bg-amber-100 text-amber-700'],
        default => ['Belum bayar', 'bg-rose-100 text-rose-700'],
    };
@endphp

<span {{ $attributes->class(['inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium', $color]) }}>{{ $label }}</span>