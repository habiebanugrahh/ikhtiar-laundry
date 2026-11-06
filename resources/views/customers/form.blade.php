@extends('layouts.panel')

@php $isEdit = $customer->exists; @endphp

@section('title', $isEdit ? 'Ubah pelanggan' : 'Tambah pelanggan')
@section('page_title', $isEdit ? 'Ubah pelanggan' : 'Tambah pelanggan')

@section('panel')
<div class="max-w-xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-brand-100">
    <form
        method="POST"
        action="{{ $isEdit ? route('customers.update', $customer) : route('customers.store') }}"
        class="space-y-5"
        novalidate
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <x-field name="name" label="Nama pelanggan" :value="$customer->name" maxlength="100" autofocus required />

        <x-field
            name="phone"
            label="Nomor telepon atau WhatsApp"
            type="tel"
            :value="$customer->phone"
            maxlength="20"
            inputmode="tel"
            hint="Contoh: 081234567890"
            required
        />

        <div>
            <label for="address" class="block text-sm font-medium text-ink-700">Alamat</label>
            <textarea
                id="address"
                name="address"
                rows="3"
                maxlength="500"
                @class([
                    'mt-1.5 block w-full rounded-lg border px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30',
                    'border-red-400' => $errors->has('address'),
                    'border-slate-300' => ! $errors->has('address'),
                ])
            >{{ old('address', $customer->address) }}</textarea>
            <p class="mt-1.5 text-xs text-ink-700/60">Boleh dikosongkan. Isi untuk pelanggan layanan antar-jemput.</p>
            @error('address')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                Simpan
            </button>
            <a href="{{ route('customers.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-ink-700 hover:bg-cyan-soft">Batal</a>
        </div>
    </form>
</div>
@endsection