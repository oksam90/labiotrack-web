<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Backfill etablissement_id sur la table destructions
     * à partir de la relation collecte → etablissement.
     */
    public function up(): void
    {
        $affected = DB::update('
            UPDATE destructions d
            JOIN collectes c ON c.id = d.collecte_id
            SET d.etablissement_id = c.etablissement_id
            WHERE d.etablissement_id IS NULL
              AND c.etablissement_id IS NOT NULL
        ');

        Log::info("[Backfill] {$affected} destructions mises à jour avec etablissement_id.");
    }

    /**
     * Rollback : remettre à NULL les valeurs backfillées.
     */
    public function down(): void
    {
        DB::update('
            UPDATE destructions
            SET etablissement_id = NULL
        ');
    }
};
