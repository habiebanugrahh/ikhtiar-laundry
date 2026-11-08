@extends('layouts.app')

@section('title', 'Sesi habis')

@section('content')
<x-error-page code="419" title="Sesi habis" message="Halaman sudah terlalu lama terbuka. Muat ulang halaman lalu coba lagi." />
@endsection