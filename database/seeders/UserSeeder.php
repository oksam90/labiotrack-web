<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $pw = Hash::make('password'); // Mot de passe par défaut : password

        $etabs = DB::table('etablissements')->orderBy('id')->get()->keyBy('slug');

        // ── SUPERADMIN GLOBAL (pas d'établissement) ────────
        DB::table('users')->insertOrIgnore([
            'etablissement_id' => null,
            'nom'   => 'Admin', 'prenom' => 'Système',
            'email' => 'admin@biomed.sn', 'password' => $pw,
            'role'  => 'superadmin', 'actif' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── ADMIN RÉSEAU (voit toutes structures) ──────────
        DB::table('users')->insertOrIgnore([
            'etablissement_id' => null,
            'nom'   => 'Réseau', 'prenom' => 'Administrateur',
            'email' => 'reseau@biomed.sn', 'password' => $pw,
            'role'  => 'admin', 'actif' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── COLLECTEUR PARTAGÉ (entre structures) ──────────
        DB::table('users')->insertOrIgnore([
            'etablissement_id' => null,
            'nom'   => 'SEAS', 'prenom' => 'Collecteur',
            'email' => 'collecteur@seas.sn', 'password' => $pw,
            'role'  => 'collecteur', 'actif' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── PRESTATAIRE DESTRUCTION ────────────────────────
        DB::table('users')->insertOrIgnore([
            'etablissement_id' => null,
            'nom'   => 'UTE', 'prenom' => 'Prestataire',
            'email' => 'prestataire@ute.sn', 'password' => $pw,
            'role'  => 'prestataire', 'actif' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── UTILISATEURS PAR STRUCTURE ─────────────────────
        $comptes = [
            'hpd' => [
                ['nom'=>'Diallo','prenom'=>'Aminata','email'=>'qhse@hpd.sn','role'=>'qhse'],
                ['nom'=>'Ndiaye','prenom'=>'Ibrahima','email'=>'agent1@hpd.sn','role'=>'agent'],
                ['nom'=>'Fall','prenom'=>'Mariama','email'=>'agent2@hpd.sn','role'=>'agent'],
                ['nom'=>'Sow','prenom'=>'Oumar','email'=>'admin@hpd.sn','role'=>'admin'],
            ],
            'pasteur' => [
                ['nom'=>'Sarr','prenom'=>'Fatoumata','email'=>'qhse@pasteur.sn','role'=>'qhse'],
                ['nom'=>'Ba','prenom'=>'Cheikh','email'=>'agent@pasteur.sn','role'=>'agent'],
                ['nom'=>'Mbaye','prenom'=>'Rokhaya','email'=>'admin@pasteur.sn','role'=>'admin'],
            ],
            'thiaroye' => [
                ['nom'=>'Diagne','prenom'=>'Moussa','email'=>'qhse@thiaroye.sn','role'=>'qhse'],
                ['nom'=>'Cissé','prenom'=>'Adama','email'=>'agent@thiaroye.sn','role'=>'agent'],
            ],
            'bioanalyse' => [
                ['nom'=>'Sall','prenom'=>'Fatou','email'=>'qhse@bioanalyse.sn','role'=>'qhse'],
                ['nom'=>'Diouf','prenom'=>'Pape','email'=>'agent@bioanalyse.sn','role'=>'agent'],
            ],
        ];

        foreach ($comptes as $slug => $users) {
            $etab = $etabs[$slug] ?? null;
            if (! $etab) continue;

            foreach ($users as $u) {
                DB::table('users')->insertOrIgnore(array_merge($u, [
                    'etablissement_id' => $etab->id,
                    'password'         => $pw,
                    'actif'            => 1,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]));
            }
        }
    }
}
