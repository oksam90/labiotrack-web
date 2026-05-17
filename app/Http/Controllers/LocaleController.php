<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * LocaleController
 *
 * Endpoint de bascule de langue. Met à jour :
 *   - la session (effet immédiat sur la requête suivante)
 *   - la colonne users.locale (persistance entre sessions, si authentifié)
 *
 * Accepte uniquement les locales déclarées dans config('app.supported_locales').
 *
 * Route : POST /locale/{lang}  (déclarée en HORS du group auth pour permettre
 * la bascule depuis l'écran de login avant authentification).
 */
class LocaleController extends Controller
{
    public function switch(Request $request, string $lang)
    {
        $supported = config('app.supported_locales', ['fr', 'en']);

        abort_unless(in_array($lang, $supported, true), 422,
            'Langue non supportée.');

        // Toujours mémoriser en session (effet immédiat même si non authentifié)
        $request->session()->put('locale', $lang);

        // Persister sur l'utilisateur authentifié (préférence durable)
        if (Auth::check()) {
            Auth::user()->update(['locale' => $lang]);
        }

        return back()->with('success', __('common.locale_changed', [], $lang));
    }
}
