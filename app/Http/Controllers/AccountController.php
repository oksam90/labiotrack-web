<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Espace « Mon compte » — droits RGPD/CDP du sujet des données :
 *  - accès (consultation de ses données personnelles) ;
 *  - portabilité (export JSON structuré).
 *
 * L'effacement se fait sur demande via l'administrateur / DPO (anonymisation,
 * cf. AdminController::destroyUser) pour préserver la traçabilité légale.
 */
class AccountController extends Controller
{
    public function donnees()
    {
        return view('account.donnees', ['user' => Auth::user()]);
    }

    public function export()
    {
        $user = Auth::user();

        $data = [
            'exporte_le' => now()->toIso8601String(),
            'profil' => [
                'id'            => $user->id,
                'nom'           => $user->nom,
                'prenom'        => $user->prenom,
                'email'         => $user->email,
                'telephone'     => $user->telephone,
                'role'          => $user->role,
                'etablissement' => optional($user->etablissement)->nom,
                'reseau'        => optional($user->reseau)->nom,
                'locale'        => $user->locale,
                'cree_le'       => optional($user->created_at)->toIso8601String(),
            ],
            'connexions' => [
                'derniere_connexion_le' => optional($user->last_login_at)->toIso8601String(),
                'derniere_ip'           => $user->last_login_ip,
            ],
            // Données métier rattachées à l'utilisateur (métadonnées, sans
            // recharger tout le contenu — principe de pertinence).
            'declarations_saisies' => DB::table('declarations')
                ->where('user_id', $user->id)
                ->select('id', 'date_declaration', 'statut', 'nombre_contenants', 'poids_estime_kg')
                ->get(),
            'signatures_apposees' => DB::table('signatures')
                ->where('signataire_user_id', $user->id)
                ->select('id', 'collecte_id', 'signataire_nom', 'signed_at', 'ip_address')
                ->get(),
        ];

        $filename = 'mes-donnees-labiotrack-' . now()->format('Ymd') . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
