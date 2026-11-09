@props(['code', 'title', 'message'])

<div class="relative isolate min-h-screen overflow-hidden">
    <x-laundry.bubbles class="pointer-events-none absolute -right-6 top-6 -z-10 w-56 text-brand-400 opacity-70 sm:w-72" />
    <x-laundry.bubbles class="pointer-events-none absolute -left-10 bottom-32 -z-10 w-44 text-brand-400 opacity-60" />
    <x-laundry.waves class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-32 w-full text-brand-100" />

    <main class="mx-auto flex min-h-screen max-w-md items-center px-5 py-10">
        <div class="w-full rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-brand-100">
            <x-laundry.washer class="mx-auto w-20" />

            <p class="mt-4 text-5xl font-bold text-brand-500">{{ $code }}</p>
            <h1 class="mt-3 text-xl font-bold text-ink-700">{{ $title }}</h1>
            <p class="mt-2 text-sm text-ink-700/70">{{ $message }}</p>

            <a href="{{ route('dashboard') }}" class="mt-6 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-ink-700">
                Kembali
            </a>
        </div>
    </main>
</div>