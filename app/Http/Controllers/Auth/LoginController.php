<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Display the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Process connection
     */
    public function login(LoginRequest $request)
    {
        $request->authenticate();

        Auth::user()->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        return redirect()
            ->intended(route('chat.index'))
            ->with('success', 'Connexion réussie !');
    }

    /**
     * Disconnect
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->update([
                'is_online' => false,
                'last_seen_at' => now(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Déconnexion réussie !');
    }
}
