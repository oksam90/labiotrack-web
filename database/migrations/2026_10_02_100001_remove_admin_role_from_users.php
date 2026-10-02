<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Suppression du profil « admin » (admin d'établissement) : ses droits sont
 * portés par l'admin réseau sur son réseau.
 *
 * Les comptes encore « admin » au moment du déploiement (non réaffectés au
 * préalable par le superadmin) deviennent QHSE de leur établissement : ils
 * gardent l'accès sans gagner de droits. Un compte sans établissement ne peut
 * pas fonctionner en QHSE : il est désactivé. Chaque conversion est tracée
 * dans activites_log (type « role ») ; `php artisan users:anciens-admins` les
 * liste pour une réaffectation au cas par cas.
 */
return new class extends Migration
{
    public function up(): void
    {
        $admins = DB::table('users')
            ->leftJoin('etablissements', 'etablissements.id', '=', 'users.etablissement_id')
            ->where('users.role', 'admin')
            ->select('users.id', 'users.prenom', 'users.nom', 'users.email', 'users.actif',
                     'users.etablissement_id', 'etablissements.nom as etablissement_nom')
            ->get();

        foreach ($admins as $u) {
            $desactive = ! $u->etablissement_id;

            DB::table('users')->where('id', $u->id)->update([
                'role'           => 'qhse',
                'actif'          => $desactive ? 0 : $u->actif,
                'remember_token' => null,
                'updated_at'     => now(),
            ]);

            DB::table('activites_log')->insert([
                'type'             => 'role',
                'action'           => 'updated',
                'subject_id'       => $u->id,
                'subject_type'     => 'App\\Models\\User',
                'moment'           => now(),
                'acteur'           => 'Système',
                'etablissement'    => $u->etablissement_nom,
                'description'      => "Profil admin supprimé : {$u->prenom} {$u->nom} converti en QHSE"
                                      . ($desactive ? ' et désactivé (aucun établissement)' : '')
                                      . ' — à réaffecter si besoin.',
                'niveau'           => 'warning',
                'user_id'          => null,
                'etablissement_id' => $u->etablissement_id,
                'metadata'         => json_encode([
                    'email'        => $u->email,
                    'ancien_role'  => 'admin',
                    'nouveau_role' => 'qhse',
                    'desactive'    => $desactive,
                ]),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        DB::statement("ALTER TABLE users MODIFY role
            ENUM('superadmin','admin_reseau','qhse','agent','collecteur','prestataire','client_signataire')
            NOT NULL DEFAULT 'agent'");
    }

    public function down(): void
    {
        // Rétablit la valeur dans l'ENUM. Les comptes convertis ne sont pas
        // repassés en « admin » automatiquement : voir activites_log (type « role »).
        DB::statement("ALTER TABLE users MODIFY role
            ENUM('superadmin','admin','admin_reseau','qhse','agent','collecteur','prestataire','client_signataire')
            NOT NULL DEFAULT 'agent'");
    }
};
