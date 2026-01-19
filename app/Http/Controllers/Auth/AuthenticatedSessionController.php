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
        // 1. Proses Login Standar
        $request->authenticate();
        $request->session()->regenerate();
        $user = Auth::user();

        // ---------------------------------------------------------------------
        // CATATAN:
        // Pengecekan undangan tidak dilakukan di sini lagi.
        // Sudah ditangani otomatis oleh: app/Listeners/CekUndanganProposal.php
        // yang berjalan saat event 'Illuminate\Auth\Events\Login' terjadi.
        // ---------------------------------------------------------------------
        // 2. Redirect Sesuai Role
        if ($user->role === 'Dosen') {
            return redirect()->route('dosen.dashboard');
        } elseif ($user->role === 'Admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'Wakil Dekan 3') {
            return redirect()->route('wakil_dekan3.dashboard');
        } elseif ($user->role === 'Dekan') {
            return redirect()->route('dekan.dashboard');
        } elseif ($user->role === 'Kepala Pusat 1' || $user->role === 'Kepala Pusat 2' || $user->role === 'Kepala Pusat 3' || $user->role === 'Kepala Pusat 4' || $user->role === 'Kepala Pusat 5') {
            return redirect()->route('kepala_pusat.dashboard');
        } elseif ($user->role === 'Wakil Rektor 3') {
            return redirect()->route('wakil_rektor.dashboard');
        }
        // Redirect default
        return redirect()->intended(route('dosen.dashboard'));
    }
    
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}