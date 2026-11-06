<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    // Daftar pelanggan dengan pencarian nama atau nomor telepon
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $like = '%' . addcslashes($search, '%_\\') . '%';

        $customers = Customer::query()
            ->withCount('transactions')
            ->when($search !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    // Pencarian pelanggan untuk form transaksi (JSON)
    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q'));
        $like = '%' . addcslashes($search, '%_\\') . '%';

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'phone', 'address']);

        return response()->json($customers);
    }

    // Tambah pelanggan cepat dari form transaksi (JSON)
    public function quickStore(Request $request): JsonResponse
    {
        $customer = Customer::create($this->validated($request));

        return response()->json($customer->only(['id', 'name', 'phone', 'address']), 201);
    }

    public function create(): View
    {
        return view('customers.form', ['customer' => new Customer()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::create($this->validated($request));

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    // Detail pelanggan beserta riwayat transaksinya
    public function show(Customer $customer): View
    {
        $transactions = $customer->transactions()->latest('id')->limit(20)->get();

        $remaining = $customer->transactions()
            ->where('payment_status', '!=', Transaction::PAYMENT_PAID)
            ->sum(DB::raw('total - paid_amount'));

        return view('customers.show', [
            'customer' => $customer,
            'transactions' => $transactions,
            'transactionCount' => $customer->transactions()->count(),
            'totalSpent' => $customer->transactions()->sum('total'),
            'remaining' => $remaining,
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validated($request));

        return redirect()->route('customers.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'min:8', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
    }
}