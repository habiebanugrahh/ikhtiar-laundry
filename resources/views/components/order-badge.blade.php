@props(['status'])

@php
    [$label, $color] = match ($status) {
        'processing' => ['Diproses', 'bg-blue-100 text-blue-700'],
        'done' => ['Selesai', 'bg-cyan-100 text-cyan-700'],
        'picked_up' => ['Diambil', 'bg-slate-100 text-slate-600'],
        default => ['Diterima', 'bg-sky-100 text-sky-700'],
    };
@endphp

<span {{ $attributes->class(['inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium', $color]) }}>{{ $label }}</span>