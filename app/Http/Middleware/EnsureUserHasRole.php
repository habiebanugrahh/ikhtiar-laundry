<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Akun yang dinonaktifkan Owner langsung dikeluarkan dari sesi yang sedang berjalan
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'username' => 'Akun kamu tidak aktif. Hubungi Owner.',
            ]);
        }

        // Role tidak sesuai, termasuk saat membuka URL langsung
        if (! in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}