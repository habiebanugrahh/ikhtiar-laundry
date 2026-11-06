@extends('layouts.panel')

@section('title', 'Data Pelanggan')
@section('page_title', 'Data Pelanggan')

@section('panel')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('customers.index') }}" class="flex w-full max-w-md gap-2">
        <label for="q" class="sr-only">Cari pelanggan</label>
        <input
            id="q"
            name="q"
            type="search"
            value="{{ $search }}"
            placeholder="Cari nama atau nomor telepon"
            class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/30"
        >
        <button type="submit" class="rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-brand-600 ring-1 ring-brand-400/60 transition hover:bg-brand-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
            Cari
        </button>
    </form>

    <a href="{{ route('customers.create') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
        Tambah pelanggan
    </a>
</div>

<section class="mt-5 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Nama</th>
                    <th class="px-5 py-2.5 font-medium">Telepon</th>
                    <th class="px-5 py-2.5 font-medium">Alamat</th>
                    <th class="px-5 py-2.5 text-right font-medium">Transaksi</th>
                    <th class="px-5 py-2.5 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($customers as $customer)
                    <tr>
                        <td class="px-5 py-3 font-medium text-ink-700">{{ $customer->name }}</td>
                        <td class="px-5 py-3">{{ $customer->phone }}</td>
                        <td class="max-w-xs truncate px-5 py-3 text-ink-700/70">{{ $customer->address ?: '-' }}</td>
                        <td class="px-5 py-3 text-right">{{ $customer->transactions_count }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('customers.show', $customer) }}" class="font-medium text-brand-600 hover:text-ink-700">Lihat</a>
                                <a href="{{ route('customers.edit', $customer) }}" class="font-medium text-brand-600 hover:text-ink-700">Ubah</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-ink-700/60">
                            @if ($search !== '')
                                Tidak ada pelanggan yang cocok dengan "{{ $search }}".
                            @else
                                Belum ada pelanggan. Tambahkan dengan tombol di atas.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($customers->hasPages())
    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@endif
@endsection