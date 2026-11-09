@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="grid min-h-screen lg:grid-cols-2">

    {{-- Panel kiri: identitas usaha dengan ilustrasi laundry (disembunyikan di layar kecil) --}}
    <aside class="relative isolate hidden overflow-hidden bg-linear-to-br from-brand-500 to-ink-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <x-laundry.swirl class="pointer-events-none absolute -right-20 -top-20 -z-10 size-96 text-white opacity-20" />
        <x-laundry.bubbles class="pointer-events-none absolute right-6 top-1/3 -z-10 w-52 text-white opacity-80" />
        <x-laundry.bubbles class="pointer-events-none absolute -left-8 bottom-40 -z-10 w-40 text-white opacity-40" />
        <x-laundry.waves class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-40 w-full text-white" />
        <x-laundry.washer class="pointer-events-none absolute bottom-12 right-12 w-40 drop-shadow-xl xl:w-44" />

        <div class="flex items-center gap-3">
            <x-laundry.logo class="size-10" />
            <p class="text-lg font-bold">Ikhtiar Laundry</p>
        </div>

        <div>
            <h1 class="max-w-sm text-4xl font-bold leading-tight">
                Catat transaksi hari ini, lihat pendapatannya saat itu juga.
            </h1>
            <p class="mt-4 max-w-sm text-brand-100">
                Rekap harian dan bulanan dihitung otomatis dari pembayaran yang benar-benar diterima.
            </p>
        </div>

        <p class="text-sm text-brand-100">Jalan Meranti No. 68</p>
    </aside>

    {{-- Panel kanan: formulir login --}}
    <main class="relative isolate flex items-center justify-center overflow-hidden bg-white px-6 py-12">
        <x-laundry.bubbles class="pointer-events-none absolute -right-10 -top-6 -z-10 w-52 text-brand-400 opacity-50" />
        <x-laundry.waves class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-24 w-full text-brand-100" />

        <div class="w-full max-w-sm">

            <div class="mb-8 flex items-center gap-2.5 lg:hidden">
                <x-laundry.logo class="size-9" />
                <p class="text-lg font-bold text-brand-600">Ikhtiar Laundry</p>
            </div>

            <h2 class="text-2xl font-bold text-ink-700">Masuk ke sistem</h2>
            <p class="mt-1 text-sm text-ink-700/70">Gunakan akun yang diberikan oleh Owner.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5" novalidate>
                @csrf

                <div>
                    <label for="username" class="block text-sm font-medium text-ink-700">Username</label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        autofocus
                        required
                        class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 @error('username') border-red-400 @enderror"
                    >
                    @error('username')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-ink-700">Kata sandi</label>
                    <div class="relative mt-1.5">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="block w-full rounded-lg border border-slate-300 py-2.5 pl-3.5 pr-24 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30 @error('password') border-red-400 @enderror"
                        >
                        <button
                            type="button"
                            id="toggle-password"
                            class="absolute inset-y-0 right-0 px-3.5 text-sm font-medium text-brand-600 hover:text-ink-700 focus:outline-none focus-visible:underline"
                        >Tampilkan</button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-ink-700">
                    <input type="checkbox" name="remember" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Ingat saya di perangkat ini
                </label>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                >
                    Masuk
                </button>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
    // Tampilkan atau sembunyikan kata sandi
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('toggle-password');

    toggleButton.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        toggleButton.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
    });
</script>
@endpush