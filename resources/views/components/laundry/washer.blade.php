@php $uid = 'washer-' . \Illuminate\Support\Str::random(6); @endphp
{{-- Ilustrasi mesin cuci depan dengan kaos dan gelembung di dalam drum. Dekoratif. --}}
<svg viewBox="0 0 220 262" fill="none" aria-hidden="true" focusable="false" {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $uid }}-water" x1="110" y1="106" x2="110" y2="198" gradientUnits="userSpaceOnUse">
            <stop stop-color="#67e8f9"/>
            <stop offset="1" stop-color="#0284c7"/>
        </linearGradient>
        <clipPath id="{{ $uid }}-glass"><circle cx="110" cy="152" r="46"/></clipPath>
    </defs>

    <rect x="40" y="246" width="30" height="12" rx="4" fill="#0c4a6e" fill-opacity="0.35"/>
    <rect x="150" y="246" width="30" height="12" rx="4" fill="#0c4a6e" fill-opacity="0.35"/>

    <rect x="14" y="8" width="192" height="242" rx="24" fill="white" stroke="#a5f3fc" stroke-width="4"/>
    <path d="M14 32a24 24 0 0 1 24-24h144a24 24 0 0 1 24 24v36H14z" fill="#ecfeff"/>
    <path d="M14 68h192" stroke="#bae6fd" stroke-width="3"/>

    <circle cx="52" cy="38" r="14" fill="#0891b2"/>
    <circle cx="52" cy="38" r="9" fill="#06b6d4"/>
    <path d="M52 30v8" stroke="white" stroke-width="3" stroke-linecap="round"/>
    <circle cx="92" cy="38" r="5" fill="#22d3ee"/>
    <circle cx="110" cy="38" r="5" fill="#67e8f9"/>
    <rect x="136" y="24" width="56" height="28" rx="7" fill="#0c4a6e"/>
    <rect x="144" y="34" width="10" height="8" rx="2" fill="#22d3ee"/>
    <rect x="158" y="34" width="10" height="8" rx="2" fill="#22d3ee"/>
    <rect x="172" y="34" width="10" height="8" rx="2" fill="#22d3ee" fill-opacity="0.45"/>

    <circle cx="110" cy="152" r="66" fill="#e0f2fe"/>
    <circle cx="110" cy="152" r="60" fill="white" stroke="#0891b2" stroke-width="8"/>
    <g clip-path="url(#{{ $uid }}-glass)">
        <circle cx="110" cy="152" r="46" fill="#0c4a6e" fill-opacity="0.08"/>
        <path d="M60 148c14-10 28 10 50 0s36-10 50 0V204H60z" fill="url(#{{ $uid }}-water)"/>
        <path d="M60 148c14-10 28 10 50 0s36-10 50 0" stroke="white" stroke-opacity="0.6" stroke-width="3"/>
        <path d="M92 150l12-7c3 5 9 5 12 0l12 7-6 12-5-3v22h-14v-22l-5 3z" fill="white" fill-opacity="0.92"/>
        <circle cx="86" cy="178" r="4" stroke="white" stroke-opacity="0.9" stroke-width="2"/>
        <circle cx="134" cy="170" r="3" stroke="white" stroke-opacity="0.9" stroke-width="2"/>
        <circle cx="126" cy="190" r="5" stroke="white" stroke-opacity="0.9" stroke-width="2"/>
        <circle cx="94" cy="194" r="2.5" stroke="white" stroke-opacity="0.9" stroke-width="2"/>
    </g>
    <path d="M74 134a40 40 0 0 1 22-24" stroke="white" stroke-opacity="0.85" stroke-width="5" stroke-linecap="round"/>
</svg>