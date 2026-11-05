@extends('layouts.app')

@section('title')

@section('content')
<main class="mx-auto flex min-h-screen max-w-xl items-center px-5 py-10">
    <div class="w-full rounded-2xl bg-white p-8 shadow-sm ring-1 ring-brand-100">

        <h1 class="text-2xl font-bold text-ink-700">Ikhtiar Laundry</h1>
        <p class="mt-1 text-sm text-ink-700/70">
            Sistem rekapitulasi pendapatan harian dan bulanan. Halaman ini hanya untuk memastikan setup berjalan.
        </p>

        <dl class="mt-6 divide-y divide-brand-100 text-sm">
            <div class="flex items-center justify-between py-3">
                <dt class="text-ink-700/70">Versi Laravel</dt>
                <dd class="font-semibold">{{ app()->version() }}</dd>
            </div>
            <div class="flex items-center justify-between py-3">
                <dt class="text-ink-700/70">Versi PHP</dt>
                <dd class="font-semibold">{{ PHP_VERSION }}</dd>
            </div>
            <div class="flex items-center justify-between py-3">
                <dt class="text-ink-700/70">Zona waktu aplikasi</dt>
                <dd class="font-semibold">{{ config('app.timezone') }}</dd>
            </div>
            <div class="flex items-center justify-between py-3">
                <dt class="text-ink-700/70">Waktu sekarang</dt>
                <dd class="font-semibold">{{ now()->translatedFormat('d F Y, H:i') }}</dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-3">
                <dt class="text-ink-700/70">Koneksi database</dt>
                <dd class="text-right font-semibold">
                    @if ($dbConnected)
                        <span class="inline-flex items-center rounded-full bg-brand-100 px-3 py-1 text-brand-600">
                            Terhubung ke {{ $dbName }}
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-red-700">
                            Gagal terhubung
                        </span>
                        <span class="mt-2 block text-xs font-normal text-red-700/80">{{ $dbError }}</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</main>
@endsection