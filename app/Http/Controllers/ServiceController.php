<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    // Daftar layanan dan tarif
    public function index(): View
    {
        $services = Service::orderByDesc('is_active')->orderBy('name')->get();

        return view('services.index', compact('services'));
    }

    public function create(): View
    {
        return view('services.form', [
            'service' => new Service(['unit' => Service::UNIT_KG, 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($this->validated($request));

        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service): View
    {
        return view('services.form', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        // Tarif baru hanya berlaku untuk transaksi berikutnya karena tarif lama
        // sudah disalin ke tabel transaction_items saat transaksi dibuat
        $service->update($this->validated($request, $service));

        return redirect()->route('services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    // Mengaktifkan atau menonaktifkan layanan (layanan tidak dihapus)
    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['is_active' => ! $service->is_active]);

        return redirect()->route('services.index')->with(
            'success',
            $service->is_active ? 'Layanan diaktifkan.' : 'Layanan dinonaktifkan.'
        );
    }

    private function validated(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('services', 'name')->ignore($service?->id)],
            'unit' => ['required', Rule::in([Service::UNIT_KG, Service::UNIT_PCS])],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}