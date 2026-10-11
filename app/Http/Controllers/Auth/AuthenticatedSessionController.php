<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Logout harusnya cepat; jangan ada query/aksi berat di sini.
        Auth::guard('web')->logout();

        // Invalidate session.
        $request->session()->invalidate();

        // Hanya regenerate token (hindari kerja berat yang tidak perlu).
        $request->session()->regenerateToken();

        // Hindari redirect ke route yang memicu controller berat.
        // Root '/' redirect ke login, tapi dengan cara yang paling ringan.
        return redirect()->route('login');
    }

}
