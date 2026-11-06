@extends('layouts.panel')

@php $isEdit = $employee->exists; @endphp

@section('title', $isEdit ? 'Ubah akun kasir' : 'Tambah akun kasir')
@section('page_title', $isEdit ? 'Ubah akun kasir' : 'Tambah akun kasir')

@section('panel')
<div class="max-w-xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-brand-100">
    <form
        method="POST"
        action="{{ $isEdit ? route('employees.update', $employee) : route('employees.store') }}"
        class="space-y-5"
        novalidate
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <x-field name="name" label="Nama lengkap" :value="$employee->name" maxlength="100" autofocus required />

        <x-field
            name="username"
            label="Username"
            :value="$employee->username"
            maxlength="30"
            autocomplete="off"
            hint="Minimal 4 karakter. Boleh huruf, angka, titik, garis bawah, dan tanda hubung."
            required
        />

        <x-field
            name="password"
            label="{{ $isEdit ? 'Kata sandi baru' : 'Kata sandi' }}"
            type="password"
            autocomplete="new-password"
            :hint="$isEdit ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : 'Minimal 6 karakter.'"
            :required="! $isEdit"
        />

        <x-field
            name="password_confirmation"
            label="Ulangi kata sandi"
            type="password"
            autocomplete="new-password"
        />

        <label class="flex items-center gap-2 text-sm text-ink-700">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                @checked($errors->any() ? old('is_active') : $employee->is_active)
            >
            Akun aktif dan bisa masuk ke sistem
        </label>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                Simpan
            </button>
            <a href="{{ route('employees.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-ink-700 hover:bg-cyan-soft">Batal</a>
        </div>
    </form>
</div>
@endsection