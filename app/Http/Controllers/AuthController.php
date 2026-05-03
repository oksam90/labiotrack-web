<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect('/dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $user->actif) {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte a été désactivé.']);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Client signataire : son seul rôle est de signer → écran collectes
            if ($user->isClientSignataire()) {
                return redirect()->route('collectes.index');
            }

            // Tous les utilisateurs globaux sans établissement fixe → dashboard réseau
            // Cela inclut : superadmin, admin, collecteur, prestataire
            if ($user->isGlobal() && ! $user->etablissement_id) {
                return redirect()->route('superadmin.index');
            }

            return redirect()->route('dashboard');
        }

        return back()
            ->withErrors(['email' => 'Identifiants incorrects.'])
            ->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
