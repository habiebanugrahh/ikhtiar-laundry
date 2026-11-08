@extends('layouts.app')

@section('title', 'Terlalu banyak percobaan')

@section('content')
<x-error-page code="429" title="Terlalu banyak percobaan" message="Tunggu sebentar, lalu coba lagi." />
@endsection