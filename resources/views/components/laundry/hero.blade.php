@props(['title', 'subtitle' => null])

{{-- Banner sambutan dashboard: gradasi air dengan pusaran, gelembung, gelombang, dan mesin cuci --}}
<section class="relative isolate overflow-hidden rounded-3xl bg-linear-to-br from-brand-500 to-ink-700 px-6 pb-16 pt-6 text-white shadow-sm sm:px-8 sm:pb-20 sm:pt-8">
    <x-laundry.swirl class="pointer-events-none absolute -right-10 -top-16 -z-10 size-60 text-white opacity-20" />
    <x-laundry.bubbles class="pointer-events-none absolute right-40 top-2 -z-10 hidden w-40 text-white opacity-80 md:block" />
    <x-laundry.bubbles class="pointer-events-none absolute -left-6 top-1/2 -z-10 w-28 text-white opacity-40" />
    <x-laundry.waves class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-14 w-full text-white sm:h-16" />
    <x-laundry.washer class="pointer-events-none absolute bottom-0 right-6 hidden w-28 drop-shadow-lg sm:block lg:w-32" />

    <div class="max-w-lg">
        <p class="text-sm text-brand-100">{{ now()->translatedFormat('l, d F Y') }}</p>
        <h2 class="mt-1 text-2xl font-bold sm:text-3xl">{{ $title }}</h2>

        @if ($subtitle)
            <p class="mt-1.5 text-brand-100">{{ $subtitle }}</p>
        @endif

        @if ($slot->isNotEmpty())
            <div class="mt-5 flex flex-wrap items-center gap-3">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>