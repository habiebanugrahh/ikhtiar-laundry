@extends('layouts.panel')

@section('title', 'Data Karyawan')
@section('page_title', 'Data Karyawan')

@section('panel')
<div class="flex flex-wrap items-center justify-between gap-3">
    <p class="max-w-xl text-sm text-ink-700/70">
        Kelola akun Kasir. Akun yang dinonaktifkan tidak bisa masuk dan langsung keluar dari sesi yang sedang berjalan.
    </p>

    <a href="{{ route('employees.create') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
        Tambah kasir
    </a>
</div>

<section class="mt-5 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-brand-100">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <thead class="bg-cyan-soft text-ink-700/70">
                <tr>
                    <th class="px-5 py-2.5 font-medium">Nama</th>
                    <th class="px-5 py-2.5 font-medium">Username</th>
                    <th class="px-5 py-2.5 text-right font-medium">Transaksi dicatat</th>
                    <th class="px-5 py-2.5 font-medium">Status</th>
                    <th class="px-5 py-2.5 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-100">
                @forelse ($employees as $employee)
                    <tr @class(['text-ink-700/50' => ! $employee->is_active])>
                        <td class="px-5 py-3 font-medium text-ink-700">{{ $employee->name }}</td>
                        <td class="px-5 py-3">{{ $employee->username }}</td>
                        <td class="px-5 py-3 text-right">{{ $employee->transactions_count }}</td>
                        <td class="px-5 py-3">
                            @if ($employee->is_active)
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('employees.edit', $employee) }}" class="font-medium text-brand-600 hover:text-ink-700">Ubah</a>

                                <form
                                    method="POST"
                                    action="{{ route('employees.toggle', $employee) }}"
                                    @if ($employee->is_active)
                                        onsubmit='return confirm(@js("Nonaktifkan akun {$employee->name}? Akun ini tidak akan bisa masuk ke sistem."))'
                                    @endif
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @class([
                                        'font-medium',
                                        'text-red-600 hover:text-red-800' => $employee->is_active,
                                        'text-brand-600 hover:text-ink-700' => ! $employee->is_active,
                                    ])>
                                        {{ $employee->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-ink-700/60">
                            Belum ada akun kasir. Tambahkan dengan tombol di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection