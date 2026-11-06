@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-ink-700">{{ $label }}</label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        {{ $attributes->class([
            'mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30',
            'border-red-400' => $errors->has($name),
            'border-slate-300' => ! $errors->has($name),
        ]) }}
    >

    @if ($hint)
        <p class="mt-1.5 text-xs text-ink-700/60">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>