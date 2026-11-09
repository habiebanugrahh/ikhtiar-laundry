@php $uid = 'logo-' . \Illuminate\Support\Str::random(6); @endphp
{{-- Logo Ikhtiar Laundry: tetesan air dengan gelembung. Dekoratif. --}}
<svg viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false" {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $uid }}" x1="4" y1="2" x2="36" y2="38" gradientUnits="userSpaceOnUse">
            <stop stop-color="#22d3ee"/>
            <stop offset="1" stop-color="#0369a1"/>
        </linearGradient>
    </defs>
    <rect width="40" height="40" rx="11" fill="url(#{{ $uid }})"/>
    <path d="M20 7.5c0 0-9.5 10.2-9.5 16.6a9.5 9.5 0 0 0 19 0C29.5 17.7 20 7.5 20 7.5z" fill="white"/>
    <path d="M15.2 25a5.2 5.2 0 0 0 3.8 4.4" stroke="#06b6d4" stroke-width="2" stroke-linecap="round"/>
    <circle cx="31" cy="9.5" r="2.6" stroke="white" stroke-width="1.6"/>
    <circle cx="34" cy="16" r="1.4" fill="white"/>
</svg>