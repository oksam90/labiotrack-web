<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillActivitesLog extends Command
{
    protected $signature = 'activites:backfill {--fresh : Vider la table avant le backfill}';

    protected $description = 'Remplit activites_log avec l\'historique existant via la vue activites_feed';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            DB::table('activites_log')->truncate();
            $this->warn('Table activites_log vidée.');
        }

        $this->info('Backfill en cours depuis la vue activites_feed...');

        $inserted = DB::statement("
            INSERT INTO activites_log
                (type, action, subject_id, subject_type, moment, acteur,
                 etablissement, description, niveau, user_id, etablissement_id,
                 metadata, created_at, updated_at)
            SELECT
                af.type,
                'created' AS action,
                af.id AS subject_id,
                CASE af.type
                    WHEN 'declaration' THEN 'App\\\\Models\\\\Declaration'
                    WHEN 'collecte'    THEN 'App\\\\Models\\\\Collecte'
                    WHEN 'checklist'   THEN 'App\\\\Models\\\\Checklist'
                    WHEN 'alerte'      THEN 'App\\\\Models\\\\Alerte'
                    WHEN 'destruction' THEN 'App\\\\Models\\\\Destruction'
                END AS subject_type,
                af.moment,
                af.acteur,
                af.etablissement,
                af.description,
                af.niveau,
                af.user_id,
                af.etablissement_id,
                NULL AS metadata,
                af.moment AS created_at,
                af.moment AS updated_at
            FROM activites_feed af
        ");

        $count = DB::table('activites_log')->count();
        $this->info("Backfill terminé : {$count} entrées dans activites_log.");

        return self::SUCCESS;
    }
}
