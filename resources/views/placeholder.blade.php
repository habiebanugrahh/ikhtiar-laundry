@extends('layouts.panel')

@section('title', $title)
@section('page_title', $title)

{{-- Halaman sementara untuk menu yang belum dibuat --}}
@section('panel')
<div class="rounded-2xl border border-dashed border-brand-400/60 bg-white p-10 text-center">
    <p class="font-semibold text-ink-700">{{ $title }}</p>
    <p class="mt-1 text-sm text-ink-700/70">Halaman ini sedang disiapkan.</p>
</div>
@endsection