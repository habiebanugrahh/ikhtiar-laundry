<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    // Menampilkan halaman login
    public function create(): View
    {
        return view('auth.login');
    }

    // Memproses login menggunakan username dan kata sandi
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Hanya akun aktif yang boleh masuk
        $attempt = Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $attempt) {
            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi salah, atau akun tidak aktif.',
            ]);
        }

        // Mencegah session fixation
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // Mengakhiri sesi pengguna
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}