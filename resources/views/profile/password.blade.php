@extends('layouts.panel')

@section('title', 'Ubah kata sandi')
@section('page_title', 'Ubah kata sandi')

@section('panel')
<div class="max-w-xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-brand-100">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" novalidate>
        @csrf
        @method('PUT')

        <x-field
            name="current_password"
            label="Kata sandi saat ini"
            type="password"
            autocomplete="current-password"
            autofocus
            required
        />

        <x-field
            name="password"
            label="Kata sandi baru"
            type="password"
            autocomplete="new-password"
            hint="Minimal 6 karakter dan berbeda dari kata sandi saat ini."
            required
        />

        <x-field
            name="password_confirmation"
            label="Ulangi kata sandi baru"
            type="password"
            autocomplete="new-password"
            required
        />

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                Simpan
            </button>
            <a href="{{ route('dashboard') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-ink-700 hover:bg-cyan-soft">Batal</a>
        </div>
    </form>
</div>
@endsection