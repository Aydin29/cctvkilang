<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// Tambahkan facade Redirect untuk memastikan proses pengalihan aman
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    /**
     * Menampilkan informasi profil pengguna yang terautentikasi.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        return view('profile.show', compact('user'));
    }

    /**
     * Menghapus akun pengguna yang terautentikasi.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 1) Logout cepat (hindari redirect yang memicu query berat)
        Auth::logout();

        // 2) Hapus akun (query utama)
        $user->delete();

        // 3) Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 4) Redirect ringan ke halaman login
        return redirect()->route('login')->with('success', 'Akun Anda telah berhasil dihapus.');

    }
}