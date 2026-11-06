@extends('layouts.panel')

@php $isEdit = $service->exists; @endphp

@section('title', $isEdit ? 'Ubah layanan' : 'Tambah layanan')
@section('page_title', $isEdit ? 'Ubah layanan' : 'Tambah layanan')

@section('panel')
<div class="max-w-xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-brand-100">
    <form
        method="POST"
        action="{{ $isEdit ? route('services.update', $service) : route('services.store') }}"
        class="space-y-5"
        novalidate
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <x-field name="name" label="Nama layanan" :value="$service->name" maxlength="100" autofocus required />

        <div>
            <label for="unit" class="block text-sm font-medium text-ink-700">Satuan</label>
            <select
                id="unit"
                name="unit"
                class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
            >
                @foreach (['kg' => 'Kilogram (kg)', 'pcs' => 'Potong atau buah (pcs)'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('unit', $service->unit) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('unit')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-field
            name="price"
            label="Tarif per satuan (Rp)"
            type="number"
            :value="$service->price"
            min="0"
            step="100"
            inputmode="numeric"
            hint="Perubahan tarif hanya berlaku untuk transaksi baru."
            required
        />

        <label class="flex items-center gap-2 text-sm text-ink-700">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                @checked($errors->any() ? old('is_active') : $service->is_active)
            >
            Layanan aktif dan bisa dipilih saat input transaksi
        </label>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                Simpan
            </button>
            <a href="{{ route('services.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-ink-700 hover:bg-cyan-soft">Batal</a>
        </div>
    </form>
</div>
@endsection