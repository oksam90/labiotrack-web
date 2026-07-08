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
                return back()->withErrors(['email' => __('auth_ui.account_disabled')]);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Client signataire : son seul rôle est de signer → écran collectes
            if ($user->isClientSignataire()) {
                return redirect()->route('collectes.index');
            }

            // Utilisateurs sans établissement fixe → vue réseau.
            // superadmin (tous réseaux) + collecteur/prestataire (leur réseau).
            // collecteur/prestataire ne sont plus isGlobal → on les liste ici.
            if (in_array($user->role, ['superadmin', 'collecteur', 'prestataire'], true)
                && ! $user->etablissement_id) {
                return redirect()->route('superadmin.index');
            }

            return redirect()->route('dashboard');
        }

        return back()
            ->withErrors(['email' => __('auth_ui.bad_credentials')])
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
