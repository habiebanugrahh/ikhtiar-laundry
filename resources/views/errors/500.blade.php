@extends('layouts.app')

@section('title', 'Terjadi kesalahan')

@section('content')
<x-error-page code="500" title="Terjadi kesalahan" message="Ada masalah di sistem. Coba lagi beberapa saat, atau hubungi pengelola jika terus berulang." />
@endsection