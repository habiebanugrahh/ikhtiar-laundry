@extends('layouts.app')

@section('title', 'Dashboard Owner')

{{-- Halaman sementara. Akan diganti dengan layout lengkap dan navigasi di langkah 4. --}}
@section('content')
<main class="mx-auto flex min-h-screen max-w-xl items-center px-5 py-10">
    <div class="w-full rounded-2xl bg-white p-8 shadow-sm ring-1 ring-brand-100">
        <h1 class="text-2xl font-bold text-ink-700">Dashboard Owner</h1>
        <p class="mt-2 text-sm text-ink-700/70">
            Halo, {{ auth()->user()->name }}. Kamu masuk sebagai <strong>Owner</strong>.
        </p>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-ink-700">
                Keluar
            </button>
        </form>
    </div>
</main>
@endsection