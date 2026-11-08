<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordController extends Controller
{
    // Form ubah kata sandi untuk pengguna yang sedang login
    public function edit(): View
    {
        return view('profile.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ]);

        // Kata sandi di-hash otomatis oleh cast pada model User
        $request->user()->update(['password' => $data['password']]);

        return redirect()->route('password.edit')
            ->with('success', 'Kata sandi berhasil diubah.');
    }
}