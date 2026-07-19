<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

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

        // SECURITY : on inclut `actif => 1` dans les credentials pour qu'un
        // compte désactivé ne soit JAMAIS authentifié (aucun cookie « remember »
        // posé puis retiré). On distingue ensuite « désactivé » de « mauvais
        // identifiants » via un contrôle mot de passe séparé, sans connecter.
        if (Auth::attempt($credentials + ['actif' => 1], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

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

        // Échec de l'attempt : soit mauvais identifiants, soit compte désactivé.
        // On lève le message « compte désactivé » UNIQUEMENT si l'email + le mot
        // de passe sont corrects mais que le compte est inactif (évite de révéler
        // l'existence d'un compte à un attaquant qui devine juste l'email).
        $user = User::where('email', $credentials['email'])->first();
        if ($user && ! $user->actif && Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => __('auth_ui.account_disabled')])
                ->withInput($request->except('password'));
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

    // ── MOT DE PASSE OUBLIÉ ────────────────────────────────────────────────

    public function showForgotForm()
    {
        if (Auth::check()) return redirect('/dashboard');
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // On envoie le lien via le broker Laravel. Le message de retour est
        // volontairement générique (anti-énumération de comptes) : qu'un compte
        // existe ou non, l'utilisateur voit le même message de succès.
        Password::sendResetLink($request->only('email'));

        return back()->with('status', __('auth_ui.reset_link_sent'));
    }

    public function showResetForm(Request $request, string $token)
    {
        if (Auth::check()) return redirect('/dashboard');
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PasswordReset) {
            return redirect()->route('login')->with('status', __('auth_ui.reset_success'));
        }

        return back()
            ->withErrors(['email' => __($status)])
            ->withInput($request->only('email'));
    }
}
