@extends('layouts.app')

@section('title', 'Akses ditolak')

@section('content')
<main class="mx-auto flex min-h-screen max-w-md items-center px-5 py-10">
    <div class="w-full rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-brand-100">
        <p class="text-5xl font-bold text-brand-500">403</p>
        <h1 class="mt-3 text-xl font-bold text-ink-700">Akses ditolak</h1>
        <p class="mt-2 text-sm text-ink-700/70">
            Akunmu tidak punya izin untuk membuka halaman ini.
        </p>

        <a href="{{ route('dashboard') }}" class="mt-6 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-ink-700">
            Kembali ke dashboard
        </a>
    </div>
</main>
@endsection