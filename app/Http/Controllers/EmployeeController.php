<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    // Daftar akun kasir
    public function index(): View
    {
        $employees = User::where('role', User::ROLE_CASHIER)
            ->withCount('transactions')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('employees.form', [
            'employee' => new User(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:4', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        User::create($data + [
            'role' => User::ROLE_CASHIER, // akun yang dibuat dari halaman ini selalu Kasir
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Akun kasir berhasil ditambahkan.');
    }

    public function edit(User $employee): View
    {
        $this->ensureCashier($employee);

        return view('employees.form', compact('employee'));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $this->ensureCashier($employee);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:4', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($employee->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        // Kata sandi hanya diganti jika diisi
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $employee->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('employees.index')
            ->with('success', 'Akun kasir berhasil diperbarui.');
    }

    // Mengaktifkan atau menonaktifkan akun kasir
    public function toggle(User $employee): RedirectResponse
    {
        $this->ensureCashier($employee);

        $employee->update(['is_active' => ! $employee->is_active]);

        return redirect()->route('employees.index')->with(
            'success',
            $employee->is_active ? 'Akun kasir diaktifkan.' : 'Akun kasir dinonaktifkan.'
        );
    }

    // Halaman ini hanya mengelola akun Kasir, bukan akun Owner
    private function ensureCashier(User $user): void
    {
        abort_unless($user->isCashier(), 404);
    }
}