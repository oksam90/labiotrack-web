<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Liste les comptes convertis lors de la suppression du profil « admin »
 * (migration 2026_10_02_100001), avec leur rôle et leur statut ACTUELS, pour
 * que le superadmin les réaffecte au cas par cas (écran Utilisateurs).
 */
class ListAnciensAdmins extends Command
{
    protected $signature = 'users:anciens-admins';

    protected $description = 'Liste les anciens comptes « admin » convertis, à réaffecter au cas par cas';

    public function handle(): int
    {
        $lignes = DB::table('activites_log')
            ->leftJoin('users', 'users.id', '=', 'activites_log.subject_id')
            ->where('activites_log.type', 'role')
            ->where('activites_log.subject_type', 'App\\Models\\User')
            ->orderBy('activites_log.subject_id')
            ->get(['activites_log.subject_id', 'activites_log.etablissement', 'activites_log.metadata',
                   'activites_log.moment', 'users.email', 'users.role', 'users.actif'])
            ->filter(fn ($l) => (json_decode($l->metadata, true)['ancien_role'] ?? null) === 'admin');

        if ($lignes->isEmpty()) {
            $this->info('Aucun ancien compte « admin » converti.');
            return self::SUCCESS;
        }

        $this->warn($lignes->count() . ' ancien(s) compte(s) « admin » converti(s) :');
        $this->table(
            ['id', 'email', 'établissement', 'rôle actuel', 'actif', 'converti le'],
            $lignes->map(fn ($l) => [
                $l->subject_id,
                $l->email ?? json_decode($l->metadata, true)['email'] ?? '—',
                $l->etablissement ?? '—',
                $l->role ?? '(supprimé)',
                $l->actif ? 'oui' : 'non',
                $l->moment,
            ])->all()
        );
        $this->line('Réaffectation : Administration › Utilisateurs (superadmin), un compte à la fois.');

        return self::SUCCESS;
    }
}
