@extends('layouts.app')

@section('title', 'Halaman tidak ditemukan')

@section('content')
<x-error-page code="404" title="Halaman tidak ditemukan" message="Alamat yang kamu buka tidak ada atau sudah dipindahkan." />
@endsection