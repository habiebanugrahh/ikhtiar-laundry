@extends('layouts.panel')

@section('title', 'Pengaturan Tarif')
@section('page_title', 'Pengaturan Tarif')

@section('panel')
<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="max-w-xl text-sm text-ink-700/70">
        Perubahan tarif hanya berlaku untuk transaksi baru. Transaksi lama tetap memakai tarif saat transaksi dibuat.
    </p>

    <a href="{{ route('services.create') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
        Tambah layanan
    </a>
</div>

<section class="mt-5 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Layanan</th>
                    <th class="px-5 py-2.5 font-medium">Satuan</th>
                    <th class="px-5 py-2.5 text-right font-medium">Tarif</th>
                    <th class="px-5 py-2.5 font-medium">Status</th>
                    <th class="px-5 py-2.5 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($services as $service)
                    <tr @class(['text-ink-700/50' => ! $service->is_active])>
                        <td class="px-5 py-3 font-medium text-ink-700">{{ $service->name }}</td>
                        <td class="px-5 py-3">{{ $service->unit }}</td>
                        <td class="px-5 py-3 text-right">{{ \App\Support\Format::rupiah($service->price) }}</td>
                        <td class="px-5 py-3">
                            @if ($service->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('services.edit', $service) }}" class="font-medium text-brand-600 hover:text-ink-700">Ubah</a>

                                <form
                                    method="POST"
                                    action="{{ route('services.toggle', $service) }}"
                                    @if ($service->is_active)
                                        onsubmit='return confirm(@js("Nonaktifkan layanan {$service->name}? Layanan tidak akan muncul saat input transaksi baru."))'
                                    @endif
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @class([
                                        'font-medium',
                                        'text-red-600 hover:text-red-800' => $service->is_active,
                                        'text-brand-600 hover:text-ink-700' => ! $service->is_active,
                                    ])>
                                        {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-2"><x-empty-state>                            Belum ada layanan. Tambahkan layanan pertama dengan tombol di atas.
                        </x-empty-state></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection