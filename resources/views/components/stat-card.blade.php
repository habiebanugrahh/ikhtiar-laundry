@props(['label', 'value', 'note' => null])

<div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-brand-100">
    <p class="text-sm text-ink-700/70">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold text-ink-700">{{ $value }}</p>
    @if ($note)
        <p class="mt-1 text-xs text-ink-700/60">{{ $note }}</p>
    @endif
</div>